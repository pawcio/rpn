<?php

namespace kalkulator;

use rpn\Expression;

$loader = require_once '../vendor/autoload.php';

$version = '1.1';
$versionDate = '28.10.2009';

try {

    if (isset($_POST['expression'])) {
        $expression = $_POST['expression'];
    } else {
        $expression = '';
    }

    $result = Expression::evaluate($expression);

    if (isset($_POST['precision'])) {
        $precision = $_POST['precision'];
    } else {
        $precision = 2;
    }

    if (is_numeric($precision)) {
        $precision = (int) $precision;
        $result = round($result, $precision);
    }
    $valid = TRUE;
} catch (Exception $e) {
    $rpn = $e->getMessage();
    $valid = FALSE;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="pl">

    <head>
        <title>Kalkulator <?php echo $version ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="Authoring_tool" content="Notepad++">

        <style type="text/css">
            body{
                background-color: #000;
                color: #DDD;
                font: normal 80%/150% Verdana, Geneva, Arial, Helvetica, sans-serif;
                margin: 10px;
            }

            a {
                color: #08F;
            }

            a:hover {
                color: #F80;
            }

            fieldset, input{
                border: 1px solid #420;
                clear: both;
                margin: 0.1em 0;
            }

            #expression, #result, #rpn {
                display: block;
                width: 100%;
                background-color: #111;
                color: #EEE;
            }
            #result {

            }

            label {
                cursor: pointer;
                display: block;
            }

            .wrong {
                color: red;
            }

            #ads {
                float: right;
                margin: 1em;
            }
        </style>

    </head>

    <body onload="document.getElementById('expression').focus()">

        <h1>Kalkulator</h1>
        <p id="version">wersja: <?php echo "{$version} ({$versionDate})"; ?>, silnik: <?php echo Expression::version(); ?> (<?php echo Expression::versionDate(); ?>)</p>
        <form action="" method="post">
            <fieldset>
                <legend><label for="expression">Wprowadź wyrażenie:</label></legend>
                <input type="text" name="expression" id="expression" value="<?php echo $expression ?>">
                <select name="precision" title="Dokładność">
                    <?php $selected = ($precision == 'na') ? ' selected="selected" ' : ''; ?>
                    <option value="na" <?php echo $selected ?>>Nie zaokrąglaj</option>
                    <optgroup label="Zaokrąglaj do...">
                        <?php
                        for ($a = 0; $a <= 14; ++$a) {
                            $selected = ($a === $precision) ? ' selected="selected" ' : '';
                            echo "<option value=\"{$a}\" {$selected}>{$a} miejsc po przecinku</option>";
                        }
                        ?>
                    </optgroup>
                </select>
                <input type="submit" value="=">
                <?php if ($valid): ?>
                    <label for="result">Wynik</label>
                    <input type="text" name="result" id="result" value="<?php echo $result ?>" readonly="readonly">
                <?php else: ?>
                    <label for="rpn" class="wrong">Błąd:</label>
                    <input type="text" name="rpn" id="rpn" value="<?php echo $rpn ?>"  readonly="readonly">
                <?php endif; ?>
            </fieldset>
        </form>

        <p>W liczbach rzeczywistych zastępujemy przecinek kropką. Nie są dozwolone skrócone formy zapisu, np 2(2 + 2). W takim przypadku należy wpisać 2 * (2 + 2)</p>

        <?php require_once '../expression-info-polish.php' ?>

        <p>Fajne? Być może spodoba Ci się również <a href="http://kwako.pl/soft/generator-wykresow/start">Generator wykresów</a>.</p>

        <p>Autorem projektu jest <a href="http://kwako.pl">kwako</a>. Oprogramowanie opensource. <a href="https://code.google.com/p/math-expression-engine/">Odwiedź repozytorium projektu!</a></p>

    </body>

</html>
