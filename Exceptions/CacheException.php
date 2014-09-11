<?php

namespace Brush\Exceptions {

	/**
	 * Represents a cache issue.
	 * @author George Brighton
	 */
	class CacheException extends BrushException {

		/**
		 * Initialise a new cache exception with message.
		 * @param string $message A description of why this exception is being created.
		 */
		public function __construct($message) {
			parent::__construct($message);
		}
	}
}
