<?php

namespace Crackle\Authentication\Applicators {

	/**
	 * Implemented by authentication mechanisms that can be used for proxies.
	 * @author George Brighton
	 */
	interface ProxyCredentials {

		/**
		 * Configures a cURL session to use the proxy authentication mechanism represented by the object.
		 * @param resource $handle The handle to modify.
		 */
		public function addProxyCredentialsTo($handle);
	}
}
