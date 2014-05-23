<?php

function validate($input, $min, $max, $default)
{
	if (is_numeric($input))
	{
		if ($input < $min)
		{
			return $min;
		}
		else if ($input > $max)
		{
			return $max;
		}
		else
		{
			return $input;
		}
	}
	else {
		return $default;
	}
}

?>
