# Brush

Brush is a complete object-oriented PHP wrapper for the Pastebin API.

## Features

 - Create pastes directly from files, with auto-detection of their formats.
 - Support for default account settings.
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

$draft = new Draft();
$draft->setContent('Some random content');

$developer = new Developer('<developer key>');
$paste = $draft->paste($developer);
echo $paste->getUrl(); // e.g. http://pastebin.com/JYvbS0fC
```

There are several things to note:

 - You only ever need to require `Brush.php`; Brush has an autoloader, which will take care of other includes for you.
 - The `Draft` class represents a paste not yet submitted to Pastebin. It has setters allowing you to configure every possible option for your new paste, including expiry, format and visibility. For an example of how to do this, see the *Draft options* section below.
 - The `Developer` class represents a developer account. A developer instance needs to be provided for all interaction with the Pastebin API.
 - Once a draft is `paste()`d, Brush will return a `Paste` object. This contains all information about the paste, including as its key, URL and expiry date.

#### Draft options

Below is an example of all possible options that can be set on a `Draft`. The only exception is `setOwner()`, which we'll cover in *Create a private paste* below.

``` php
```

Brush validates drafts before attempting to send them to Pastebin. If an error is detected (e.g. no content set), then a `ValidationException` will be thrown when attempting to `paste()` the draft.

### Create a private paste

Private pastes require a user account, but Brush makes this easy to set up.

``` php
require '../Brush.php';

use \Brush\Accounts\Developer;
use \Brush\Accounts\Account;
use \Brush\Accounts\Credentials;
use \Brush\Pastes\Draft;

$account = new Account(new Credentials('<username>', '<password>'));

$draft = new Draft();
$draft->setContent('Paste content');
$draft->setOwner($account);

$developer = new Developer('<developer key>');
$paste = $draft->paste($developer);
```

The `Account` class represents a Pastebin account. It can be created from a set of credentials as above, or directly from a user session key. Once you have create an account object, you simply need to call `setOwner()` on the draft passing it as the only argument. When the draft is pasted, Brush will make sure the draft is associated with the specified account.

### Retrieve an account's pastes

Retrieving an account's pastes is easy:

``` php
require 'Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;

$account = new Account('<user session key>'); // or credentials
$developer = new Developer('<developer key>');
$pastes = $account->getPastes($developer);
```

`Account`'s `getPastes()` method returns an array of `Paste` objects, representing pastes submitted by that account. It takes an optional second argument: the maximum number of pastes to retrieve. This defaults to 50.

### Delete a paste

Once you have a paste object, simply call `delete()` on it (providing a `Developer` instance) to delete it:

``` php
require 'Brush.php';

use \Brush\Accounts\Account;
use \Brush\Accounts\Developer;

$account = new Account('<user session key>');
$developer = new Developer('<developer key>');
$pastes = $account->getPastes($developer, 10);

// delete the first 10 account pastes
foreach ($pastes as $paste) {
	$paste->delete($developer);
}
```

N.B. Only pastes retrieved from an account can be deleted. If you attempt to delete a trending paste, Brush will throw a `ValidationException`.

### Retrieve trending pastes

``` php
require 'Brush.php';

use \Brush\Accounts\Developer;
use \Brush\Pastes\Trending;

$developer = new Developer('<developer key>');
$pastes = Trending::getPastes($developer);
```

## Contributing

Suggestions and pull requests are welcome. Please submit these through the normal GitHub channels.

If you discover a bug, please open a [new issue](https://github.com/gebn/Brush/issues/new).

## Licence

Brush is released under the MIT Licence - see the LICENSE file for details. For more information about how this allows you to use the library, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).
