<?php
	/*
	
	1.6.3
	+ kompresja obrazu
	
	1.6.2
	+ opcja ukrywania osi
	+ opcja ukrywania siatki
	
	*/

	#error_reporting(E_ALL);
	require_once 'settings.php';
	require_once ENGINE_URL;
	require_once 'input.php';
	
	$version = '1.6.3';
    $versionDate = '03.02.2011';
	$defaultExpression =  'cos ( x * euler ) + sqrt ( abs ( pi - x ) )';

	if (isset($_POST['expression']) && ( ! empty($_POST['expression'])))
	{
		$dane = $_POST['expression'];
	}
	else
	{
		$file = fopen('last.txt', 'a+');
		$dane = (filesize('last.txt') > 0)
			? fgets($file, filesize('last.txt'))
			: $defaultExpression;
		fclose($file);
	}
	
	try
	{
		$ex = Expression::to_onp($dane);
		$expression = implode(' ', Expression::tokenize($dane));
		$rpn = implode(' ', $ex);
		$valid = TRUE;

		$file = fopen('last.txt', 'w');
		fwrite($file, $expression.' ');
		fclose($file);
		
		$expression_url = rawurlencode(str_replace(' ', '', $expression));
		
	}
	catch (Exception $e)
	{
		$valid = FALSE;
		$expression = $dane;
		$rpn = $e->getMessage();
	}

	$width = validate($_POST['w'], WIDTH_MIN, WIDTH_MAX, WIDTH_DEFAULT);
	$height = validate($_POST['h'], HEIGHT_MIN, HEIGHT_MAX, HEIGHT_DEFAULT);
	$scale = validate($_POST['s'], SCALE_MIN, SCALE_MAX, SCALE_DEFAULT);
	
	$nogrid = (isset($_POST['nogrid']) ? $_POST['nogrid'] : 0);
	$noaxis = (isset($_POST['noaxis']) ? $_POST['noaxis'] : 0);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="pl">

<head>
<title>Generator Wykresow <?php echo $version ?></title>
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

#expression {
	clear: none;
	width: 95%;
}

#console {
	display: block;
}

img{
	border: 2px solid #420;
}

.info, .warning {
	font-weight: bold;
	border-style: solid;
	border-width: medium;
	background-color: white;
	padding: 20px;
	text-align: center;
}

.info {
	color: Green;
	border-color: Green;
}

.warning {
	color: Red;
	border-color: Red;
}

#ads {
	float: right;
	margin: 1em;
}
</style>

</head>
<body onload="document.getElementById('expression').focus()">

<h1>Generator wykresów</h1>
<p id="version">wersja: <?php echo "{$version} ({$versionDate})"; ?>, silnik: <?php echo Expression::version(); ?> (<?php echo Expression::versionDate(); ?>)</p>
<form method="post" action="./">
<fieldset>
<legend>Ustawienia</legend>

<label for="expression" id="ex">f(x) = </label>
<input type="text" name="expression" id="expression" value="<?php echo $expression; ?>">

<label for="w">szerokość:</label>
<input type="text" name="w" id="w" value="<?php echo $width; ?>">

<label for="h">wysokość:</label>
<input type="text" name="h" id="h" value="<?php echo $height; ?>">

<label for="s">skala (px&nbsp;/&nbsp;jednostka):</label>
<input type="text" name="s" id="s" value="<?php echo $scale; ?>">

<input type="checkbox" name="nogrid" id="nog" value="1" <?php if ($nogrid == 1): ?> checked="checked" <?php endif;?> >
<label for="nog">ukryj siatkę</label>

<input type="checkbox" name="noaxis" id="noax" value="1" <?php if ($noaxis == 1): ?> checked="checked" <?php endif;?> >
<label for="noax">ukryj osie</label>

<input type="submit" value="Narysuj">

</fieldset>
</form>

<?php if ($valid): ?>
 
	<p style="text-align: center">
		<?php echo "<img src=\"chart.php?r={$expression_url}&amp;w={$width}&amp;h={$height}&amp;s={$scale}&amp;nogrid={$nogrid}&amp;noaxis={$noaxis}\" alt=\"wykres funkcji y = {$expression}\" width=\"{$width}\" height=\"{$height}\">"; ?>
	</p>
<?php else: ?>

	<p class="warning"><?php echo $rpn; ?></p>
	
<?php endif ?>

<p>Wykres przedstawia zależność f(x).</p>

<p>W polach długość i wysokość podajemy w pikselach długość i wysokość generowanego obrazu z wykresem. Liczba w polu skala odpowiada ilości pikseli na jednostkę.</p>

<p>W liczbach rzeczywistych zastępujemy przecinek kropką. Nie są dozwolone skrócone formy zapisu, np 2x. W takim przypadku należy wpisać <b>2*x</b>.</p>

<?php require_once DESCRIPTION_URL ?>

<p>Stworzyłem też <a href="http://kwako.pl/soft/kalkulator/start">kalkulator</a> o takich samych możliwościach obliczeniowych.</p>

<p>Autorem projektu jest <a href="http://kwako.pl">kwako</a>. Oprogramowanie opensource. <a href="https://code.google.com/p/math-expression-engine/">Odwiedź repozytorium projektu!</a></p>

<p style="text-align: right">
	<a href="http://validator.w3.org/check?uri=referer">
		<img
			style="border:0;width:88px;height:31px"
			src="http://www.w3.org/Icons/valid-html401"
			alt="Valid HTML 4.01 Strict"
			height="31"
			width="88"
		>
	</a>

	<a href="http://jigsaw.w3.org/css-validator/check/referer">
		<img
			style="border:0;width:88px;height:31px"
			src="http://jigsaw.w3.org/css-validator/images/vcss"
			alt="Poprawny CSS!"
			height="31"
			width="88"
		>
    </a>
</p>

</body>
</html>
