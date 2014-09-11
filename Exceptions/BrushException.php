<?php

namespace Brush\Exceptions {

	use \Exception;

	/**
	 * Represents a generic exception thrown by Brush.
	 * @author George Brighton
	 */
	abstract class BrushException extends Exception {

		/**
		 * Initialise a new Brush exception with message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
