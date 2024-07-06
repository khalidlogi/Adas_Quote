<?php
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
	 * Display the quote button form.
	 *
	 * This function handles the display and functionality of the quote button form.
	 */
	public static function display_quote_button_form() {
		global $product;

		// Get the selected products from the options.
		$selected_products = get_option( 'adas_quote_selected_products', array() );

		// Get the current product categories.
		$current_categories = array_reverse( get_the_terms( $product->get_id(), 'product_cat' ) );

		// Get the selected categories from the options.
		// Get the selected categories from the options.
		$selected_categories       = get_option( 'adas_quote_selected_categories' );
		$selected_categories_by_id = get_term_by( 'name', $selected_categories, 'product_cat' );

		if ( $selected_categories_by_id ) {
			$selected_categories_by_id = $selected_categories_by_id->term_id;
		} else {
			$selected_categories_by_id = null; // or handle the case where the term is not found.
		}

		// Check if the current product or its category matches the selected categories.
		$category = array_reverse( $current_categories )[0];
		if ( in_array( $product->get_id(), (array) $selected_products )
		|| ( strtolower( $category->name ) === strtolower( $selected_categories )
		|| term_is_ancestor_of( $selected_categories_by_id, $category->term_id, 'product_cat' ) ) ) {

		} else {

			return;
		}

		// Get the product type.
		$product_     = wc_get_product( get_the_ID() );
		$product_type = ( $product_->get_type() ) ? 'variation' : $product_->get_type();

		// Get the user email if logged in.
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
		} else {
			$user_email = '';
		}

		// Get the product image URL.
		$product_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		$post_thumb        = $product_image_url ? $product_image_url[0] : '';

		// Display the quote button form
		echo '<div class="custom-quote-form-wrapper">';
		echo '<form id="custom-quote-form" class="custom-quote-form" method="post" onsubmit="return false;">';
		echo '<input type="hidden" name="action" value="custom_quote_request">';
		echo '<input placeholder="product_name" type="text" name="product_name" value="' . esc_attr( $product->get_name() ) . '">';
		echo '<input type="hidden" name="product_image" value="' . esc_attr( $post_thumb ) . '"  />';
		echo '<input type="hidden" name="product_link" value="' . esc_url( get_permalink() ) . '"  />';
		echo '<input type="hidden" name="product_type" class="product_type" value="' . esc_attr( $product_type ) . '"  />';
		echo '<input type="hidden" name="product_id" value="' . esc_attr( $product->get_id() ) . '">';
		echo '<input type="hidden" class="product_quantity" name="product_quantity" placeholder="Quantity" value="1">';
		echo '<input type="text" name="adas_user_email" placeholder="Email" id="adas_user_email" value="' . esc_attr( $user_email ) . '">';
		echo '<input type="text" name="phone_number" placeholder="Phone Number" value="0644099468" id="phone_number">';

		// Display additional fields for variable products
		if ( $product->is_type( 'variable' ) ) {
			echo '<input type="hidden" placeholder="variation_id" name="variation_id" id="quote_variation_id">';
			echo '<input type="hidden" name="variations_attr" id="variationsAttr">';
		}

		echo '<textarea name="message_quote" style="width: 100%;" placeholder="Additional Notes"></textarea>';
		echo '<input type="hidden" name="action" value="adas_send_quote" />';

		// Add reCAPTCHA.
		if ( get_option( 'adas_quote_recaptcha_site_key' ) !== ''
		&& get_option( 'adas_quote_recaptcha_secret_key' ) !== ''
		&& get_option( 'adas_quote_enable_recaptcha' ) !== '' ) {
			echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( get_option( 'adas_quote_recaptcha_site_key' ) ) . '"></div>';
		}
		echo '<input type="hidden" id="adas_quote_nonce" name="adas_quote_nonce" value="' . esc_attr( wp_create_nonce( 'adas_quote_action' ) ) . '" />';

		// Add loading indicator.
		echo '  <div id="loadingIndicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> Loading...
    </div>';
		echo '<br><button type="submit" class="custom-quote-button">Add to quote</button>';
		echo '</form>';
		echo '</div>';

		// Include the quote success modal.
		include plugin_dir_path( __FILE__ ) . 'quote-success-modal.php';
	}
}

new QuoteButtonForm();