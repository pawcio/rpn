<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
        array_push($this->stack, $token);
    }

    public function amend($token) {
        $this->stack[count($this->stack) - 1] .= $token;
    }

    public function peek() {
        $size = $this->size();

        if ($size > 0) {
            return $this->stack[count($this->stack) - 1];
        }

        return null;
    }

    public function size() {
        return count($this->stack);
    }

    public function pop() {
        return array_shift($this->stack);
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
