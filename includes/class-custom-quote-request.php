<?php
/**
 * Custom Quote Request Handler
 *
 * This file contains the main class for handling custom quote requests.
 *
 * @package AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class Custom_Quote_Request
 */
class Custom_Quote_Request {

	/**
	 * Function initialize
	 */
	public static function init() {

		// Hook to enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		if ( get_option( 'adas_quote_hide_price' ) == 1 ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		add_action( 'wp_footer', array( __CLASS__, 'AQ_hide_add_to_cart_button' ) );

		// Hook to handle form submission.
		add_action( 'wp_ajax_nopriv_handle_quote_request', array( __CLASS__, 'handle_quote_request' ) );
		add_action( 'wp_ajax_handle_quote_request', array( __CLASS__, 'handle_quote_request' ) );
	}

	/**
	 * Hide Add to Cart Button.
	 *
	 * This function hides the add to cart button on single product pages
	 * if the 'adas_quote_hide_add_to_cart' option is set to 1.
	 *
	 * @return void
	 */
	public static function AQ_hide_add_to_cart_button() {
		if ( get_option( 'adas_quote_hide_add_to_cart' ) == 1 ) {
			?>
<style type="text/css">
.single-product .single_add_to_cart_button,
.single-product button.single_add_to_cart_button,
.single-product a.single_add_to_cart_button {
	display: none !important;
}
</style>

			<?php
		}
	}



	/**
	 * Enqueue necessary scripts for the custom quote functionality.
	 *
	 * This function ensures that jQuery is enqueued and then enqueues a custom script
	 * for handling the quote request functionality. It also localizes the script to
	 * pass the AJAX URL to the JavaScript.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		// Ensure jQuery is enqueued.
		wp_enqueue_script( 'jquery' );

		// Enqueue custom script.
		wp_enqueue_script(
			'custom-quote-script',
			plugin_dir_url( __FILE__ ) . '../js/custom-quote.js',
			array( 'jquery' ),
			'1.0.0', // Version number added here.
			true
		);

		// Localize script to send AJAX URL to JavaScript.
		wp_localize_script(
			'custom-quote-script',
			'custom_quote_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}





	/**
	 * Handle Quote Request
	 *
	 * @return void
	 */
	public static function handle_quote_request() {
		global $wpdb;

		// Verify nonce.
		$nonce = isset( $_POST['custom_quote_request_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_quote_request_nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'adas_quote_action' ) ) {
			wp_send_json_error( 'Nonce not verified', 403 );
			wp_die();
		}

		// Sanitize and validate inputs.
		$product_id       = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$product_name     = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
		$product_quantity = isset( $_POST['product_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['product_quantity'] ) ) : '';
		$useremail        = isset( $_POST['useremail'] ) ? sanitize_email( wp_unslash( $_POST['useremail'] ) ) : '';
		$phone_number     = isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';
		$product_type     = isset( $_POST['product_type'] ) ? sanitize_text_field( wp_unslash( $_POST['product_type'] ) ) : '';
		$product_image    = isset( $_POST['product_image'] ) ? esc_url_raw( wp_unslash( $_POST['product_image'] ) ) : '';
		$variation_id     = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : '';
		$message_quote    = isset( $_POST['message_quote'] ) ? sanitize_text_field( wp_unslash( $_POST['message_quote'] ) ) : '';
		$variations_attr  = isset( $_POST['variations_attr'] ) ? json_decode( stripslashes( sanitize_text_field( wp_unslash( $_POST['variations_attr'] ) ) ), true ) : array();

		// Verify reCAPTCHA.
		if ( get_option( 'adas_quote_recaptcha_site_key' ) !== ''
		&& get_option( 'adas_quote_recaptcha_secret_key' ) !== ''
		&& get_option( 'adas_quote_enable_recaptcha' ) !== '' ) {

			$recaptcha_secret = get_option( 'adas_quote_recaptcha_secret_key' );
			if ( isset( $_POST['g-recaptcha-response'] ) ) {
				$recaptcha_response = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
			}
			$verify_response = wp_remote_get(
				"https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response"
			);

			if ( is_wp_error( $verify_response ) ) {
				wp_send_json_error( 'Failed to verify reCAPTCHA' );
				return;
			}
		}

		// Validate required fields.
		if ( empty( $product_id ) ) {

			wp_send_json_error( 'Missing required data', 400 );
			wp_die();
		}

		// Prepare data for database insertion.
		$data = array(
			'product_id'       => $product_id,
			'product_name'     => $product_name,
			'product_quantity' => $product_quantity,
			'user_email'       => $useremail,
			'phone_number'     => $phone_number,
			'date_submitted'   => current_time( 'mysql' ),
			'page_id'          => get_permalink( $product_id ),
			'product_type'     => $product_type,
			'product_image'    => $product_image,
			'variation_id'     => $variation_id,
			'message_quote'    => $message_quote,
			'variations_attr'  => maybe_serialize( $variations_attr ),
		);

		// Send email.
		PluginToolbox::send_email( $data );

		// Insert data into 'kh_woo' table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert( $wpdb->prefix . 'kh_woo', $data );

		// Send success response.
		$response = array(
			'success' => true,
			'message' => 'Quote request received successfully!',
			'data'    => $data,
		);

		wp_send_json_success( $response );
		wp_die();
	}
}