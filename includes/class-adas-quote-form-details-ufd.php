<?php
/**
 * Adas Quote Form Details Ufd
 *
 * This file contains the Adas_Quote_Form_Details_Ufd class which handles the display of details for a submitted form.
 *
 * @package   AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Adas_Form_Details_Ufd
 *
 * This class handles the display of details for a submitted form.
 * It retrieves the form data from the database and renders an HTML page
 * to show the submitted form values, submission date, and other relevant information.
 */
class Adas_Quote_Form_Details_Ufd {


	/**
	 * The ID of the form this record is for.
	 *
	 * @var int
	 */
	private $quote_id;
	/**
	 * The ID of the product associated with the form.
	 *
	 * @var int
	 */
	private $product_id;
	/**
	 * Adas_Form_Details_Ufd constructor.
	 *
	 * Initializes the class and calls the form_details_page() method.
	 */
	public function __construct() {
		$this->init();
		$this->form_details_page();
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * Verifies the nonce (security token) and retrieves the form ID and submission ID
	 * from the URL parameters. If the nonce is invalid or the required parameters
	 * are missing, the script exits with an error message.
	 */
	public function init() {
		// Verify the nonce.
		$view_nonce          = isset( $_GET['view_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['view_nonce'] ) ) : '';
		$view_nonce_verified = isset( $_GET['view_nonce'] ) ? wp_verify_nonce( $view_nonce, 'view_action' ) : false;

		// Verify the nonce and retrieve the form ID and submission ID.
		if ( isset( $_GET['fid'] ) && $view_nonce_verified ) {
			$this->product_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
			$this->quote_id   = isset( $_GET['ufid'] ) ? (int) $_GET['ufid'] : '';
		} else {
			wp_die( 'No action taken' );
		}
	}

		/**
		 * Retrieves the submitted form values for the given form ID.
		 *
		 * @param string $quote_id The ID of the quote to retrieve.
		 * @return array|null An array containing the form values and other relevant information, or null if no results are found.
		 */
	public function retrieve_form_values( $quote_id = '' ) {
		global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kh_woo WHERE id = %s AND product_id = %d ORDER BY date_submitted DESC LIMIT 1",
				$quote_id,
				$this->product_id
			)
		);

		if ( ! $results ) {

			return null;
		}

		foreach ( $results as $result ) {
			$date            = sanitize_text_field( $result->date_submitted );
			$serialized_data = ( $result->variations_attr );
			$product_id      = sanitize_text_field( $result->product_id );
			$id              = absint( $result->id );

			// Unserialize the serialized form value.
			$unserialized_data = unserialize( $serialized_data );

			$form_values = array(
				'product_id'       => sanitize_text_field( $product_id ),
				'id'               => absint( $id ),
				'read_status'      => sanitize_text_field( $result->read_status ),
				'date_submitted'   => sanitize_text_field( $result->date_submitted ),
				'date'             => sanitize_text_field( $date ),
				'message_quote'    => sanitize_textarea_field( $result->message_quote ),
				'data'             => array_map( 'sanitize_text_field', $unserialized_data ),
				'user_email'       => sanitize_email( $result->user_email ),
				'phone_number'     => sanitize_text_field( $result->phone_number ),
				'product_name'     => sanitize_text_field( $result->product_name ),
				'product_quantity' => absint( $result->product_quantity ),
				'product_type'     => sanitize_text_field( $result->product_type ),
				'product_image'    => esc_url( $result->product_image ),
			);
		}

		return $form_values;
	}

	/**
	 * Renders the form details page.
	 *
	 * Retrieves the form data from the database, formats it, and outputs an HTML structure
	 * to display the form details, including the form ID, submission date, read status,
	 * and the submitted form values.
	 */
	public function form_details_page() {
		global $wpdb;

		$result           = $this->retrieve_form_values( $this->quote_id );
		$results          = $result['data'];
		$form_data        = $result['data'];
		$product_id       = $result['product_id'];
		$product_name     = $result['product_name'];
		$useremail        = $result['user_email'];
		$phone_number     = $result['phone_number'];
		$product_quantity = $result['product_quantity'];
		$product_type     = $result['product_type'];
		$product_image    = $result['product_image'];
		$message_quote    = $result['message_quote'];
		$read_status      = $result['read_status'];
		$read_status      = ( '1' === $read_status ) ? 'Read' : 'Not Read';
		$date_submitted   = $result['date_submitted'];

		if ( empty( $result ) ) {
			echo( 'Not valid contact form' );

		}

		// Output the HTML structure to display the form details.
		echo '<style>
            .adas-form-details-wrap {' .
			'font-size: 16px;' .
			'}' .
			'.form-information span {' .
			'margin-left: 1em;' .
			'}</style>' .
			'<div class="adas-form-details-wrapper">' .
			'<div id="welcome-panel" class="cfdb7-panel">' .
			'<div class="cfdb7-panel-content">' .
			'<div class="welcome-panel-column-container">';

		if ( ! empty( $product_name ) ) {
			echo '<div class="title-image"><p class="product_name"><b>Product Name:</b> ' . esc_html( $product_name ) . '</p>';
		}

		if ( ! empty( $product_image ) ) {
			echo '<p><b></b> <img src="' . esc_url( $product_image ) . '" alt="Product Image" /></p></div>';
		}

		if ( ! empty( $product_quantity ) ) {
			echo '<p><b>Product Quantity:</b> ' . esc_html( $product_quantity ) . '</p>';
		}

		if ( ! empty( $phone_number ) ) {
			echo '<p><b>Phone Number:</b> ' . esc_html( $phone_number ) . '</p>';
		}

		if ( ! empty( $product_type ) ) {
			echo '<p><b>Product Type:</b> ' . esc_html( $product_type ) . '</p>';
		}

		if ( ! empty( $message_quote ) ) {
			echo '<p><b>Message/Note:</b> ' . nl2br( esc_html( $message_quote ) ) . '</p>';
		}

		if ( ! empty( $date_submitted ) ) {
			echo '<p><b>Submission Date:</b> ' . esc_html( $date_submitted ) . '</p>';
		}

		if ( ( $results ) ) {
			$form_data = ( $results );
		}

		foreach ( $form_data as $key => $data ) :

			// Check if the key or value is empty.
			if ( '' === $key || '' === $data ) {
				continue;
			}

			// If the value is an array, extract the 'value' key or the array itself.
			if ( is_array( $data ) ) {
				$data = $data['value'] ?? $data;
			}

			// If the value is an array again, implode it into a comma-separated string with newlines between each value, and then convert it to a formatted string.
			if ( is_array( $data ) ) {
				$key_val      = ucfirst( $key );
				$arr_str_data = implode( ', ', $data );
				$arr_str_data = nl2br( $arr_str_data );
			} else {
				// Otherwise, just convert the value to a formatted string.
				$key_val = ucfirst( $key );
				$data    = nl2br( $data );
			}

				// If it is not, display the value as a regular string.
				echo '<p><b>' . esc_html( $key_val ) . '</b>: ' . esc_html( $data ) . '</p>';

		endforeach;

		// Add a button to send an email to the client.

		if ( isset( $useremail ) && ! empty( $useremail ) && filter_var( $useremail, FILTER_VALIDATE_EMAIL ) ) {
			echo '<a href="mailto:' . esc_attr( $useremail ) . '">
                    <button style="margin-top: 1em;color: white; border: none; padding: 0.5em 1.5em; cursor:pointer; white; background-color: #6a6ae8;" type="button">Reply to email</button>
                  </a>';
		}

		$form_data = serialize( $form_data );
		$id        = $result['id'];

		// Update the read_status and read_date for the current submission.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}kh_woo SET read_status = %s, read_date = NOW() WHERE id = %d",
				'1',
				$id
			)
		);

		if ( false === $result ) {
			return;
		}

		echo '</div></div></div></div>';
	}
}
