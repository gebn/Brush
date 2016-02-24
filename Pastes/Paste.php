<?php

namespace Brush\Pastes {

	use \Brush\Accounts\Account;
	use \Brush\Accounts\Developer;
	use \Brush\Pastes\Options\Expiry;
	use \Brush\Pastes\Options\Visibility;
	use \Brush\Utilities\ApiRequest;
	use \Brush\Exceptions\ArgumentException;
	use \Brush\Exceptions\RequestException;
	use \Brush\Exceptions\ValidationException;

	use \Crackle\Requests\GETRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMElement;

	/**
	 * Represents a paste retrieved from Pastebin.
	 * @author George Brighton
	 */
	class Paste extends AbstractPaste {

		/**
		 * Pastebin's relative endpoint for retrieving the content of private pastes.
		 * @var string
		 */
		const PRIVATE_CONTENTS_ENDPOINT = 'api_raw.php';

		/**
		 * Pastebin's relative endpoint for deleting pastes.
		 * @var string
		 */
		const DELETE_ENDPOINT = 'api_post.php';

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
		public final function getKey() {
			return $this->key;
		}

		/**
		 * Retrieve the URL of this paste.
		 * @return string The URL of this paste.
		 */
		public function getUrl() {
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
		 * @throws \Brush\Exceptions\ArgumentException If the URL isn't in the correct format.
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
		public final function getDate() {
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
		public final function getSize() {
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
		public final function getHits() {
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
		public final function getExpires() {
			return $this->expires;
		}

		/**
		 * Find whether this paste has no expiry date.
		 * @return boolean True if it will never expire, false if it will at some point.
		 */
		public final function isImmortal() {
			return $this->getExpires() == 0;
		}

		/**
		 * Find how long this paste expires in.
		 * @return int 0 if this paste never expires, otherwise the number of seconds in which it will expire.
		 */
		public final function getExpiresIn() {
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
		protected function __construct() {
			parent::__construct();
		}

		/**
		 * Retrieve the content of this paste.
		 * @param \Brush\Accounts\Developer $developer If this is a private paste and the content is being accessed
		 *                                             for the first time, a developer must be passed.
		 * @return string The content of this paste.
		 * @throws \Brush\Exceptions\ValidationException If this paste is private.
		 */
		public function getContent(Developer $developer = null) {
			if (parent::getContent() === null) {
				if ($this->getVisibility() === Visibility::VISIBILITY_PRIVATE) {
					if ($developer === null) {
						throw new ValidationException('A developer instance must be passed the first time the contents '
							. 'of a private paste are retrieved');
					}
					$this->setContent(self::getPrivateContent($developer, $this));
				}
				else {
					$this->setContent(self::getPublicContent($this));
				}
			}

			return parent::getContent();
		}

		/**
		 * Retrieve the content of a public or unlisted paste.
		 * @param \Brush\Pastes\Paste $paste The paste whose content to retrieve.
		 * @return string The paste content.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin fails.
		 */
		private static final function getPublicContent(Paste $paste) {
			assert($paste->getVisibility() === Visibility::VISIBILITY_PUBLIC
				|| $paste->getVisibility() === Visibility::VISIBILITY_UNLISTED);

			$request = new GETRequest('http://pastebin.com/raw/' . $paste->getKey());

			try {
				return $request->getResponse()->getBody();
			}
			catch (CrackleRequestException $e) {
				throw new RequestException($request);
			}
		}

		/**
		 * Retrieve the content of a public or unlisted paste.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @param \Brush\Pastes\Paste $paste The paste whose content to retrieve.
		 * @return string The paste content.
		 */
		private static final function getPrivateContent(Developer $developer, Paste $paste) {
			assert($paste->getVisibility() === Visibility::VISIBILITY_PRIVATE);

			$pastebin = new ApiRequest($developer, self::PRIVATE_CONTENTS_ENDPOINT);
			$pastebin->setOption('show_paste');
			$pastebin->getRequest()->getVariables()->set('api_paste_key', $paste->getKey());
			$paste->getOwner()->sign($pastebin->getRequest(), $developer);
			return $pastebin->send();
		}

		/**
		 * Create a paste instance from its XML representation.
		 * @param \DOMElement $element The `<paste>` element to parse.
		 * @param \Brush\Accounts\Account $owner The optional owner of this paste. Omit if anonymous.
		 * @return \Brush\Pastes\Paste The created paste.
		 */
		private static function fromXml(DOMElement $element, Account $owner = null) {
			$paste = new Paste();
			$paste->parse($element);
			if ($owner !== null) {
				$paste->setOwner($owner);
			}
			return $paste;
		}

		/**
		 * Parse a successful paste listing response.
		 * @param string $response The response to parse.
		 * @param \Brush\Accounts\Account $owner The optional owner of these pastes.
		 * @return \Brush\Pastes\Paste[] The parsed pastes.
		 */
		public static function parsePastes($response, Account $owner = null) {
			if ($response == 'No pastes found.') {
				return array();
			}

			$pastes = array();
			foreach (ApiRequest::toElement('<pastes>' . $response . '</pastes>')->getElementsByTagName('paste') as $paste) {
				$pastes[] = Paste::fromXml($paste, $owner);
			}
			return $pastes;
		}

		/**
		 * Apply the values stored in a chunk of XML to this object.
		 * @param \DOMElement $paste The `<paste>` element from Pastebin to parse.
		 */
		protected function parse(DOMElement $paste) {
			parent::parse($paste);
			$this->setKey($paste->getElementsByTagName('paste_key')->item(0)->nodeValue);
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

			if ($draft->hasOwner()) {
				$this->setOwner($draft->getOwner());
			}
		}

		/**
		 * Remove this paste from Pastebin.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @throws \Brush\Exceptions\ValidationException If this paste does not have an owner
		 *                                               (e.g. it was retrieved via the trending pastes API).
		 */
		public function delete(Developer $developer) {

			// we can only delete pastes for which an Account is set
			if (!$this->hasOwner()) {
				throw new ValidationException('A paste must have an owner to be deleted.');
			}

			$pastebin = new ApiRequest($developer, self::DELETE_ENDPOINT);
			$pastebin->setOption('delete');

			$request = $pastebin->getRequest();
			$this->getOwner()->sign($request, $developer);
			$request->getVariables()->set('api_paste_key', $this->getKey());

			$pastebin->send();
		}
	}
}
