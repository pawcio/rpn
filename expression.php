<?php

require_once 'PostfixConverter.php';
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
    
    public static function version() {
        return self::$version;
    }

    public static function versionDate() {
        return self::$versionDate;
    }

    public static function tokenize($expression) {
        $tokenizer = new Tokenizer();
        return $tokenizer->tokenize($expression);
    }
    
    public static function evaluate_onp($input, $x = FALSE) {
        $stack = array();

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
        $tokenizer = new Tokenizer();
        $converter = new PostfixConverter();
        
        $tokens = $tokenizer->tokenize($expression);
        $postfix = $converter->convert($tokens);
        $result = self::evaluate_onp($postfix);
        return $result;
    }

}
