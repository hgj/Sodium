<?php

if (isset($number)) {
	$errorNumber = $number;
} else if (!empty($_GET['number'])) {
	$errorNumber = $_GET['number'];
} else {
	$errorNumber = 500;
}

echo "$errorNumber\n";
