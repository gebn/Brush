<?php

namespace Crackle {

	use \Crackle\Exceptions\IOException;
	use \Crackle\Utilities\Path;
	use \Crackle\Headers;

	/**
	 * Represents the data returned by a request.
	 * @author George Brighton
	 */
	class Response {

		/**
		 * The final URL reached, after any redirects - this may not match the original requested URL.
		 * @var string
		 */
		private $url;

		/**
		 * The HTTP status code returned by the server.
		 * @var int
		 */
		private $statusCode;

		/**
		 * Response headers, indexed by name.
		 * @var \Crackle\Response
		 */
		private $headers;

		/**
		 * The response body.
		 * @var string
		 */
		private $body;

		/**
		 * Get the effective URL of this resource.
		 * @return string The final URL.
		 */
		public final function getUrl() {
			return $this->url;
		}

		/**
		 * Set the effective URL of this resource.
		 * @param string $url The final URL.
		 */
		private final function setUrl($url) {
			$this->url = (string)$url;
		}

		/**
		 * Get the HTTP status code returned by the destination server.
		 * @return int The status code.
		 */
		public final function getStatusCode() {
			return $this->statusCode;
		}

		/**
		 * Set the HTTP status code returned by the destination server.
		 * @param int $statusCode The status code.
		 */
		private final function setStatusCode($statusCode) {
			$this->statusCode = (int)$statusCode;
		}

		/**
		 * Retrieve the list of response headers.
		 * @return \Crackle\Headers The list of headers.
		 */
		public final function getHeaders() {
			return $this->headers;
		}

		/**
		 * Set the list of headers received in the response.
		 * @param \Crackle\Headers $headers The new list of headers.
		 */
		private final function setHeaders(Headers $headers) {
			$this->headers = $headers;
		}

		/**
		 * Get the raw body of this response.
		 * @return string The body.
		 */
		public final function getBody() {
			return $this->body;
		}

		/**
		 * Set the raw body of this response.
		 * @param string $body The body.
		 */
		private final function setBody($body) {
			$this->body = (string)$body;
		}

		/**
		 * Forgets the content body of this response.
		 * This method can be called in the request callback to free up some memory when downloading lots of large files.
		 */
		public final function clearBody() {
			$this->body = null;
		}

		/**
		 * Initialises a new response object.
		 */
		public function __construct() {
			$this->setHeaders(new Headers());
		}

		/**
		 * Get an instance of this class representing the response of an executed cURL handle.
		 * @param resource $handle The executed handle to import.
		 * @return \Crackle\Response The created Response instance.
		 */
		public static function factory($handle) {
			$response = new Response();
			$response->import($handle);
			return $response;
		}

		/**
		 * Make this response represent an executed cURL handle.
		 * @param resource $handle An executed cURL handle.
		 */
		private function import($handle) {
			$this->setUrl(curl_getinfo($handle, CURLINFO_EFFECTIVE_URL));
			$this->setStatusCode(curl_getinfo($handle, CURLINFO_HTTP_CODE));

			$content = curl_multi_getcontent($handle);
			$separation = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
			$this->getHeaders()->parse(substr($content, 0, $separation));
			$this->setBody(substr($content, $separation));
		}

		/**
		 * Retrieve the name of the returned resource.
		 * @return string The name of the resource returned by the request.
		 */
		public final function getFilename() {
			// the name may be passed in a header
			if ($this->getHeaders()->exists('Content-Disposition')) {
				$disposition = $this->getHeaders()->get('Content-Disposition');
				$pos = strpos($disposition, 'filename=');
				if ($pos !== false) {
					return substr($disposition, $pos + 9);
				}
			}

			// default to the last chunk of the URL
			return basename(parse_url($this->getUrl(), PHP_URL_PATH));
		}

		/**
		 * Write this resource to a file.
		 * @param string $directory The directory to write to.
		 * @param string $name The name of the file to write. If omitted, the original name will be used.
		 * @throws \Crackle\Exceptions\IOException If the file cannot be written.
		 */
		public function writeTo($directory, $name = null) {
			if ($name === null) {
				$name = $this->getFilename();
			}

			if (!is_writable($directory)) {
				throw new IOException('Insufficient permissions to write to directory.');
			}

			if (!@file_put_contents(Path::join($directory, $name), $this->getBody())) {
				throw new IOException('Failed to write file.');
			}
		}

		/**
		 * Output this response as the response to the current request.
		 * Headers and the response body will be duplicated.
		 */
		public function passthrough() {
			$this->getHeaders()->sendAll();
			echo $this->getBody();
		}
	}
}
