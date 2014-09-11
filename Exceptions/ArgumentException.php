<?php

namespace Brush\Exceptions {

	/**
	 * Represents an issue with an argument passed to a method.
	 * @author George Brighton
	 */
	class ArgumentException extends BrushException {

		/**
		 * Initialise a new argument exception with message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
