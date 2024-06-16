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

		$selected_products = get_option( 'adas_quote_selected_products', array() );

		// if ( in_array( $product->get_id(), $selected_products ) ) {
		// echo '<div style="border:1px solid black;">';
		// echo '<pre> product->get_id : ';
		// print_r( $product->get_id() );
		// echo '</pre>';
		// echo '<pre> Exisit in selected_products : ';
		// print_r( $selected_products );
		// echo '</pre> </div>';
		// } else {
		// return;
		// }

		// Get the gategory name saved.
		$current_categories = array_reverse( get_the_terms( $product->get_id(), 'product_cat' ) );

		$selected_categories       = get_option( 'adas_quote_selected_categories' );
		$selected_categories_by_id = get_term_by( 'name', $selected_categories, 'product_cat' );
		$selected_categories_by_id = $selected_categories_by_id->term_id;

		error_log( '$current_categories: ' . print_r( $current_categories, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		// Get the selected categories by id

		echo '<pre>';
		print_r( array_reverse( $current_categories ) );
		echo '</pre>';

		echo '<br> selected_categories_by_id';
		var_dump( $selected_categories_by_id );

		$category = array_reverse( $current_categories );
		$category = $category [0];
		if ( strtolower( $category->name ) === strtolower( $selected_categories ) || ( in_array( $product->get_id(), $selected_products ) )
		|| term_is_ancestor_of( $selected_categories_by_id, $category->term_id, 'product_cat' ) ) {
			print_r( "The current category '" . $category->name . "'  matches the selected category which is: '" . $selected_categories . "'." );

		} else {
			return;
		}

		// get the category
			// Get the child categories of the current category

				// $child_categories = get_term_children( $current_category->term_id, 'product_cat' );

		// Get the current product's categories

		// Check if the current product has any categories
		// if ( $current_categories && ! is_wp_error( $current_categories ) ) {
		// Loop through the current categories
		// foreach ( $current_categories as $category ) {
		// Check if the current category matches the selected category
		// if ( strtolower( $category->name ) === strtolower( $selected_categories )
		// || term_is_ancestor_of( $category->term_id, $selected_categories_by_id, 'product_cat' ) ) {
		// print_r( "The current category '" . $category->name . "' matches the selected category which is: '" . $selected_categories . "'." );
		// print_r( 'The current category '{$category->name}' matches the selected category which is: '{$selected_categories }'. );
		// echo "The current category '{$category->name}' matches the selected category which is: '{$selected_categories }'.";
		// break; // Exit the loop if a match is found
		// } else {
		// echo '<br>';
		// print_r( "The current category '" . $category->name . "' DOES NOT matches the selected category which is: '" . $selected_categories . "'." );

		// }
		// }

		// If no match was found, log a message
		// if ( ! $category->name === $selected_categories ) {
		// error_log( "The current product's categories do not match the selected category: '{$selected_categories}'" );
		// }
		// } else {
		// error_log( 'No categories found for the current product.' );
		// }

		/*
		// Output the current category and its child categories
		echo 'Current category: ' . $current_category->name . "\n";
		echo "Child categories:\n";

		if ( $child_categories ) {
			foreach ( $child_categories as $child_category_id ) {
				$child_category = get_term_by( 'id', $child_category_id, 'product_cat' );
				echo '- ' . $child_category->name . "\n";
				// error_log( ' $child_category->name: ' . print_r( $child_category->name, true ) );
				// error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
			}
		} else {
			echo "- None\n";
		}

		*/

		$category_ids = array();
		// error_log( '$category_ids: ' . print_r( $selected_categories, true ) );

		$product_categories = get_the_terms( $product->get_id(), 'product_cat' );

		$category_id = array();
		if ( ! empty( $product_categories ) ) {
			foreach ( $product_categories as $category ) {
				$category_id[] = $category->term_id;
			}
		}

		// $intersection = array_intersect( $category_id, explode( ',', $selected_categories[0] ) );

		// if ( empty( $intersection ) && ! in_array( $product->get_id(), $selected_products ) ) {
		// return;
		// }

		$product_     = wc_get_product( get_the_ID() );
		$product_type = ( $product_->get_type() ) ? 'variation' : $product_->get_type();

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
		} else {
			$user_email = '';
		}

		$product_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		$post_thumb        = $product_image_url ? $product_image_url[0] : '';

		echo '<div class="custom-quote-form-wrapper">';
		echo '<form id="custom-quote-form" class="custom-quote-form" action="" method="post">';

		echo '<input type="text" name="action" value="custom_quote_request">';
		echo '<input placeholder="product_name" type="text" name="product_name" value="' . esc_attr( $product->get_name() ) . '">';
		echo '<input type="text" name="product_image" value="' . esc_attr( $post_thumb ) . '"  />';
		echo '<input type="text" name="product_link" value="' . esc_url( get_permalink() ) . '"  />';
		echo '<input type="text" name="product_type" class="product_type" value="' . esc_attr( $product_type ) . '"  />';
		echo '<input type="text" name="product_id" value="' . esc_attr( $product->get_id() ) . '">';
		echo '<input type="text" class="product_quantity" name="product_quantity" placeholder="Quantity" value="1">';
		echo '<input type="text" name="adas_user_email" id="adas_user_email" value="' . esc_attr( $user_email ) . '">';
		echo '<input type="text" name="phone_number" placeholder="Phone Number" id="phone_number">';

		if ( $product->is_type( 'variable' ) ) {
			echo '<input type="text" placeholder="variation_id" name="variation_id" id="quote_variation_id">';
			echo '<input type="text" name="variations_attr" id="variationsAttr">';
		}

		echo '<textarea name="message_quote" style="width: 100%;" placeholder="Additional Notes"></textarea>';
		echo '<input type="hidden" name="action" value="adas_send_quote" />';
		echo '<input type="hidden" id="adas_quote_nonce" name="adas_quote_nonce" value="' . esc_attr( wp_create_nonce( 'adas_quote_action' ) ) . '" />';
		echo '<br><button type="submit" class="custom-quote-button">_add_to_quote</button>';
		echo '</form>';
		echo '</div>';

		include plugin_dir_path( __FILE__ ) . 'quote-success-modal.php';
	}
}

new QuoteButtonForm();
