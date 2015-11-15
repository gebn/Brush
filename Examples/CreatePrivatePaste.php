<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Accounts\Developer;
use \Brush\Accounts\Account;
use \Brush\Accounts\Credentials;
use \Brush\Pastes\Draft;
use \Brush\Pastes\Options\Visibility;
use \Brush\Exceptions\BrushException;

// this time, create a draft directly from a file
$draft = Draft::fromFile('passwords.txt');

// an Account object represents a Pastebin user account
$account = new Account(new Credentials('<username>', '<password>'));

// link the draft to the account
$draft->setOwner($account);

// specify that we don't want this paste to be publicly accessible
$draft->setVisibility(Visibility::VISIBILITY_PRIVATE);

// the Developer class manages a developer key
$developer = new Developer('<developer key>');

try {
	// submit the draft and retrieve the final paste in the same way as above
	$paste = $draft->paste($developer);

	// print out the key of the newly created paste
	echo $paste->getKey(), PHP_EOL;
}
catch (BrushException $e) {
	echo $e->getMessage(), PHP_EOL;
}
