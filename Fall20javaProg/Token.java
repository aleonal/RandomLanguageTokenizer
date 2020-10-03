package Fall20javaProg;

public class Token {
    TokenType type;
    String value;

    Token(TokenType theType){
        type = theType;
        value = "";
    }
    Token (TokenType theType, String theValue){
        type = theType;
        value = theValue;
    }
}
