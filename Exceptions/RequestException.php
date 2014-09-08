<?php

namespace Brush\Exceptions {

	use \Crackle\Requests\GETRequest;

	/**
	 * Represents a problem reaching Pastebin's API.
	 * @author George Brighton
	 */
	class RequestException extends BrushException {

		/**
		 * Initialise a new request exception with a message.
		 * @param \Crackle\Requests\GETRequest $request The request that failed.
		 */
		public function __construct(GETRequest $request) {
			parent::__construct('Request to Pastebin failed: ' . $request->getError());
		}
	}
}
