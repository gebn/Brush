<?php

namespace Crackle\Requests\Fields {

	/**
	 * Represents the GET parameters of a request.
	 * @author George Brighton
	 */
	class Parameters extends Fields {

		/**
		 * Retrieve the GET parameters as a query string.
		 * @return string The parameters formatted as a query string.
		 */
		public function getQueryString() {
			// Crackle permits duplicate keys, so we cannot use PHP's http_build_query()
			$parts = array();
			foreach($this->getPairs() as $pair) {
				$parts[] = urlencode($pair->getKey()) . '=' . urlencode($pair->getValue());
			}
			return implode('&', $parts);
		}

		/**
		 * Import the parameters in a query string.
		 * @param string $queryString The query string to parse.
		 */
		public function parse($queryString) {
			parse_str($queryString, $params);
			foreach ($params as $name => $value) {
				$this->set($name, $value);
			}
		}
	}
}
