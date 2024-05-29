<?php
class Custom_Bootstrap_Enqueue {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	public static function enqueue_scripts() {
		// Ensure jQuery is enqueued
		wp_enqueue_script( 'jquery' );

		// Enqueue Bootstrap CSS
		wp_enqueue_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' );

        // Enqueue custom CSS
		wp_enqueue_style(
			'custom-quote-style',
			plugin_dir_url( __FILE__ ) . '../css/custom-quote.css'
		);
        
		// Enqueue Bootstrap JS
		wp_enqueue_script( 'bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ), null, true );
	}
}

Custom_Bootstrap_Enqueue::init();