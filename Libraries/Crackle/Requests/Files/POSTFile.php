<?php

namespace Crackle\Requests\Files {

	use \Crackle\Requests\Parts\POSTRequestPart;

	/**
	 * Represents a file that can be sent as part of a POST request.
	 * @author George Brighton
	 */
	class POSTFile extends File implements POSTRequestPart {

		/**
		 * The name of this file, including extension.
		 * @var string
		 */
		private $name;

		/**
		 * The raw content of this file.
		 * @var string
		 */
		private $content;

		/**
		 * Get the name of this file.
		 * @return string The name of this file, including extension.
		 */
		private final function getName() {
			return $this->name;
		}

		/**
		 * Set the name of this file.
		 * @param string $name The name of this file, including extension.
		 */
		public final function setName($name) {
			$this->name = (string)$name;
		}
		/**
		 * Get the raw content of this file.
		 * @return string The raw content of this file.
		 */
		private final function getContent() {
			return $this->content;
		}

		/**
		 * Set the raw content of this file.
		 * @param string $content The raw content of this file.
		 */
		public final function setContent($content) {
			$this->content = (string)$content;
		}

		/**
		 * Initialise a new POSTFile.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Add this file to a multipart request.
		 * @param array $lines The lines array to append to.
		 * @param string $name The field name of this file within the request.
		 */
		public function appendPart(array &$lines, $name) {
			$lines[] = 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $this->getName() . '"';
			$lines[] = 'Content-Type: ' . $this->getMimeType();
			$lines[] = '';
			$lines[] = $this->getContent();
		}

		/**
		 * Get a file object representing a file at a location.
		 * @param string $path The absolute or relative (to this script) path to the file.
		 * @return \Crackle\Requests\Parts\Files\POSTFile The created object.
		 */
		public static function factory($path) {
			$file = parent::factory($path);
			$file->setName(basename($path));
			$file->setContent(file_get_contents($path));
			return $file;
		}
	}
}
