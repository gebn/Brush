<?php

namespace Crackle\Requests {

	use \Crackle\Requests\Files\PUTFile;

	use \Exception;

	/**
	 * Represents an HTTP request sent using the PUT method.
	 * @author George Brighton
	 */
	class PUTRequest extends GETRequest {

		/**
		 * The file to send in this request (optional).
		 * @var \Crackle\Requests\Files\PUTFile
		 */
		private $file;

		/**
		 * Get the file to send in this request.
		 * @return \Crackle\Requests\Files\PUTFile The file to send in this request.
		 */
		private final function getFile() {
			return $this->file;
		}

		/**
		 * Set the file to send in this request.
		 * @param \Crackle\Requests\Files\PUTFile $file The file to send in this request.
		 */
		public final function setFile(PUTFile $file) {
			$this->file = $file;
		}

		/**
		 * Initialise a new HTTP PUT request.
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
			curl_setopt($this->getHandle(), CURLOPT_PUT, true);

			// the PUT file is optional
			if($this->getFile() !== null) {
				$this->getFile()->addTo($this->getHandle());
			}
		}
	}
}
