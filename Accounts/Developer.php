<?php

namespace Brush\Accounts {

	use \Crackle\Requests\POSTRequest;

	/**
	 * Represents an API developer account.
	 * @author George Brighton
	 */
	class Developer {

		/**
		 * The developer API key for this account.
		 * @var string
		 */
		private $key;

		/**
		 * Retrieve the developer API key for this account.
		 * @return string The developer API key for this account.
		 */
		private final function getKey() {
			return $this->key;
		}

		/**
		 * Set the developer API key for this account.
		 * @param string $key The new developer API key for this account.
		 */
		private final function setKey($key) {
			$this->key = $key;
		}

		/**
		 * Initialise a new developer account.
		 * @param string $key The API key for this account.
		 */
		public function __construct($key) {
			$this->setKey($key);
		}

		/**
		 * Add the credentials contained in this object to a request.
		 * @param \Crackle\Requests\POSTRequest $request The request to authenticate.
		 */
		public final function sign(POSTRequest $request) {
			$request->getVariables()->set('api_dev_key', $this->getKey());
		}
	}
}
