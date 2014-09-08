<?php

namespace Crackle\Exceptions {

	/**
	 * Represents an error caused by Crackle's environment.
	 * @author George Brighton
	 */
	class DependencyException extends CrackleException {

		/**
		 * Initialise a new dependency exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
