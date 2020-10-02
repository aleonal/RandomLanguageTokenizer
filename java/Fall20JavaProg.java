package Fall20javaProg;

import java.net.URL;
import java.io.*;
import java.util.HashMap;

/**
 * @author Luc Longpre for Secure Web-Based Systems, Fall 2020
 *
 * This program parses and interprets a simple programming output, assignments
 * and switch statements. No loop.
 *
 * <input> ::= <section>
 * <section> ::= '[' <statement>* ']'
 * <statement> ::=  <assignment> | <switch> | <output>
 * <assignment> ::= ID '=' (INT | ID)
 * <output> ::= 'output' (INT | ID | STRING)
 * <switch> ::= 'switch' ID '{' <case>* [ 'default' ':' <statement>* ] '}'
 * <case> ::= 'case' INT ':' <statement>* 'break' 
 * ID ::= [a-zA-Z_]+ 
 * INT ::= [0-9]+ 
 * STRING starts and ends with double quotes ("), " can by escaped: (\")
 */
public class Fall20JavaProg {

    static Token currentToken;
    static Tokenizer t;
    static HashMap<String, Integer> map;
    static String oneIndent = "   ";
    static String result; // string containing the result of execution
    static String EOL = System.lineSeparator(); // new line, depends on OS

    public static void main(String[] args) throws Exception {
        // open the URL into a buffered reader,
        // print the header,
        // parse each section, printing a formatted version
        //     followed by the result of the execution
        // print the footer.
        String inputSource;
        inputSource = "http://localhost/4339_f20_assignment1/fall20Testing.txt";
        //inputSource = "http://cs5339.cs.utep.edu/longpre/fall20Testing.txt";

        URL inputUrl = new URL(inputSource);
        BufferedReader in = new BufferedReader(new InputStreamReader(inputUrl.openStream()));
        String header = "<html>" + EOL
                + "  <head>" + EOL
                + "    <title>CS 4339/5339 PHP assignment</title>" + EOL
                + "  </head>" + EOL
                + "  <body>" + EOL
                + "    <pre>";
        String footer = "    </pre>" + EOL
                + "  </body>" + EOL
                + "</html>";
        String inputLine;
        String inputFile = "";
        while ((inputLine = in.readLine()) != null) {
            inputFile += inputLine + EOL;
        }
        t = new Tokenizer(inputFile);
        System.out.println(header);
        currentToken = t.nextToken();
        int section = 0;

        // Loop through all sections, for each section printing result
        // If a section causes exception, catch and jump to next section
        while (currentToken.type != TokenType.EOF) {
            System.out.println("section " + ++section);
            try {
                evalSection();
                System.out.println("Section result:");
                System.out.println(result);
            } catch (EvalSectionException ex) {
                // skip to the end of section
                while (currentToken.type != TokenType.RSQUAREBRACKET
                        && currentToken.type != TokenType.EOF) {
                    currentToken = t.nextToken();
                }
                currentToken = t.nextToken();
            }
        }
        System.out.println(footer);
    }

    static void evalSection() throws EvalSectionException {
        // <section> ::= [ <statement>* ]
        map = new HashMap<>();
        result = "";
        if (currentToken.type != TokenType.LSQUAREBRACKET) {
            throw new EvalSectionException("A section must start with \"[\"");
        }
        System.out.println("[");
        currentToken = t.nextToken();
        while (currentToken.type != TokenType.RSQUAREBRACKET
                && currentToken.type != TokenType.EOF) {
            evalStatement(oneIndent, true);
        }
        System.out.println("]");
        currentToken = t.nextToken();
    }

    static void evalStatement(String indent, boolean exec) throws EvalSectionException {
        // exec is true if we are executing the statements in addition to parsing
        // <statement> ::=  <assignment> | <switch> | <output>
        switch (currentToken.type) {
            case ID:
                evalAssignment(indent, exec);
                break;
            case SWITCH:
                evalSwitch(indent, exec);
                break;
            case OUTPUT:
                evalOutput(indent, exec);
                break;
            default:
                throw new EvalSectionException("invalid statement");
        }
    }

    static void evalAssignment(String indent, boolean exec) throws EvalSectionException {
        // <assignment> ::= ID '=' (INT | ID)
        // we know currentToken is ID 
        String key = currentToken.value;
        System.out.print(indent + key);
        currentToken = t.nextToken();
        if (currentToken.type != TokenType.EQUAL) {
            throw new EvalSectionException("equal sign expected");
        }
        System.out.print("=");
        currentToken = t.nextToken();
        if (currentToken.type == TokenType.INT) {
            int value = Integer.parseInt(currentToken.value);
            System.out.println(value);
            currentToken = t.nextToken();
            if (exec) {
                map.put(key, value);
            }
        } else if (currentToken.type == TokenType.ID) {
            String key2 = currentToken.value;
            System.out.println(key2);
            currentToken = t.nextToken();
            if (exec) {
                Integer value = map.get(key2);
                if (value == null) {
                    throw new EvalSectionException("undefined variable");
                }
                map.put(key, value);
            }
        } else {
            throw new EvalSectionException("ID or Integer expected");
        }
    }

    static void evalOutput(String indent, boolean exec) throws EvalSectionException {
        // <output> ::= 'output' (INT | ID | STRING)
        // we know currentToken is 'output'
        System.out.print(indent + "output ");
        currentToken = t.nextToken();
        // <value>  ::= INT | ID | STRING
        switch (currentToken.type) {
            case STRING:
                if (exec) {
                    result += currentToken.value + EOL;
                }
                // To print exactly the input, we need to re-escape the quotes in the string
                System.out.println("\"" + currentToken.value.replace("\"", "\\\"") + "\"");
                currentToken = t.nextToken();
                break;
            case INT:
                if (exec) {
                    result += currentToken.value + EOL;
                }
                System.out.println(currentToken.value);
                currentToken = t.nextToken();
                break;
            case ID:
                String key = currentToken.value;
                System.out.println(key);
                if (exec) {
                    Integer value = null; // value associated with ID      
                    value = map.get(key);
                    if (value == null) {
                        throw new EvalSectionException("undefined variable");
                    }
                    result += value + EOL;
                }
                currentToken = t.nextToken();
                break;
            default:
                throw new EvalSectionException("expected a string, integer, or Id");
        }
    }

    static void evalSwitch(String indent, boolean exec) throws EvalSectionException {
        // <switch> ::= 'switch' ID '{' <case>* [ 'default' ':' <statement>* ] '}'
        // We know currentToken is "switch"
        Integer value = null; // value of the switch ID
        System.out.print(indent + "switch ");
        currentToken = t.nextToken();
        if (currentToken.type != TokenType.ID) {
            throw new EvalSectionException("ID expected");
        }
        String key = currentToken.value;
        System.out.print(key);
        if (exec) {
            value = map.get(key);
            if (value == null) {
                throw new EvalSectionException("undefined variable");
            }
        }
        currentToken = t.nextToken();
        if (currentToken.type != TokenType.LBRACKET) {
            throw new EvalSectionException("Left bracket exptected");
        }
        System.out.println(" {");
        currentToken = t.nextToken();
        while (currentToken.type == TokenType.CASE) {
            currentToken = t.nextToken();
            System.out.print(indent + oneIndent + "case ");
            exec = evalCase(indent + oneIndent + oneIndent, exec, value);
        }
        if (currentToken.type == TokenType.DEFAULT) {
            System.out.print(indent + oneIndent + "default");
            currentToken = t.nextToken();
            if (currentToken.type != TokenType.COLON) {
                throw new EvalSectionException("colon expected");
            }
            System.out.println(":");
            currentToken = t.nextToken();
            while (currentToken.type != TokenType.RBRACKET) {
                evalStatement(indent + oneIndent + oneIndent, exec);
            }
        }
        if (currentToken.type == TokenType.RBRACKET) {
            System.out.println(indent + "}");
            currentToken = t.nextToken();
        } else {
            throw new EvalSectionException("right bracket expected");
        }
    }

    static boolean evalCase(String indent, boolean exec, Integer target) throws EvalSectionException {
        // <case> ::= 'case' 'INT' ':' <statement>* 'break'
        if (currentToken.type != TokenType.INT) {
            throw new EvalSectionException("integer expected");
        }
        int value = Integer.parseInt(currentToken.value);
        System.out.print(value);
        currentToken = t.nextToken();
        if (currentToken.type != TokenType.COLON) {
            throw new EvalSectionException("colon expected");
        }
        System.out.println(':');
        currentToken = t.nextToken();
        while (currentToken.type != TokenType.BREAK) {
            evalStatement(indent, exec && value == target);
        }
        System.out.println(indent + "break");
        currentToken = t.nextToken();
        return exec && !(value == target); //only one case is executed
    }
}
