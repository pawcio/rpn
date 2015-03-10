<?php

require_once 'Tokenizer.php';

class Expression {

    private static $version = '1.0.1';
    private static $versionDate = '25.01.2010';
    private static $functions = array
        (
        'abs', 'sgn',
        'sin', 'cos', 'tan', 'tg', 'ctan', 'ctg', 'sec', 'cosec', 'csc',
        'asin', 'arcsin', 'acos', 'arccos', 'atan', 'arctg', 'actan', 'arcctg',
        'sqrt',
        'ln', 'log',
        'exp'
    );
    private static $operators = array('^', '*', '/', '%', '+', '-');
    
    private static $tokenizer = null;

    public static function version() {
        return self::$version;
    }

    public static function versionDate() {
        return self::$versionDate;
    }
    
    private static function getTokenizer() {
        if (self::$tokenizer === null) {
            self::$tokenizer = new Tokenizer();
        }
        
        return self::$tokenizer;
    }

    public static function tokenize($expression) {
        $tokenizer = self::getTokenizer();
        return $tokenizer->tokenize($expression);
    }

    public static function to_onp($input, $has_x = TRUE) {
        $tokens = self::tokenize($input);
        $output = array();
        $errors = array('ERROR');
        $stack = array();
        $operorder = array('^' => 3, '*' => 2, '/' => 2, '%' => 2, '+' => 1,
            '-' => 1);

        $operassoc = array('^' => 'r', '*' => 'b', '/' => 'l', '+' => 'b',
            '-' => 'l');

        while (count($tokens) > 0) {
            $token = array_shift($tokens);

            if ($token == 'x') {
                if ($has_x === TRUE) {
                    array_push($output, 'x');
                } else {
                    throw new Exception("Nieznany operator: {$token}");
                }
            } else if ($token == 'pi') {
                array_push($output, M_PI);
            } else if ($token == 'e') {
                array_push($output, M_E);
            } else if ($token == 'euler') {
                array_push($output, M_EULER);
            } else if (is_numeric($token)) {
                array_push($output, $token);
            } else if (in_array($token, self::$functions)) {
                array_push($stack, $token);
            } else if ($token == ',') {
                while ((count($stack) > 0) && (end($stack) != '(')) {
                    array_push($output, array_pop($stack));
                }
            } else if (in_array($token, self::$operators)) {
                $isRightAssoc = $operassoc[$token] == 'r';
                $tokenOrder = $operorder[$token];

                while ((count($stack) > 0) &&
                (
                (!$isRightAssoc && ($tokenOrder <= $operorder[end($stack)])) || ($isRightAssoc && ($tokenOrder < $operorder[end($stack)]))
                )) {
                    array_push($output, array_pop($stack));
                }

                // push o1 onto the stack.
                array_push($stack, $token);
            } else if ($token == '(') {
                array_push($stack, '(');
            } else if ($token == ')') {
                if (!in_array('(', $stack)) {
                    throw new Exception("brak nawiasu otwierającego");
                }

                // Until the token at the top of the stack is a left parenthesis,
                // pop operators off the stack onto the output queue.
                while ((end($stack) != '(') && in_array('(', $stack)) {
                    array_push($output, array_pop($stack));
                }

                // Pop the left parenthesis from the stack, but not onto the output queue.
                array_pop($stack);

                // If the token at the top of the stack is a function token, pop it and onto the output queue.
                if (in_array(end($stack), self::$functions)) {
                    array_push($output, array_pop($stack));
                }
            } else if (!empty($token)) {
                throw new Exception("Nieznany operator: {$token}");
            }
        }

        while (count($stack) > 0) {
            $operator = array_pop($stack);

            if (($operator == '(') || ($operator == ')')) {
                throw new Exception("brak nawiasu zamykającego");
            } else {
                array_push($output, $operator);
            }
        }

        return $output;
    }

    public static function evaluate_onp($input, $x = FALSE) {
        $stack = array();
        $output = array();

        foreach ($input as $token) {
            if ($token == 'x') {
                if ($x === FALSE) {
                    throw new Exception("Nieznany operator: x");
                } else {
                    array_push($stack, $x);
                }
            } else if (is_numeric($token)) {
                array_push($stack, $token);
            } else if (in_array($token, self::$operators)) {
                $a = array_pop($stack);
                $b = array_pop($stack);

                switch ($token) {
                    case '^':
                        array_push($stack, pow($b, $a));
                        break;
                    case '*':
                        array_push($stack, $b * $a);
                        break;
                    case '/':
                        array_push($stack, (($a == 0) || is_nan($a)) ? NAN : $b / $a);
                        break;
                    case '%':
                        array_push($stack, (($a == 0) || is_nan($a)) ? NAN : $b % $a);
                        break;
                    case '+':
                        array_push($stack, $b + $a);
                        break;
                    case '-':
                        array_push($stack, $b - $a);
                        break;
                }
            } else if (in_array($token, self::$functions)) {
                $a = array_pop($stack); // pierwszy argument funkcji

                if (is_nan($a)) {
                    array_push($stack, NAN);
                } else {
                    switch ($token) {
                        case 'abs':
                            array_push($stack, abs($a));
                            break;

                        case 'sgn': {
                                if ($a > 0) {
                                    array_push($stack, 1);
                                } elseif ($a < 0) {
                                    array_push($stack, -1);
                                } else {
                                    array_push($stack, 0);
                                }
                                break;
                            }

                        case 'sin';
                            array_push($stack, sin($a));
                            break;

                        case 'cos':
                            array_push($stack, cos($a));
                            break;

                        case 'tan':
                        case 'tg':
                            array_push($stack, tan($a));
                            break;

                        case 'ctan':
                        case 'ctg':
                            array_push($stack, tan(M_PI / 2 - $a));
                            break;

                        case 'asin':
                        case 'arcsin':
                            array_push($stack, asin($a));
                            break;

                        case 'acos':
                        case 'arccos':
                            array_push($stack, acos($a));
                            break;

                        case 'atan':
                        case 'arctg':
                            array_push($stack, atan($a));
                            break;

                        case 'actan':
                        case 'arcctg':
                            array_push($stack, -atan($a) + M_PI / 2);
                            break;

                        case 'sqrt':
                            array_push($stack, sqrt($a));
                            break;

                        case 'ln':
                            array_push($stack, log($a));
                            break;

                        case 'log':
                            array_push($stack, log10($a));
                            break;

                        case 'exp':
                            array_push($stack, exp($a));
                            break;

                        case 'sec': {
                                $z = cos($a);

                                if ($z == 0) {
                                    array_push($stack, NAN);
                                } else {
                                    array_push($stack, 1 / $z);
                                }

                                break;
                            }

                        case 'cosec':
                        case 'csc':
                            $z = sin($a);

                            if ($z == 0) {
                                array_push($stack, NAN);
                            } else {
                                array_push($stack, 1 / $z);
                            }
                            break;

                        default:
                            array_push($a);
                    }
                }
            }
        }

        return array_pop($stack);
    }

    public static function evaluate($expression) {
        return self::evaluate_onp(self::to_onp($expression));
    }

}
