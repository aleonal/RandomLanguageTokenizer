<?php

class EvalSectionException extends Exception {

    public function __construct($message) {
        parent::__construct($message);
    }

    public function __toString() {
        return "Exception: " ."{$this->message}";
    }
}
?>