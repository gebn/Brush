# Brush [![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](LICENSE) [![Stars](https://img.shields.io/github/stars/gebn/brush.svg?style=flat)](https://github.com/gebn/Brush/stargazers) [![Forks](https://img.shields.io/github/forks/gebn/brush.svg?style=flat)](https://github.com/gebn/Brush/network/members) [![Issues](https://img.shields.io/github/issues/gebn/brush.svg?style=flat)](https://github.com/gebn/Brush/issues)

Brush is a complete object-oriented PHP wrapper for the Pastebin API.

## Features

 - Create pastes directly from files, with automatic language detection.
 - Easily apply default account settings to new pastes.
 - High performance with aggressive caching.
 - End-to-end UTF-8 support.

## Dependencies

 - PHP 5.3.0+
 - cURL

## Getting Started

### Create an anonymous paste

Below is a minimal example showing how to submit a new paste:

``` php
require 'Brush/Brush.php';

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
	echo $paste->getUrl(); // e.g. http://pastebin.com/JYvbS0fC
}
catch (BrushException $e) {
	// some sort of error occurred; check the message for the cause
	echo $e->getMessage();
}
```

There are several things to note:

 - You only ever need to require `Brush.php`; Brush has an autoloader, which will take care of other includes for you.
 - The `Draft` class represents a paste not yet submitted to Pastebin. It has setters allowing you to configure every possible option for your new paste, including expiry, format and visibility.
 - The `Developer` class represents a developer account. An instance needs to be passed in all situations where Brush could interact with the Pastebin API.
 - When `paste()` is called on a draft, Brush checks for basic errors before attempting to send the draft to Pastebin. If an error is detected (e.g. no content set), a `ValidationException` will be thrown.
 - All exceptions thrown by Brush extend `BrushException`. This allows you to easily handle every single possible error in a single `catch` clause, or use multiple clauses for more fine-grained handling.
 - Once a draft is `paste()`d, Brush automatically creates and return a `Paste` object without any further interaction with the Pastebin API. This object contains all information about the paste, including its key, URL and expiry date.
 - A `Draft`'s `paste()` method can be safely called multiple times, changing the draft between invocations if required.
 - For a complete method reference, see [METHODS.md](METHODS.md).

### Create a private paste

Private pastes must have an account associated with them, but Brush makes this easy to set up:

``` php
require 'Brush/Brush.php';

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
	echo $paste->getKey();
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

The `Account` class represents a Pastebin account. At the lowest level, it manages a user session key, which has to be provided when doing operations affecting a particular account. An instance can be created in two ways:

 1. Via a set of credentials, as above. Brush will make an HTTP request to Pastebin to retrieve a new user key when one is first needed, and will cache it for the rest of execution.
 2. Directly by passing a session key string as the only argument to `Account`'s constructor. This saves a request, and is the recommended way if you always want to work with the same account.

In the above example, instead of manually writing a draft, we asked Brush to automatically create one from a local file. Brush will set the draft title to the name of the file, the content as the file content, and attempt to recognise the format from the file's extension. The mappings it uses to do this are in `Configuration/extensions.ini`. This is designed to be edited by you, so feel free to add lines according to your requirements. If you add a large number of maps, please consider contributing them in a pull request so that others may benefit!

You can also create a draft paste inheriting an account's default settings using the `fromOwner(Account, Developer)` method. This will retrieve the defaults for the supplied account, apply them to a new draft, and set the account as the owner.

### Retrieve an account's pastes

Retrieving pastes belonging to an account is easy:

``` php
require 'Brush/Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;
use \Brush\Exceptions\BrushException;

$account = new Account('<user session key>');
$developer = new Developer('<developer key>');

try {
	// retrieve the first 50 (see below) account pastes
	$pastes = $account->getPastes($developer);

	// print out the name of each paste followed by a line feed
	foreach ($pastes as $paste) {
		echo $paste->getTitle(), "\n";
	}
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

`Account`'s `getPastes()` method returns an array of `Paste` objects, representing pastes submitted by that account. It takes an optional second argument, the maximum number of pastes to retrieve, which defaults to 50.

#### Delete a paste

Pastes retrieved in the above way can be removed by calling `delete()` on them:

``` php
require 'Brush/Brush.php';

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
		echo 'Deleted ', $paste->getKey(), "\n";
	}
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

N.B. For reasons of authentication, only pastes retrieved from an account can be deleted. If you attempt to delete a paste obtained via other means (e.g. a trending paste), Brush will detect this and throw a `ValidationException`, as Pastebin would simply reject the request. Brush will always try to warn you of errors before bothering Pastebin.

### Retrieve trending pastes

``` php
require 'Brush/Brush.php';

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
	echo $e->getMessage();
}
```

## Contributing

Suggestions and pull requests are welcome. Please submit these through the normal GitHub channels.

If you discover a bug, please open a [new issue](https://github.com/gebn/Brush/issues/new).

## Licence

Brush is released under the MIT Licence - see the LICENSE file for details. For more information about how this allows you to use the library, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).
