<?php

namespace Brush\Pastes {

	use \Brush\Accounts\Developer;
	use \Brush\Accounts\Account;
	use \Brush\Pastes\Options\Format;
	use \Brush\Pastes\Options\Visibility;
	use \Brush\Exceptions\ValidationException;

	use \Crackle\Requests\POSTRequest;

	use \DOMElement;

	/**
	 * Represents a generic paste.
	 * @author George Brighton
	 */
	abstract class AbstractPaste {

		/**
		 * The name of this paste.
		 * @var string
		 */
		private $title;

		/**
		 * The content of this paste.
		 * @var string
		 */
		private $content;

		/**
		 * The format of this paste.
		 * @var \Brush\Pastes\Options\Format
		 */
		private $format;

		/**
		 * The visibility of this paste.
		 * @var int
		 */
		private $visibility;

		/**
		 * The account that owns this paste.
		 * This is required if the visibility of this paste is private.
		 * @var \Brush\Accounts\Account
		 */
		private $owner;

		/**
		 * Retrieve the name of this paste.
		 * @return string The name of this paste.
		 */
		public final function getTitle() {
			return $this->title;
		}

		/**
		 * Set the name of this paste.
		 * @param string $title The name of this paste.
		 */
		public final function setTitle($title) {
			$this->title = $title;
		}

		/**
		 * Retrieve the content of this paste.
		 * @return string The content of this paste.
		 */
		public function getContent() {
			return $this->content;
		}

		/**
		 * Set the content of this paste.
		 * @param string $content The content of this paste.
		 */
		public final function setContent($content) {
			$this->content = $content;
		}

		/**
		 * Retrieve the format of this paste.
		 * @return \Brush\Pastes\Options\Format The format of this paste.
		 */
		public final function getFormat() {
			return $this->format;
		}

		/**
		 * Set the format of this paste.
		 * @param \Brush\Pastes\Options\Format $format The format of this paste.
		 */
		public final function setFormat(Format $format) {
			$this->format = $format;
		}

		/**
		 * Retrieve the visibility of this paste.
		 * @return int The visibility of this paste.
		 */
		public final function getVisibility() {
			return $this->visibility;
		}

		/**
		 * Set the visibility of this paste.
		 * @param int $visibility The visibility of this paste.
		 */
		public final function setVisibility($visibility) {
			$this->visibility = (int)$visibility;
		}

		/**
		 * Retrieve the account that owns this paste.
		 * @return \Brush\Accounts\Account The account that owns this paste.
		 */
		public final function getOwner() {
			return $this->owner;
		}

		/**
		 * Find whether this paste has an owner set.
		 * If it doesn't, it cannot have private visibility.
		 * @return boolean True if this paste has an assigned owner; false otherwise.
		 */
		public final function hasOwner() {
			return $this->getOwner() !== null;
		}

		/**
		 * Set the account that owns this paste.
		 * @param \Brush\Accounts\Account $owner The account that owns this paste.
		 */
		public final function setOwner(Account $owner) {
			$this->owner = $owner;
		}

		/**
		 * Initialise properties to defaults.
		 */
		protected function __construct() {
			$this->setTitle('Untitled');
			$this->setFormat(Format::fromCode('text'));
			$this->setVisibility(Visibility::VISIBILITY_PUBLIC);
		}

		/**
		 * Ensure this paste is ready to be sent to Pastebin.
		 * These checks are not exhaustive - they don't scan for setting invalid expirations or visibilities for example.
		 * @throws \Brush\Exceptions\ValidationException If any errors are found; check the message for details.
		 */
		protected function validate() {
			if ($this->getContent() == null) { // includes the empty string
				throw new ValidationException('The paste must have some content.');
			}
		}

		/**
		 * Apply the values stored in a chunk of XML to this object.
		 * @param \DOMElement $paste The `<paste>` element from Pastebin to parse.
		 */
		protected function parse(DOMElement $paste) {
			$this->setTitle($paste->getElementsByTagName('paste_title')->item(0)->nodeValue);
			$this->setFormat(Format::fromXml($paste));
			$this->setVisibility($paste->getElementsByTagName('paste_private')->item(0)->nodeValue);
		}

		/**
		 * Add the values in this paste to a new paste request.
		 * @param \Crackle\Requests\POSTRequest $request The request to add variables to.
		 * @param \Brush\Accounts\Developer $developer The developer that will send the request.
		 */
		protected function addTo(POSTRequest $request, Developer $developer) {
			$variables = $request->getVariables();
			$variables->set('api_paste_name', $this->getTitle());
			$variables->set('api_paste_code', $this->getContent());
			$variables->set('api_paste_private', $this->getVisibility());
			$this->getFormat()->addTo($request);
			if ($this->hasOwner()) {
				$this->getOwner()->sign($request, $developer);
			}
		}
	}
}
