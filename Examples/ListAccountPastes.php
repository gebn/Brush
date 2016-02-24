<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;
use \Brush\Exceptions\BrushException;

$account = new Account('<user session key>');
$developer = new Developer('<developer key>');

try {
	// retrieve the first 50 account pastes
	$pastes = $account->getPastes($developer, 50);

	// print out the name of each paste followed by a line feed
	foreach ($pastes as $paste) {
		echo $paste->getTitle(), PHP_EOL;
	}
}
catch (BrushException $e) {
	echo $e->getMessage(), PHP_EOL;
}
