<?php

namespace Brush\Accounts {

	use \Brush\Brush;
	use \Brush\Pastes\Paste;
	use \Brush\Exceptions\ApiException;
	use \Brush\Exceptions\ArgumentException;
	use \Brush\Exceptions\CacheException;
	use \Brush\Exceptions\RequestException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMDocument;

	/**
	 * Represents a Pastebin account's identity. From a technical perspective, this is simply its session key.
	 * @author George Brighton
	 */
	class Account {

		/**
		 * The endpoint of the session key service.
		 * @var string
		 */
		const LOGIN_ENDPOINT = 'api_login.php';

		/**
		 * The endpoint of the paste listing service.
		 * @var string
		 */
		const PASTES_ENDPOINT = 'api_post.php';

		/**
		 * The credentials for this account.
		 * These are stored to enable lazy loading of user keys.
		 * @var \Brush\Accounts\Credentials
		 */
		private $credentials;

		/**
		 * This account's API user key.
		 * @var string
		 */
		private $key;

		/**
		 * An internal user key cache.
		 * Maps usernames to keys.
		 * @var array[string]
		 */
		private static $keys = array();

		/**
		 * Retrieve the credentials for this account.
		 * @return \Brush\Accounts\Credentials The credentials for this account.
		 */
		private final function getCredentials() {
			return $this->credentials;
		}

		/**
		 * Set the credentials for this account.
		 * @param \Brush\Accounts\Credentials $credentials The new credentials for this account.
		 */
		private final function setCredentials(Credentials $credentials) {
			$this->credentials = $credentials;
		}

		/**
		 * Set this user's API key.
		 * @param string $key The new API key.
		 */
		private final function setKey($key) {
			$this->key = $key;
		}

		/**
		 * Find if there's a cached key for a user.
		 * @param string $username The username to check.
		 * @return boolean True if there is; false if there isn't.
		 */
		private static final function isKeyCached($username) {
			return isset(self::$keys[$username]);
		}

		/**
		 * Retrieve the cached user key by its username.
		 * @param string $username The username of the account whose key to retrieve.
		 * @throws \Brush\Exceptions\CacheException If the key is not cached.
		 * @return string The cached key.
		 */
		private static final function getUserKey($username) {
			if (!self::isKeyCached($username)) {
				throw new CacheException('Username key is not cached.');
			}
			return self::$keys[$username];
		}

		/**
		 * Update the cached key for a user.
		 * @param string $username The username whose key this is.
		 * @param string $key The new API key.
		 */
		private final function setUserKey($username, $key) {
			self::$keys[$username] = $key;
		}

		/**
		 * Initialise a new account.
		 * @param \Brush\Accounts\Credentials|string $mechanism This account's credentials or API user key.
		 */
		public function __construct($mechanism) {
			if (is_string($mechanism)) {
				// we've been given a straight user key
				$this->setKey($mechanism);
			}
			else if (self::isKeyCached($mechanism->getUsername())) {
				// key cache hit
				$this->setKey(self::getUserKey($mechanism->getUsername()));
			}
			else {
				// key cache miss
				$this->setCredentials($mechanism);
			}
		}

		/**
		 * Retrieve the user key for this account. Caching.
		 * @param \Brush\Accounts\Developer $developer The developer account to use if the key needs to be fetched.
		 * @return string The user key.
		 */
		private function getKey(Developer $developer) {
			if ($this->key === null) {
				$key = $this->fetchKey($developer);
				self::setUserKey($this->getCredentials()->getUsername(), $key);
				$this->setKey($key);
			}
			return $this->key;
		}

		/**
		 * Get a string unique to this account, suitable for caching.
		 * @param \Brush\Accounts\Developer $developer The developer to use for any necessary requests.
		 * @return string This account's cache key.
		 */
		public function getCacheKey(Developer $developer) {
			return md5($this->getKey($developer));
		}

		/**
		 * Find the API user key for this account.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @return string The user's key.
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicates an error in the request.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin fails.
		 */
		private function fetchKey(Developer $developer) {

			// if this method is being called, we should always have credentials available
			assert($this->getCredentials() !== null);

			$request = new POSTRequest(Brush::API_BASE_URL . self::LOGIN_ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			$this->getCredentials()->sign($request);
			$developer->sign($request);

			try {
				// send the request and get the response body
				$body = $request->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Failed to retrieve user key: ' . substr($body, 17));
				}

				// must be success (the entire content is the key)
				return $body;
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($request);
			}
		}

		/**
		 * Authenticate a request from this user.
		 * @param \Crackle\Requests\POSTRequest $request The request to authenticate.
		 * @param \Brush\Accounts\Developer $developer The developer account to use if the key needs to be fetched.
		 */
		public function sign(POSTRequest $request, Developer $developer) {
			$request->getVariables()->set('api_user_key', $this->getKey($developer));
		}

		/**
		 * Retrieve pastes created by this account.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @param int $number The maximum number of pastes to retrieve. 1 <= $number <= 1000. Defaults to 50.
		 * @throws \Brush\Exceptions\ArgumentException If the number of pastes is outside the allowed range.
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicates an error in the request.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin fails.
		 * @return array[\Brush\Pastes\Paste] This account's pastes, up to the limit.
		 */
		public function getPastes(Developer $developer, $number = 50) {

			// check 1 <= $number <= 1000
			if ($number < 1 || $number > 1000) {
				throw new ArgumentException('The number of pastes must be in the range 1 to 1000 inclusive.');
			}

			$request = new POSTRequest(Brush::API_BASE_URL . self::PASTES_ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			$developer->sign($request);
			$this->sign($request, $developer);

			$variables = $request->getVariables();
			$variables->set('api_option', 'list');
			$variables->set('api_results_limit', $number);

			try {
				// send the request and get the response body
				$body = $request->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Failed to retrieve user key: ' . substr($body, 17));
				}

				// must be success (the entire content is the key)
				return $this->parsePastes($body);
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($request);
			}
		}

		/**
		 * Parse a successful paste listing response.
		 * @param string $response The response to parse.
		 * @return array[\Brush\Pastes\Paste] The parsed pastes.
		 */
		private function parsePastes($response) {
			if ($response == 'No pastes found.') {
				return array();
			}

			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXML('<pastes>' . $response . '</pastes>');

			$pastes = array();
			foreach ($dom->documentElement->getElementsByTagName('paste') as $paste) {
				$pastes[] = Paste::fromXml($paste, $this);
			}
			return $pastes;
		}
	}
}
