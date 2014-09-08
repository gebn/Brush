<?php

namespace Crackle\Exceptions {

	/**
	 * Represents an I/O error, for example network or stream related.
	 * @author George Brighton
	 */
	class IOException extends CrackleException {

		/**
		 * Initialise a new I/O exception with a message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
