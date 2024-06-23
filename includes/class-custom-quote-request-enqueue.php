<?php
/**
 * Class Custom_Bootstrap_Enqueue
 *
 * Handles the enqueuing of scripts and styles for both the front-end and admin pages.
 */
class Custom_Enqueue {
	/**
	 * Initialize the class by setting up the necessary actions.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'adas_quote_admin_styles' ) );
	}

	/**
	 * Enqueue custom CSS for the admin settings page.
	 */
	public static function adas_quote_admin_styles() {
		wp_enqueue_style( 'adas-quote-admin-styles', plugin_dir_url( __FILE__ ) . '../css/admin-styles.css', array(), '1.0.0' );
	}

	/**
	 * Enqueue necessary scripts and styles for the front-end.
	 */
	public static function enqueue_scripts() {
		// Ensure jQuery is enqueued
		wp_enqueue_script( 'jquery' );

		// Enqueue Bootstrap CSS.
		wp_enqueue_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2' );

		// Enqueue custom CSS.
		wp_enqueue_style(
			'custom-quote-style',
			plugin_dir_url( __FILE__ ) . '../css/custom-quote.css',
			array(),
			'1.0.0'
		);

		// Enqueue Bootstrap JS.
		wp_enqueue_script( 'bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ), '4.5.2', true );
	}
}

Custom_Enqueue::init();
