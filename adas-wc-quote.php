<?php
/**
 * Plugin Name: Adas Quote for WC
 * Description: Request a Quote for WooCommerce.
 * Version: 1.0.0
 * Author: Khalidlogi
 * Text Domain: adas_quote_request
 *
 * @package AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Initialize the database for the plugin.
 */
function adas_db_init() {
	if ( is_admin() ) {
		require_once 'adas-quote-wplist.php';
		require_once 'includes/class-adas-quote-form-details.php';
		require_once 'includes/adas-quote-details-ufd.php';
	}
}

add_action( 'init', 'adas_db_init' );

// Include the main class file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-quote-request.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-settings-adas-quote.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-enqueue.php';

// Include the  class-plugintoolbox.php file.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugintoolbox.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-quote-button-form.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class_kh_woo_db.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-aq-error-logger.php';

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'Custom_Quote_Request', 'init' ) );

/**
 *
 *  This function is used to initialize the plugin.
 *
 * @return void
 */
function adas_quote_init() {
	ADAS_Quote_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'adas_quote_init' );

// Register activation hook to create custom table.
register_activation_hook( __FILE__, array( 'KH_Woo_DB', 'create_kh_woo_table' ) );