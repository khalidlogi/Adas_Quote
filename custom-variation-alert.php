<?php
/**
 * Plugin Name: Custom Variation Alert
 * Description: Display an alert when a WooCommerce product variation is changed.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: custom-variation-alert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


add_action( 'init', 'adas_db_init' );

function adas_db_init() {
	if ( is_admin() ) {
		require_once 'adas-quote-wplist.php';
		require_once 'includes/adas-quote-details.php';
		require_once 'includes/adas-quote-details-ufd.php';
	}
}

// Include the main class file
require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-quote-request.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-settings-adas-quote.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-quote-request_enqueue.php';

// Include the functions.php file
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcode-request.php';

// Initialize the plugin
add_action( 'plugins_loaded', array( 'Custom_Quote_Request', 'init' ) );

// Register activation hook to create custom table
register_activation_hook( __FILE__, array( 'Custom_Quote_Request', 'create_kh_woo_table' ) );