<?php

namespace Brush\Accounts {

	use \Brush\Pastes\Paste;
	use \Brush\Utilities\Cache;
	use \Brush\Utilities\ApiRequest;
	use \Brush\Exceptions\ArgumentException;

	use \Crackle\Requests\POSTRequest;

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
		 * An internal cache of user session keys.
		 * @var \Brush\Utilities\Cache
		 */
		private static $cache;

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
		 * Retrieve the user session key cache.
		 * @return \Brush\Utilities\Cache The user session key cache.
		 */
		private static final function getCache() {
			return self::$cache;
		}

		/**
		 * Set the user session key cache.
		 * @param \Brush\Utilities\Cache $cache The new user session key cache.
		 */
		private static final function setCache(Cache $cache) {
			self::$cache = $cache;
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
			else if (self::getCache()->isCached($mechanism->getUsername())) {
				// key cache hit
				$this->setKey(self::getCache()->get($mechanism->getUsername()));
			}
			else {
				// key cache miss
				$this->setCredentials($mechanism);
			}
		}

		/**
		 * Initialise static properties of this class.
		 * This method should not be called from user code.
		 */
		public static function initialise() {
			self::setCache(new Cache());
		}

		/**
		 * Retrieve the user key for this account. Caching.
		 * @param \Brush\Accounts\Developer $developer The developer account to use if the key needs to be fetched.
		 * @return string The user key.
		 */
		private function getKey(Developer $developer) {
			if ($this->key === null) {
				$key = $this->fetchKey($developer);
				self::getCache()->set($this->getCredentials()->getUsername(), $key);
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
		 */
		private function fetchKey(Developer $developer) {

			// if this method is being called, we should always have credentials available
			assert($this->getCredentials() !== null);

			$pastebin = new ApiRequest($developer, self::LOGIN_ENDPOINT);
			$this->getCredentials()->sign($pastebin->getRequest());
			return $pastebin->send();
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
		 * Retrieve pastes created by this account in descending order of date created.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @param int $limit The maximum number of pastes to retrieve. 1 <= $number <= 1000. Defaults to 50.
		 * @throws \Brush\Exceptions\ArgumentException If the number of pastes is outside the allowed range.
		 * @return \Brush\Pastes\Paste[] This account's pastes, up to the limit.
		 */
		public function getPastes(Developer $developer, $limit = 50) {

			// check 1 <= $number <= 1000
			if ($limit < 1 || $limit > 1000) {
				throw new ArgumentException('The number of pastes must be in the range 1 to 1000 inclusive.');
			}

			$pastebin = new ApiRequest($developer, self::PASTES_ENDPOINT);
			$pastebin->setOption('list');

			$request = $pastebin->getRequest();
			$this->sign($request, $developer);
			$request->getVariables()->set('api_results_limit', $limit);

			return Paste::parsePastes($pastebin->send(), $this);
		}
	}

	Account::initialise();
}
