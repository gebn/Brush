<?php

namespace Brush\Pastes {

	use \Brush\Exceptions\RequestException;
	use \Brush\Exceptions\ArgumentException;

	use \Crackle\Requests\GETRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMElement;

	/**
	 * Represents a paste retrieved from Pastebin.
	 * @author George Brighton
	 */
	class Paste extends AbstractPaste {

		/**
		 * The unique ID of this paste.
		 * @var string
		 */
		private $key;

		/**
		 * When this paste was submitted, as a UNIX timestamp.
		 * @var int
		 */
		private $date;

		/**
		 * The size of the content of this paste, in bytes.
		 * @var int
		 */
		private $size;

		/**
		 * The number of visits this paste has seen.
		 * @var int
		 */
		private $hits;

		/**
		 * When this paste expires, as a UNIX timestamp.
		 * @var int
		 */
		private $expires;

		/**
		 * Retrieve the unique ID of this paste.
		 * @return string The unique ID of this paste.
		 */
		private final function getKey() {
			return $this->key;
		}

		/**
		 * Retrieve the URL of this paste.
		 * @return string The URL of this paste.
		 */
		private function getUrl() {
			return 'http://pastebin.com/' . $this->getKey();
		}

		/**
		 * Set the unique ID of this paste.
		 * @param string $key The unique ID of this paste.
		 */
		private final function setKey($key) {
			$this->key = $key;
		}

		/**
		 * Set this paste's URL.
		 * @param string $url This paste's URL.
		 * @throws InvalidArgumentException If the URL isn't in the correct format.
		 */
		private final function setUrl($url) {
			$url = rtrim($url, '/');
			$slash = strrpos($url, '/');
			if ($slash === false) {
				throw new ArgumentException('Invalid paste URL.');
			}
			$this->setKey(substr($url, $slash + 1));
		}

		/**
		 * Find when this paste was submitted, as a UNIX timestamp.
		 * @return int When this paste was submitted, as a UNIX timestamp.
		 */
		private final function getDate() {
			return $this->date;
		}

		/**
		 * Set when this paste was submitted, as a UNIX timestamp.
		 * @param int $date When this paste was submitted, as a UNIX timestamp.
		 */
		private final function setDate($date) {
			$this->date = (int)$date;
		}

		/**
		 * Retrieve the size of the content of this paste, in bytes.
		 * @return int The size of the content of this paste, in bytes.
		 */
		private final function getSize() {
			return $this->size;
		}

		/**
		 * Set the size of the content of this paste, in bytes.
		 * @param int $size The size of the content of this paste, in bytes.
		 */
		private final function setSize($size) {
			$this->size = (int)$size;
		}

		/**
		 * Retrieve the number of visits this paste has seen.
		 * @return int The number of visits this paste has seen.
		 */
		private final function getHits() {
			return $this->hits;
		}

		/**
		 * Set the number of visits this paste has seen.
		 * @param int $hits The number of visits this paste has seen.
		 */
		private final function setHits($hits) {
			$this->hits = (int)$hits;
		}

		/**
		 * Find when this paste expires, as a UNIX timestamp.
		 * N.B. 0 means this paste never expires.
		 * @return int When this paste expires, as a UNIX timestamp.
		 */
		private final function getExpires() {
			return $this->expires;
		}

		/**
		 * Find whether this paste has no expiry date.
		 * @return boolean True if it will never expire, false if it will at some point.
		 */
		private final function isImmortal() {
			return $this->getExpires() == 0;
		}

		/**
		 * Find how long this paste expires in.
		 * @return int 0 if this paste never expires, otherwise the number of seconds in which it will expire.
		 */
		private final function getExpiresIn() {
			return $this->isImmortal() ? 0 : $this->getExpires() - time();
		}

		/**
		 * Set when this paste expires, as a UNIX timestamp.
		 * @param int $expires When this paste expires, as a UNIX timestamp.
		 */
		private final function setExpires($expires) {
			$this->expires = (int)$expires;
		}

		/**
		 * Direct outside initialisation of this class is prohibited.
		 */
		protected function __construct() {}

		/**
		 * Retrieve the content of this paste.
		 * @return string The content of this paste.
		 */
		public function getContent() {
			if (parent::getContent() === null) {
				$this->loadContent();
			}
			return parent::getContent();
		}

		/**
		 * Load the content of this paste.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin fails.
		 */
		private final function loadContent() {
			$request = new GETRequest('http://pastebin.com/raw.php');
			$request->getParameters()->set('i', $this->getKey());

			try {
				$this->setContent($request->getResponse()->getBody());
			}
			catch (CrackleRequestException $e) {
				throw new RequestException($request);
			}
		}

		/**
		 * Create a paste instance from its XML representation.
		 * @param \DOMElement $element The `<paste>` element to parse.
		 * @return \Brush\Pastes\Paste The created paste.
		 */
		public static function fromXml(DOMElement $element) {
			$paste = new Paste();
			$paste->parse($element);
			return $paste;
		}

		/**
		 * Apply the values stored in a chunk of XML to this object.
		 * @param \DOMElement $paste The `<paste>` element from Pastebin to parse.
		 */
		protected function parse(DOMElement $paste) {
			parent::parse($paste);
			$this->setDate($paste->getElementsByTagName('paste_date')->item(0)->nodeValue);
			$this->setSize($paste->getElementsByTagName('paste_size')->item(0)->nodeValue);
			$this->setExpires($paste->getElementsByTagName('paste_expire_date')->item(0)->nodeValue);
			$this->setHits($paste->getElementsByTagName('paste_hits')->item(0)->nodeValue);
		}

		/**
		 * Create a paste instance from a key and draft.
		 * @param string $url The URL of the paste.
		 * @param \Brush\Pastes\Draft $draft The draft to import.
		 * @return \Brush\Pastes\Paste The created paste.
		 */
		public static function fromPasted($url, Draft $draft) {
			$paste = new Paste();
			$paste->import($draft);
			$paste->setUrl($url);
			$paste->setDate(time());
			$paste->setHits(0);

			$offset = Expiry::getOffset($draft->getExpiry());
			$paste->setExpires($offset == 0 ? 0 : $paste->getDate() + $offset);

			return $paste;
		}

		/**
		 * Copy properties from a draft paste.
		 * @param \Brush\Pastes\Draft $draft The draft to copy from.
		 */
		private function import(Draft $draft) {
			$this->setTitle($draft->getTitle());
			$this->setContent($draft->getContent());
			$this->setSize(strlen($draft->getContent()));
			$this->setFormat($draft->getFormat());
			$this->setVisibility($draft->getVisibility());
		}
	}
}
