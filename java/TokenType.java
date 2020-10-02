package Fall20javaProg;

public enum TokenType {
      LBRACKET, //{
      RBRACKET, //}
      LSQUAREBRACKET, //[
      RSQUAREBRACKET, //]    
      EQUAL, //=
      COLON, //:
      OUTPUT, //'output'
      SWITCH, //'switch'
      CASE, //'case'
      BREAK, //'break'
      DEFAULT, //'default'
      ID, //[a-zA-Z]+
      INT, //[0-9]+
      STRING, // same as Java string
      EOF, // end of file
      OTHER // anything else
}
