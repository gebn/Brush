<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;
use \Brush\Exceptions\BrushException;

$account = new Account('<user session key>');
$developer = new Developer('<developer key>');

try {
	// retrieve up to 10 account pastes
	$pastes = $account->getPastes($developer, 10);

	// delete each one
	foreach ($pastes as $paste) {
		$paste->delete($developer);
		echo 'Deleted ', $paste->getKey(), PHP_EOL;
	}
}
catch (BrushException $e) {
	echo $e->getMessage(), PHP_EOL;
}
