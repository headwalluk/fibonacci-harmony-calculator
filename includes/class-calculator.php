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
 * Precision: the seed resolves to the nearest 1/SEED_SCALE, so each value is the
 * integer (classic Fibonacci x scaled-seed) divided by SEED_SCALE. Working in
 * integers avoids floating-point artifacts. PHP's native 64-bit int holds the
 * scaled product up to roughly count = 80; beyond that the front-end BigInt path
 * is authoritative for the live view.
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
	 *                                                with index, value, arc_standard, arc_asian.
	 */
	public function get_ordinals() {
		$classic    = self::classic_fibonacci( $this->count );
		$std_step   = CIRCLE_DEGREES_STANDARD / $this->count;
		$asian_step = CIRCLE_DEGREES_ASIAN / $this->count;

		$ordinals = array();
		foreach ( $classic as $index => $classic_value ) {
			$ordinals[ $index ] = array(
				'index'        => $index,
				'value'        => $this->format_value( $classic_value * $this->scaled_seed ),
				'arc_standard' => self::format_degrees( $index * $std_step ),
				'arc_asian'    => self::format_degrees( $index * $asian_step ),
			);
		}

		return $ordinals;
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
	 * Adds thousands separators to the integer part and shows decimals only when
	 * non-zero, with trailing zeros trimmed (4181.50 -> "4,181.5", 2584.00 ->
	 * "2,584").
	 *
	 * @param int $numerator Value multiplied by SEED_SCALE.
	 * @return string
	 */
	private function format_value( $numerator ) {
		$integer_part = intdiv( $numerator, SEED_SCALE );
		$fraction     = abs( $numerator % SEED_SCALE );

		$formatted = number_format( $integer_part );

		if ( $fraction > 0 ) {
			// SEED_SCALE is 100, so the fraction is at most two digits.
			$fraction_string = rtrim( sprintf( '%02d', $fraction ), '0' );
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
