<?php

namespace Brush\Utilities {

	use \Brush\Exceptions\CacheException;

	/**
	 * A generic key/value cache.
	 * @author George Brighton
	 */
	class Cache {

		/**
		 * The cached object store.
		 * Items are stored as key => value.
		 * @var array[mixed]
		 */
		private $cache;

		/**
		 * Find whether a key is in the cache.
		 * @param string $key The key to check.
		 * @return True if the key is cached, false otherwise.
		 */
		public function isCached($key) {
			return isset($this->cache[$key]);
		}

		/**
		 * Retrieve the value stored under a key.
		 * @param string $key The key of the value to retrieve.
		 * @throws \Brush\Exceptions\CacheException If the key is not in the cache.
		 * @return mixed The value stored under the key.
		 */
		public function get($key) {
			if (!$this->isCached($key)) {
				throw new CacheException(sprintf('No value is stored under the the key \'%s\'.', $key));
			}

			return $this->cache[$key];
		}

		/**
		 * Add or update a cache entry.
		 * @param string $key The key of the value. If it exists, the value will be overwritten.
		 * @param mixed $value The value to store under the key.
		 */
		public function set($key, $value) {
			$this->cache[$key] = $value;
		}
	}
}
