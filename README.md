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
require 'Brush.php';

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
 - The `Developer` class represents a developer account. A developer instance needs to be provided for all interaction with the Pastebin API.
 - Brush validates drafts before attempting to send them to Pastebin. If an error is detected (e.g. no content set), then a `ValidationException` will be thrown when attempting to `paste()` the draft.
 - Once a draft is `paste()`d, Brush will return a `Paste` object. This contains all information about the paste, including as its key, URL and expiry date.
 - For a complete method reference, see [METHODS.md](METHODS.md).

### Create a private paste

Private pastes require a user account, but Brush makes this easy to set up.

``` php
require '../Brush.php';

use \Brush\Accounts\Developer;
use \Brush\Accounts\Account;
use \Brush\Accounts\Credentials;
use \Brush\Pastes\Draft;
use \Brush\Exceptions\BrushException;

// an account object represents a Pastebin user account
$account = new Account(new Credentials('<username>', '<password>'));

// this time, create a draft directly from a file
$draft = Draft::fromFile('passwords.txt');

// link the draft to the account we specified above
$draft->setOwner($account);

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

The `Account` class represents a Pastebin account. It can be created from a set of credentials as above, or directly from a user session key. Once you have create an account object, you simply need to call `setOwner()` on the draft passing it as the only argument. When the draft is pasted, Brush will make sure the draft is associated with the specified account.

### Retrieve an account's pastes

Retrieving an account's pastes is easy:

``` php
require 'Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;
use \Brush\Exceptions\BrushException;

$account = new Account('<user session key>'); // or credentials
$developer = new Developer('<developer key>');

try {
	$pastes = $account->getPastes($developer);
}
catch (BrushException $e) {
	echo $e->getMessage();
}
```

`Account`'s `getPastes()` method returns an array of `Paste` objects, representing pastes submitted by that account. It takes an optional second argument: the maximum number of pastes to retrieve. This defaults to 50.

### Delete a paste

Once you have a paste object, simply call `delete()` on it (providing a `Developer` instance) to delete it:

``` php
require 'Brush.php';

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

N.B. Only pastes retrieved from an account can be deleted. If you attempt to delete a trending paste, Brush will throw a `ValidationException`.

### Retrieve trending pastes

``` php
require 'Brush.php';

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
