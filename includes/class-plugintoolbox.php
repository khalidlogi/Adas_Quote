<?php
// class-plugintoolbox.php

class PluginToolbox {


	// public static function send_email( $data ) {
	// $message = '<p style="font-size: 16px;">You have requested a quote for the following product:</p>';

	// $message .= '<div style="width:90%;margin:0 auto;border: 1px solid #e5e5e5;">';
	// $message .= '<table style="width: 100%;border-collapse: collapse;">';
	// $message .= '<thead>';
	// $message .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
	// $message .= __( 'Product Image', AQ );
	// $message .= '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
	// $message .= __( 'Product Title', AQ );
	// $message .= '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
	// $message .= __( 'Product Variation', AQ );
	// $message .= '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">';
	// $message .= __( 'Product Quantity', AQ );
	// $message .= '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;padding:10px;">';
	// $message .= __( 'Total', AQ );
	// $message .= '</th>';
	// $message .= '</tr>';
	// $message .= '</thead>';
	// $message .= '<tbody>';

	// $quote_post[] = $data;
	// error_log( '$quote_post[] : ' . print_r( $quote_post, true ) );
	// error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

	// $product              = wc_get_product( $quote_post[0]['product_id'] );
	// $product_variation_id = $quote_post[0]['product_id'];
	// $_product             = wc_get_product( $product_variation_id );
	// if ( $_product->is_type( 'simple' ) || $_product->is_type( 'grouped' ) ) {
	// $product_price = $product->get_price();
	// }

	// $message        .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $message        .= '<a href="' . $quote_post[0]['product_image'] . '" ><img src="' . $quote_post[0]['product_image'] . '" width="100" /></a>';
	// $message        .= '</td>';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $message        .= $quote_post[0]['product_name'];
	// $message        .= ' : <b>' . get_post_meta( $quote_post[0]['variation_id'], 'attribute_size', true ) . '</b>';
	// $message        .= '</td>';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $variations_attr = unserialize( $quote_post[0]['variations_attr'] );
	// foreach ( $variations_attr as $attr_name => $attr_value ) {
	// $message .= $attr_name . ': ' . $attr_value . '<br>';
	// }
	// $message   .= '</td>';
	// $message   .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $message   .= $quote_post[0]['product_quantity'];
	// $message   .= '</td>';
	// $message   .= '<td style="width: 16.66%;padding:10px;text-align: center;">';
	// $message   .= ( $quote_post[0]['product_quantity'] );
	// $message   .= '</td>';
	// $message   .= '</tr>';
	// $product_id = $quote_post[0]['product_id'];
	// $product    = wc_get_product( $product_id );
	// $quantity   = (int) $quote_post[0]['product_quantity'];
	// $sale_price = 200; // $product->get_price();

	// $message .= '</tbody>';
	// $message .= '<tfoot>';
	// $message .= '<tr>';
	// $message .= '<td></td>';
	// $message .= '<td></td>';
	// $message .= '<td></td>';
	// $message .= '</tr>';
	// $message .= '</tfoot>';
	// $message .= '</table>';
	// $message .= '</div>';

	// $custom_email_message = get_option( 'adas_quote_custom_email_message' );
	// if ( ! empty( $custom_email_message ) ) {
	// $message .= '<p>' . nl2br( esc_html( $custom_email_message ) ) . '</p>';
	// }

	// $quote_admin_email = get_option( 'wc_settings_quote_admin_email' );
	// if ( $quote_admin_email != '' ) {
	// $admin_email = $quote_admin_email;
	// } else {
	// $admin_email = get_option( 'admin_email' );
	// }
	// $site_title  = get_bloginfo( 'name' );
	// $admin_email = get_option( 'admin_email' );
	// $to_send     = $quote_post[0]['user_email'];

	// $attachments = '';

	// $headers           = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $site_title . ' <' . $admin_email . '>' );
	// $quote_email_title = 'quote_email_title'; // get_option( 'wc_settings_quote_email_subject' );
	// $email_title       = ( ! empty( $quote_email_title ) ? $quote_email_title : __( 'Quote', 'AQ' ) );
	// if ( wp_mail( $to_send, $email_title, $message, $headers, $attachments ) ) {
	// $message .= '<p>' . __( 'Quote has been sent to', 'AQ' ) . ' ' . str_replace( ',', ', ', $to_send ) . '</p>';
	// wp_mail( $admin_email, __( 'Quote Enquiry', 'AQ' ), $message, $headers, $attachments );
	// }
	// }


	// public static function send_email( $data ) {
	// $message  = '<p style="font-size: 16px;">You have requested a quote for the following product:</p>';
	// $message .= '<div style="width:90%;margin:0 auto;border: 1px solid #e5e5e5;">';
	// $message .= '<table style="width: 100%;border-collapse: collapse;">';
	// $message .= '<thead>';
	// $message .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">' . __( 'Product Image', 'AQ' ) . '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">' . __( 'Product Title', 'AQ' ) . '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">' . __( 'Product Variation', 'AQ' ) . '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">' . __( 'Product Quantity', 'AQ' ) . '</th>';
	// $message .= '<th style="width: 16.66%;text-align: center;padding:10px;">' . __( 'Total', 'AQ' ) . '</th>';
	// $message .= '</tr>';
	// $message .= '</thead>';
	// $message .= '<tbody>';

	// $quote_post[] = $data;
	// error_log( '$quote_post[] : ' . print_r( $quote_post, true ) );
	// error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );

	// $product  = wc_get_product( $quote_post[0]['product_id'] );
	// $_product = wc_get_product( $quote_post[0]['product_id'] );
	// if ( $_product->is_type( 'simple' ) || $_product->is_type( 'grouped' ) ) {
	// $product_price = $product->get_price();
	// }

	// $message        .= '<tr style="border-bottom: 1px solid #e5e5e5;">';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $message        .= '<a href="' . $quote_post[0]['product_image'] . '" ><img src="' . $quote_post[0]['product_image'] . '" width="100" /></a>';
	// $message        .= '</td>';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $message        .= $quote_post[0]['product_name'] . ' : <b>' . get_post_meta( $quote_post[0]['variation_id'], 'attribute_size', true ) . '</b>';
	// $message        .= '</td>';
	// $message        .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">';
	// $variations_attr = unserialize( $quote_post[0]['variations_attr'] );
	// foreach ( $variations_attr as $attr_name => $attr_value ) {
	// $message .= $attr_name . ': ' . $attr_value . '<br>';
	// }
	// $message .= '</td>';
	// $message .= '<td style="width: 16.66%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">' . $quote_post[0]['product_quantity'] . '</td>';
	// $message .= '<td style="width: 16.66%;padding:10px;text-align: center;">' . $quote_post[0]['product_quantity'] . '</td>';
	// $message .= '</tr>';

	// $message .= '</tbody>';
	// $message .= '<tfoot>';
	// $message .= '<tr>';
	// $message .= '<td></td>';
	// $message .= '<td></td>';
	// $message .= '<td></td>';
	// $message .= '</tr>';
	// $message .= '</tfoot>';
	// $message .= '</table>';
	// $message .= '</div>';

	// $custom_email_message = get_option( 'adas_quote_custom_email_message' );
	// if ( ! empty( $custom_email_message ) ) {
	// $message .= '<p>' . nl2br( esc_html( $custom_email_message ) ) . '</p>';

	// }

	// $quote_admin_email = get_option( 'wc_settings_quote_admin_email' );
	// $admin_email       = $quote_admin_email ? $quote_admin_email : get_option( 'admin_email' );
	// $site_title        = get_bloginfo( 'name' );
	// $to_send           = $quote_post[0]['user_email'];

	// $headers           = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $site_title . ' <' . $admin_email . '>' );
	// $quote_email_title = 'quote_email_title'; // get_option( 'wc_settings_quote_email_subject' );
	// $email_title       = ( ! empty( $quote_email_title ) ? $quote_email_title : __( 'Quote', 'AQ' ) );

	// if ( wp_mail( $to_send, $email_title, $message, $headers ) ) {
	// $message .= '<p>' . __( 'Quote has been sent to', 'AQ' ) . ' ' . str_replace( ',', ', ', $to_send ) . '</p>';
	// wp_mail( $admin_email, __( 'Quote Enquiry', 'AQ' ), $message, $headers );
	// }
	// }

	public static function send_email( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			error_log( 'Invalid data provided to send_email function' );
			return false;
		}

		$product = wc_get_product( $data['product_id'] );
		if ( ! $product ) {
			error_log( 'Invalid product ID provided to send_email function' );
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
			error_log( sprintf( 'Failed to send quote email to %s', $to_send ) );
			return false;
		}
	}

	/*
		private static function generate_email_body( $data, $product ) {
		ob_start();
		?>
	<p style="font-size: 16px;"><?php esc_html_e( 'You have requested a quote for the following product:', 'AQ' ); ?></p>
	<div style="width:90%;margin:0 auto;border: 1px solid #e5e5e5;">
	<table style="width: 100%;border-collapse: collapse;">
		<thead>
			<tr style="border-bottom: 1px solid #e5e5e5;">
				<th style="width: 20%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">
					<?php esc_html_e( 'Product Image', 'AQ' ); ?></th>
				<th style="width: 20%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">
					<?php esc_html_e( 'Product Title', 'AQ' ); ?></th>
				<th style="width: 20%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">
					<?php esc_html_e( 'Product Variation', 'AQ' ); ?></th>
				<th style="width: 20%;text-align: center;border-right:1px solid #e5e5e5;padding:10px;">
					<?php esc_html_e( 'Product Quantity', 'AQ' ); ?></th>
				<th style="width: 20%;text-align: center;padding:10px;"><?php esc_html_e( 'Total', 'AQ' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr style="border-bottom: 1px solid #e5e5e5;">
				<td style="width: 20%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">
					<img src="<?php echo esc_url( $data['product_image'] ); ?>" width="100"
						alt="<?php echo esc_attr( $data['product_name'] ); ?>" />
				</td>
				<td style="width: 20%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">
					<?php echo esc_html( $data['product_name'] ); ?>
					<?php if ( ! empty( $data['variation_id'] ) ) : ?>
					: <b><?php echo esc_html( get_post_meta( $data['variation_id'], 'attribute_size', true ) ); ?></b>
					<?php endif; ?>
				</td>
				<td style="width: 20%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">
					<?php
							$variations_attr = maybe_unserialize( $data['variations_attr'] );
					if ( is_array( $variations_attr ) ) {
						foreach ( $variations_attr as $attr_name => $attr_value ) {
							echo esc_html( $attr_name ) . ': ' . esc_html( $attr_value ) . '<br>';
						}
					}
					?>
				</td>
				<td style="width: 20%;padding:10px;text-align: center;border-right:1px solid #e5e5e5;">
					<?php echo esc_html( $data['product_quantity'] ); ?>
				</td>
				<td style="width: 20%;padding:10px;text-align: center;">
					<?php echo esc_html( $data['product_quantity'] ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<?php
		$custom_email_message = get_option( 'adas_quote_custom_email_message' );
		if ( ! empty( $custom_email_message ) ) {
			echo '<p>' . nl2br( esc_html( $custom_email_message ) ) . '</p>';
		}

		return ob_get_clean();
	}*/

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
	public static function getAllProductCategories() {
		$args           = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		);
		$all_categories = get_terms( $args );

		return $all_categories;
	}



	/**
	 * Get all category IDs
	 *
	 * @return array
	 */
	public static function getAllProductCategoryIds() {
		$all_categories = self::getAllProductCategories();
		$category_ids   = wp_list_pluck( $all_categories, 'term_id' );

		return $category_ids;
	}
}