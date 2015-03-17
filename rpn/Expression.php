<?php

namespace rpn;

use rpn\Tokenizer;

class Expression {

    private static $version = '1.0.2';
    private static $versionDate = '17.03.2015';

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

    public static function evaluate($expression, $x = FALSE) {
        $tokenizer = new Tokenizer();
        $converter = new PostfixConverter();
        $evaluator = new PostfixEvaluator();

        $tokens = $tokenizer->tokenize($expression);
        $postfix = $converter->convert($tokens, $x);
        $result = $evaluator->evaluate($postfix, $x);
        return $result;
    }

}
