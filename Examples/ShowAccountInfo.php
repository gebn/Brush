<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;
use \Brush\Accounts\User;
use \Brush\Exceptions\BrushException;

$account = new Account('<user session key>');
$developer = new Developer('<developer key>');

try {
	echo User::fromAccount($account, $developer), PHP_EOL;
}
catch (BrushException $e) {
	echo $e->getMessage(), PHP_EOL;
}
