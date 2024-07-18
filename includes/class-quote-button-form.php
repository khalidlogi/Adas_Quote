<?php

/**
 * Class QuoteButtonForm
 *
 * This class handles the display and functionality of the quote button form.
 *
 * @package AdasQuoteForWC
 */

/**
 * Class QuoteButtonForm
 *
 * This class handles the display and functionality of the quote button form.
 *
 * @package AdasQuoteForWC
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
	 * Determine if the quote button form should be displayed based on the product's stock status.
	 *
	 * @param WC_Product $product The WooCommerce product object.
	 * @return bool True if the form should be displayed, false otherwise.
	 */
	private static function should_display_based_on_stock_status( $product ) {
		$stock_status = $product->get_stock_status();
			// Get the stock status option.
		$stock_status_option = get_option( 'adas_quote_stock_status_option', 'show_for_all' );

		switch ( $stock_status_option ) {
			case 'show_for_all':
				return true; // Show for all if option is set to 'Show for all'.
			case 'hide_for_outofstock':
				if ( $stock_status === 'outofstock' ) {
					return false;
				}
			case 'out_of_stock_only':
				if ( $stock_status === 'outofstock' ) {
					return true;
				}
			default:
				return true; // Default to showing for all if option is not recognized
		}
	}
	/**
	 * Display the quote button form.
	 *
	 * This function handles the display and functionality of the quote button form.
	 */
	public static function display_quote_button_form() {
		global $product;
		// Check if the current product or its category matches the selected categories.
		$product_matches = false;

		// Get the selected products from the options.
		$selected_products = get_option( 'adas_quote_selected_products', array() );
		// Get the selected categories from the options.
		$selected_categories = get_option( 'adas_quote_selected_categories', array() );

		// Check if we should display the form based on stock status
		$should_display = self::should_display_based_on_stock_status( $product );

		if ( ! $should_display ) {
			$product_matches = false;
		} else {
			$product_matches = true;
		}

			// Check for parent categories and add their children.
		$updated_categories = array();
		foreach ( $selected_categories as $category_id ) {
			$updated_categories[] = $category_id;

			// Get children of this category
			$children = get_term_children( $category_id, 'product_cat' );

			if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
				$updated_categories = array_merge( $updated_categories, $children );
			}
		}

		// Remove duplicates and update the selected categories.
		$selected_categories = array_unique( $updated_categories );

		// Get the current product categories.
		$current_categories = wp_get_post_terms( $product->get_id(), 'product_cat' );

		if ( in_array( $product->get_id(), $selected_products ) ) {
			$product_matches = true;
		} else {
			foreach ( $current_categories as $category ) {
				if ( in_array( $category->term_id, $selected_categories ) ||
					in_array( $category->slug, $selected_categories ) ||
					in_array( $category->name, $selected_categories ) ) {
					$product_matches = true;
					break;
				}
			}
		}

		if ( ! $product_matches ) {
			return;
		}

		// Get the product type.
		$product_     = wc_get_product( get_the_ID() );
		$product_type = ( $product_->get_type() == 'variation' ) ? 'variation' : $product_->get_type();

		// Get the user email if logged in.
		$user_email = '';
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
		}

		// Get the product image URL.
		$product_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		$post_thumb        = $product_image_url ? $product_image_url[0] : '';

		// Load the form template.
		include plugin_dir_path( __FILE__ ) . '/quote-form.php';
		// Include the quote success modal.
		include plugin_dir_path( __FILE__ ) . 'quote-success-modal.php';
	}
}

new QuoteButtonForm();
