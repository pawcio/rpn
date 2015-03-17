<?php

namespace rpn;

use ArrayIterator;
use IteratorAggregate;

/**
 * Description of TokenStack
 *
 * @author Paweł Kaczmarz <pawel@kaczmarz.pl>
 */
class Stack implements IteratorAggregate {

    private $stack;

    public function __construct() {
        $this->stack = array();
    }

    public function push($token) {
        $this->stack[] = $token;
    }

    public function amend($token) {
        $this->stack[count($this->stack) - 1] .= $token;
    }

    public function peek() {
        $size = $this->size();

        if ($size > 0) {
            return $this->stack[$size - 1];
        }

        return null;
    }

    public function size() {
        return count($this->stack);
    }

    public function pop() {
        return array_pop($this->stack);
    }

    public function has($token) {
        return in_array($token, $this->stack);
    }

    public function __toString() {
        return implode(' ', $this->stack);
    }

    public function getIterator() {
        return new ArrayIterator($this->stack);
    }

}
