<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AQ', 'adas_quote_request' );

class Custom_Quote_Request {

	public static function init() {

		// add_action( 'template_redirect', array( __CLASS__, 'get_current_page_id' ) );

		// Hook to enqueue scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		if ( get_option( 'adas_quote_hide_price' ) === 1 ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		// Hook to add custom form with _add_to_quote button
		// add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'display_quote_button_form' ), 25 );

		add_action( 'wp_footer', array( __CLASS__, 'AQ_hide_add_to_cart_button' ) );

		// Hook to handle form submission
		add_action( 'wp_ajax_nopriv_handle_quote_request', array( __CLASS__, 'handle_quote_request' ) );
		add_action( 'wp_ajax_handle_quote_request', array( __CLASS__, 'handle_quote_request' ) );
	}

	static function AQ_hide_add_to_cart_button() {
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



	public static function enqueue_scripts() {
		// Ensure jQuery is enqueued
		wp_enqueue_script( 'jquery' );

		// Enqueue custom script
		wp_enqueue_script(
			'custom-quote-script',
			plugin_dir_url( __FILE__ ) . '../js/custom-quote.js',
			array( 'jquery' ),
			null,
			true
		);

		// Localize script to send AJAX URL to JavaScript
		wp_localize_script(
			'custom-quote-script',
			'custom_quote_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}






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
		$product_name     = isset( $_POST['product_name'] ) ? sanitize_text_field( stripslashes( wp_unslash( $_POST['product_name'] ) ) ) : '';
		$product_quantity = isset( $_POST['product_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['product_quantity'] ) ) : '';
		$useremail        = isset( $_POST['useremail'] ) ? sanitize_email( wp_unslash( $_POST['useremail'] ) ) : '';
		$phone_number     = isset( $_POST['phone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['phone_number'] ) ) : '';
		$product_type     = isset( $_POST['product_type'] ) ? sanitize_text_field( wp_unslash( $_POST['product_type'] ) ) : '';
		$product_image    = isset( $_POST['product_image'] ) ? esc_url_raw( wp_unslash( $_POST['product_image'] ) ) : '';
		$variation_id     = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : '';
		$message_quote    = isset( $_POST['message_quote'] ) ? sanitize_text_field( wp_unslash( $_POST['message_quote'] ) ) : '';
		$variations_attr  = isset( $_POST['variations_attr'] ) ? json_decode( stripslashes( wp_unslash( $_POST['variations_attr'] ) ), true ) : array();

		// Validate required fields.
		if ( empty( $product_id ) || empty( $variation_id ) || empty( $useremail ) ) {
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

		// Send email
		PluginToolbox::send_email( $data );

		// Insert data into 'kh_woo' table.
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
