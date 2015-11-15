<?php

require dirname(__FILE__) . '/../Brush.php';

use \Brush\Pastes\Draft;
use \Brush\Accounts\Developer;
use \Brush\Exceptions\BrushException;

$draft = new Draft(); // drafts represent unsent pastes
$draft->setContent('Some random content'); // set the paste content

// the Developer class encapsulates a developer API key; an instance
// needs to be provided whenever Brush might interact with Pastebin
$developer = new Developer('<developer key>');

try {
	// send the draft to Pastebin; turn it into a full blown Paste object
	$paste = $draft->paste($developer);

	// print out the URL of the new paste
	echo $paste->getUrl(), PHP_EOL; // e.g. http://pastebin.com/JYvbS0fC
}
catch (BrushException $e) {
	// some sort of error occurred; check the message for the cause
	echo $e->getMessage(), PHP_EOL;
}
