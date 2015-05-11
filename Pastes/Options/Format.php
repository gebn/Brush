<?php

namespace Brush\Pastes\Options {

	use \Brush\Configuration\Loader;
	use \Brush\Exceptions\FormatException;

	use \Crackle\Requests\POSTRequest;

	use \DOMElement;

	/**
	 * Represents the format of a paste.
	 * @author George Brighton
	 */
	class Format {

		/**
		 * The relative path of the supported formats file within the configuration directory.
		 * @var string
		 */
		const FORMATS_INI = 'formats.ini';

		/**
		 * The relative path of the extension mappings configuration file within the configuration directory.
		 * @var string
		 */
		const EXTENSIONS_INI = 'extensions.ini';

		/**
		 * The short name of this format, e.g. '6502acme'.
		 * @var string
		 */
		private $code;

		/**
		 * The longer, more descriptive name of this format, e.g. '6502 ACME Cross Assembler'.
		 * @var string
		 */
		private $name;

		/**
		 * Retrieve the short name of this format.
		 * @return string The short name of this format.
		 */
		public final function getCode() {
			return $this->code;
		}

		/**
		 * Set the short name of this format.
		 * @param string $code The new short name of this format.
		 */
		private final function setCode($code) {
			$this->code = $code;
		}

		/**
		 * Retrieve the longer, more descriptive name of this format.
		 * @return string The longer, more descriptive name of this format.
		 */
		public final function getName() {
			return $this->name;
		}

		/**
		 * Set the longer, more descriptive name of this format.
		 * @param string $name The new longer, more descriptive name of this format.
		 */
		private final function setName($name) {
			$this->name = $name;
		}

		/**
		 * Initialise a new format.
		 * @param string $code The short name of this format.
		 * @param string $name The longer, more descriptive name of this format.
		 */
		private function __construct($code, $name) {
			$this->setCode($code);
			$this->setName($name);
		}

		/**
		 * Attach this format to a new paste request.
		 * @param POSTRequest $request The request to add this format to.
		 */
		public function addTo(POSTRequest $request) {
			$request->getVariables()->set('api_paste_format', $this->getCode());
		}

		/**
		 * Retrieve a format instance from its code.
		 * @param string $code The code to get the corresponding full format of.
		 * @return \Brush\Pastes\Options\Format The created format.
		 * @throws \Brush\Exceptions\BrushException If the code does not match a supported format.
		 */
		public static function fromCode($code) {
			$formats = Loader::get(self::FORMATS_INI);
			if (!isset($formats[$code])) {
				throw new FormatException(sprintf('\'%s\' is not a recognised format code.', $code));
			}
			return new Format($code, $formats[$code]);
		}

		/**
		 * Retrieve a format by a file extension.
		 * @param string $extension The file extension to search for. No leading dot.
		 * @return \Brush\Pastes\Options\Format The created format.
		 */
		public static function fromExtension($extension) {
			$formats = Loader::get(self::EXTENSIONS_INI);

			// search the list of mappings
			if (isset($formats[$extension])) {
				return self::fromCode($formats[$extension]);
			}

			// try using the code directly; if it fails, let the exception bubble up
			return self::fromCode($extension);
		}

		/**
		 * Retrieve a format from a paste's XML.
		 * @param \DOMElement $paste The `<paste>` element to parse.
		 * @return \Brush\Pastes\Options\Format The created format.
		 */
		public static function fromXml(DOMElement $paste) {
			return self::fromCode($paste->getElementsByTagName('paste_format_short')->item(0)->nodeValue);
		}

		/**
		 * Get the string representation of this object.
		 * @return string This format's long name.
		 */
		public function __toString() {
			return $this->getName();
		}
	}
}
