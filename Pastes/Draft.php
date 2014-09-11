<?php

namespace Brush\Pastes {

	use \Brush\Brush;
	use \Brush\Accounts\Account;
	use \Brush\Accounts\Developer;
	use \Brush\Exceptions\ApiException;
	use \Brush\Exceptions\RequestException;
	use \Brush\Exceptions\ValidationException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMDocument;

	/**
	 * Represents a paste yet to be sent to Pastebin.
	 * @author George Brighton
	 */
	class Draft extends AbstractPaste {

		/**
		 * The Pastebin API's relative endpoint for submitting new pastes.
		 * @var string
		 */
		const ENDPOINT = 'api_post.php';

		/**
		 * When this paste expires.
		 * @var string
		 */
		private $expiry;

		/**
		 * The account that owns this paste.
		 * This is required if the visibility of this paste is private.
		 * @var \Brush\Accounts\Account
		 */
		private $owner;

		/**
		 * Find when this paste expires.
		 * @return string When this paste expires.
		 */
		public final function getExpiry() {
			return $this->expiry;
		}

		/**
		 * Set when this paste expires.
		 * @param string $expiry When this paste expires.
		 */
		public final function setExpiry($expiry) {
			$this->expiry = $expiry;
		}

		/**
		 * Retrieve the account that owns this paste.
		 * @return \Brush\Accounts\Account The account that owns this paste.
		 */
		private final function getOwner() {
			return $this->owner;
		}

		/**
		 * Find whether this paste has an owner set.
		 * If it doesn't, it cannot have private visibility.
		 * @return boolean True if this paste has an assigned owner; false otherwise.
		 */
		private final function hasOwner() {
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
		public function __construct() {
			parent::__construct();
			$this->setExpiry(Expiry::EXPIRY_NEVER);
		}

		/**
		 * Submit this draft paste to the Pastebin API.
		 * @param \Brush\Accounts\Developer $developer The developer account to use to send the request.
		 * @throws \Brush\Exceptions\ApiException If the Pastebin API returns failure. Check the message for the cause.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin cannot be sent.
		 */
		public function paste(Developer $developer) {

			// throw on any errors
			$this->validate();

			// draft is ready; create the request that will send it
			$request = new POSTRequest(Brush::API_BASE_URL . self::ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			// add POST variables
			$request->getVariables()->set('api_option', 'paste');
			$developer->sign($request);
			$this->addTo($request, $developer);

			try {
				// send the request and get the response body
				$body = $request->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Failed submit paste: ' . substr($body, 17));
				}

				// must be success; create and return a paste
				return Paste::fromPasted($body, $this);
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($request);
			}
		}

		/**
		 * Ensure this draft has no errors before sending to Pastebin.
		 * These checks are not exhaustive - they don't scan for setting invalid expirations or visibilities for example.
		 * @throws \Brush\Exceptions\ValidationException If any errors are found; check the message for details.
		 */
		private function validate() {
			if ($this->getVisibility() == Visibility::VISIBILITY_PRIVATE && !$this->hasOwner()) {
				throw new ValidationException('Private pastes must have an owner set. Change the visibility, or specify the owner.');
			}
		}

		/**
		 * Add the values in this paste to a new paste request.
		 * @param \Crackle\Requests\POSTRequest $request The request to add variables to.
		 * @param \Brush\Accounts\Developer $developer The developer that will send the request.
		 */
		protected function addTo(POSTRequest $request, Developer $developer) {
			parent::addTo($request, $developer);
			$request->getVariables()->set('api_paste_expire_date', $this->getExpiry());
			if ($this->hasOwner()) {
				$this->getOwner()->sign($request, $developer);
			}
		}
	}
}
