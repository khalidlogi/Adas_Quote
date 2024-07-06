<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'adas_quote_request', 'adas_quote_request' );

class Custom_Variation_Alert {
	public static function init() {
		// Hook to enqueue scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		// Hook to add custom form for variations
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'display_variation_options_form' ), 25 );
	}

	public static function enqueue_scripts() {
		// Enqueue jQuery
		wp_enqueue_script( 'jquery' );

		// Enqueue custom script
		wp_enqueue_script(
			'custom-variation-script',
			plugin_dir_url( __FILE__ ) . '../js/custom-variation.js',
			array( 'jquery' ),
			null,
			true
		);
	}

	public static function display_variation_options_form() {
		global $product;

		// Only run for variable products
		if ( $product->is_type( 'variable' ) ) {
			echo '<form id="custom-variation-form">';
			echo '<button type="button">Check Selected Variation</button>';
			echo '</form>';
		}
	}
}
