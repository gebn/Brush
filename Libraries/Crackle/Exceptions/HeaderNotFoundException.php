<?php

namespace Crackle\Exceptions {

	/**
	 * Thrown when we are asked to provide a header that does not exist.
	 * @author George Brighton
	 */
	class HeaderNotFoundException extends CrackleException {

		/**
		 * Initialise a new header not found exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
