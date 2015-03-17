<?php

ob_start();

require_once 'settings.php';

require_once ENGINE_URL;
require_once 'input.php';

$debug_mode = ($_GET['debug'] == 1);
$show_grid = ($_GET['nogrid'] != 1);
$show_axis = ($_GET['noaxis'] != 1);

if ($debug_mode) {
    error_reporting(E_ALL);
} else {
    header("Content-type: image/png");
}

$rownanie = rawurldecode($_GET['r']);
$rownanieONP = Expression::to_onp($rownanie);
#print_r($rownanieONP);

$width = validate($_GET['w'], WIDTH_MIN, WIDTH_MAX, WIDTH_DEFAULT);
$height = validate($_GET['h'], HEIGHT_MIN, HEIGHT_MAX, HEIGHT_DEFAULT);
$scale = validate($_GET['s'], SCALE_MIN, SCALE_MAX, SCALE_DEFAULT);

$hcenter = $width / 2;
$vcenter = $height / 2;

$image = imagecreate($width, $height);

$colors = array(
    'background' => imagecolorallocate($image, 0, 0, 0),
    'grid' => imagecolorallocate($image, 32, 32, 32),
    'axis' => imagecolorallocate($image, 127, 127, 127),
    'chart' => imagecolorallocate($image, 0, 136, 255),
    'text' => imagecolorallocate($image, 127, 127, 127)
);

imagefill($image, $colors['background'], 0, 0);



// SIATKA
if ($show_grid) {
    // pionowe
    for ($x = $hcenter % $scale; $x < $width; $x += $scale) {
        imageline($image, $x, 0, $x, $height, $colors['grid']);
    }

    //poziome
    for ($y = $vcenter % $scale; $y < $height; $y += $scale) {
        imageline($image, 0, $y, $width, $y, $colors['grid']);
    }
}


// tworzy uklad wspolrzednych
if ($show_axis) {
    imageline($image, 0, $vcenter, $width, $vcenter, $colors['axis']);
    imageline($image, $hcenter, 0, $hcenter, $height, $colors['axis']);

    // wyznacza podzielnik, w celu "rozgeszczenia" numerkow przy osiach
    $skip = ($scale < 15) ? 5 : 1;

    //rysuje podzialke na osi x
    for ($x = $hcenter % $scale, $value = ($x - $hcenter) / $scale; $x < $width; $x += $scale, ++$value) {
        imageline($image, $x, $vcenter - 2, $x, $vcenter + 2, $colors['axis']);
        if ($value % $skip == 0) {
            imagestring($image, 1, $x + 4, $vcenter + 4, $value, $colors['text']);
        }
    }

    //rysuje podzialke na osi y
    for ($y = $vcenter % $scale, $value = ($y - $vcenter) / $scale; $y < $height; $y += $scale, ++$value) {
        imageline($image, $hcenter - 2, $y, $hcenter + 2, $y, $colors['axis']);

        if ($value % $skip == 0 && $value != 0) {
            imagestring($image, 1, $hcenter + 4, $y - 4, -$value, $colors['text']);
        }
    }
}

// rysujemy wykres


if ($debug_mode) {
    echo('<table>');
    echo('<tr><td>argument</td><td>wartosc</td><td>x</td><td>y</td><tr>');
}

for ($x = 0, $last_y = FALSE; $x < $width; ++$x) {

    $arg = ($x - $hcenter) / $scale;

    $value = Expression::evaluate_onp($rownanieONP, $arg);

    if ($debug_mode) {
        echo("<tr><td>{$arg}</td><td>{$value}</td><td>{$x}</td>");
    }

    if ($value != NAN && is_finite($value)) {
        $y = -($value * $scale) + $vcenter;

        if ($last_y) {
            imageline($image, $x - 1, $last_y, $x, $y, $colors['chart']);

            if ($debug_mode)
                echo("<td>{$y}</td>");
        }
        $last_y = (($y > -100) and ( $y < $height + 100)) ? $y : FALSE;
    } else if ($debug_mode) {
        echo('<td></td>');
    }
    if ($debug_mode)
        echo('</tr>');
}

if ($debug_mode) {
    echo('</table>');
} else {
    imagepng($image, NULL, 9);
}

ob_end_flush();
?>