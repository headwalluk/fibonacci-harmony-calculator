<?php
/**
 * Plugin core.
 *
 * @package Fibonacci_Harmony_Calculator
 */

namespace Fibonacci_Harmony_Calculator;

defined( 'ABSPATH' ) || die();

/**
 * The plugin's core orchestrator. All hooks are registered here; behaviour lives
 * in the respective classes.
 */
class Plugin {

	/**
	 * Shortcode handler.
	 *
	 * @var Shortcode
	 */
	private $shortcode;

	/**
	 * Set up the plugin's handlers. Run before WordPress has initialised.
	 *
	 * @return void
	 */
	public function run() {
		$this->shortcode = new Shortcode();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this->shortcode, 'register_assets' ) );
	}

	/**
	 * WP init action handler.
	 *
	 * @return void
	 */
	public function init() {
		$this->load_textdomain();
		$this->shortcode->register();
	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			TEXT_DOMAIN,
			false,
			dirname( plugin_basename( FHC_DIR . FHC_NAME . '.php' ) ) . '/languages'
		);
	}
}
