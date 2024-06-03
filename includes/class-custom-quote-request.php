<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AQ', 'adas_quote_request' );

class Custom_Quote_Request {

	public static function init() {
		// error_log( 'Custom_Quote_Request class init' );

		// Hook to enqueue scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		if ( get_option( 'adas_quote_hide_price' ) == 1 ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		// Hook to add custom form with _add_to_quote button
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'display_quote_button_form' ), 25 );

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

	public static function create_kh_woo_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'kh_woo';

		// Check if the table already exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {

			// Table doesn't exist, so create it
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                 id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_id mediumint(9) NOT NULL,
            product_name text NOT NULL,
            product_quantity mediumint(9)  NULL,
            product_type text  NULL,
            product_image text  NULL,
            variation_id mediumint(9)  NULL,
            variations_attr text  NULL,
            message_quote text  NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
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

	public static function display_quote_button_form() {
		global $product;

		// Get selected products and categories from settings
		$selected_products = get_option( 'adas_quote_selected_products', array() );
		// $selected_categories = get_option( 'adas_quote_selected_categories', array() );

		// Check if the current product's category is in the selected categories
		// $product_categories = wp_get_post_terms(
		// $product->get_id(),
		// 'product_cat',
		// array(
		// 'fields'       => 'ids',
		// 'hierarchical' => true,
		// )
		// );

		// if ( ! empty( $selected_categories ) && ! array_intersect( $selected_categories, $product_categories ) ) {
		// return;
		// }
		// Check if the current product is in the selected products
		if ( ! empty( $selected_products ) && ! in_array( $product->get_id(), $selected_products ) ) {
			return;
		}
		$product_type = '';
		$product_     = '';
		if ( function_exists( 'get_product' ) ) {
			$product_     = wc_get_product( get_the_ID() );
			$product_type = ( $product_->get_type() ) ? 'variation' : $product_->get_type();
			if ( $product_->is_type( 'variable' ) ) {
				$class = '_hide';
			}
		}

		// get user email
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
		} else {
			$user_email = '';
		}

		$product_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		if ( $product_image_url && isset( $product_image_url ) ) {
			$post_thumb = $product_image_url[0];
			error_log( 'product_image_url: ' . print_r( $post_thumb, true ) );
			error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		} else {
			$post_thumb = '';
		}

		// Only run for variable products

		echo '<div class="custom-quote-form-wrapper">';
		echo '<form id="custom-quote-form" class="custom-quote-form" action="" method="post">';

		echo '<input type="text" name="action" value="custom_quote_request">';

		echo '<input placeholder="product_name" type="text" name="product_name" value="' . esc_attr( $product->get_name() ) . '">';

		echo '<input type="text" name="product_image" value="' . $post_thumb . '"  />';

		echo '<input type="text" name="product_link" value="' . get_permalink() . '"  />';

		echo '<input type="text" name="product_type" class="product_type" value="' . $product_type . '"  />';

		echo '<input type="text" name="product_id" value="' . esc_attr( $product->get_id() ) . '">';
		echo '<input type="text" class="product_quantity" name="product_quantity" placeholder="Quantity" value="1">';

		// email
		echo '<input type="text" name="adas_user_email" id="adas_user_email" value="' . $user_email . '">';

		if ( $product->is_type( 'variable' ) ) {
			echo '<input type="text" placeholder="variation_id" name="variation_id" id="quote_variation_id">';
			echo '<input type="text" name="variations_attr" id="variationsAttr">';
		}
		echo '<textarea name="message_quote" style="width: 100%;" placeholder="Additional Notes"></textarea>';
		echo '<input type="hidden" name="action" value="adas_send_quote" />';

		echo '<input type="hidden" id="adas_quote_nonce" name="adas_quote_nonce"
    value="' . esc_attr( wp_create_nonce( 'adas_quote_action' ) ) . '" />';

		echo '<button type="submit" class="custom-quote-button">_add_to_quote</button>';
		echo '</form>';
		echo '</div>';

		// Include the modal HTML
		include plugin_dir_path( __FILE__ ) . 'quote-success-modal.php';
	}
	// modal

	public static function send_email( $data ) {
		if ( $_POST ) {

			$useremail = sanitize_email( $_POST['useremail'] );

			// error_log( 'product_id' . $product_id );

		}
		$message = '<p style="font-size: 16px;">You have requested a quote for the following product:</p>';

		$message .= '<div style="width:90%;margin:0 auto;border: 1px solid #e5e5e5;">';
		$message .= '<table style="width: 100%;border-collapse: collapse;">';
		$message .= '<thead>';
		$message .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
		$message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
		$message .= __( 'Product Image', AQ );
		$message .= '</th>';

		$message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
		$message .= __( 'Product Title', AQ );
		$message .= '</th>';

		$message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
		$message .= __( 'Product Variation', AQ );
		$message .= '</th>';
		$message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
		$message .= __( 'Product Quantity', AQ );
		$message .= '</th>';
		$message .= '<th style="width: 16.66%;text-align: center;padding:10px;">';
		$message .= __( 'Total', AQ );
		$message .= '</th>';
		$message .= '</tr>';
		$message .= '</thead>';
		$message .= '<tbody>';

		/*
		$quote_post[] = array(
			'product_id'       => $sub_data['product_id'],
			'product_image'    => $sub_data['product_image'],
			'product_title'    => $sub_data['product_title'],
			'product_price'    => $sub_data['product_price'],
			'product_quantity' => $sub_data['product_quantity'],
			'product_type'     => $sub_data['product_type'],
			'variation_id'     => $sub_data['variation_id'],
			'sub_total'        => $sub_data['sub_total'],
			'quote_total'      => $_POST['quote_total'],
		);*/

		$quote_post[] = $data;

		// Get the price
			$product              = wc_get_product( $quote_post[0]['product_id'] );
			$product_variation_id = $quote_post[0]['product_id'];
			$_product             = wc_get_product( $product_variation_id );
		if ( $_product->is_type( 'simple' ) || $_product->is_type( 'grouped' ) ) {
			$product_price = $product->get_price();
		}

		error_log( 'quote_post: ' . print_r( $quote_post, true ) );

		$message .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
		$message .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		$message .= '<a href="' . $quote_post[0]['product_image'] . '" ><img src="' . $quote_post[0]['product_image'] . '" width="100" /></a>';
		$message .= '</td>';
		$message .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		$message .= $quote_post[0]['product_name'];

		/*get variable product send to admin email*/

		$product = wc_get_product( $quote_post[0]['product_id'] );
		// if ( $product->is_type( 'variable' ) ) {

			$message .= ' : <b>' . get_post_meta( $quote_post[0]['variation_id'], 'attribute_size', true ) . '</b>';
		// }

		$variations_attr = unserialize( $quote_post[0]['variations_attr'] );

					$message .= '</td>';
					$message .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		foreach ( $variations_attr as $attr_name => $attr_value ) {
			$message .= $attr_name . ': ' . $attr_value . '<br>';
		}

					$message   .= '</td>';
					$message   .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
					$message   .= $quote_post[0]['product_quantity'];
					$message   .= '</td>';
					$message   .= '<td style="width: 16.66%;padding:10px;text-align: center;">';
					$message   .= ( $quote_post[0]['product_quantity'] );
					$message   .= '</td>';
					$message   .= '</tr>';
					$product_id = $quote_post[0]['product_id'];
					$product    = wc_get_product( $product_id );
					$quantity   = (int) $quote_post[0]['product_quantity'];
					$sale_price = 200; // $product->get_price();
					// $gett      += $quote_post['sub_total'];

					$message .= '</tbody>';
					$message .= '<tfoot>';
					$message .= '<tr>';
					$message .= '<td></td>';
					$message .= '<td></td>';
					$message .= '<td></td>';
					// $message .= '<td style="width: 16.66%;padding:10px;text-align: center;border-left:1px solid #e5e5e5;">' . __( 'Page link', AQ ) . '</td>';
					// $current_link = $quote_post[0]['current_url'];
		// $message                 .= '<td style="width: 16.66%;padding:10px;text-align: center;border-left:1px solid #e5e5e5;"><a href="' . $current_link . '">' . $current_link . '</a></td>';
		$message             .= '</tr>';
					$message .= '</tfoot>';
					$message .= '</table>';
					$message .= '</div>';

					// Add custom email message from settings
		$custom_email_message = get_option( 'adas_quote_custom_email_message' );
		if ( ! empty( $custom_email_message ) ) {
			$message .= '<p>' . nl2br( esc_html( $custom_email_message ) ) . '</p>';
		}

					// add amin email setting
					$quote_admin_email = get_option( 'wc_settings_quote_admin_email' );
		if ( $quote_admin_email != '' ) {
			$admin_email = $quote_admin_email;
		} else {
			$admin_email = get_option( 'admin_email' );
		}
				$site_title  = get_bloginfo( 'name' );
				$admin_email = get_option( 'admin_email' );
				$to_send     = 'khalidlogi2@gmail.com';
				$attachments = '';

				$headers           = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $site_title . ' <' . $admin_email . '>' );
				$quote_email_title = 'quote_email_title'; // get_option( 'wc_settings_quote_email_subject' );
				$email_title       = ( ! empty( $quote_email_title ) ? $quote_email_title : __( 'Quote', AQ ) );
		if ( wp_mail( $to_send, $email_title, $message, $headers, $attachments ) ) {

			/*
			$remove_quote_after_email = (bool) get_option( 'wc_settings_empty_quote_after_email' );
			if ( $remove_quote_after_email ) {
				setcookie( '_quotes_elem', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			}*/

			$message .= '<p>' . __( 'Quote has been sent to', AQ ) . ' ' . str_replace( ',', ', ', $to_send ) . '</p>';
			wp_mail( $admin_email, __( 'Quote Enquiry', AQ ), $message, $headers, $attachments );

			// }
		}
	}
	public static function handle_quote_request() {
		global $wpdb;

		$nonce = sanitize_text_field( $_POST['custom_quote_request_nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'adas_quote_action' ) ) {
			wp_die( 'nonce not verified' );
		}

			// Get data from AJAX request

		$product_id       = sanitize_text_field( $_POST['product_id'] );
		$product_name     = sanitize_text_field( stripslashes( $_POST['product_name'] ) );
		$product_quantity = sanitize_text_field( $_POST['product_quantity'] );
		$product_type     = sanitize_text_field( $_POST['product_type'] );
		$product_image    = sanitize_text_field( $_POST['product_image'] );
		$variation_id     = sanitize_text_field( $_POST['variation_id'] );
		// $current_url      = $_POST['current_url'];

		$message_quote   = sanitize_text_field( $_POST['message_quote'] );
		$variations_attr = json_decode( stripslashes( $_POST['variations_attr'] ), true );

		// Prepare data for database insertion
		$data = array(
			'product_id'       => $product_id,
			'product_name'     => $product_name,
			'product_quantity' => $product_quantity,
			'product_type'     => $product_type,
			'product_image'    => $product_image,
			'variation_id'     => $variation_id,
			'message_quote'    => $message_quote,
			'variations_attr'  => maybe_serialize( $variations_attr ),
		);

		self::send_email( $data );

		// Check if required data is set
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['variation_id'] ) ) {
			wp_send_json_error( 'Missing required data' );
		}

		// Insert data into 'kh_woo' table
		$wpdb->insert( $wpdb->prefix . 'kh_woo', $data );

		$response = array(
			'success' => true,
			'message' => 'Quote request received successfully!',
			'data'    => $data,
		);

		wp_send_json_success( $response );
		wp_die();
	}
}