<?php

namespace Crackle\Requests\Parts {

	/**
	 * Represents the value of a single POST variable.
	 * @author George Brighton
	 */
	class POSTVariable implements POSTRequestPart {

		/**
		 * The value of this POST variable.
		 * @var string
		 */
		private $value;

		/**
		 * Retrieve the value of this POST variable.
		 * @return string The value of this POST variable.
		 */
		private final function getValue() {
			return $this->value;
		}

		/**
		 * Set the value of this POST variable.
		 * @param string $value The new value of this POST variable.
		 */
		private final function setValue($value) {
			$this->value = (string)$value;
		}

		/**
		 * Initialise a new POST variable.
		 * @param string $value The value of this POST variable.
		 */
		public function __construct($value) {
			$this->setValue($value);
		}

		/*
		 * @see \Crackle\Requests\Parts\POSTRequestPart::appendPart()
		 */
		public function appendPart(array &$lines, $name) {
			$lines[] = 'Content-Disposition: form-data; name="' . $name . '"';
			$lines[] = '';
			$lines[] = $this->getValue();
		}
	}
}
