<?php

namespace Crackle {

	use \Crackle\Exceptions\HeaderNotFoundException;

	/**
	 * Represents the HTTP headers of a request/response.
	 * @author George Brighton
	 */
	class Headers {

		/**
		 * The lookup of array of headers, indexed by lowercase header name for case-insensitive searching.
		 * @var array[string]
		 */
		private $headers;

		/**
		 * Get the lookup array of headers.
		 * @return array[string] The lookup array of headers.
		 */
		private final function getHeaders() {
			return $this->headers;
		}

		/**
		 * Set the lookup array of headers.
		 * @param array[string] $headers The lookup array of headers.
		 */
		private final function setHeaders(array $headers) {
			$this->headers = $headers;
		}

		/**
		 * Initialise a new collection of headers.
		 */
		public function __construct() {
			$this->setHeaders(array());
		}

		/**
		 * Find whether a header has been set.
		 * @param string $name The name of the header to check.
		 * @return boolean True if the a header exists with that name, false otherwise.
		 */
		public final function exists($name) {
			return isset($this->headers[strtolower($name)]);
		}

		/**
		 * Retrieve the value of a header.
		 * @param string $name The name of the header whose value to retrieve.
		 * @return string The value of the given header.
		 * @throws \Crackle\Exceptions\HeaderNotFoundException If the header does not exist.
		 */
		public final function get($name) {
			if(!$this->exists($name)) {
				throw new HeaderNotFoundException('The header \'' . $name . '\' does not exist.');
			}
			return $this->headers[strtolower($name)];
		}

		/**
		 * Set a header's value. Any existing value will be overwritten.
		 * @param string $name The name of the header.
		 * @param string $value The value to store under that header.
		 */
		public function set($name, $value) {
			$this->headers[strtolower($name)] = (string)$value;
		}

		/**
		 * Format a header name. Not technically necessary as HTTP headers are case insensitive (RFC 2616), but arguably more expected.
		 * @param string $name The header name to format.
		 * @return string The formatted name.
		 */
		private static function format($name) {
			return implode('-',
					array_map(
							function($piece) {
								return ucfirst($piece);
							},
							explode('-', $name)));
		}

		/**
		 * Parses a string of headers. The current headers will be replaced.
		 * Intended to emulate http_parse_headers() in the pecl_http package.
		 * @param string $head A string of headers.
		 * @link http://www.php.net/manual/en/function.http-parse-headers.php#112986
		 */
		public function parse($head) {
			$headers = array();
			$key = '';

			foreach (explode("\n", $head) as $line) {
				$pieces = explode(':', $line, 2);

				if (!isset($pieces[1])) {
					// it could be a blank line, the HTTP/1.x string, or the continuation of a value
					$trimmed = trim($pieces[0]);
					if ($trimmed !== '' && ctype_space(substr($pieces[0], 0, 1))) {
						// indented headers indicate a continuation of the previous line's value
						$headers[$key] .= ' ' . $trimmed;
					}
					continue;
				}

				$name = strtolower($pieces[0]);
				$value = trim($pieces[1]);

				if (!isset($headers[$name])) {
					$headers[$name] = $value;
				}
				else if (is_array($headers[$name])) {
					$headers[$name] = array_merge(
							$headers[$name],
							array($value));
				}
				else {
					$headers[$name] = array_merge(
							array($headers[$name]),
							array($value));
				}

				// store the last inserted header name in case its value wraps across lines
				$key = $name;
			}

			$this->setHeaders($headers);
		}

		/**
		 * Get a new instance representing a set of headers.
		 * @param string $head The headers to import.
		 * @return \Crackle\Headers The created object.
		 */
		public static function factory($head) {
			$headers = new Headers();
			$headers->parse($head);
			return $headers;
		}

		/**
		 * Send all of the headers contained in this object.
		 */
		public function sendAll() {
			foreach($this->getHeaders() as $name => $value) {
				header(self::format($name) . ': ' . $value);
			}
		}

		/**
		 * Adds the headers contained in this object to a cURL session.
		 * @param resource $handle A cURL session handle returned by curl_init().
		 */
		public function addTo($handle) {
			// format the header names
			$formatted = array();
			foreach($this->getHeaders() as $name => $value) {
				$formatted[] = self::format($name) . ': ' . $value;
			}

			// add them to the cURL handle
			curl_setopt($handle, CURLOPT_HTTPHEADER, $formatted);
		}
	}
}
