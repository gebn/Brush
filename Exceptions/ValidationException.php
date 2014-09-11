<?php

namespace Brush\Exceptions {

	/**
	 * Thrown when an internal error is discovered in a request.
	 * @author George Brighton
	 */
	class ValidationException extends BrushException {

		/**
		 * Initialise a new header not found exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
