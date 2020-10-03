<?php
require_once "Token.php";

class Tokenizer {
    private $e; // char array containing input file characters
    private $i; // index of the current character
    private $currentChar; // the actual current character, but it is not used? Raised question on BB

    public function __construct($s) {
        $this->e = $s;
        $this->i = 0;
    }

    public function nextToken() {
        
        // skip blanklike characters
        do {
             $blankCount = ctype_space($this->e[$this->i]);
            if ($blankCount === FALSE)
                $blankCount = -1;
            else
                $this->i += 1;

        } while($this->i < strlen($this->e) && $blankCount >= 0);

        // if at the end of string, return end of file token
        if ($this->i >= strlen($this->e)) {
            return Token::create()->setType(TokenType::EOF)->setValue('');
        }

        // check for INT
        $inputString = '';
        do {
            $intCount = strpos("0123456789", $this->e[$this->i]);
            if ($intCount === FALSE)
                $intCount = -1;
            else
                $inputString = $inputString . $this->e[$this->i++];
        } while($this->i < strlen($this->e) && $intCount >=0);

        // If found INT, return token containing INT
        if ($inputString != '') {
            return Token::create()->setType(TokenType::INT)->setValue($inputString);
        }

        // check for ID or reserved word
        do {
            $swordCount = strpos("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_", $this->e[$this->i]);
            if ($swordCount === FALSE)
                $swordCount = -1;
            else
                $inputString = $inputString . $this->e[$this->i++];
        } while($this->i < strlen($this->e) && $swordCount >=0);

        // if found ID or reserved word, return token containing ID or reserved word
        if ($inputString != '') {
            if ($inputString == 'output') {
                return Token::create()->setType(TokenType::OUTPUT);
            }
            if ($inputString == 'switch') {
                return Token::create()->setType(TokenType::SWITCH);
            }
            if ($inputString == 'case') {
                return Token::create()->setType(TokenType::CASE);
            }
            if ($inputString == 'break') {
                return Token::create()->setType(TokenType::BREAK);
            }
            if ($inputString == 'default') {
                return Token::create()->setType(TokenType::DEFAULT);
            }
            return Token::create()->setType(TokenType::ID)->setValue($inputString);
        }

        // we're left with strings or one-character tokens
        switch ($this->e[$this->i++]) {
            case '{':
                return Token::create()->setType(TokenType::LBRACKET)->setValue('{');
            case '}':
                return Token::create()->setType(TokenType::RBRACKET)->setValue('}');
            case '[':
                return Token::create()->setType(TokenType::LSQUAREBRACKET)->setValue('[');
            case ']':
                return Token::create()->setType(TokenType::RSQUAREBRACKET)->setValue(']');
            case '=':
                return Token::create()->setType(TokenType::EQUAL)->setValue('=');
            case ':':
                return Token::create()->setType(TokenType::COLON)->setValue(':');
            case '"':
                $value = "";

                while($this->i < strlen($this->e) && $this->e[$this->i] != '"') {
                    $char = $this->e[$this->i++];
                    
                    if ($this->i >= strlen($this->e)) {
                        return Token::create()->setType(TokenType::OTHER);
                    }

                    // check for escaped double quote
                    if ($char == '\\' && $this->e[$this->i] == '"') {
                        $char = '"';
                        $this->i += 1;
                    }

                    $value = $value . $char;
                }
                
                $this->i += 1;
                return Token::create()->setType(TokenType::STRING)->setValue($value);
            default:
                // OTHER should result in exception
                return Token::create()->setType(TokenType::OTHER);
        }
    }
}
?>