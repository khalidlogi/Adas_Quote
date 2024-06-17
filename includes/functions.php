<?php
// functions.php

class PluginToolbox {


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
	 * Get the selected categories
	 *
	 * @return array
	 */
	public static function getAdasQuoteSelectedCategories() {
		$selected_categories = get_option( 'adas_quote_selected_categories', array() );

		if ( is_string( $selected_categories ) ) {
			return unserialize( $selected_categories );
		} else {
			return $selected_categories;
		}
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

	/**
	 * Display a list of all categories
	 */
	public static function displayAllProductCategories() {
		$all_categories = self::getAllProductCategories();

		if ( ! empty( $all_categories ) ) {
			echo '<ul>';
			foreach ( $all_categories as $category ) {
				echo '<li>Category ID: ' . $category->term_id . ', Category Name: ' . $category->name . '</li>';
			}
			echo '</ul>';
		} else {
			echo 'No product categories found.';
		}
	}
}
