<?php

/**
 * Display the quote button form.
 *
 * @package AdasQuoteForWC
 */

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
		echo '<input type="text" name="phone_number" placeholder="Phone Number"  id="phone_number">';

		// Display additional fields for variable products.
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
		echo '<div id="loadingIndicator" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>';
		$button_label = get_option( 'adas_quote_custom_button_label', 'Add to quote' );
		echo '<br><button type="submit" class="custom-quote-button">' . esc_html( $button_label ) . '</button>';

		echo '</form>';
		echo '</div>';
