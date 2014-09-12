<?php

namespace Brush\Pastes {

	use \Brush\Brush;
	use \Brush\Pastes\Paste;
	use \Brush\Accounts\Developer;
	use \Brush\Exceptions\ApiException;
	use \Brush\Exceptions\RequestException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

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
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicates an error in the request.
		 * @throws \Brush\Exceptions\RequestException If the request to Pastebin fails.
		 * @return array[\Brush\Pastes\Paste] Trending pastes.
		 */
		public static function getPastes(Developer $developer) {
			$request = new POSTRequest(Brush::API_BASE_URL . self::ENDPOINT);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

			$developer->sign($request);
			$request->getVariables()->set('api_option', 'trends');

			try {
				// send the request and get the response body
				$body = $request->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Failed to retrieve user key: ' . substr($body, 17));
				}

				// must be success (the entire content is the key)
				return Paste::parsePastes($body);
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($request);
			}
		}
	}
}
