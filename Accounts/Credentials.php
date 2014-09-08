<?php

namespace Brush\Accounts {

	use \Crackle\Requests\POSTRequest;

	/**
	 * Represents an account's Pastebin credentials.
	 * @author George Brighton
	 */
	class Credentials {

		/**
		 * The account username.
		 * @var string
		 */
		private $username;

		/**
		 * The account password.
		 * @var string
		 */
		private $password;

		/**
		 * Retrieve the account username.
		 * @return string The account username.
		 */
		public final function getUsername() {
			return $this->username;
		}

		/**
		 * Set the account username.
		 * @param string $username The new account username.
		 */
		private final function setUsername($username) {
			$this->username = $username;
		}

		/**
		 * Retrieve the account password.
		 * @return string The account password.
		 */
		private final function getPassword() {
			return $this->password;
		}

		/**
		 * Set the account password.
		 * @param string $password The new account password.
		 */
		private final function setPassword($password) {
			$this->password = $password;
		}

		/**
		 * Initialise a new set of credentials.
		 * @param string $username The account username.
		 * @param string $password The account password.
		 */
		public function __construct($username, $password) {
			$this->setUsername($username);
			$this->setPassword($password);
		}

		/**
		 * Add the credentials contained in this object to a request.
		 * @param \Crackle\Requests\POSTRequest $request The request to authenticate.
		 */
		public final function sign(POSTRequest $request) {
			$variables = $request->getVariables();
			$variables->set('api_user_name', $this->getUsername());
			$variables->set('api_user_password', $this->getPassword());
		}
	}
}
