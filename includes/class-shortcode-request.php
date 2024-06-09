<?php
/**
 * Class QuoteButtonForm
 *
 * This class handles the display and functionality of the quote button form.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class QuoteButtonForm
 *
 * This class handles the display and functionality of the quote button form.
 */
class QuoteButtonForm {

	/**
	 * Constructor.
	 *
	 * Adds an action to display the quote button form on the single product summary.
	 */
	public function __construct() {
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'display_quote_button_form' ), 25 );
	}

	/**
	 * Display the quote button form.
	 *
	 * This function handles the display and functionality of the quote button form.
	 */
	public static function display_quote_button_form() {
		global $product;

		// // Use the PluginToolbox class.
		// $all_category_ids = PluginToolbox::displayAllProductCategories();
		// error_log( '$all_category_ids: ' . print_r( $all_category_ids, true ) );
		// error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

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
		// Get all the categories.
		$all_categories = get_categories();

		// Loop through the categories and display them.
		if ( ! empty( $all_categories ) ) {
			echo '<ul>';
			foreach ( $all_categories as $category ) {
				echo '<li>Category ID: ' . esc_html( $category->term_id ) . ', Category Name: ' . esc_html( $category->name ) . '</li>';
			}
			echo '</ul>';
		} else {
			echo 'No categories found.';
		}

		$category_id = array();
		// Check if any categories were found.
		if ( ! empty( $product_categories ) ) {
			// Loop through the categories.
			foreach ( $product_categories as $category ) {
				// Access category properties.
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
			return;
		}

		$product_type = '';
		$product_     = wc_get_product( get_the_ID() ); // This function is part of WooCommerce.
		$product_type = ( $product_->get_type() ) ? 'variation' : $product_->get_type();
		if ( $product_->is_type( 'variable' ) ) {
			$class = '_hide';
		}

		// Get user email.
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

		echo '<input type="text" name="product_image" value="' . esc_attr( $post_thumb ) . '"  />';

		echo '<input type="text" name="product_link" value="' . esc_url( get_permalink() ) . '"  />';

		echo '<input type="text" name="product_type" class="product_type" value="' . esc_attr( $product_type ) . '"  />';

		echo '<input type="text" name="product_id" value="' . esc_attr( $product->get_id() ) . '">';
		echo '<input type="text" class="product_quantity" name="product_quantity" placeholder="Quantity" value="1">';

		// Email.
		echo '<input type="text" name="adas_user_email" id="adas_user_email" value="' . esc_attr( $user_email ) . '">';

		echo '<input stype="text" name="phone_number" placeholder="Phone Number" name="phone_number" id="phone_number">';

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

		// Include the modal HTML.
		include plugin_dir_path( __FILE__ ) . 'quote-success-modal.php';
	}
}

new QuoteButtonForm();