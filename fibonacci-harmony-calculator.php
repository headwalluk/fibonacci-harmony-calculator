<?php
/**
 * Plugin Name:       Fibonacci Harmony Calculator
 * Plugin URI:        https://headwall-hosting.com/
 * Description:       Shortcode that renders an interactive calculator and graphic exploring the Fibonacci 60 Repeating Pattern.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fibonacci-harmony-calculator
 * Domain Path:       /languages
 *
 * @package Fibonacci_Harmony_Calculator
 */

defined( 'ABSPATH' ) || die();

const FHC_NAME    = 'fibonacci-harmony-calculator';
const FHC_VERSION = '1.0.0';

define( 'FHC_DIR', plugin_dir_path( __FILE__ ) );
define( 'FHC_URL', plugin_dir_url( __FILE__ ) );
define( 'FHC_INCLUDES_DIR', trailingslashit( FHC_DIR . 'includes' ) );
define( 'FHC_PUBLIC_TEMPLATES_DIR', trailingslashit( FHC_DIR . 'public-templates' ) );
define( 'FHC_ASSETS_URL', trailingslashit( FHC_URL . 'assets' ) );

// Load constants and plugin classes.
require_once FHC_DIR . 'constants.php';
require_once FHC_INCLUDES_DIR . 'class-calculator.php';
require_once FHC_INCLUDES_DIR . 'class-shortcode.php';
require_once FHC_INCLUDES_DIR . 'class-plugin.php';

/**
 * Launch the plugin core.
 *
 * @return void
 */
function fhc_plugin_run() {
	global $fhc_plugin;

	$fhc_plugin = new Fibonacci_Harmony_Calculator\Plugin();
	$fhc_plugin->run();
}
fhc_plugin_run();
