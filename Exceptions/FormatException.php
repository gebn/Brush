<?php

namespace Brush\Exceptions {

	/**
	 * Represents a problem related to a paste's format.
	 * @author George Brighton
	 */
	class FormatException extends BrushException {

		/**
		 * Initialise a new format exception with message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
