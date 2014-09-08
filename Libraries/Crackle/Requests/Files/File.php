<?php

namespace Crackle\Requests\Files {

	use \Crackle\Exceptions\IOException;

	use \Exception;

	/**
	 * Represents a file that can be attached to a request.
	 * @author George Brighton
	 */
	abstract class File {

		/**
		 * The MIME type of this file.
		 * @var string
		 */
		private $mimeType;

		/**
		 * Get the MIME type of this file.
		 * @return string The MIME type of this file.
		 */
		protected final function getMimeType() {
			return $this->mimeType;
		}

		/**
		 * Set the MIME type of this file.
		 * @param string $mimeType The MIME type of this file.
		 */
		public final function setMimeType($mimeType) {
			$this->mimeType = (string)$mimeType;
		}

		/**
		 * Initialise this file with a default MIME type.
		 */
		public function __construct() {
			$this->setMimeType('application/octet-stream');
		}

		/**
		 * Create a new file object representing a path.
		 * @param string $path The path to read from.
		 * @return \Crackle\Requests\Parts\Files\File The created file.
		 * @throws \Crackle\Exceptions\IOException If the path does not point to a regular, readable file.
		 */
		public static function factory($path) {
			if(!is_file($path)) {
				throw new IOException('The path must be the path of a file.');
			}

			if(!is_readable($path)) {
				throw new IOException('The supplied file path cannot be read.');
			}

			$file = new static();
			if(extension_loaded('fileinfo')) {
				$file->setMimeType(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path));
			}
			return $file;
		}

		/**
		 * Set the content of this file.
		 * @param string $content The new content to set.
		 */
		public abstract function setContent($content);
	}
}
