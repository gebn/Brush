<?php

namespace Crackle\Authentication {

	/**
	 * Represents authentication data.
	 * @author George Brighton
	 */
	abstract class Credentials {

		/**
		 * The username to send.
		 * @var string
		 */
		private $username;

		/**
		 * The password to send.
		 * @var string
		 */
		private $password;

		/**
		 * Initialise this class with an optional username and password.
		 * @param string $username The username to send.
		 * @param string $password The password to send.
		 */
		public function __construct($username = null, $password = null) {
			if ($username !== null) {
				$this->setUsername($username);
			}
			if ($password !== null) {
				$this->setPassword($password);
			}
		}

		/**
		 * Get the username to send.
		 * @return string The username to send.
		 */
		private final function getUsername() {
			return $this->username;
		}

		/**
		 * Set the username to send.
		 * @param string $username The username to send.
		 */
		public final function setUsername($username) {
			$this->username = (string)$username;
		}

		/**
		 * Get the password to send.
		 * @return string The password to send.
		 */
		private final function getPassword() {
			return $this->password;
		}

		/**
		 * Set the password to send.
		 * @param string $password The password to send.
		 */
		public final function setPassword($password) {
			$this->password = (string)$password;
		}

		/**
		 * Retrieve the credentials represented by this object as a string.
		 * @return string The credentials in the form '[username]:[password]'.
		 */
		public function __toString() {
			$username = $this->getUsername() == '' ? 'X' : $this->getUsername();
			$password = $this->getPassword() == '' ? 'X' : $this->getPassword();
			return $username . ':' . $password;
		}
	}
}
