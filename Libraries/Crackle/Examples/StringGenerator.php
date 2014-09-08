<?php

namespace Crackle\Examples {

	require '../Crackle.php';

	use \Crackle\Requests\GETRequest;
	use \Crackle\Exceptions\RequestException;

	use \OutOfBoundsException;
	use \RuntimeException;

	/*
	 * Example usage:
	 * $generator = new StringGenerator();
	 * print_r($generator->generate(10));
	 */

	/**
	 * Generates a truely random string using RANDOM.ORG.
	 * @author George Brighton
	 */
	class StringGenerator {

		/**
		 * The base URL of RANDOM.ORG's string generator.
		 * @var string
		 */
		private static $endpoint = 'http://www.random.org/strings';

		/**
		 * The length of the string to generate.
		 * Defaults to 10.
		 * @var integer
		 */
		private $length;

		/**
		 * Whether to include numbers in the string.
		 * Defaults to true.
		 * @var boolean
		 */
		private $digits;

		/**
		 * Whether to include lowercase alphabetic characters in the string.
		 * Defaults to true.
		 * @var boolean
		 */
		private $lowerAlpha;

		/**
		 * Whether to include uppercase alphabetic characters in the string.
		 * Defaults to true.
		 * @var boolean
		 */
		private $upperAlpha;

		/**
		 * Retrieve the length of the string to generate.
		 * @return integer The length of the string to generate.
		 */
		private final function getLength() {
			return $this->length;
		}

		/**
		 * Set the length of the string to generate.
		 * @param integer $length The new length of the string to generate, between 1 and 20 inclusive.
		 * @throws \OutOfBoundsException If the length is out of range.
		 */
		public final function setLength($length) {
			if ($length < 1 || $length > 20) {
				throw new OutOfBoundsException('The length must be between 1 and 20 inclusive.');
			}

			$this->length = (int)$length;
		}

		/**
		 * Find whether the string should contain numbers.
		 * @return boolean Whether the string should contain numbers.
		 */
		private final function getDigits() {
			return $this->digits;
		}

		/**
		 * Set whether the string should contain numbers.
		 * @param boolean $digits Whether the string should contain numbers.
		 */
		public final function setDigits($digits) {
			$this->digits = (bool)$digits;
		}

		/**
		 * Find whether the string should contain lowercase alphabetic characters.
		 * @return boolean Whether the string should contain lowercase alphabetic characters.
		 */
		private final function getLowerAlpha() {
			return $this->lowerAlpha;
		}

		/**
		 * Set whether the string should contain lowercase alphabetic characters.
		 * @param boolean $lowerAlpha Whether the string should contain lowercase alphabetic characters.
		 */
		public final function setLowerAlpha($lowerAlpha) {
			$this->lowerAlpha = (bool)$lowerAlpha;
		}

		/**
		 * Find whether the string should contain uppercase alphabetic characters.
		 * @return boolean Whether the string should contain uppercase alphabetic characters.
		 */
		private final function getUpperAlpha() {
			return $this->upperAlpha;
		}

		/**
		 * Set whether the string should contain uppercase alphabetic characters.
		 * @param boolean $upperAlpha Whether the string should contain uppercase alphabetic characters.
		 */
		public final function setUpperAlpha($upperAlpha) {
			$this->upperAlpha = (bool)$upperAlpha;
		}

		/**
		 * Initialise a new random string generator.
		 */
		public function __construct() {
			$this->setLength(10);
			$this->setDigits(true);
			$this->setLowerAlpha(true);
			$this->setUpperAlpha(true);
		}

		/**
		 * Generate one or more strings using the current settings.
		 * @param integer $quantity The number of strings to generate, between 1 and 10,000 inclusive.
		 * @throws \RuntimeException If a validation error occurrs, or RANDOM.ORG cannot be reached.
		 * @throws \OutOfBoundsException If the quantity is out of range.
		 * @return string array[string] generated string(s). If quantity is 1, this will be a string; otherwise it will be an array of strings.
		 */
		public function generate($quantity = 1) {
			if (!$this->getDigits() && !$this->getLowerAlpha() && !$this->getUpperAlpha()) {
				throw new RuntimeException('The string must contain digits, lowercase or uppercase letters.');
			}

			if ($quantity < 1 || $quantity > 10000) {
				throw new OutOfBoundsException('The quantity must be between 1 and 10,000 inclusive.');
			}

			$random = new GETRequest(self::$endpoint);
			$params = $random->getParameters();
			$params->set('num', (int)$quantity);
			$params->set('len', $this->getLength());
			$params->set('digits', $this->getDigits() ? 'on' : 'off');
			$params->set('loweralpha', $this->getLowerAlpha() ? 'on' : 'off');
			$params->set('upperalpha', $this->getUpperAlpha() ? 'on' : 'off');
			$params->set('unique', 'on');
			$params->set('format', 'plain');
			$params->set('rnd', 'new');

			try {
				$body = $random->getResponse()->getBody();

				if (substr($body, 0, 5) == 'Error') {
					throw new RuntimeException($body);
				}

				$strings = explode("\n", rtrim($body));
				return $quantity === 1 ? $strings[0] : $strings;
			}
			catch (RequestException $e) {
				throw new RuntimeException('Failed to reach RANDOM.ORG: ' . $e->getMessage());
			}
		}
	}
}
