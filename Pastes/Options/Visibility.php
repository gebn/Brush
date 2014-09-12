<?php

namespace Brush\Pastes\Options {

	/**
	 * Container for visibility constants, used to set the visibility of new pastes and compare that of existing ones.
	 * @author George Brighton
	 */
	class Visibility {

		/**
		 * A publically accessible paste.
		 * This is the default for new pastes.
		 * @var int
		 */
		const VISIBILITY_PUBLIC = 0;

		/**
		 * A paste that is publically visible, but doesn't appear in search results.
		 * @var int
		 */
		const VISIBILITY_UNLISTED = 1;

		/**
		 * A privately accessible paste, only visible to the account that created it.
		 * @var int
		 */
		const VISIBILITY_PRIVATE = 2;
	}
}
