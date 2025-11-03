<?php
/**
 * Plugin Name: Featured Archive Scroller
 * Plugin URI: https://github.com/yourusername/featured-archive-scroller
 * Description: Adds a /gallery endpoint to archive URLs, displaying featured images in a horizontal scrolling gallery.
 * Version: 1.0.0
 * Author: Donncha O Caoimh
 * Author URI: https://odd.blog
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: featured-archive-scroller
 *
 * @package FeaturedArchiveScroller
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'FAS_VERSION', '1.0.6' );
define( 'FAS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FAS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FAS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
class Featured_Archive_Scroller {

	/**
	 * Single instance of the class.
	 *
	 * @var Featured_Archive_Scroller
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class.
	 *
	 * @return Featured_Archive_Scroller
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once FAS_PLUGIN_DIR . 'includes/class-endpoint.php';
		require_once FAS_PLUGIN_DIR . 'includes/class-settings.php';
		require_once FAS_PLUGIN_DIR . 'includes/class-render.php';
		require_once FAS_PLUGIN_DIR . 'includes/class-debug.php';
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components() {
		FAS_Endpoint::get_instance();
		FAS_Settings::get_instance();
		FAS_Render::get_instance();
		FAS_Debug::get_instance();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// No additional hooks needed here.
		// WordPress.org automatically loads translations for plugins since WP 4.6.
	}
}

/**
 * Plugin activation hook.
 */
function fas_activate_plugin() {
	// Load the endpoint class if not already loaded.
	if ( ! class_exists( 'FAS_Endpoint' ) ) {
		require_once FAS_PLUGIN_DIR . 'includes/class-endpoint.php';
	}

	// Initialize the endpoint instance to register rewrite rules.
	$endpoint = FAS_Endpoint::get_instance();
	$endpoint->register_endpoint();

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'fas_activate_plugin' );

/**
 * Plugin deactivation hook.
 */
function fas_deactivate_plugin() {
	// Flush rewrite rules to remove custom endpoint.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'fas_deactivate_plugin' );

/**
 * Initialize the plugin.
 */
function fas_init() {
	return Featured_Archive_Scroller::get_instance();
}

// Start the plugin.
fas_init();
