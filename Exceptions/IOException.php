<?php

namespace Brush\Exceptions {

	/**
	 * Represents a file, network or stream related error.
	 * @author George Brighton
	 */
	class IOException extends BrushException {

		/**
		 * Initialise a new IO exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
