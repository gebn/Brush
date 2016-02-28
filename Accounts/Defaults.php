<?php

namespace Brush\Accounts {

	use \Brush\Pastes\Draft;
	use \Brush\Pastes\Options\Format;
	use \Brush\Pastes\Options\Visibility;
	use \Brush\Pastes\Options\Expiry;

	use \DOMElement;

	/**
	 * Contains default paste settings for an account.
	 * @author George Brighton
	 */
	class Defaults {

		/**
		 * The format of new pastes.
		 * @var \Brush\Pastes\Options\Format
		 */
		private $format;

		/**
		 * The visibility of new pastes.
		 * @var int
		 */
		private $visibility;

		/**
		 * The expiry time of new pastes.
		 * @var string
		 */
		private $expiry;

		/**
		 * Retrieve the format of new pastes.
		 * @return \Brush\Pastes\Options\Format The format of new pastes.
		 */
		private final function getFormat() {
			return $this->format;
		}

		/**
		 * Set the format of new pastes.
		 * @param \Brush\Pastes\Options\Format $format The format of new pastes.
		 */
		private final function setFormat(Format $format) {
			$this->format = $format;
		}

		/**
		 * Retrieve the visibility of new pastes.
		 * @return int The visibility of new pastes.
		 */
		private final function getVisibility() {
			return $this->visibility;
		}

		/**
		 * Set the visibility of new pastes.
		 * @param int $visibility The visibility of new pastes.
		 */
		private final function setVisibility($visibility) {
			$this->visibility = (int)$visibility;
		}

		/**
		 * Retrieve the expiry time of new pastes.
		 * @return string The expiry time of new pastes.
		 */
		private final function getExpiry() {
			return $this->expiry;
		}

		/**
		 * Set the expiry time of new pastes.
		 * @param string $expiry The expiry time of new pastes.
		 */
		private final function setExpiry($expiry) {
			$this->expiry = $expiry;
		}

		/**
		 * Apply the default settings contained in this object to a draft paste.
		 * @param \Brush\Pastes\Draft $draft The draft to modify.
		 */
		public function applyTo(Draft $draft) {
			$draft->setFormat($this->getFormat());
			$draft->setVisibility($this->getVisibility());
			$draft->setExpiry($this->getExpiry());
		}

		/**
		 * Import information from a <user> node.
		 * @param \DOMElement $user The user element to parse.
		 */
		protected function parse(DOMElement $user) {
			$this->setFormat(Format::fromCode($user->getElementsByTagName('user_format_short')->item(0)->nodeValue));
			$this->setVisibility($user->getElementsByTagName('user_private')->item(0)->nodeValue);
			$this->setExpiry($user->getElementsByTagName('user_expiration')->item(0)->nodeValue);
		}

		/**
		 * Create a new defaults instance from user XML.
		 * @param \DOMElement $element The `<user>` node to parse.
		 * @return \Brush\Accounts\Defaults The created settings container.
		 */
		public static final function fromXml(DOMElement $element) {
			$defaults = new Defaults();
			$defaults->parse($element);
			return $defaults;
		}

		/**
		 * Get a plaintext string representation of these defaults.
		 * @return string The paste defaults.
		 */
		public function __toString() {
			$defaults = 'Paste Defaults:' . PHP_EOL;
			$defaults .= "\t" . 'Format: ' . $this->getFormat()->getName() . PHP_EOL;
			$defaults .= "\t" . 'Visibility: ' . Visibility::toString($this->getVisibility()) . PHP_EOL;
			$defaults .= "\t" . 'Expiry: ' . Expiry::toString($this->getExpiry());
			return $defaults;
		}
	}
}
