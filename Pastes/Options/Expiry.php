<?php

namespace Brush\Pastes\Options {

	use \Brush\Exceptions\ArgumentException;

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
		 * @throws \Brush\Exceptions\ArgumentException If the value is an invalid expiry.
		 * @return int The number of seconds in the future, or 0 if never.
		 */
		public static function getOffset($expiry) {
			switch ($expiry) {
				case self::EXPIRY_TEN_MINUTES: return 60 * 10;
				case self::EXPIRY_ONE_HOUR: return 60 * 60;
				case self::EXPIRY_ONE_DAY: return 60 * 60 * 24;
				case self::EXPIRY_ONE_WEEK: return 60 * 60 * 24 * 7;
				case self::EXPIRY_TWO_WEEKS: return 60 * 60 * 24 * 7 * 2;
				case self::EXPIRY_ONE_MONTH: return 60 * 60 * 24 * 28;
				case self::EXPIRY_NEVER: return 0;
				default: throw new ArgumentException(sprintf('Unrecognised expiry: \'%s\'', $expiry));
			}
		}

		/**
		 * Find the meaning of an expiry constant.
		 * @param $expiry int The constant to translate.
		 * @return string The meaning of the constant.
		 * @throws \Brush\Exceptions\ArgumentException If $expiry is unrecognised.
		 */
		public static function toString($expiry) {
			switch ($expiry) {
				case self::EXPIRY_TEN_MINUTES: return '10 minutes';
				case self::EXPIRY_ONE_HOUR: return '1 hour';
				case self::EXPIRY_ONE_DAY: return '1 day';
				case self::EXPIRY_ONE_WEEK: return '1 week';
				case self::EXPIRY_TWO_WEEKS: return '2 weeks';
				case self::EXPIRY_ONE_MONTH: return '1 month';
				case self::EXPIRY_NEVER: return 'Never';
				default: throw new ArgumentException(sprintf('Unrecognised expiry: \'%s\'', $expiry));
			}
		}
	}
}
