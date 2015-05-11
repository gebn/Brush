<?php

namespace Brush\Configuration {

	/**
	 * Lazy loads configuration files.
	 * @author George Brighton
	 */
	class Loader {

		/**
		 * Cache for loaded and parsed files, indexed by file name.
		 * @var string[string[]]
		 */
		private static $content = array();

		/**
		 * Get the parsed contents of a configuration file.
		 * @param string $file The path of the file to load.
		 * @return string[] The parsed contents of the file.
		 */
		public static function get($file) {
			if (!isset(self::$content[$file])) {
				// load the file into the cache
				self::$content[$file] = parse_ini_file($file);
			}

			return self::$content[$file];
		}
	}
}
