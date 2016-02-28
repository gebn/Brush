<?php

namespace Brush\Accounts {

	use \Brush\Utilities\Cache;
	use \Brush\Utilities\ApiRequest;

	use \DOMElement;

	/**
	 * Represents a user's information.
	 * @author George Brighton
	 */
	class User {

		/**
		 * The endpoint of the user information retrieval service.
		 * @var string
		 */
		const ENDPOINT = 'api_post.php';

		/**
		 * The account type of a normal Pastebin user.
		 * @var int
		 */
		const TYPE_NORMAL = 0;

		/**
		 * The account type of a Pastebin user with PRO membership.
		 * @var int
		 */
		const TYPE_PRO = 1;

		/**
		 * This user's username.
		 * @var string
		 */
		private $username;

		/**
		 * The URL of this user's avatar.
		 * @var string
		 */
		private $avatarUrl;

		/**
		 * This user's email address.
		 * @var string
		 */
		private $email;

		/**
		 * The address of this user's website.
		 * Null if unknown.
		 * @var string
		 */
		private $websiteUrl;

		/**
		 * Where this user is from.
		 * Null if unknown.
		 * @var string
		 */
		private $location;

		/**
		 * This user's account type.
		 * @var int
		 */
		private $type;

		/**
		 * This user's default settings.
		 * @var \Brush\Accounts\Defaults
		 */
		private $defaults;

		/**
		 * An internal cache of user objects.
		 * @var \Brush\Utilities\Cache
		 */
		private static $cache;

		/**
		 * Retrieve this user's username.
		 * @return string This user's username.
		 */
		public final function getUsername() {
			return $this->username;
		}

		/**
		 * Set this user's username.
		 * @param string $username The new username.
		 */
		private final function setUsername($username) {
			$this->username = $username;
		}

		/**
		 * Retrieve the URL of this user's avatar.
		 * @return string The URL of this user's avatar.
		 */
		public final function getAvatarUrl() {
			return $this->avatarUrl;
		}

		/**
		 * Find whether this user has a custom avatar set.
		 * @return bool True if they do, false if it's the default.
		 */
		public final function hasCustomAvatar() {
			return $this->getAvatarUrl() != 'http://pastebin.com/i/guest.gif';
		}

		/**
		 * Set the URL of this user's avatar.
		 * @param string $avatarUrl The new URL of this user's avatar.
		 */
		private final function setAvatarUrl($avatarUrl) {
			$this->avatarUrl = $avatarUrl;
		}

		/**
		 * Retrieve this user's email address.
		 * @return string This user's email address.
		 */
		public final function getEmail() {
			return $this->email;
		}

		/**
		 * Set this user's email address.
		 * @param string $email The new email address.
		 */
		private final function setEmail($email) {
			$this->email = $email;
		}

		/**
		 * Retrieve the address of this user's website.
		 * @return string The address of this user's website.
		 */
		public final function getWebsiteUrl() {
			return $this->websiteUrl;
		}

		/**
		 * Set the address of this user's website.
		 * @param string $websiteUrl The new address of this user's website.
		 */
		private final function setWebsiteUrl($websiteUrl) {
			$this->websiteUrl = $websiteUrl;
		}

		/**
		 * Retrieve where this user is from.
		 * @return string Where this user is from.
		 */
		public final function getLocation() {
			return $this->location;
		}

		/**
		 * Set where this user is from.
		 * @param string $location The new location of this user.
		 */
		private final function setLocation($location) {
			$this->location = $location;
		}

		/**
		 * Retrieve this user's account type.
		 * @return int This user's account type.
		 */
		public final function getType() {
			return $this->type;
		}

		/**
		 * Set the new account type of this user.
		 * @param int $type The new account type.
		 */
		private final function setType($type) {
			$this->type = (int)$type;
		}

		/**
		 * Find whether this a PRO user.
		 * @return boolean True if this user is a PRO user; false if they are a normal user.
		 */
		public final function isPro() {
			return $this->getType() === self::TYPE_PRO;
		}

		/**
		 * Retrieve this user's default settings.
		 * @return \Brush\Accounts\Defaults This user's default settings.
		 */
		public final function getDefaults() {
			return $this->defaults;
		}

		/**
		 * Set this user's default settings.
		 * @param \Brush\Accounts\Defaults $defaults This user's default settings.
		 */
		private final function setDefaults(Defaults $defaults) {
			$this->defaults = $defaults;
		}

		/**
		 * Retrieve the user instance cache.
		 * @return \Brush\Utilities\Cache The user instance cache.
		 */
		private static final function getCache() {
			return self::$cache;
		}

		/**
		 * Set the user instance cache.
		 * @param \Brush\Utilities\Cache $cache The new user instance cache.
		 */
		private static final function setCache(Cache $cache) {
			self::$cache = $cache;
		}

		/**
		 * This class should never be publicly instantiated.
		 */
		private function __construct() {}

		/**
		 * Initialise static properties of this class.
		 * This method should not be called from user code.
		 */
		public static function initialise() {
			self::setCache(new Cache());
		}

		/**
		 * Import information from a user element into this user.
		 * @param \DOMElement $user The user element to parse.
		 */
		protected function parse(DOMElement $user) {
			$this->setUsername($user->getElementsByTagName('user_name')->item(0)->nodeValue);
			$this->setAvatarUrl($user->getElementsByTagName('user_avatar_url')->item(0)->nodeValue);
			$this->setEmail($user->getElementsByTagName('user_email')->item(0)->nodeValue);

			$website = $user->getElementsByTagName('user_website')->item(0)->nodeValue;
			$this->setWebsiteUrl($website === '' ? null : $website);

			$location = $user->getElementsByTagName('user_location')->item(0)->nodeValue;
			$this->setLocation($location === '' ? null : $location);

			$this->setType($user->getElementsByTagName('user_account_type')->item(0)->nodeValue);
			$this->setDefaults(Defaults::fromXml($user));
		}

		/**
		 * Create a new user from their XML representation.
		 * @param \DOMElement $element The user's XML node returned by the Pastebin API.
		 * @return \Brush\Accounts\User The created user.
		 */
		private static final function fromXml(DOMElement $element) {
			$user = new User();
			$user->parse($element);
			return $user;
		}

		/**
		 * Retrieve a user's information from their account.
		 * @param \Brush\Accounts\Account $account The account whose corresponding information to retrieve.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @return \Brush\Accounts\User A user instance containing the account's information.
		 */
		public static final function fromAccount(Account $account, Developer $developer) {
			$key = $account->getCacheKey($developer);
			if (self::getCache()->isCached($key)) {
				// user cache hit
				return self::getCache()->get($key);
			}

			$pastebin = new ApiRequest($developer, self::ENDPOINT);
			$pastebin->setOption('userdetails');
			$account->sign($pastebin->getRequest(), $developer);

			$user = self::fromXml(ApiRequest::toElement($pastebin->send()));
			self::getCache()->set($key, $user);
			return $user;
		}

		/**
		 * Get a plaintext string representation of this user.
		 * @return string This user's information and defaults.
		 */
		public function __toString() {
			$user = $this->getUsername() . PHP_EOL . PHP_EOL;
			$user .= 'Information:' . PHP_EOL;
			$user .= "\t" . 'Email: ' . $this->getEmail() . PHP_EOL;
			$user .= "\t" . 'Website: '
					. ($this->getWebsiteUrl() === null ? 'unknown' : $this->getWebsiteUrl())
					. PHP_EOL;
			$user .= "\t" . 'Location: '
					. ($this->getLocation() === null ? 'unknown' : $this->getLocation())
					. PHP_EOL;
			$user .= "\t" . 'Account Type: ' . ($this->isPro() ? 'Pro' : 'Normal') . PHP_EOL;
			$user .= PHP_EOL;
			$user .= $this->getDefaults();
			return $user;
		}
	}

	User::initialise();
}
