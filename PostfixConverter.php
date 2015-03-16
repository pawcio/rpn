<?php

require_once 'Stack.php';

/**
 * Description of PostfixConverter
 *
 * @author Paweł Kaczmarz <pawel@kaczmarz.pl>
 */
class PostfixConverter {

    private $functions = array(
        'abs', 'sgn',
        'sin', 'cos', 'tan', 'tg', 'ctan', 'ctg', 'sec', 'cosec', 'csc',
        'asin', 'arcsin', 'acos', 'arccos', 'atan', 'arctg', 'actan', 'arcctg',
        'sqrt',
        'ln', 'log',
        'exp'
    );
    private $operators = array('^', '*', '/', '%', '+', '-');
    private $operorder = array('^' => 3, '*' => 2, '/' => 2, '%' => 2, '+' => 1, '-' => 1);
    private $operassoc = array('^' => 'r', '*' => 'b', '/' => 'l', '+' => 'b', '-' => 'l');
    private $hasX;

    /**
     *
     * @var Stack 
     */
    private $output;

    /**
     *
     * @var Stack 
     */
    private $stack;

    public function convert(Traversable $input, $hasX = TRUE) {

        $this->hasX = $hasX;
        $this->output = new Stack();
        $this->stack = new Stack();

        $this->parseInputTokens($input);
        $this->flushStack();

        return $this->output;
    }

    private function parseInputTokens($input) {
        foreach ($input as $token) {
            $this->parseInputToken($token);
        }
    }

    private function parseInputToken($token) {
        if ($token == 'x' && $this->hasX) {
            $this->output->push('x');
            return;
        }

        if ($token == 'pi') {
            $this->output->push(M_PI);
            return;
        }

        if ($token == 'e') {
            $this->output->push(M_E);
            return;
        }

        if ($token == 'euler') {
            $this->output->push(M_EULER);
            return;
        }

        if (is_numeric($token) || in_array($token, $this->functions)) {
            $this->output->push($token);
            return;
        }

        if ($token == ',') {
            $this->flushFunctionArguments();
            return;
        }

        if (in_array($token, $this->operators)) {
            $this->parseOperator($token);
            return;
        }

        if ($token == '(') {
            $this->stack->push('(');
            return;
        }

        if ($token == ')') {
            $this->parseParenthesis();
            return;
        }

        throw new Exception("Nieznany operator: {$token}");
    }

    private function flushStack() {
        while ($this->stack->size() > 0) {
            $operator = $this->stack->pop();
            $this->errorOnParenthesis($operator);
            $this->output->push($operator);
        }
    }

    private function errorOnParenthesis($token) {
        if (($token == '(') || ($token == ')')) {
            throw new Exception("brak nawiasu zamykającego");
        }
    }

    private function flushFunctionArguments() {
        while (($this->stack->size() > 0) && ($this->stack->peek() != '(')) {
            $this->output->push($this->stack->pop());
        }
    }

    private function parseOperator($token) {
        while ($this->parseTopStackOperator($token)) {
            $this->output->push($this->stack->pop());
        }
        
        $this->stack->push($token);
    }
    
    private function parseTopStackOperator($inputToken) {
        
        if ($this->stack->size() === 0) {
            return false;
        }
        
        if (!in_array($this->stack->peek(), $this->operators)) {
            return false;
        }
        
        $inputIsRightAssociative = $this->operassoc[$inputToken] == 'r';
        $inputOrder = $this->operorder[$inputToken];
        $stackOperator = $this->stack->peek();
        $stackOperatorOrder = $this->operorder[$stackOperator];
        
        if (( ! $inputIsRightAssociative && $inputOrder <= $stackOperatorOrder)
                || ($inputIsRightAssociative && $inputOrder < $stackOperatorOrder)) {
            return true;
        }
    }

    private function parseParenthesis() {

        if (!$this->stack->has('(')) {
            throw new Exception("brak nawiasu otwierającego");
        }

        // Until the token at the top of the stack is a left parenthesis,
        // pop operators off the stack onto the output queue.
        while ($this->stack->peek() != '(') {
            $this->output->push($this->stack->pop());
        }

        // Pop the left parenthesis from the stack, but not onto the output queue.
        $this->stack->pop();

        // If the token at the top of the stack is a function token, pop it and onto the output queue.
        if (in_array($this->stack->peek(), $this->functions)) {
            $this->output->push($this->stack->pop());
        }
    }

}
