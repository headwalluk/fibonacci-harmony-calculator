<?php
/**
 * Public template for the [fibonacci_harmony] shortcode.
 *
 * Renders the clock/cross layout: a North call-out and a left table on one side
 * of the wheel, a right table and a South call-out on the other. See
 * dev-notes/01-requirements.md section 4 for the exact arrangement.
 *
 * Available variables:
 *
 * @var \Fibonacci_Harmony_Calculator\Calculator $calculator  Sequence calculator.
 * @var string                                   $instance_id Unique container id.
 * @var string                                   $image_url   Wheel graphic URL (escaped).
 *
 * @package Fibonacci_Harmony_Calculator
 */

namespace Fibonacci_Harmony_Calculator;

defined( 'ABSPATH' ) || die();

$fhc_ordinals = $calculator->get_ordinals();
$fhc_count    = $calculator->get_count();
$fhc_seed     = $calculator->get_seed();
$fhc_parts    = Calculator::partition_indices( $fhc_count );

/**
 * Echo the four table cells for one ordinal. The value cell carries
 * data-fhc-index so the front-end script can update it live.
 *
 * @param array $ordinal Ordinal data (index, value, arc_standard, arc_asian).
 * @return void
 */
$fhc_render_cells = static function ( array $ordinal ) {
	printf(
		'<td class="fhc-cell fhc-cell--index">%1$s</td>' .
		'<td class="fhc-cell fhc-cell--value" data-fhc-index="%2$d">%3$s</td>' .
		'<td class="fhc-cell fhc-cell--arc">%4$s&deg;</td>' .
		'<td class="fhc-cell fhc-cell--arc">%5$s&deg;</td>',
		esc_html( $ordinal['index'] ),
		(int) $ordinal['index'],
		esc_html( $ordinal['value'] ),
		esc_html( $ordinal['arc_standard'] ),
		esc_html( $ordinal['arc_asian'] )
	);
};

/**
 * Echo a number table for a list of indices, in the given display order.
 *
 * @param int[] $indices  Ordinal indices, top row first.
 * @param array $ordinals All ordinals keyed by index.
 * @param array $cells_cb Cell renderer callback.
 * @return void
 */
$fhc_render_table = static function ( array $indices, array $ordinals, callable $cells_cb ) {
	if ( empty( $indices ) ) {
		return;
	}
	?>
	<table class="fhc-table">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html_x( '#', 'table column: ordinal index', 'fibonacci-harmony-calculator' ); ?></th>
				<th scope="col"><?php echo esc_html_x( 'Value', 'table column: the Fibonacci number', 'fibonacci-harmony-calculator' ); ?></th>
				<th scope="col"><?php echo esc_html_x( 'Std', 'table column: standard 360-degree arc angle', 'fibonacci-harmony-calculator' ); ?></th>
				<th scope="col"><?php echo esc_html_x( 'Asian', 'table column: Asian 432-degree arc angle', 'fibonacci-harmony-calculator' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $indices as $fhc_index ) : ?>
				<?php if ( isset( $ordinals[ $fhc_index ] ) ) : ?>
					<tr><?php $cells_cb( $ordinals[ $fhc_index ] ); ?></tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
};

/**
 * Echo a single-value call-out box (North / South).
 *
 * @param array  $ordinal Ordinal data.
 * @param string $label   Position label (e.g. North).
 * @return void
 */
$fhc_render_callout = static function ( array $ordinal, $label ) {
	?>
	<div class="fhc-callout">
		<span class="fhc-callout__label"><?php echo esc_html( $label ); ?></span>
		<span class="fhc-callout__index">
			<?php
			/* translators: %s: ordinal index. */
			printf( esc_html__( '#%s', 'fibonacci-harmony-calculator' ), esc_html( $ordinal['index'] ) );
			?>
		</span>
		<span class="fhc-callout__value" data-fhc-index="<?php echo (int) $ordinal['index']; ?>"><?php echo esc_html( $ordinal['value'] ); ?></span>
		<span class="fhc-callout__arcs">
			<?php echo esc_html( $ordinal['arc_standard'] ); ?>&deg; / <?php echo esc_html( $ordinal['arc_asian'] ); ?>&deg;
		</span>
	</div>
	<?php
};
?>
<div
	class="fhc-calculator"
	id="<?php echo esc_attr( $instance_id ); ?>"
	data-fhc-count="<?php echo esc_attr( $fhc_count ); ?>"
	data-fhc-seed="<?php echo esc_attr( $fhc_seed ); ?>"
	data-fhc-seed-min="<?php echo esc_attr( SEED_MIN ); ?>"
	data-fhc-seed-max="<?php echo esc_attr( SEED_MAX ); ?>"
	data-fhc-seed-step="<?php echo esc_attr( SEED_STEP ); ?>"
>
	<div class="fhc-controls">
		<label class="fhc-controls__label" for="<?php echo esc_attr( $instance_id ); ?>-seed">
			<?php echo esc_html_x( 'Seed', 'calculator input: starting value for the sequence', 'fibonacci-harmony-calculator' ); ?>
		</label>
		<input
			type="range"
			class="fhc-seed fhc-seed--range"
			min="<?php echo esc_attr( SEED_MIN ); ?>"
			max="<?php echo esc_attr( SEED_MAX ); ?>"
			step="<?php echo esc_attr( SEED_STEP ); ?>"
			value="<?php echo esc_attr( $fhc_seed ); ?>"
			aria-label="<?php esc_attr_e( 'Seed value slider', 'fibonacci-harmony-calculator' ); ?>"
		/>
		<input
			type="number"
			id="<?php echo esc_attr( $instance_id ); ?>-seed"
			class="fhc-seed fhc-seed--number"
			min="<?php echo esc_attr( SEED_MIN ); ?>"
			max="<?php echo esc_attr( SEED_MAX ); ?>"
			step="<?php echo esc_attr( SEED_STEP ); ?>"
			value="<?php echo esc_attr( $fhc_seed ); ?>"
		/>
	</div>

	<p class="fhc-mobile-note">
		<?php esc_html_e( 'This visualisation works best in landscape or on a larger screen.', 'fibonacci-harmony-calculator' ); ?>
	</p>

	<div class="fhc-stage">
		<div class="fhc-region fhc-region--north">
			<?php $fhc_render_callout( $fhc_ordinals[ $fhc_parts['north'] ], esc_html_x( 'North', 'position on the wheel: top / 12 o\'clock', 'fibonacci-harmony-calculator' ) ); ?>
		</div>

		<div class="fhc-region fhc-region--left">
			<?php $fhc_render_table( $fhc_parts['left'], $fhc_ordinals, $fhc_render_cells ); ?>
		</div>

		<div class="fhc-region fhc-region--centre">
			<img class="fhc-wheel" src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Fibonacci 60 pattern wheel', 'fibonacci-harmony-calculator' ); ?>" />
		</div>

		<div class="fhc-region fhc-region--right">
			<?php $fhc_render_table( $fhc_parts['right'], $fhc_ordinals, $fhc_render_cells ); ?>
		</div>

		<div class="fhc-region fhc-region--south">
			<?php $fhc_render_callout( $fhc_ordinals[ $fhc_parts['south'] ], esc_html_x( 'South', 'position on the wheel: bottom / 6 o\'clock', 'fibonacci-harmony-calculator' ) ); ?>
		</div>
	</div>
</div>
