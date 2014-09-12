<?php

namespace Brush\Utilities {

	use \Brush\Accounts\Developer;
	use \Brush\Exceptions\ApiException;
	use \Brush\Exceptions\RequestException;

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Exceptions\RequestException as CrackleRequestException;

	use \DOMDocument;

	/**
	 * Functionality common to all Pastebin API requests.
	 * @author George Brighton
	 */
	class ApiRequest {

		/**
		 * The base address of all API endpoints.
		 * @var string
		 */
		const BASE_URL = 'https://pastebin.com/api/';

		/**
		 * The underlying request that will be sent to Pastebin.
		 * @var \Crackle\Requests\POSTRequest
		 */
		private $request;

		/**
		 * Retrieve the underlying request.
		 * @return \Crackle\Requests\POSTRequest The underlying request.
		 */
		public function getRequest() {
			return $this->request;
		}

		/**
		 * Set the underlying request.
		 * @param \Crackle\Requests\POSTRequest $request The new underlying request.
		 */
		private function setRequest(POSTRequest $request) {
			$this->request = $request;
		}

		/**
		 * Initialise a new API request.
		 * @param \Brush\Accounts\Developer $developer The developer account to sign the request with.
		 * @param string $url The relative URL of the endpoint to send the request to, e.g. `api_post.php`.
		 */
		public function __construct(Developer $developer, $url) {
			$request = new POSTRequest(self::BASE_URL . $url);
			curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);
			$developer->sign($request);
			$this->setRequest($request);
		}

		/**
		 * Set the `api_option` POST variable of this request.
		 * @param string $option The option value, e.g. `paste`.
		 */
		public final function setOption($option) {
			$this->getRequest()->getVariables()->set('api_option', $option);
		}

		/**
		 * Send this request.
		 * @throws \Brush\Exceptions\ApiException If Pastebin indicates an error in the request.
		 * @throws \Brush\Exceptions\RequestException If the request fails.
		 * @return string The body of the response.
		 */
		public function send() {
			try {
				// send the request and get the response body
				$body = $this->getRequest()->getResponse()->getBody();

				// identify error from prefix (Pastebin does not use HTTP status codes)
				if (substr($body, 0, 15) == 'Bad API request') {
					throw new ApiException('Pastebin API request failed: ' . substr($body, 17));
				}

				// success
				return $body;
			}
			catch (CrackleRequestException $e) {
				// transport failure
				throw new RequestException($this->getRequest());
			}
		}

		/**
		 * Utility method to turn a response into a DOMElement.
		 * @param string $body The response body, which should be XML.
		 * @return \DOMElement The created element.
		 */
		public static function toElement($body) {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->loadXML($body);
			return $dom->documentElement;
		}
	}
}
