<?php

namespace Crackle\Authentication\Applicators {

	/**
	 * Implemented by authentication mechanisms that can be used for HTTP requests themselves.
	 * @author George Brighton
	 */
	interface RequestCredentials {

		/**
		 * Configures a cURL session to use the request authentication mechanism represented by the object.
		 * @param resource $handle The handle to modify.
		 */
		public function addRequestCredentialsTo($handle);
	}
}
