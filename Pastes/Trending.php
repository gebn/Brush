<?php

namespace Brush\Pastes {

	use \Brush\Accounts\Developer;
	use \Brush\Utilities\ApiRequest;

	/**
	 * Retrieves trending pastes.
	 * @author George Brighton
	 */
	class Trending {

		/**
		 * The endpoint of the trending paste listing service.
		 * @var string
		 */
		const ENDPOINT = 'api_post.php';

		/**
		 * Get the 18 currently trending pastes.
		 * @param \Brush\Accounts\Developer $developer The developer account to use for the request.
		 * @return \Brush\Pastes\Paste[] Trending pastes.
		 */
		public static function getPastes(Developer $developer) {
			$pastebin = new ApiRequest($developer, self::ENDPOINT);
			$pastebin->setOption('trends');
			return Paste::parsePastes($pastebin->send());
		}
	}
}
