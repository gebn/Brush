<?php

namespace Brush\Pastes {

	use \Brush\Exceptions\ValidationException;

	/**
	 * Container for expiry constants to use for draft pastes.
	 * @author George Brighton
	 */
	class Expiry {

		/**
		 * Indicates the paste should expire in 10 minutes.
		 * @var string
		 */
		const EXPIRY_TEN_MINUTES = '10M';

		/**
		 * Indicates the paste should expire in an hour.
		 * @var string
		 */
		const EXPIRY_ONE_HOUR = '1H';

		/**
		 * Indicates the paste should expire in a day.
		 * @var string
		 */
		const EXPIRY_ONE_DAY = '1D';

		/**
		 * Indicates the paste should expire in a week.
		 * @var string
		 */
		const EXPIRY_ONE_WEEK = '1W';

		/**
		 * Indicates the paste should expire in a fortnight.
		 * @var string
		 */
		const EXPIRY_TWO_WEEKS = '2W';

		/**
		 * Indicates the paste should expire in a month.
		 * @var string
		 */
		const EXPIRY_ONE_MONTH = '1M';

		/**
		 * Indicates the paste should never expire.
		 * @var string
		 */
		const EXPIRY_NEVER = 'N';

		/**
		 * Find the number of seconds into the future that an expiry constant corresponds to.
		 * @param string $expiry The value to parse.
		 * @throws \Brush\Exceptions\ValidationException If the value is an invalid expiry.
		 * @return int The number of seconds in the future, or 0 if never.
		 */
		public static function getOffset($expiry) {
			if ($expiry == Expiry::EXPIRY_TEN_MINUTES) return 60 * 10;
			if ($expiry == Expiry::EXPIRY_ONE_HOUR) return 60 * 60;
			if ($expiry == Expiry::EXPIRY_ONE_DAY) return 60 * 60 * 24;
			if ($expiry == Expiry::EXPIRY_ONE_WEEK) return 60 * 60 * 24 * 7;
			if ($expiry == Expiry::EXPIRY_TWO_WEEKS) return 60 * 60 * 24 * 7 * 2;
			if ($expiry == Expiry::EXPIRY_ONE_MONTH) return 60 * 60 * 24 * 28;
			if ($expiry == Expiry::EXPIRY_NEVER) return 0;
			throw new ValidationException(sprintf('Invalid expiry: \'%s\'', $expiry));
		}
	}
}
