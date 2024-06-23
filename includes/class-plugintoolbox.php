<?php
/**
 * PluginToolbox class
 *
 * This class handles various functionalities for the plugin, including sending emails for quote requests,
 * generating email bodies, and retrieving product categories.
 */
class PluginToolbox {





	/**
	 * Send an email for the quote request.
	 *
	 * @param array $data The data for the quote request.
	 * @return bool True if the email was sent successfully, false otherwise.
	 */
	public static function send_email( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$product = wc_get_product( $data['product_id'] );
		if ( ! $product ) {
			return false;
		}

		$message = self::generate_email_body( $data, $product );

		$quote_admin_email = get_option( 'wc_settings_quote_admin_email' );
		$admin_email       = $quote_admin_email ? sanitize_email( $quote_admin_email ) : get_option( 'admin_email' );
		$site_title        = get_bloginfo( 'name' );
		$to_send           = sanitize_email( $data['user_email'] );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', esc_html( $site_title ), $admin_email ),
		);

		$quote_email_title = get_option( 'wc_settings_quote_email_subject', __( 'Quote', 'AQ' ) );
		$email_title       = sanitize_text_field( $quote_email_title );

		$customer_email_sent = wp_mail( $to_send, $email_title, $message, $headers );

		if ( $customer_email_sent ) {
			$admin_message = $message . '<p>' . sprintf( __( 'Quote has been sent to %s', 'AQ' ), esc_html( $to_send ) ) . '</p>';
			wp_mail( $admin_email, __( 'Quote Enquiry', 'AQ' ), $admin_message, $headers );
			return true;
		} else {
			return false;
		}
	}



		/**
		 * Generate the email body for the quote request.
		 *
		 * @param array      $data The data for the quote request.
		 * @param WC_Product $product The WooCommerce product object.
		 * @return string The generated email body.
		 */
	private static function generate_email_body( $data, $product ) {
		ob_start();
		?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Quote Request', 'AQ' ); ?></title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #0066cc; text-align: center;"><?php esc_html_e( 'Quote Request', 'AQ' ); ?></h1>

    <p style="font-size: 16px;"><?php esc_html_e( 'You have requested a quote for the following product:', 'AQ' ); ?>
    </p>

    <div style="width: 100%; margin: 20px auto; border: 1px solid #e5e5e5;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f8f8;">
                    <th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
                        <?php esc_html_e( 'Product Title', 'AQ' ); ?></th>
                    <th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
                        <?php esc_html_e( 'Product Variation', 'AQ' ); ?></th>
                    <th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
                        <?php esc_html_e( 'Product Quantity', 'AQ' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #e5e5e5; padding: 10px;">
                        <?php echo esc_html( $product->get_name() ); ?>
                        <?php if ( ! empty( $data['variation_id'] ) ) : ?>
                        <br><small><?php echo esc_html( get_post_meta( $data['variation_id'], 'attribute_size', true ) ); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="border: 1px solid #e5e5e5; padding: 10px;">
                        <?php
								$variations_attr = maybe_unserialize( $data['variations_attr'] );
						if ( is_array( $variations_attr ) ) {
							foreach ( $variations_attr as $attr_name => $attr_value ) {
								echo esc_html( $attr_name ) . ': ' . esc_html( $attr_value ) . '<br>';
							}
						}
						?>
                    </td>
                    <td style="border: 1px solid #e5e5e5; padding: 10px;">
                        <?php echo esc_html( $data['product_quantity'] ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php
			$custom_email_message = get_option( 'adas_quote_custom_email_message' );
		if ( ! empty( $custom_email_message ) ) {
			echo '<div style="background-color: #f8f8f8; padding: 15px; margin-top: 20px; border-left: 4px solid #0066cc;">';
			echo wp_kses_post( nl2br( $custom_email_message ) );
			echo '</div>';
		}
		?>

    <p style="margin-top: 20px; font-style: italic;">
        <?php esc_html_e( 'Thank you for your interest. We will review your quote request and get back to you shortly.', 'AQ' ); ?>
    </p>

    <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
        <p><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
        <p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
    </div>
</body>

</html>
<?php
		return ob_get_clean();
	}

	/**
	 * Get all the categories
	 *
	 * @return array
	 */
	public static function get_all_product_categories() {
		$args           = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		);
		$all_categories = get_terms( $args );

		return $all_categories;
	}


}