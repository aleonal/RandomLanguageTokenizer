package Fall20javaProg;

public class Tokenizer {

    private final char[] e; //char array containing input file characters
    private int i; //index of the current character
    char currentChar; //the actual current character

    public Tokenizer(String s) {
        // constructor
        e = s.toCharArray();
        i = 0;
    }

    public Token nextToken() {
        // skip blanklike characters
        while (i < e.length && " \n\t\r".indexOf(e[i]) >= 0) {
            i++;
        }
        if (i >= e.length) {
            return new Token(TokenType.EOF,"");
        }
        // check for INT
        String inputString = "";
        while (i < e.length && "0123456789".indexOf(e[i]) >= 0) {
            inputString += e[i++];
        }
        if (!"".equals(inputString)) {
            return new Token(TokenType.INT, inputString);
        }
        // check for ID or reserved word        
        while (i < e.length && "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_".indexOf(e[i]) >= 0) {
            inputString += e[i++];
        }
        if (!"".equals(inputString)) {
            if ("output".equals(inputString)) {
                return new Token(TokenType.OUTPUT);
            }
            if ("switch".equals(inputString)) {
                return new Token(TokenType.SWITCH);
            }
            if ("case".equals(inputString)) {
                return new Token(TokenType.CASE);
            } 
            if ("break".equals(inputString)) {
                return new Token(TokenType.BREAK);
            }  
            if ("default".equals(inputString)) {
                return new Token(TokenType.DEFAULT);
            }              
            return new Token(TokenType.ID, inputString);
        }        
        // We're left with strings or one character tokens
        switch (e[i++]) {
            case '{':
                return new Token(TokenType.LBRACKET,"{");              
            case '}':
                return new Token(TokenType.RBRACKET,"}");                
            case '[':
                return new Token(TokenType.LSQUAREBRACKET,"[");
            case ']':
                return new Token(TokenType.RSQUAREBRACKET,"]");
            case '=':
                return new Token(TokenType.EQUAL,"=");
            case ':':
                return new Token(TokenType.COLON,":");
            case '"':
                String value="";
                while (i<e.length && e[i]!='"'){
                    char c=e[i++];
                    if (i>=e.length)
                        return new Token(TokenType.OTHER);
                    // check for escaped double quote
                    if (c=='\\' && e[i]=='"'){
                        c='"';
                        i++;
                    }
                    value+=c;
                } 
                i++;
                return new Token(TokenType.STRING, value);
            default:
                // OTHER should result in exception
                return new Token(TokenType.OTHER);
        }
    }
}