<?php

namespace rpn;

use Exception;

/**
 * Description of PostfixEvaluator
 *
 * @author Paweł Kaczmarz <pawel@kaczmarz.pl>
 */
class PostfixEvaluator {

    // FIXME: all of these details should be kept inside token
    // FIXME: the only difference between function and operator is number of params...
    private $functions;
    private $operators;
    private $x;

    /**
     *
     * @var Stack 
     */
    private $stack;

    // FIXME: naive aproach -> possible fix: create tokens with evaluate(params) function
    public function __construct() {
        $this->operators = array(
            '^' => function($a, $b) {
                return pow($b, $a);
            },
            '*' => function($a, $b) {
                return $a * $b;
            },
            '/' => function($a, $b) {
                return ($a === 0) ? NAN : ($b / $a);
            },
            '%' => function($a, $b) {
                return ($a === 0) ? NAN : ($b % $a);
            },
            '+' => function($a, $b) {
                return $a + $b;
            },
            '-' => function($a, $b) {
                return $a - $b;
            },
        );

        $ctan = function($a) {
            return tan(M_PI / 2 - $a);
        };

        $actan = function($a) {
            return -atan($a) + M_PI / 2;
        };

        $cosec = function($a) {
            $z = sin($a);
            if ($z == 0) {
                return NAN;
            }
            return 1 / $z;
        };

        $this->functions = array(
            'abs' => 'abs',
            'sgn' => function($a) {
                if ($a > 0) {
                    return 1;
                }
                if ($a < 0) {
                    return -1;
                }
                return 0;
            },
            'sin' => 'sin',
            'cos' => 'cos',
            'tan' => 'tan',
            'tg' => 'tan',
            'ctan' => $ctan,
            'ctg' => $ctan,
            'asin' => 'asin',
            'arcsin' => 'asin',
            'acos' => 'acos',
            'arcccos' => 'acos',
            'atan' => 'atan',
            'arctg' => 'atan',
            'actan' => $actan,
            'arcctg' => $actan,
            'sqrt' => 'sqrt',
            'ln' => 'log',
            'log' => 'log10',
            'exp' => 'exp',
            'sec' => function($a) {
                $z = cos($a);
                if ($z === 0) {
                    return NAN;
                }
                return 1 / $z;
            },
            'cosec' => $cosec,
            'csc' => $cosec,
        );
    }

    // FIXME
    public function evaluate($input, $x = FALSE) {

        $this->stack = new Stack();
        $this->x = $x;

        foreach ($input as $token) {
            $result = $this->parseToken($token);
            $this->stack->push($result);
        }

        return $this->stack->pop();
    }

    private function parseToken($token) {
        if ($token == 'x') {
            return $this->parseX();
        }

        if (is_numeric($token)) {
            return $token;
        }

        if (isset($this->operators[$token])) {
            return $this->parseOperator($token);
        }

        if (in_array($token, $this->functions)) {
            return $this->parseFunction($token);
        }

        // FIXME: unknown token
        return $token;
    }

    private function parseX() {
        if ($this->x === FALSE) {
            throw new Exception("Nieznany operator: x");
        }

        return $this->x;
    }

    private function parseOperator($token) {
        $a = $this->stack->pop();
        $b = $this->stack->pop();

        if (is_nan($a) || is_nan($b)) {
            return NAN;
        }

        $operator = $this->operators[$token];
        return $operator($a, $b);
    }

    private function parseFunction($token) {

        $a = $this->stack->pop();

        if (is_nan($a)) {
            return NAN;
        }

        $function = $this->functions[$token];
        return $function($a);
    }

}
