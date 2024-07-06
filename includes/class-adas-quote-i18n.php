<?php
/**
 * Class for handling internationalization (i18n) for the Adas Quote for WC plugin.
 *
 * @package AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}







/**
 * Class Adas_Quote_I18n
 *
 * Handles the loading of the plugin text domain for translation.
 */
class Adas_Quote_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function load_textdomain() {
		$plugin_rel_path = dirname( plugin_basename( __DIR__ ) ) . '/languages';
		return load_plugin_textdomain( 'adas_quote_request', false, $plugin_rel_path );
	}
}

add_action( 'plugins_loaded', array( 'Adas_Quote_I18n', 'load_textdomain' ) );


/*
add_action(
	'plugins_loaded',
	function () {
		$locale = determine_locale();
		$mofile = WP_PLUGIN_DIR . '/adas-quote-for-wc/languages/adas_quote_request-' . $locale . '.mo';

		error_log( 'Attempting to load MO file: ' . $mofile );

		$loaded = load_textdomain( 'adas_quote_request', $mofile );

		if ( $loaded ) {
			error_log( 'Successfully loaded text domain from file' );
		} else {
			error_log( 'Failed to load text domain from file' );
		}
	},
	0
);*/
