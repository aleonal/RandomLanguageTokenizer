<?php
require_once "TokenType.php";

// Usage: $token = Token::create()->setType(TokenType::LBRACKET)->setValue('[');
class Token {

    protected $type = TokenType::OTHER;
    protected $value = '';

    public function __construct() {
    }

    public static function create() {
        $instance = new self();
        return $instance;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function getValue() {
        return $this->value;
    }
}
?>