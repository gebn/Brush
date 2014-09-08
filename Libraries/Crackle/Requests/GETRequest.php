<?php

namespace Crackle\Requests {

	use \Crackle\Headers;
	use \Crackle\Response;
	use \Crackle\Requests\Fields\Parameters;
	use \Crackle\Authentication\Applicators\RequestCredentials;
	use \Crackle\Proxies\Proxy;
	use \Crackle\Utilities\Curl;
	use \Crackle\Exceptions\ValidationException;
	use \Crackle\Exceptions\RequestException;

	use \InvalidArgumentException;

	/**
	 * Represents an HTTP request sent using the GET method.
	 * @author George Brighton
	 */
	class GETRequest {

		/**
		 * The cURL handle representing this request.
		 * @var resource
		 */
		private $handle;

		/**
		 * The URL this request will be sent to.
		 * @var string
		 */
		private $url;

		/**
		 * GET parameters to send with this request.
		 * @var \Crackle\Requests\Fields\Parameters
		 */
		private $parameters;

		/**
		 * Additional that will be sent with this request.
		 * @var \Crackle\Headers
		 */
		private $headers;

		/**
		 * The credentials to use to authenticate this request.
		 * @var \Crackle\Authentication\Applicators\RequestCredentials
		 */
		private $credentials;

		/**
		 * An optional proxy to use for this request.
		 * @var \Crackle\Proxies\Proxy
		 */
		private $proxy;

		/**
		 * Container for a possible cURL error message returned when this request is sent.
		 * @var string
		 */
		private $error;

		/**
		 * The response representing data returned from this request.
		 * @var \Crackle\Response
		 */
		private $response;

		/**
		 * A function to run upon completion of this request.
		 * This fuction should accept the original request object (either \Crackle\Requests\GETRequest or a derived class) as its only parameter.
		 * @var callable
		 */
		private $callback;

		/**
		 * Get the cURL handle representing this request.
		 * @return resource The cURL handle representing this request.
		 */
		public final function getHandle() {
			return $this->handle;
		}

		/**
		 * Set the cURL handle representing this request.
		 * @param resource $handle The cURL handle representing this request.
		 */
		private final function setHandle($handle) {
			$this->handle = $handle;
		}

		/**
		 * Get the URL this request will be sent to.
		 * @return string The URL this request will be sent to.
		 */
		public final function getUrl() {
			$queryString = $this->getParameters()->getQueryString();
			if ($queryString == '') {
				return $this->url;
			}
			return $this->url . '?' . $queryString;
		}

		/**
		 * Set the URL this request will be sent to.
		 * @param string $url The URL this request will be sent to.
		 * @throws \InvalidArgumentException If the URL is malformed.
		 */
		public final function setUrl($url) {
			$parts = parse_url($url);
			if ($parts === false) {
				throw new InvalidArgumentException('The supplied URL is invalid.');
			}

			if (isset($parts['query'])) {

				// pass them on for parsing
				$this->getParameters()->parse($parts['query']);

				// extract the part before the query string
				$this->url = substr($url, 0, strpos($url, '?'));
			}
			else {
				// there is no query string - set the entire thing
				$this->url = $url;
			}
		}

		/**
		 * Retrieve the GET parameters to send with this request.
		 * @return \Crackle\Requests\Fields\Parameters The GET parameters to send with this request.
		 */
		public final function getParameters() {
			return $this->parameters;
		}

		/**
		 * Set the GET parameters to send with this request.
		 * @param \Crackle\Requests\Fields\Parameters $parameters The new GET parameters to send with this request.
		 */
		private final function setParameters(Parameters $parameters) {
			$this->parameters = $parameters;
		}

		/**
		 * Retrieve the list of extra request headers.
		 * @return \Crackle\Headers The list of headers.
		 */
		public final function getHeaders() {
			return $this->headers;
		}

		/**
		 * Set the list of headers to send in this request.
		 * @param \Crackle\Headers $headers The new list of headers.
		 */
		private final function setHeaders(Headers $headers) {
			$this->headers = $headers;
		}

		/**
		 * Get the credentials to use to authenticate this request.
		 * @return \Crackle\Authentication\Applicators\RequestCredentials The credentials to use to authenticate this request.
		 */
		private final function getCredentials() {
			return $this->credentials;
		}

		/**
		 * Set the credentials to use to authenticate this request.
		 * @param \Crackle\Authentication\Applicators\RequestCredentials $credentials The credentials to use to authenticate this request.
		 */
		public final function setCredentials(RequestCredentials $credentials) {
			$this->credentials = $credentials;
		}

		/**
		 * Get the proxy to use for this request.
		 * @return \Crackle\Proxies\Proxy The proxy to use for this request.
		 */
		private final function getProxy() {
			return $this->proxy;
		}

		/**
		 * Set the proxy to use for this request.
		 * @param \Crackle\Proxies\Proxy $proxy The proxy to use for this request.
		 */
		public final function setProxy(Proxy $proxy) {
			$this->proxy = $proxy;
		}

		/**
		 * Get any error message returned by cURL when this request was sent.
		 * @return string Any error message returned by cURL when this request was sent.
		 */
		public final function getError() {
			return $this->error;
		}

		/**
		 * Find whether cURL returned an error when asked to execute this request.
		 * @return boolean True if it failed; false if it didn't.
		 */
		public final function failed() {
			return is_string($this->getError());
		}

		/**
		 * Find whether this request was successful.
		 * @return boolean True if it succeeded; false if it wasn't.
		 */
		public final function succeeded() {
			return $this->error === false;
		}

		/**
		 * Set the error status of this request.
		 * @param boolean|string $error False if no error occurred, or the error message if one did.
		 */
		private final function setError($error) {
			$this->error = $error;
		}

		/**
		 * Get the response of this request. This method will fire the request if it hasn't been already.
		 * @return \Crackle\Response The response representing data returned from this request.
		 * @throws \Crackle\Exceptions\RequestException If the request fails.
		 */
		public final function getResponse() {
			if (!$this->isFired()) {
				// fire the request implicitly
				$this->fire();
			}

			return $this->response;
		}

		/**
		 * Set the response representing data returned from this request.
		 * @param \Crackle\Response $response The response representing data returned from this request.
		 */
		private final function setResponse(Response $response) {
			$this->response = $response;
		}

		/**
		 * Get the function to run upon completion of this request.
		 * @return callable The function to run upon completion of this request.
		 */
		private final function getCallback() {
			return $this->callback;
		}

		/**
		 * Set the function to run upon completion of this request.
		 * @param callable $callback The function to run upon completion of this request.
		 * @throws \InvalidArgumentException If the callback is not a callable function.
		 */
		public final function setCallback($callback) {
			if (!is_callable($callback)) {
				throw new InvalidArgumentException('The request callback must be callable.');
			}
			$this->callback = $callback;
		}

		/**
		 * Initialise a new HTTP request.
		 * @param string $url Optional: the URL to send this request to.
		 */
		public function __construct($url = null) {
			$this->setHandle(curl_init());
			$this->setHeaders(new Headers());
			$this->setParameters(new Parameters());
			$this->setDefaultOptions();

			if ($url !== null) {
				$this->setUrl($url);
			}
		}

		/**
		 * Dispose of the handle when this object goes out of scope.
		 */
		public function __destruct() {
			curl_close($this->getHandle());
		}

		/**
		 * Sets default options for the cURL session used by this request.
		 * Should be used to specify options that users may want to change.
		 */
		private function setDefaultOptions() {
			curl_setopt_array($this->getHandle(), array(
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 5));
		}

		/**
		 * Find whether this request has been sent.
		 * @return boolean True if it has, false if it hasn't.
		 */
		public final function isFired() {
			return $this->error !== null;
		}

		/**
		 * Execute this request.
		 * To fire multiple requests simultaneously, have a look at \Crackle\Requester.
		 * @throws \Crackle\Exceptions\RequestException If the request fails.
		 */
		public final function fire() {
			$this->finalise();
			curl_exec($this->getHandle());
			$this->recover(curl_errno($this->getHandle()));

			if ($this->failed()) {
				throw new RequestException('The request failed: ' . $this->getError());
			}
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Should be used to specify options that users shouldn't be changing.
		 */
		public function finalise() {
			// make sure everything is in order
			$this->validate();

			// set options that Crackle requires to operate
			curl_setopt_array($this->getHandle(), array(
					CURLOPT_HEADER => true,
					CURLOPT_URL => $this->getUrl(),
					CURLOPT_RETURNTRANSFER => true));

			// add any additional headers to the session
			$this->getHeaders()->addTo($this->getHandle());

			// add authentication if specified
			if ($this->getCredentials() !== null) {
				$this->getCredentials()->addRequestCredentialsTo($this->getHandle());
			}

			// add proxy if configured
			if ($this->getProxy() !== null) {
				$this->getProxy()->addTo($this->getHandle());
			}
		}

		/**
		 * Checks this request for errors before it is sent.
		 * @throws \Crackle\Exceptions\ValidationException If an issue is found.
		 */
		protected function validate() {
			// check that a URL has been set
			if ($this->getUrl() == null) { // null or empty
				throw new ValidationException('A request URL must be specified.');
			}
		}

		/**
		 * Builds the response object and executes the callback for this request.
		 * Called immediately after the request has finished.
		 * @param int $result One of the CURLE_* constants, indicating the status.
		 */
		public function recover($result) {

			// if an error occurred, set it
			$this->setError($result === CURLE_OK ? false : Curl::getStringError($result));

			if (!$this->failed()) {
				// build the response
				$this->setResponse(Response::factory($this->getHandle()));
			}

			// execute any callback
			if ($this->getCallback() !== null) {
				call_user_func($this->getCallback(), $this);
			}
		}
	}
}
