<?php

/**
 * Brush is a complete object-oriented PHP wrapper for the Pastebin API.
 * @author George Brighton
 * @link https://github.com/gebn/Brush
 */
namespace Brush {

	// let Crackle initialise itself
	require 'Libraries/Crackle/Crackle.php';

	use \Brush\Exceptions\DependencyException;

	/*
	 * This file should be included using `require[_once]` before Brush is used.
	 */
	Brush::load();

	/**
	 * Sets up the environment required by Brush.
	 * @author George Brighton
	 */
	final class Brush {

		/**
		 * This version of Brush.
		 * @var string
		 */
		const VERSION = '1.0.1';

		/**
		 * This class should not be instantiated.
		 */
		private function __construct() {}

		/**
		 * Readies Brush for use.
		 */
		public static function load() {
			static $loaded = false;

			// we only want to initialise once
			if (!$loaded) {
				self::initialise();
				$loaded = true;
			}
		}

		/**
		 * Carries out everything needed to get Brush up and running.
		 */
		private static function initialise() {
			self::registerAutoloader();
			self::checkRequirements();
		}

		/**
		 * Adds Brush's autoloader function.
		 */
		private static function registerAutoloader() {
			spl_autoload_register(function($qualified) {
				if (substr($qualified, 0, 6) == 'Brush\\') {
					require str_replace('\\', DIRECTORY_SEPARATOR, substr($qualified, 6)) . '.php';
				}
			}, true);
		}

		/**
		 * Ensures this installation of PHP meets Brush's minimum requirements.
		 * @throws \Brush\Exceptions\DependencyException If any requirement is not met.
		 */
		private static function checkRequirements() {
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				throw new DependencyException('Brush requires PHP 5.3.0 or later.');
			}
		}
	}
}
