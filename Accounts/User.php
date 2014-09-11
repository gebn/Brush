<?php

namespace Brush\Accounts {

	use \Brush\Brush;
	use \Brush\Exceptions\ApiException;
	use \Brush\Exceptions\RequestException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMElement;
	use \DOMDocument;

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
		private $website;

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
		public final function getWebsite() {
			return $this->website;
		}

		/**
		 * Set the address of this user's website.
		 * @param string $website The new address of this user's website.
		 */
		private final function setWebsite($website) {
			$this->website = $website;
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
		 * This class should never be publically instantiated.
		 */
		private function __construct() {}

		/**
		 * Import information from a user element into this user.
		 * @param \DOMElement $user The user element to parse.
		 */
		protected function parse(DOMElement $user) {
			$this->setUsername($user->getElementsByTagName('user_name')->item(0)->nodeValue);
			$this->setAvatarUrl($user->getElementsByTagName('user_avatar_url')->item(0)->nodeValue);
			$this->setEmail($user->getElementsByTagName('user_email')->item(0)->nodeValue);
			$this->setWebsite($user->getElementsByTagName('user_website')->item(0)->nodeValue);
			$location = $user->getElementsByTagName('user_location')->item(0)->nodeValue;
			$this->setLocation($location === '' ? null : $location);
			$this->setType($user->getElementsByTagName('user_account_type')->item(0)->nodeValue);
		}

		/**
		 * Create a new user from their XML representation.
		 * @param \DOMElement $element The user's XML node returned by the Pastebin API.
		 * @return \Brush\Accounts\User The created user.
		 */
		private static final function fromXml(DOMElement $element) {
			$user = new static();
			$user->parse($element);
			return $user;
		}

		/**
		 * Retrieve a user's information from their account.
		 * @param \Brush\Accounts\Account $account The account whose corresponding information to retrieve.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicates an error in our request.
		 * @throws \Brush\Exceptions\RequestException If our request to Pastebin's API fails.
		 * @return \Brush\Accounts\User A user instance containing the account's information.
		 */
		public static final function fromAccount(Account $account, Developer $developer) {
			$request = new POSTRequest(Brush::API_BASE_URL . self::ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			$developer->sign($request);
			$account->sign($request, $developer);
			$request->getVariables()->set('api_option', 'userdetails');

			try {
				// send the request and get the response body
				$body = $request->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Failed to retrieve user key: ' . substr($body, 17));
				}

				// must be success
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->loadXML($body);
				return self::fromXml($dom->documentElement);
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($request);
			}
		}
	}
}
