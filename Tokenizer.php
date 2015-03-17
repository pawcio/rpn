<?php

require_once 'Stack.php';

/**
 * Description of Tokenizer
 *
 * @author pawka_000
 */
class Tokenizer {

    private $operators_braces = array('^', '*', '/', '%', '+', '-', '(', ')');

    /**
     *
     * @var Stack 
     */
    private $tokens;

    public function tokenize($expression) {

        $this->tokens = new Stack();
        
        $input = str_replace(array("\s", "\n", "\r", "\t", ' ', '_', '"', "'", '\\'), '', $expression);
        $input = str_replace("&#8722;", '-', $input);
        $input = trim(strtolower($input));
        
        if (strlen($input) == 0) {
            return $this->tokens;
        }
        
        $input = str_split($input);

        foreach ($input as $char) {
            $this->parseCharacter($char);
        }

        return $this->tokens;
    }

    private function parseCharacter($char) {

        if ($this->isNumber($char)) {
            $this->parseNumber($char);
            return;
        }

        if ($this->isOperator($char)) {
            $this->addToken($char);
            return;
        }

        if (is_string($char)) {
            $this->parseString($char);
            return;
        }

        // ignore other characters
    }

    private function parseNumber($char) {
        if ($this->isNumber($this->tokens->peek())) {
            $this->tokens->amend($char);
            return;
        }

        $this->addToken($char);
    }

    private function parseString($char) {

        $peek = $this->tokens->peek();

        if (is_string($peek) && !$this->isOperator($peek)) {
            $this->tokens->amend($char);
            return;
        }

        $this->addToken($char);
    }

    private function isNumber($char) {
        return is_numeric($char) || $char == '.';
    }

    private function isOperator($char) {
        return in_array($char, $this->operators_braces);
    }

    private function addToken($char) {
        $this->tokens->push($char);
    }

}