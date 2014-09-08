<?php

namespace Crackle\Exceptions {

	use \Exception;

	/**
	 * Represents a generic exception thrown by Crackle.
	 * N.B. Crackle also throws some SPL exceptions.
	 * @author George Brighton
	 */
	abstract class CrackleException extends Exception {

		/**
		 * Initialise a new Crackle exception with message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
