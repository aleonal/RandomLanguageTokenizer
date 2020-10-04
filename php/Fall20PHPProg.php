<?php
require_once "Tokenizer.php";
require_once "EvalSectionException.php";

class Fall20PHPProg {

    public static $currentToken;     // Token
    public static $t;                // Tokenizer
    public static $map;              // <String, Integer> map
    public static $oneIndent = "   "; // One indent, tab
    public static $result;           // string containing the result of execution
    public static $EOL = PHP_EOL;    // new line, compatible with PHP version 5.0.2+

    public static function main() {
        // open the URL into a buffered reader,
        // print the header,
        // parse each section, printing a formatted version
        //     followed by the result of the execution
        // print the footer.

        $inputSource = 'http://localhost/secure1/fall20Testing.txt';
        // $inputSource = 'http://cs5339.cs.utep.edu/longpre/fall20Testing.txt'; // debugging

        $header = "<html>" . self::$EOL .
            "  <head>" . self::$EOL .
            "    <title>CS 4339/5339 PHP assignment</title>" . self::$EOL .
            "  </head>" . self::$EOL .
            "  <body>" . self::$EOL .
            "    <pre>";

        $footer = "    </pre>" . self::$EOL .
            "  </body>" . self::$EOL .
            "</html>";

        $in = fopen($inputSource, 'r') or die("File does not exist.");
        $inputLine;
        $inputFile = "";

        // from php.net
        while (($inputLine = fgets($in)) !== FALSE) {
            $inputFile = $inputFile . $inputLine;
        }
        
        if (!feof($in)) {
            die("Error reading file.");
        } else {
            fclose($in);
        }

        self::$t = new Tokenizer($inputFile);
        self::$currentToken = self::$t->nextToken();
        $section = 0;
        echo $header . self::$EOL;

        // loop through all sections, for each section printing result
        // if a section causes exception, catch and jump to next section
        while (self::$currentToken->getType() !== TokenType::EOF) {
            echo "section " . ++$section . self::$EOL;

            try {
                self::evalSection();
                echo "Section result:" . self::$EOL;
                echo self::$result . self::$EOL;

            } catch (EvalSectionException $ex) {
                // skip to the end of the section 
                echo $ex . self::$EOL;

                while (self::$currentToken->getType() !== TokenType::RSQUAREBRACKET && self::$currentToken->getType() !== TokenType::EOF) {
                    self::$currentToken = self::$t->nextToken();
                }
                self::$currentToken = self::$t->nextToken();
            }
        }
        echo $footer . self::$EOL;
    }

    public static function evalSection() {
        // <section> ::= [ <statement>* ]
        self::$map = array();
        self::$result = '';

        if (self::$currentToken->getType() !== TokenType::LSQUAREBRACKET) {
            throw new EvalSectionException("A section must start with \"[\"");
        }
        echo '[' . self::$EOL;
        self::$currentToken = self::$t->nextToken();

        while (self::$currentToken->getType() !== TokenType::RSQUAREBRACKET && self::$currentToken->getType() !== TokenType::EOF) {
            self::evalStatement(self::$oneIndent, true);
        }
        echo ']' . self::$EOL;
        self::$currentToken = self::$t->nextToken();
    }

    public static function evalStatement($indent, $exec) {
        // exec is true if we are executing the statements in addition to parsing
        // <statement> ::=  <assignment> | <switch> | <output>
        switch (self::$currentToken->getType()) {
            case TokenType::ID:
                self::evalAssignment($indent, $exec);
                break;
            case TokenType::SWITCH:
                self::evalSwitch($indent, $exec);
                break;
            case TokenType::OUTPUT:
                self::evalOutput($indent, $exec);
                break;
            default:
                throw new EvalSectionException("invalid statement");
        }
    }

    public static function evalAssignment($indent, $exec) {
        // <assignment> ::= ID '=' (INT | ID)
        // we know currentToken is ID
        $key = self::$currentToken->getValue();
        echo $indent . $key;
        self::$currentToken = self::$t->nextToken();

        if (self::$currentToken->getType() !== TokenType::EQUAL) {
            throw new EvalSectionException("equal sign expected");
        }
        echo '=';
        self::$currentToken = self::$t->nextToken();

        if (self::$currentToken->getType() === TokenType::INT) {
            $value = intval(self::$currentToken->getValue());
            echo $value . self::$EOL;
            self::$currentToken = self::$t->nextToken();

            if ($exec) {
                self::$map[$key] = $value;
            }
        } else if (self::$currentToken->getType() === TokenType::ID) {
            $key2 = self::$currentToken->getValue();
            echo $key2 . self::$EOL;
            self::$currentToken = self::$t->nextToken();

            if ($exec) {
                $value = self::$map[$key2];
                
                if ($value === NULL) {
                    throw new EvalSectionException("undefined variable");
                }
                self::$map[$key] = $value;
            }
        } else {
            throw new EvalSectionException("ID or Integer expected");
        }
    }

    public static function evalOutput($indent, $exec) {
        // <output> ::= 'output' (INT | ID | STRING)
        // we know currentToken is 'output'
        echo $indent . "output ";
        self::$currentToken = self::$t->nextToken();

        // <value>  ::= INT | ID | STRING
        switch(self::$currentToken->getType()) {
            case TokenType::STRING:
                if ($exec) {
                    self::$result = self::$result . self::$currentToken->getValue() . self::$EOL;
                }

                // To print exactly the input, we need to re-escape the quotes in the string
                print("\"" . str_replace("\"", "\\\"", self::$currentToken->getValue()) . "\"" . self::$EOL);
                self::$currentToken = self::$t->nextToken();
                break;
            case TokenType::INT:
                if ($exec) {
                    self::$result = self::$result . self::$currentToken->getValue() . self::$EOL;
                }
                echo self::$currentToken->getValue() . self::$EOL;
                self::$currentToken = self::$t->nextToken();
                break;
            case TokenType::ID:
                $key = self::$currentToken->getValue();
                echo $key . self::$EOL;

                if ($exec) {
                    $value = NULL; // value associated with ID
                    $value = self::$map[$key];

                    if ($value === NULL) {
                        throw new EvalSectionException("undefined variable");
                    }
                    self::$result = self::$result . $value . self::$EOL;
                }
                self::$currentToken = self::$t->nextToken();
                break;
            default:
                throw new EvalSectionException("expected a string, integer, or Id");
        }
    }

    public static function evalSwitch($indent, $exec) {
        // <switch> ::= 'switch' ID '{' <case>* [ 'default' ':' <statement>* ] '}'
        // We know currentToken is "switch"
        $value = NULL;
        echo $indent . "switch ";
        self::$currentToken = self::$t->nextToken();

        if (self::$currentToken->getType() !== TokenType::ID) {
            throw new EvalSectionException("ID expected");
        }
        $key = self::$currentToken->getValue();
        echo $key;

        if ($exec) {
            $value = self::$map[$key];

            if ($value === NULL) {
                throw new EvalSectionException("undefined variable");
            }
        }
        self::$currentToken = self::$t->nextToken();

        if (self::$currentToken->getType() !== TokenType::LBRACKET) {
            throw new EvalSectionException("Left bracket expected");
        }
        echo ' {' . self::$EOL;
        self::$currentToken = self::$t->nextToken();

        while (self::$currentToken->getType() === TokenType::CASE) {
            self::$currentToken = self::$t->nextToken();
            echo $indent . self::$oneIndent . "case ";
            $exec = self::evalCase($indent . self::$oneIndent . self::$oneIndent, $exec, $value);
        }

        if (self::$currentToken->getType() === TokenType::DEFAULT) {
            echo $indent . self::$oneIndent . "default";
            self::$currentToken = self::$t->nextToken();

            if (self::$currentToken->getType() !== TokenType::COLON) {
                throw new EvalSectionException("colon expected");
            }
            echo ':' . self::$EOL;
            self::$currentToken = self::$t->nextToken();

            while (self::$currentToken->getType() !== TokenType::RBRACKET) {
                self::evalStatement($indent . self::$oneIndent . self::$oneIndent, $exec);
            }
        }

        if (self::$currentToken->getType() == TokenType::RBRACKET) {
            echo $indent . '}' . self::$EOL;
            self::$currentToken = self::$t->nextToken();
        } else {
            throw new EvalSectionException("right bracket expected");
        }
    }

    public static function evalCase($indent, $exec, $target) {
        // <case> ::= 'case' 'INT' ':' <statement>* 'break'
        if (self::$currentToken->getType() !== TokenType::INT) {
            throw new EvalSectionException("integer expected");
        }
        $value = intval(self::$currentToken->getValue());
        echo $value;
        self::$currentToken = self::$t->nextToken();

        if (self::$currentToken->getType() !== TokenType::COLON) {
            throw new EvalSectionException("colon expected");
        }
        echo ':' . self::$EOL;
        self::$currentToken = self::$t->nextToken();

        while (self::$currentToken->getType() !== TokenType::BREAK) {
            self::evalStatement($indent, $exec && $value == $target);
        }
        echo $indent . "break" . self::$EOL;
        self::$currentToken = self::$t->nextToken();

        return $exec && !($value == $target); // only one case is executed
    }
}

Fall20PHPProg::main();
?>