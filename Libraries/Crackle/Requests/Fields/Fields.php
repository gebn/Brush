<?php

namespace Crackle\Requests\Fields {

	use \Crackle\Structures\KeyValuePair;

	use \InvalidArgumentException;

	/**
	 * Holds fields of a specific type for a request, e.g. GET parameters or POST files.
	 * @author George Brighton
	 */
	class Fields {

		/**
		 * The named fields to be included in the request.
		 * @var array
		 */
		private $fields;

		/**
		 * Retrieve the fields to be included in the request.
		 * @return array The fields to be included in the request.
		 */
		private final function getFields() {
			return $this->fields;
		}

		/**
		 * Set the fields to be included in the request.
		 * @param array $fields The new fields to be included in the request.
		 */
		private final function setFields(array $fields) {
			$this->fields = $fields;
		}

		/**
		 * Initialise a new fields container.
		 */
		public function __construct() {
			$this->setFields(array());
		}

		/**
		 * Set the value of a field.
		 * @param string $name The name of the field.
		 * @param string|array $value The value of the field.
		 */
		public function set($name, $value) {
			$this->fields[$name] = $value;
		}

		/**
		 * Set fields in bulk using an existing array.
		 * @param array[string|array] $pairs The array of fields to read from.
		 */
		public final function setAll(array $pairs) {
			foreach ($pairs as $name => $value) {
				$this->set($name, $value);
			}
		}

		/**
		 * Retrieve the field pairs.
		 * @return array[\Crackle\Structures\KeyValuePair] KVPs of fields in this container.
		 */
		public final function getPairs() {
			$pairs = array();
			foreach ($this->getFields() as $key => $value) {
				if (is_array($value)) {
					self::addPairs($pairs, $value, $key);
				}
				else {
					$pairs[] = new KeyValuePair($key, $value);
				}
			}
			return $pairs;
		}

		/**
		 * The recursive cousin of getPairs(), used to extract pairs from nested arrays of fields.
		 * @param array[\Crackle\Structures\KeyValuePair] $pairs The array of KVPs to append to.
		 * @param array $fields The array of fields to process.
		 * @param string $prefix The string to prepend to all field names.
		 */
		private static function addPairs(array &$pairs, array $fields, $prefix) {
			foreach ($fields as $key => $value) {
				if (is_array($value)) {
					self::addPairs($pairs, $value, $prefix . '[' . $key . ']');
				}
				else {
					$pairs[] = new KeyValuePair($prefix . '[' . $key . ']', $value);
				}
			}
		}
	}
}
