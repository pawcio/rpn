<?php

/**
 * Description of Tokenizer
 *
 * @author pawka_000
 */
class Tokenizer {

    private $operators_braces = array('^', '*', '/', '%', '+', '-', '(', ')');

    public function tokenize($expression) {

        $input = str_replace(array("\s", "\n", "\r", "\t", ' ', '_', '"', "'", '\\'), '', $expression);
        $input = str_replace("&#8722;", '-', $input);
        $input = strtolower($input);
        $input = str_split($input);
        
        $output = array();

        foreach ($input as $char) {
            $output = $this->parseCharacter($char, $output);
        }

        return $output;
    }

    private function parseCharacter($char, $output) {

        if ($this->isNumber($char)) {
            return $this->parseNumber($char, $output);
        }

        if ($this->isOperator($char)) {
            return $this->addToken($output, $char);
        }

        if (is_string($char)) {
            return $this->parseString($char, $output);
        }
        
        // ignore other characters
    }

    private function parseNumber($char, $output) {
        if ($this->isNumber(end($output))) {
            $output[count($output) - 1] .= $char;
            return $output;
        }

        return $this->addToken($output, $char);
    }

    private function parseString($char, $output) {
        if (is_string(end($output)) && ! $this->isOperator(end($output))) {
            $output[count($output) - 1] .= $char;
            return $output;
        }
        
        return $this->addToken($output, $char);
    }
    
    private function isNumber($char) {
        return is_numeric($char) || $char == '.';
    }

    private function isOperator($char) {
        return in_array($char, $this->operators_braces);
    }
    
    private function addToken($output, $char) {
        array_push($output, $char);
        return $output;
    }
}
