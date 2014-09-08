<?php

namespace Crackle\Requests {

	use \Crackle\Requests\Fields\Fields;
	use \Crackle\Requests\Parts\POSTVariable;
	use \Crackle\Requests\Files\POSTFile;

	/**
	 * Represents an HTTP request sent using the POST method.
	 * @author George Brighton
	 */
	class POSTRequest extends GETRequest {

		/**
		 * The raw content body of this request.
		 * @var string
		 */
		private $body;

		/**
		 * The POST variables to send with this request.
		 * @var \Crackle\Requests\Fields\Fields
		 */
		private $variables;

		/**
		 * POST files to send with this request.
		 * @var \Crackle\Requests\Fields\Fields
		 */
		private $files;

		/**
		 * Retrieve the raw content body of this request.
		 * @return string The raw content body of this request.
		 */
		private final function getBody() {
			return $this->body;
		}

		/**
		 * Set the raw content body of the request.
		 * @param string $body The new raw content body of this request.
		 */
		public final function setBody($body) {
			$this->body = $body;
		}

		/**
		 * Retrieve the POST variables to send with this request.
		 * @return \Crackle\Requests\Fields\Fields The POST variables to send with this request.
		 */
		public final function getVariables() {
			return $this->variables;
		}

		/**
		 * Set the POST variables to send with this request.
		 * @param \Crackle\Requests\Fields\Fields $variables The new POST variables to send with this request.
		 */
		private final function setVariables(Fields $variables) {
			$this->variables = $variables;
		}

		/**
		 * Get the files to send with this request.
		 * @return \Crackle\Requests\Fields\Fields The files to send with this request.
		 */
		public final function getFiles() {
			return $this->files;
		}

		/**
		 * Set the files to send with this request.
		 * @param \Crackle\Requests\Fields\Fields The $files files to send with this request.
		 */
		private final function setFiles(Fields $files) {
			$this->files = $files;
		}

		/**
		 * Initialise a new HTTP POST request.
		 * @param string $url Optional: the URL to send this request to.
		 */
		public function __construct($url = null) {
			parent::__construct($url);
			$this->setVariables(new Fields());
			$this->setFiles(new Fields());
		}

		/**
		 * Push all data contained in this object to the handle.
		 * Called just prior to sending the request.
		 * @see \Crackle\Requests\GETRequest::finalise()
		 */
		public function finalise() {
			$this->buildRequest();
			parent::finalise();
		}

		/**
		 * Creates the content of this POST request.
		 * Adapted from Beau Simensen's function on GitHub: https://gist.github.com/simensen/288242
		 */
		private function buildRequest() {
			// we only need to do this if using Crackle's files and fields modules
			if ($this->getBody() === null) {
				$boundary = self::generateBoundary();
				$this->setBody($this->buildBody($boundary));

				$headers = $this->getHeaders();
				$headers->set('Content-Length', strlen($this->getBody()));
				$headers->set('Content-Type', 'multipart/form-data; boundary=' . $boundary);
			}

			curl_setopt_array($this->getHandle(), array(
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $this->getBody()));
		}

		/**
		 * Creates a boundary to divide parts of the request.
		 * @return string The generated boundary.
		 */
		private static function generateBoundary() {
			$algorithms = hash_algos();
			$preferences = array('sha1', 'md5');
			foreach ($preferences as $algorithm) {
				if (in_array($algorithm, $algorithms)) {
					return '----------------------------' . substr(hash('sha1', 'crackle' . microtime()), 0, 12);
				}
			}
			return '----------------------------' . substr(hash($algorithms[0], 'crackle' . microtime()), 0, 12);
		}

		/**
		 * Builds the lines of the request content.
		 * @param string $boundary The boundary to use to divide parts.
		 * @return string The created body.
		 */
		private function buildBody($boundary) {
			// will contain lines that make up this request
			$lines = array();

			// add variables
			foreach ($this->getVariables()->getPairs() as $pair) {
				$lines[] = '--' . $boundary;
				$variable = new POSTVariable($pair->getValue());
				$variable->appendPart($lines, $pair->getKey());
			}

			// add files
			foreach ($this->getFiles()->getPairs() as $pair) {
				$lines[] = '--' . $boundary;
				$file = $pair->getValue() instanceof POSTFile ? $pair->getValue() : POSTFile::factory($pair->getValue());
				$file->appendPart($lines, $pair->getKey());
			}

			$lines[] = '--' . $boundary . '--';
			$lines[] = '';

			return implode("\r\n", $lines);
		}
	}
}
