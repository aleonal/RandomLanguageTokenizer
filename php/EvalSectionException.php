<?php

class EvalSectionException extends Exception {

    public function __construct($message) {
        parent::__construct($message);
    }

    public function __toString() {
        return PHP_EOL . __CLASS__ . ": " . "\"{$this->message}\"" . ", in line {$this->getLine()}." . PHP_EOL;
    }
}
?>