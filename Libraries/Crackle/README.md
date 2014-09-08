# Crackle

Crackle is a powerful yet easy to use object-oriented HTTP client for PHP.

## Features

 - GET, POST, HEAD, PUT, DELETE
 - Request authentication (basic, digest, NTLM)
 - Proxy support (with optional basic and NTLM authentication)
 - Easy header management
 - Fine-grained field handling
 - Simultaneous processing of multiple requests
 - Request callbacks
 - Many more...

## Dependencies

### Required

 - PHP 5.3.0+
 - cURL extension

### Optional

 - Fileinfo extension (enables automatic detection of file MIME types)

## Getting Started

### A simple GET request

Below is a minimal example showing how to issue a GET request to GitHub's API, and print out the response content body.

``` php
require_once 'Crackle.php';
use \Crackle\Requests\GETRequest;
use \Crackle\Exceptions\RequestException;

try {
	$request = new GETRequest('https://api.github.com/users/gebn');
	echo $request->getResponse()->getBody();
}
catch (RequestException $e) {
	echo $e->getMessage();
}
```

There are several things to note:

 - You only ever need to require `Crackle.php`; Crackle has an autoloader, which will take care of other includes for you.
 - All types of request (`GET`, `POST` etc.) are stored in the `\Crackle\Requests` namespace. They have names of the form `<VERB>Request.php` to make them easy to find.
 - Requests have a `fire()` method which is called implicitly the first time `getResponse()` is called on a request.
 - Crackle will throw a `RequestException` on failure, so all requests should be wrapped in a try/catch statement. All exceptions thrown by Crackle (excluding those in the SPL) extend `CrackleException` and are located in the `\Crackle\Exceptions` namespace.
 - `getResponse()` returns an object containing the final URL, response headers, HTTP status code, and of course content body.

### A POST request

This example introduces fields. Crackle uses the following terminology:

 - **GET fields** are referred to as **_parameters_**
 - **POST fields** are referred to as **_variables_**
 - **POST files** are referred to as **_files_**

For example, by calling `getParameters()`, you are retrieving the object containing fields that will be appended to the request's URL.

``` php
require_once 'Crackle.php';
use \Crackle\Requests\POSTRequest;
use \Crackle\Requests\Parts\Files\POSTFile;
use \Crackle\Exceptions\RequestException;

try {
	$request = new POSTRequest('http://pastebin.com/api/api_post.php');
	$request->getParameters()->set('api_request', 'beer');
	$request->getVariables()->set('api_option', 'paste');
	$request->getFiles()->set('api_file', POSTFile::factory('leaked-credentials.txt'));
	$request->fire();
}
catch (RequestException $e) {
	echo $e->getMessage();
}
```

Crackle allows you to arbitrarily nest all three types of field depending on your needs using PHP's array syntax:

``` php
$variables = $request->getVariables();
$variables->set('details', array(
		'name' => array(
			'forename' => 'George',
			'surname' => 'Brighton'),
		'address' => 'nice try'));

// you can also add to the hierarchy later, or build it up manually:
$variables->set('details[name][nickname]', 'brighty');
```

### Adding authentication and proxies

All types of request can be authenticated and proxied:

``` php
require_once 'Crackle.php';
use \Crackle\Requests\PUTRequest;
use \Crackle\Requests\Parts\Files\PUTFile;
use \Crackle\Proxies\SOCKS5Proxy;
use \Crackle\Authentication\Methods\BasicCredentials;
use \Crackle\Authentication\Methods\NTLMCredentials;

$request = new PUTRequest('https://api.trello.com/1/cards/OeU7nvW6');

// Basic, Digest and NTLM are supported
$request->setCredentials(new BasicCredentials('<username>', '<password>'));

$file = new PUTFile(); // PUTFile also has a factory($path) method
$file->setContent('virtual file content');
$file->setMimeType('text/plain'); // optional
$request->setFile($file);

$proxy = new SOCKS5Proxy('10.11.12.13'); // HTTP proxies are also supported
$proxy->setCredentials( // N.B. proxy (not request) credentials
		new NTLMCredentials('<username>', '<password>')); // Basic and NTLM supported
$request->setProxy($proxy);
```

Hopefully you're now getting an idea of how the various different components of Crackle fit together. For additional examples, see the contents of `/Examples`.

## Advanced Use

### Parallel requests with callbacks

Crackle includes a `Requester` class in the default namespace which has a single purpose: to fire off requests simultaneously. Its use is highly recommended if you're dealing with multiple requests - performance gains can be significant. So significant in fact, that Crackle was started because I wanted an easier way to manage parallel requests. The name *Crackle* comes from a fire crackling, in the same way you could imagine Crackle firing off multiple requests.

A callback is a function that can be attached to a Crackle request. As soon as the request finishes, regardless of success or failure, the callback is executed. It is passed the original request object as its only argument.

This sample will fire off requests to BBC News and Twitter, and announce when each has finished (it won't always be in the same order):

``` php
require_once 'Crackle.php';
use \Crackle\Requests\GETRequest;
use \Crackle\Requests\POSTRequest;
use \Crackle\Requester;

$bbc = new GETRequest('http://www.bbc.co.uk/news/');
$bbc->setCallback(function(GETRequest $request) {
	echo 'BBC request finished', "\n";
});

$twitter = new POSTRequest('https://api.twitter.com/1.1/statuses/update');
$twitter->setCallback(function(POSTRequest $request) {
	echo 'Twitter request finished', "\n";
});

$requester = new Requester();
$requester->queue($bbc);
$requester->queue($twitter);
$requester->fireAll();
```

Callbacks can be deployed elsewhere, but they come into their own when used with simultaneous requests. They allow you to do processing while Crackle is still sending other requests in the queue. You can even add new requests to the queue from within callbacks while it is still being processed!

Crackle can fire all types of request simultaneously - `PUTRequest` and `DELETERequest` won't fight if they're put in the same queue.

When requests are fired via `Requester`, they do not throw an exception on failure. Instead, you can check `$request->succeeded()` or `$request->failed()` inside the callback. A call to `$request->getResponse()` will throw a `ResponseException` if the request did not succeed - so check it did first!

### Accessing the cURL handle

Crackle allows direct manipulation of a request's underlying cURL handle. This can be retrieved by calling `getHandle()` on its object:

``` php
$request = new GETRequest('https://example.com');
curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);
```

N.B. Some options are set by Crackle immediately before sending the request, so overriding them manually will have no effect.

## Development

Crackle is on Trello! [Click here](https://trello.com/crackle) to visit the organisation's homepage. To see what's in the pipeline, have a peek at the [Development](https://trello.com/b/91q94waP/development) board!

If you discover a bug, first check Trello to make sure I'm not already aware of it. If you can't see a match, please open a [new issue](https://github.com/gebn/Crackle/issues/new) on GitHub.

## Contributing

Please feel free to fork Crackle, create a feature branch, and send me a pull request!

## Licence

Crackle is released under the MIT Licence - see the LICENSE file for details. For more information about how this allows you to use the library, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).
