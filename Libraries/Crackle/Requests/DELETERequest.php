<?php

namespace Crackle\Requests {

	/**
	 * Represents an HTTP request sent using the DELETE method.
	 * @author George Brighton
	 */
	class DELETERequest extends GETRequest {

		/**
		 * Initialise a new HTTP DELETE request.
		 * @param string $url An optional URL to initialise with.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 * @see \Crackle\Requests\GETRequest::finalise()
		 */
		public function finalise() {
			parent::finalise();
			curl_setopt($this->getHandle(), CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
	}
}
