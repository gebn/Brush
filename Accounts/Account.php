<?php

namespace Brush\Accounts {

	use \Brush\Brush;
	use \Brush\Exceptions\RequestException;
	use \Brush\Exceptions\ApiException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	/**
	 * Represents a Pastebin account's identity. From a technical perspective, this is simply its session key.
	 * @author George Brighton
	 */
	class Account {

		/**
		 * The endpoint of the session key service.
		 * @var string
		 */
		const ENDPOINT = 'api_login.php';

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
		 * Initialise a new account.
		 * @param \Brush\Accounts\Credentials|string $mechanism This account's credentials or API user key.
		 */
		public function __construct($mechanism) {
			if ($mechanism instanceof Credentials) {
				$this->setCredentials($mechanism);
			}
			else {
				$this->setKey($mechanism);
			}
		}

		/**
		 * Retrieve the user key for this account. Caching.
		 * @param \Brush\Accounts\Developer $developer The developer account to use if the key needs to be fetched.
		 * @return string The user key.
		 */
		private function getKey(Developer $developer) {
			if ($this->key === null) {
				$this->setKey($this->fetchKey($developer));
			}
			return $this->key;
		}

		/**
		 * Find the API user key for this account.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @return string The user's key.
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicate an error in our request.
		 * @throws \Brush\Exceptions\RequestException If our request to Pastebin's API fails.
		 */
		private function fetchKey(Developer $developer) {

			// if this method is being called, we should always have credentials available
			assert($this->getCredentials() !== null);

			$request = new POSTRequest(Brush::API_BASE_URL . self::ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			try {
				$this->getCredentials()->sign($request);
				$developer->sign($request);

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
	}
}
