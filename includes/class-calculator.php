<?php
/**
 * Sequence maths.
 *
 * @package Fibonacci_Harmony_Calculator
 */

namespace Fibonacci_Harmony_Calculator;

defined( 'ABSPATH' ) || die();

/**
 * Computes the seeded Fibonacci sequence and its per-ordinal derived values.
 *
 * This class is deliberately free of WordPress functions so the maths is
 * unit-testable and mirrors the front-end JavaScript exactly. The sequence is
 * F(1) = F(2) = seed, F(n) = F(n-1) + F(n-2), i.e. seed x classic Fibonacci.
 *
 * Precision: the seed resolves to the nearest 1/SEED_SCALE (SEED_DECIMALS places),
 * so each value is the integer (classic Fibonacci x scaled-seed) divided by
 * SEED_SCALE. Working in integers avoids floating-point artifacts. With the larger
 * SEED_SCALE this product fits PHP's native 64-bit int up to roughly count = 62;
 * beyond that the front-end BigInt path is authoritative for the live view, and PHP
 * only renders the initial (default-seed) first paint anyway.
 */
class Calculator {

	/**
	 * Seed value, clamped to [SEED_MIN, SEED_MAX].
	 *
	 * @var float
	 */
	private $seed;

	/**
	 * Number of ordinals in the sequence.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Seed expressed as an integer scaled by SEED_SCALE (e.g. 1.0 -> 100).
	 *
	 * @var int
	 */
	private $scaled_seed;

	/**
	 * Constructor.
	 *
	 * @param float $seed  Seed value (clamped to the allowed range).
	 * @param int   $count Number of ordinals (minimum 1).
	 */
	public function __construct( $seed, $count ) {
		$this->seed        = self::clamp_seed( (float) $seed );
		$this->count       = max( 1, (int) $count );
		$this->scaled_seed = (int) round( $this->seed * SEED_SCALE );
	}

	/**
	 * Get the clamped seed value.
	 *
	 * @return float
	 */
	public function get_seed() {
		return $this->seed;
	}

	/**
	 * Get the ordinal count.
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->count;
	}

	/**
	 * Build the full list of ordinals.
	 *
	 * @return array<int, array<string, int|string>> Keyed by 1-based index, each
	 *                                                with index, value, decimals,
	 *                                                arc_standard, arc_ancient.
	 */
	public function get_ordinals() {
		$classic      = self::classic_fibonacci( $this->count );
		$std_step     = CIRCLE_DEGREES_STANDARD / $this->count;
		$ancient_step = CIRCLE_DEGREES_ANCIENT / $this->count;

		// Number of azimuth divisions around the circle (cardinals x thirds).
		$divisions = COMPASS_POINT_COUNT * QUADRANT_SUBDIVISIONS;

		$ordinals = array();
		foreach ( $classic as $index => $classic_value ) {
			$decimals           = self::value_decimals( $index, $this->count );
			$ordinals[ $index ] = array(
				'index'        => $index,
				'value'        => $this->format_value( $classic_value * $this->scaled_seed, $decimals ),
				'decimals'     => $decimals,
				'arc_standard' => self::format_degrees( $index * $std_step ),
				'arc_ancient'  => self::format_degrees( $index * $ancient_step ),
				'azimuth'      => $this->azimuth( $index, $divisions ),
			);
		}

		return $ordinals;
	}

	/**
	 * Number of decimal places to display for a row's value.
	 *
	 * Rows in the first 1/PRECISE_ROWS_FRACTION of the sequence (the top quarter,
	 * i.e. the top of the right-hand table) show up to VALUE_DECIMALS_MAX places so
	 * a finely-tuned decimal seed is visible; every other row caps at
	 * VALUE_DECIMALS_DEFAULT. Trailing zeros are trimmed downstream either way.
	 *
	 * @param int $index 1-based ordinal index.
	 * @param int $count Number of ordinals.
	 * @return int Maximum decimal places for this row.
	 */
	public static function value_decimals( $index, $count ) {
		return ( PRECISE_ROWS_FRACTION * $index <= $count ) ? VALUE_DECIMALS_MAX : VALUE_DECIMALS_DEFAULT;
	}

	/**
	 * Standard-angle azimuth for a row, if it lands exactly on a compass division.
	 *
	 * Returns the integer angle in degrees when the ordinal sits on a cardinal
	 * point or a quadrant-third division, otherwise null (no class is added). The
	 * row's angle is index x (360 / count); it lands on a division of (360 /
	 * $divisions) degrees exactly when count divides index x $divisions.
	 *
	 * @param int $index     1-based ordinal index.
	 * @param int $divisions Number of equal divisions around the circle.
	 * @return int|null Angle in degrees, or null when off-division.
	 */
	private function azimuth( $index, $divisions ) {
		if ( 0 !== ( $index * $divisions ) % $this->count ) {
			return null;
		}

		return intdiv( $index * CIRCLE_DEGREES_STANDARD, $this->count );
	}

	/**
	 * Partition the ordinal indices into the clock layout regions.
	 *
	 * Reads clockwise around the wheel: North (top) -> right table (top to
	 * bottom) -> South (bottom) -> left table (bottom to top). The returned
	 * 'left' array is in display order (top row first), so the largest index
	 * sits at the top and the smallest at the bottom.
	 *
	 * @param int $count Number of ordinals.
	 * @return array{north:int, south:int, right:int[], left:int[]}
	 */
	public static function partition_indices( $count ) {
		$count = max( 1, (int) $count );
		$south = intdiv( $count, 2 );

		$right = array();
		for ( $i = 1; $i <= $south - 1; $i++ ) {
			$right[] = $i;
		}

		// Display order is top to bottom, so iterate downwards from the largest.
		$left = array();
		for ( $i = $count - 1; $i >= $south + 1; $i-- ) {
			$left[] = $i;
		}

		return array(
			'north' => $count,
			'south' => $south,
			'right' => $right,
			'left'  => $left,
		);
	}

	/**
	 * Clamp a seed value to the allowed range.
	 *
	 * @param float $seed Raw seed.
	 * @return float
	 */
	public static function clamp_seed( $seed ) {
		return min( SEED_MAX, max( SEED_MIN, (float) $seed ) );
	}

	/**
	 * Clamp an ordinal count to the allowed range.
	 *
	 * Guards the input boundary so an out-of-range `count` attribute can't render a
	 * runaway DOM. The lower bound keeps at least one ordinal; the upper bound caps
	 * the row count.
	 *
	 * @param int $count Raw count.
	 * @return int
	 */
	public static function clamp_count( $count ) {
		return min( COUNT_MAX, max( COUNT_MIN, (int) $count ) );
	}

	/**
	 * Generate the classic Fibonacci sequence (1, 1, 2, 3, ...) as integers.
	 *
	 * @param int $count Number of terms.
	 * @return array<int, int> Keyed by 1-based index.
	 */
	private static function classic_fibonacci( $count ) {
		$sequence = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			if ( $i <= 2 ) {
				$sequence[ $i ] = 1;
			} else {
				$sequence[ $i ] = $sequence[ $i - 1 ] + $sequence[ $i - 2 ];
			}
		}

		return $sequence;
	}

	/**
	 * Format a scaled value (numerator / SEED_SCALE) for display.
	 *
	 * Rounds to at most $decimals places, adds thousands separators to the integer
	 * part, and shows decimals only when non-zero with trailing zeros trimmed
	 * (e.g. 4181.50 -> "4,181.5", 2584.00 -> "2,584"). The rounding is done in
	 * integers so there is no floating-point noise.
	 *
	 * @param int $numerator Value multiplied by SEED_SCALE.
	 * @param int $decimals  Maximum decimal places to display (0..SEED_DECIMALS).
	 * @return string
	 */
	private function format_value( $numerator, $decimals ) {
		// Drop the (SEED_DECIMALS - $decimals) least-significant digits, rounding
		// half up. The seed is non-negative, so the numerator is too.
		$factor  = 10 ** ( SEED_DECIMALS - $decimals );
		$rounded = intdiv( $numerator + intdiv( $factor, 2 ), $factor );

		$unit         = 10 ** $decimals;
		$integer_part = intdiv( $rounded, $unit );
		$fraction     = $rounded % $unit;

		$formatted = number_format( $integer_part );

		if ( $fraction > 0 ) {
			$fraction_string = rtrim( sprintf( '%0' . $decimals . 'd', $fraction ), '0' );
			$formatted      .= '.' . $fraction_string;
		}

		return $formatted;
	}

	/**
	 * Format an angle in degrees, rounded and with trailing zeros trimmed.
	 *
	 * @param float $degrees Angle in degrees.
	 * @return string
	 */
	private static function format_degrees( $degrees ) {
		$formatted = number_format( $degrees, ARC_DECIMALS, '.', '' );

		if ( false !== strpos( $formatted, '.' ) ) {
			$formatted = rtrim( rtrim( $formatted, '0' ), '.' );
		}

		return $formatted;
	}
}
