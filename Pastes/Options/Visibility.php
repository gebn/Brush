<?php

namespace Brush\Pastes\Options {

	use \Brush\Exceptions\ArgumentException;

	/**
	 * Container for visibility constants, used to set the visibility of new pastes and compare that of existing ones.
	 * @author George Brighton
	 */
	class Visibility {

		/**
		 * A publicly accessible paste.
		 * This is the default for new pastes.
		 * @var int
		 */
		const VISIBILITY_PUBLIC = 0;

		/**
		 * A paste that is publicly visible, but doesn't appear in search results.
		 * @var int
		 */
		const VISIBILITY_UNLISTED = 1;

		/**
		 * A privately accessible paste, only visible to the account that created it.
		 * @var int
		 */
		const VISIBILITY_PRIVATE = 2;

		/**
		 * Find the meaning of a visibility constant.
		 * @param $visibility int The constant to translate.
		 * @return string The meaning of the constant.
		 * @throws \Brush\Exceptions\ArgumentException If $visibility is unrecognised.
		 */
		public static function toString($visibility) {
			switch ($visibility) {
				case self::VISIBILITY_PUBLIC: return 'Public';
				case self::VISIBILITY_UNLISTED: return 'Unlisted';
				case self::VISIBILITY_PRIVATE: return 'Private';
				default: throw new ArgumentException(sprintf('Unrecognised visibility: \'%s\'', $visibility));
			}
		}
	}
}
