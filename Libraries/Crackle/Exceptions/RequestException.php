<?php

namespace Crackle\Exceptions {

	/**
	 * Represents an error directly related to a request or its execution.
	 * @author George Brighton
	 */
	class RequestException extends CrackleException {

		/**
		 * Initialise a new request exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
