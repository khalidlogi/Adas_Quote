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

// Include the main class file
require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-quote-request.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-settings-adas-quote.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-quote-request_enqueue.php';

// Initialize the plugin
add_action( 'plugins_loaded', array( 'Custom_Quote_Request', 'init' ) );

// Register activation hook to create custom table
register_activation_hook( __FILE__, array( 'Custom_Quote_Request', 'create_kh_woo_table' ) );