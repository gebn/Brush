<?php

namespace Brush\Pastes {

	use \Brush\Accounts\Account;
	use \Brush\Accounts\Developer;
	use \Brush\Accounts\User;
	use \Brush\Pastes\Options\Expiry;
	use \Brush\Pastes\Options\Format;
	use \Brush\Pastes\Options\Visibility;
	use \Brush\Utilities\ApiRequest;
	use \Brush\Exceptions\IOException;
	use \Brush\Exceptions\FormatException;
	use \Brush\Exceptions\ValidationException;

	use \Crackle\Requests\POSTRequest;

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
		 * Initialise properties to defaults.
		 */
		public function __construct() {
			parent::__construct();
			$this->setExpiry(Expiry::EXPIRY_NEVER);
		}

		/**
		 * Submit this draft paste to the Pastebin API.
		 * @param \Brush\Accounts\Developer $developer The developer account to use to send the request.
		 * @param \Brush\Accounts\Account $owner The account that should own the paste. Omit for anonymous paste.
		 * @return \Brush\Pastes\Paste The created paste.
		 */
		public function paste(Developer $developer, Account $owner = null) {

			// throw on any errors
			$this->validate();

			// carry out draft owner validation
			if ($this->getVisibility() == Visibility::VISIBILITY_PRIVATE && $owner === null) {
				throw new ValidationException('Private pastes must have an owner set. Change the visibility, or specify the owner.');
			}

			// create the request
			$pastebin = new ApiRequest($developer, self::ENDPOINT);
			$pastebin->setOption('paste');
			$owner->sign($pastebin->getRequest(), $developer);
			$this->addTo($pastebin->getRequest(), $developer);

			// send and create a paste from this draft
			return Paste::fromPasted($pastebin->send(), $this, $owner);
		}

		/**
		 * Add the values in this paste to a new paste request.
		 * @param \Crackle\Requests\POSTRequest $request The request to add variables to.
		 * @param \Brush\Accounts\Developer $developer The developer that will send the request.
		 */
		protected function addTo(POSTRequest $request, Developer $developer) {
			parent::addTo($request, $developer);
			$request->getVariables()->set('api_paste_expire_date', $this->getExpiry());
		}

		/**
		 * Create a new draft with an account's default settings.
		 * @param \Brush\Accounts\Account $account The owner's account whose settings to use.
		 * @param \Brush\Accounts\Developer $developer The developer account to use if settings need to be retrieved.
		 * @return \Brush\Pastes\Draft The created draft paste.
		 */
		public static function fromOwner(Account $account, Developer $developer) {
			$draft = new Draft();
			User::fromAccount($account, $developer)->getDefaults()->applyTo($draft);
			return $draft;
		}

		/**
		 * Create a draft paste from a file.
		 * @param string $path The path of the file to create a draft from.
		 * @throws \Brush\Exceptions\IOException If the file cannot be read.
		 * @return \Brush\Pastes\Draft The created draft paste.
		 */
		public static function fromFile($path) {
			if (!is_readable($path)) {
				throw new IOException(sprintf('Cannot read from \'%s\'. Check file permissions.', $path));
			}

			$draft = new Draft();
			$draft->setTitle(basename($path));
			$draft->setContent(file_get_contents($path));

			try {
				$draft->setFormat(Format::fromExtension(pathinfo($path, PATHINFO_EXTENSION)));
			} catch (FormatException $e) {
				// couldn't auto-detect format; ah well
			}

			return $draft;
		}
	}
}
