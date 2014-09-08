<?php

namespace Crackle\Utilities {

	/**
	 * Performs operations on strings that contain file or directory path information.
	 * @author George Brighton
	 */
	class Path {

		/**
		 * Combine multiple path strings to form a single path.
		 * Slashes will be replaced with DIRECTORY_SEPARATOR.
		 * @param string ... An unlimited number of paths fragments to join.
		 * @return string The combined path.
		 */
		public static function join() {
			$paths = array_filter(func_get_args());
			return preg_replace('/(\/|\\\)+/', DIRECTORY_SEPARATOR, implode('\/', $paths));
		}
	}
}
