# Brush

Brush is a complete object-oriented PHP wrapper for the Pastebin API.

## Features

 - Create pastes directly from files, with automatic language detection.
 - Easily apply default account settings to new pastes.
 - High performance with aggressive caching.

## Dependencies

 - PHP 5.3.0+
 - cURL extension

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

// a developer account is required for all interactions with the API
$developer = new Developer('<developer key>');

try {
	// send the draft to Pastebin; turn it into a full blown paste
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

// an account object represents a Pastebin user account
$account = new Account(new Credentials('<username>', '<password>'));

// this time, create a draft directly from a file
$draft = Draft::fromFile('passwords.txt');

// link the draft to the account we specified above
$draft->setOwner($account);

// specify that we don't want this paste to be visible outside the account
$draft->setVisibility(Visibility::VISIBILITY_PRIVATE);

// the Developer class manages a developer key and the signing of requests with it
$developer = new Developer('<developer key>');

try {
	// submit the draft and retrieve the final paste in the same way as above
	$paste = $draft->paste($developer);
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

The `Account` class represents a Pastebin account. At the lowest level, it manages a user session key, which has to be provided when doing operations affecting a particular account. An instance can be created in two ways:

 1. Via a set of credentials, as above. Brush will make an HTTP request to Pastebin when a user key is first needed, and cache it for the rest of execution.
 2. Directly by passing a session key string as the only argument to `Account`'s constructor. This saves a request, and is the recommended way of using the class if you always want to work with the same account.

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
	$pastes = $account->getPastes($developer);
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
	$pastes = $account->getPastes($developer, 10);

	// delete the first 10 account pastes
	foreach ($pastes as $paste) {
		$paste->delete($developer);
	}
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

N.B. For authentication reasons, only pastes retrieved from an account can be deleted. If you attempt to delete a paste obtained via other means (e.g. a trending paste), Brush will detect this and throw a `ValidationException`, as Pastebin would simply reject the request.

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
