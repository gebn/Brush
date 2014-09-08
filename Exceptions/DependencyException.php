<?php

namespace Brush\Exceptions {

	/**
	 * Represents an error caused by Brush's environment.
	 * @author George Brighton
	 */
	class DependencyException extends BrushException {

		/**
		 * Initialise a new dependency exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
