<?php

namespace Crackle\Authentication\Methods {

	use \Crackle\Authentication\Credentials;
	use \Crackle\Authentication\Applicators\RequestCredentials;

	/**
	 * Represents a set of HTTP digest authentication credentials.
	 * @author George Brighton
	 */
	class DigestCredentials extends Credentials implements RequestCredentials {

		/**
		 * Initialise a new set of digest credentials.
		 * @param string $username The username to send.
		 * @param string $password The password to send.
		 */
		public function __construct($username = null, $password = null) {
			parent::__construct($username, $password);
		}

		/**
		 * Add request credentials defined by this object to a cURL handle.
		 * @param resource $handle The cURL handle to configure.
		 * @see \Crackle\Authentication\Applicators\RequestCredentials::addRequestCredentialsTo()
		 */
		public function addRequestCredentialsTo($handle) {
			curl_setopt_array($handle, array(
					CURLOPT_USERPWD => $this->__toString(),
					CURLOPT_HTTPAUTH => CURLAUTH_DIGEST));
		}
	}
}
