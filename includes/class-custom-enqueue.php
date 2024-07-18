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
	public static function adas_quote_admin_styles( $hook_suffix ) {
		// Check for the correct page
		if ( 'settings_page_adas-quote-settings' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'adas-quote-admin-styles', plugin_dir_url( __FILE__ ) . '../css/admin-styles.css', array(), '1.0.0' );

		wp_enqueue_media();
		wp_enqueue_script( 'adas-admin-script', plugin_dir_url( __FILE__ ) . '../js/admin-script.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_script( 'adas-quote-user-roles', plugin_dir_url( __FILE__ ) . '../js/adas-quote-user-roles.js', array( 'jquery' ), '1.0.0', true );

		// Localize the script with new data.
		$script_data = array(
			'roles'      => wp_roles()->get_names(),
			'savedRoles' => get_option( 'adas_quote_user_roles', '' ),
		);
		wp_localize_script( 'adas-quote-user-roles', 'adasQuoteUserRoles', $script_data );
	}

	/**
	 * Enqueue necessary scripts and styles for the front-end.
	 */
	public static function enqueue_scripts() {
		// Ensure jQuery is enqueued.
		wp_enqueue_script( 'jquery' );

		// Enqueue Bootstrap CSS.
		wp_enqueue_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2' );

		// Bootstrap JS bundle
		wp_enqueue_script( 'bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );

		// Enqueue custom CSS.
		wp_enqueue_style(
			'custom-quote-style',
			plugin_dir_url( __FILE__ ) . '../css/custom-quote.css',
			array(),
			'1.0.0'
		);

		if ( get_option( 'adas_quote_recaptcha_site_kzey' ) && get_option( 'adas_quote_recaptcha_secret_key' ) ) {
			wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
		}

		// Enqueue Bootstrap JS.
		wp_enqueue_script( 'bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array( 'jquery' ), '4.5.2', true );
	}
}

Custom_Enqueue::init();
