<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Accounts\Developer;
use \Brush\Pastes\Trending;
use \Brush\Exceptions\BrushException;

$developer = new Developer('<developer key>');

try {
	// retrieve an array of the top 18 currently trending pastes
	$pastes = Trending::getPastes($developer);

	// print out the titles and hit counts of each one
	foreach ($pastes as $paste) {
		printf("%-70s%d\n", $paste->getTitle(), $paste->getHits());
	}
}
catch (BrushException $e) {
	echo $e->getMessage(), PHP_EOL;
}
