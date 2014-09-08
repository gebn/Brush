<?php

/**
 * Crackle is a powerful yet easy to use object-oriented HTTP client.
 * @author George Brighton
 * @link https://github.com/gebn/Crackle
 */
namespace Crackle {

	use \Crackle\Exceptions\DependencyException;

	/*
	 * This file is self-running - it just needs to be `include`d in the script before Crackle is used.
	 */
	Crackle::load();

	/**
	 * Checks dependencies and sets up Crackle's environment.
	 * @author George Brighton
	 */
	final class Crackle {

		/**
		 * This release's version identifier.
		 * @var string
		 */
		const VERSION = '2.5';

		/**
		 * This class should not be instantiated.
		 */
		private function __construct() {}

		/**
		 * Readies Crackle for use.
		 */
		public static function load() {
			static $loaded = false;

			// we only want to initialise once
			if(!$loaded) {
				self::initialise();
				$loaded = true;
			}
		}

		/**
		 * Carries out everything needed to get Crackle up and running.
		 */
		private static function initialise() {
			self::checkRequirements();
			self::registerAutoloader();
		}

		/**
		 * Ensures this installation of PHP meets Crackle's minimum requirements.
		 * @throws \Crackle\Exceptions\DependencyException If any requirement is not met.
		 */
		private static function checkRequirements() {
			if(version_compare(PHP_VERSION, '5.3.0', '<')) {
				throw new DependencyException('Crackle requires PHP 5.3.0 or later.');
			}

			if(!extension_loaded('curl')) {
				throw new DependencyException('The cURL module must be available for Crackle to operate.');
			}
		}

		/**
		 * Adds the autoloader function to the queue.
		 */
		private static function registerAutoloader() {
			spl_autoload_register(function($qualified) {
				if(substr($qualified, 0, 8) == 'Crackle\\') {
					require str_replace('\\', DIRECTORY_SEPARATOR, substr($qualified, 8)) . '.php';
				}
			}, true);
		}
	}
}
