<?php

namespace Crackle\Requests\Parts {

	/**
	 * Implemented by classes representing parts of a POST request (i.e. variables and files).
	 * @author George Brighton
	 */
	interface POSTRequestPart {

		/**
		 * Add this part to a request.
		 * @param array[string] $lines The lines array to append to.
		 * @param string $name The name of this request part.
		 */
		public function appendPart(array &$lines, $name);
	}
}
