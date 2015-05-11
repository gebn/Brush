<?php

namespace Brush\Exceptions {

	/**
	 * Represents an error returned to us by Pastebin's API.
	 * @author George Brighton
	 */
	class ApiException extends BrushException {

		/**
		 * Initialise a new API exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
