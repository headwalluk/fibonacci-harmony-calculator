<?php
/**
 * Shortcode handler.
 *
 * @package Fibonacci_Harmony_Calculator
 */

namespace Fibonacci_Harmony_Calculator;

defined( 'ABSPATH' ) || die();

/**
 * Registers and renders the [fibonacci_harmony] shortcode.
 *
 * Assets are registered on wp_enqueue_scripts and only enqueued from within the
 * render callback, so CSS/JS load only on pages that actually use the shortcode.
 */
class Shortcode {

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( SHORTCODE_TAG, array( $this, 'render' ) );
	}

	/**
	 * Register front-end assets (enqueued on demand from render()).
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			ASSET_HANDLE_CSS,
			FHC_ASSETS_URL . 'fhc-public.css',
			array(),
			FHC_VERSION
		);

		wp_register_script(
			ASSET_HANDLE_JS,
			FHC_ASSETS_URL . 'fhc-public.js',
			array(),
			FHC_VERSION,
			true
		);
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render( $atts ) {
		/**
		 * Filter the shortcode's default attribute values.
		 *
		 * @param array $defaults Default attributes (keyed by ATT_* values).
		 */
		$defaults = apply_filters(
			'fhc_default_atts',
			array(
				ATT_SEED  => DEF_SEED,
				ATT_COUNT => DEF_COUNT,
				ATT_IMAGE => '',
			)
		);

		$atts = shortcode_atts( $defaults, $atts, SHORTCODE_TAG );

		$seed  = Calculator::clamp_seed( $atts[ ATT_SEED ] );
		$count = Calculator::clamp_count( $atts[ ATT_COUNT ] );

		$image = '' !== trim( (string) $atts[ ATT_IMAGE ] )
			? $atts[ ATT_IMAGE ]
			: FHC_ASSETS_URL . DEF_WHEEL_IMAGE;

		/**
		 * Filter the wheel graphic URL before it is escaped and output.
		 *
		 * @param string $image The wheel image URL.
		 * @param array  $atts  The resolved shortcode attributes.
		 */
		$image_url = esc_url( apply_filters( 'fhc_wheel_image_url', $image, $atts ) );

		$calculator  = new Calculator( $seed, $count );
		$instance_id = wp_unique_id( 'fhc-' );

		// Load assets now that we know the shortcode is on the page.
		wp_enqueue_style( ASSET_HANDLE_CSS );
		wp_enqueue_script( ASSET_HANDLE_JS );

		$output = $this->render_template(
			array(
				'calculator'  => $calculator,
				'instance_id' => $instance_id,
				'image_url'   => $image_url,
			)
		);

		/**
		 * Filter the final rendered shortcode HTML.
		 *
		 * @param string     $output     Rendered HTML markup.
		 * @param Calculator $calculator The calculator instance for this render.
		 * @param array      $atts       The resolved shortcode attributes.
		 */
		return apply_filters( 'fhc_output', $output, $calculator, $atts );
	}

	/**
	 * Render the public template, allowing theme overrides.
	 *
	 * @param array $args Variables passed to the template.
	 * @return string Buffered template output.
	 */
	private function render_template( array $args ) {
		$template = locate_template( SHORTCODE_TAG . '/calculator.php' );
		if ( ! $template ) {
			$template = FHC_PUBLIC_TEMPLATES_DIR . 'calculator.php';
		}

		/**
		 * Filter the absolute path to the template used to render the shortcode.
		 *
		 * @param string $template Absolute path to the template file.
		 */
		$template = apply_filters( 'fhc_template', $template );

		// Variables consumed by the template.
		$calculator  = $args['calculator'];
		$instance_id = $args['instance_id'];
		$image_url   = $args['image_url'];

		ob_start();
		include $template;
		return ob_get_clean();
	}
}
