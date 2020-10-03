package Fall20javaProg;

import java.io.PrintWriter;

public class EvalSectionException extends Exception{
    
    public EvalSectionException(String m) {
        System.out.println(Fall20JavaProg.EOL+"Exception: "+m+Fall20JavaProg.EOL);
    }   
}
