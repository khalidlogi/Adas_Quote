<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AQ', 'adas_quote_request' );

class Custom_Quote_Request {

	public static function init() {
		// error_log( 'Custom_Quote_Request class init' );

		add_action( 'template_redirect', array( __CLASS__, 'get_current_page_id' ) );

		// Hook to enqueue scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		if ( get_option( 'adas_quote_hide_price' ) == 1 ) {
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

	public static function create_kh_woo_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'kh_woo';

		// Check if the table already exists
		$table_name = $wpdb->prefix . 'kh_woo';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                product_id mediumint(9) NOT NULL,
                product_name text NOT NULL,
                product_quantity mediumint(9) NULL,
                product_type text NULL,
                product_image text NULL,
                user_email text NOT NULL,
                phone_number text NULL,
                variation_id mediumint(9) NULL,
                variations_attr text NULL,
                message_quote text NULL,
                date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_status INT DEFAULT 0,
        read_date TIMESTAMP NULL,                
        page_id varchar(255) NOT NULL,
                PRIMARY KEY (id),
                KEY product_id (product_id),
                KEY variation_id (variation_id),
                KEY date_submitted (date_submitted)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			// Check for errors during table creation
			if ( $wpdb->last_error !== '' ) {
				error_log( 'Error creating table: ' . $wpdb->last_error );
			}
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



	/*
	public static function display_quote_button_form() {
		global $product;

		// Use the PluginToolbox class
		$all_category_ids = PluginToolbox::displayAllProductCategories();
		error_log( '$all_category_ids: ' . print_r( $all_category_ids, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		// print_r( 'ssss' . $all_category_ids );

		// Get selected products and categories from settings.
		$selected_products = get_option( 'adas_quote_selected_products', array() );
		error_log( '$selected_products: ' . print_r( $selected_products, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		$selected_categories = get_option( 'adas_quote_selected_categories', array() );

		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		$product_categories = get_the_terms( $product->get_id(), 'product_cat' );
		// Get all the categories
		$all_categories = get_categories();

		// Loop through the categories and display them
		if ( ! empty( $all_categories ) ) {
			echo '<ul>';
			foreach ( $all_categories as $category ) {
				echo '<li>Category ID: ' . $category->term_id . ', Category Name: ' . $category->name . '</li>';
			}
			echo '</ul>';
		} else {
			echo 'No categories found.';
		}

		$category_id = array();
		// Check if any categories were found
		if ( ! empty( $product_categories ) ) {
			// Loop through the categories
			foreach ( $product_categories as $category ) {
				// Access category properties
				$category_id[] = $category->term_id;
				error_log( '$category_id[]: ' . print_r( $category->term_id, true ) );
				error_log( '$name[]: ' . print_r( $category->name, true ) );
			}
		}

				$intersection = array_intersect( $category_id, explode( ',', $selected_categories[0] ) );
				error_log( ' $selected_products: ' . print_r( $selected_products, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		error_log( ' $product->get_id(): ' . print_r( $product->get_id(), true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

		if ( empty( $intersection ) && ! in_array( $product->get_id(), $selected_products ) ) {

			error_log( 'Array A does not have any element that is present in Array B.' );
			return;     }

				$product_type = '';
				$product_     = '';

		if ( function_exists( 'get_product' ) ) {
			$product_     = wc_get_product( get_the_ID() );
			$product_type = ( $product_->get_type() ) ? 'variation' : $product_->get_type();
			if ( $product_->is_type( 'variable' ) ) {
				$class = '_hide';
			}
		}

				// get user email.
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

				// Only run for variable products.

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

			*/
	public static function send_email( $data ) {
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

		$quote_post[] = $data;

		$product              = wc_get_product( $quote_post[0]['product_id'] );
		$product_variation_id = $quote_post[0]['product_id'];
		$_product             = wc_get_product( $product_variation_id );
		if ( $_product->is_type( 'simple' ) || $_product->is_type( 'grouped' ) ) {
			$product_price = $product->get_price();
		}

		$message        .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
		$message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		$message        .= '<a href="' . $quote_post[0]['product_image'] . '" ><img src="' . $quote_post[0]['product_image'] . '" width="100" /></a>';
		$message        .= '</td>';
		$message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		$message        .= $quote_post[0]['product_name'];
		$message        .= ' : <b>' . get_post_meta( $quote_post[0]['variation_id'], 'attribute_size', true ) . '</b>';
		$message        .= '</td>';
		$message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
		$variations_attr = unserialize( $quote_post[0]['variations_attr'] );
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

		$message .= '</tbody>';
		$message .= '<tfoot>';
		$message .= '<tr>';
		$message .= '<td></td>';
		$message .= '<td></td>';
		$message .= '<td></td>';
		$message .= '</tr>';
		$message .= '</tfoot>';
		$message .= '</table>';
		$message .= '</div>';

		$custom_email_message = get_option( 'adas_quote_custom_email_message' );
		if ( ! empty( $custom_email_message ) ) {
			$message .= '<p>' . nl2br( esc_html( $custom_email_message ) ) . '</p>';
		}

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
			$message .= '<p>' . __( 'Quote has been sent to', AQ ) . ' ' . str_replace( ',', ', ', $to_send ) . '</p>';
			wp_mail( $admin_email, __( 'Quote Enquiry', AQ ), $message, $headers, $attachments );
		}
	}

	public static function get_current_page_id() {
		// return wc_get_product_id_by_url();
	}


	public static function handle_quote_request() {
		global $wpdb;

		$nonce = sanitize_text_field( $_POST['custom_quote_request_nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'adas_quote_action' ) ) {
			wp_die( 'nonce not verified' );
		}

			// Get data from AJAX request

		$product_id = sanitize_text_field( $_POST['product_id'] );
		error_log( 'handle_quote_request $product_id: ' . print_r( $product_id, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		$product_name     = sanitize_text_field( stripslashes( $_POST['product_name'] ) );
		$product_quantity = sanitize_text_field( $_POST['product_quantity'] );
		$useremail        = sanitize_text_field( $_POST['useremail'] );
		$phone_number     = sanitize_text_field( $_POST['phone_number'] );
		error_log('$phone_number : ' . print_r($phone_number , true)); 
		error_log('in ' . __FILE__ . ' on line ' . __LINE__); 
		$product_type     = sanitize_text_field( $_POST['product_type'] );
		$product_image    = sanitize_text_field( $_POST['product_image'] );
		$variation_id     = sanitize_text_field( $_POST['variation_id'] );
		// $current_url      = $_POST['current_url'];
			// Get the current page ID
		// $id      = self::get_current_page_id();
		$page_id = get_permalink( $id );
			error_log( '$page_url: ' . print_r( $page_id, true ) );
			error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

		$date_submitted = current_time( 'mysql' );

		$message_quote   = sanitize_text_field( $_POST['message_quote'] );
		$variations_attr = json_decode( stripslashes( $_POST['variations_attr'] ), true );

		// Prepare data for database insertion
		$data = array(
			'product_id'       => $product_id,
			'product_name'     => $product_name,
			'product_quantity' => $product_quantity,
			'user_email'       => $useremail,
            'phone_number' => $phone_number,
			'date_submitted'   => $date_submitted,
			'page_id'          => get_permalink( $product_id ), // Get the permalink of the current page
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