<?php
/**
 * PluginToolbox class
 *
 * This file contains the PluginToolbox class which handles various functionalities for the plugin,
 * including sending emails for quote requests, generating email bodies, and retrieving product categories.
 *
 * @package AdasQuoteForWC
 */

/**
 * PluginToolbox class
 *
 * This class handles various functionalities for the plugin, including sending emails for quote requests,
 * generating email bodies, and retrieving product categories.
 */
class PluginToolbox {


	/**
	 * Send an admin notification email.
	 *
	 * @param string $admin_email The admin email address.
	 * @param string $customer_email The customer email address.
	 * @param string $original_message The original message to be sent.
	 * @return bool True if the email was sent successfully, false otherwise.
	 */
	private static function send_admin_notification( $admin_email, $customer_email, $original_message ) {
		$mail = self::configure_smtp();

		if ( ! $mail ) {
			return false;
		}

		try {
			$mail->setFrom( $admin_email, get_bloginfo( 'name' ) );
			$mail->addAddress( $admin_email );

			$admin_message = $original_message;

			$mail->isHTML( true );
			$mail->Subject = __( 'Quote Enquiry', 'adas_quote_request' );
			$mail->Body    = $admin_message;

			$mail->send();
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}



	/**
	 * Send an email for a quote request.
	 *
	 * @param array $data The data for the quote request.
	 * @return bool True if the email was sent successfully, false otherwise.
	 * @throws Exception If the email could not be sent.
	 */
	public static function send_email( $data ) {

		delete_option( 'adas_quote_email_errors' );

		if ( ! is_array( $data ) || empty( $data ) ) {
			AQ_Error_Logger::log_error( 'Invalid or empty data' );
			return false;
		}

		$product = wc_get_product( $data['product_id'] );
		if ( ! $product ) {
			AQ_Error_Logger::log_error( 'Invalid product' );
			return false;
		}

		$message = self::generate_email_body( $data, $product );

		$quote_admin_email = get_option( 'adas_quote_admin_email' );
		$admin_email       = $quote_admin_email ? sanitize_email( $quote_admin_email ) : get_option( 'admin_email' );
		$site_title        = get_bloginfo( 'name' );
		$to_send           = sanitize_email( $data['user_email'] );

		$quote_email_title = get_option( 'adas_quote_email_subject', __( 'Quote', 'adas_quote_request' ) );
		$email_title       = sanitize_text_field( $quote_email_title );

		// Load PHPMailer.
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

		$mail = self::configure_smtp();

		if ( ! $mail ) {
			AQ_Error_Logger::log_error( 'SMTP configuration failed, falling back to wp_mail' );
			return self::send_wp_mail( $to_send, $email_title, $message, $admin_email, $site_title );
		}

		try {
			// Recipients.
			$mail->setFrom( $admin_email, $site_title );
			$mail->addAddress( $to_send );

			// Content.
			$mail->isHTML( true );
			$mail->Subject = $email_title;
			$mail->Body    = $message;

			if ( ! $mail->send() ) {
				throw new Exception( $mail->ErrorInfo );
			}

			AQ_Error_Logger::log_error( 'Email sent successfully via SMTP' );

			// Send admin notification.
			$message_to_admin = '<p>' . sprintf( esc_html__( 'Quote has been sent to %s', 'adas_quote_request' ), esc_html( $to_send ) ) . '</p>';
			$admin_notified   = self::send_admin_notification( $admin_email, $to_send, $message_to_admin );
			if ( ! $admin_notified ) {
				AQ_Error_Logger::log_error( 'Failed to send admin notification' );
			} else {
				AQ_Error_Logger::log_error( 'Admin notification sent successfully' );
			}

			return true;
		} catch ( Exception $e ) {
			AQ_Error_Logger::log_error( 'SMTP send failed, attempting fallback to wp_mail' );
			self::handle_email_error( $e, $admin_email );
			return self::send_wp_mail( $to_send, $email_title, $message, $admin_email, $site_title );
		}
	}

		/**
		 * Configure SMTP settings for PHPMailer.
		 *
		 * @return PHPMailer\PHPMailer\PHPMailer|false The configured PHPMailer instance, or false on failure.
		 */
	private static function configure_smtp() {
		$mail = new PHPMailer\PHPMailer\PHPMailer( true );

		$username = get_option( 'adas_quote_gmail_smtp_username' );
		$password = get_option( 'adas_quote_gmail_smtp_password' );

		if ( empty( $username ) || empty( $password ) ) {
			AQ_Error_Logger::log_error( 'SMTP credentials missing' );
			return false;
		}

		// Server settings.
		$mail->isSMTP();
		$mail->Host       = 'smtp.gmail.com';
		$mail->SMTPAuth   = true;
		$mail->Username   = $username;
		$mail->Password   = $password;
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port       = 587;

		return $mail;
	}

	/**
	 * Send an email using wp_mail as a fallback.
	 *
	 * @param string $to The recipient email address.
	 * @param string $subject The email subject.
	 * @param string $message The email message.
	 * @param string $from_email The sender email address.
	 * @param string $from_name The sender name.
	 * @return bool True if the email was sent successfully, false otherwise.
	 */
	private static function send_wp_mail( $to, $subject, $message, $from_email, $from_name ) {
		AQ_Error_Logger::log_error( 'Attempting to send via wp_mail' );

		$to         = sanitize_email( $to );
		$from_email = sanitize_email( $from_email );
		$from_name  = wp_strip_all_tags( $from_name );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', esc_html( $from_name ), $from_email ),
		);

		// Add an action to capture PHPMailer errors
		add_action(
			'wp_mail_failed',
			function ( $wp_error ) {
				AQ_Error_Logger::log_error( 'PHPMailer log: ' . $wp_error->get_error_message() );
			}
		);

		$customer_email_sent = wp_mail( $to, $subject, $message, $headers );

		if ( $customer_email_sent ) {
			AQ_Error_Logger::log_error( 'Customer email sent successfully via wp_mail' );

			$admin_email      = get_option( 'admin_email' ); // Use WordPress admin email.
			$admin_message    = $message . '<p>' . sprintf( esc_html__( 'Quote has been sent to %s', 'adas_quote_request' ), esc_html( $to ) ) . '</p>';
			$admin_email_sent = wp_mail( $admin_email, esc_html__( 'Quote Enquiry', 'adas_quote_request' ), $admin_message, $headers );

			if ( $admin_email_sent ) {
				AQ_Error_Logger::log_error( 'Admin notification sent successfully via wp_mail' );
				$result['admin_email_sent'] = true;
			} else {
				AQ_Error_Logger::log_error( 'Failed to send admin notification via wp_mail' );
				$result['errors'][] = 'Failed to send admin notification';
			}
			return true;
		} else {
			AQ_Error_Logger::log_error( 'Failed to send customer email via wp_mail' );

			// Log any PHP errors.
			$error = error_get_last();
			if ( $error ) {
				AQ_Error_Logger::log_error( 'PHP log: ' . print_r( $error, true ) );
			}

			// Log PHPMailer errors if available.
			global $phpmailer;
			if ( isset( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
				AQ_Error_Logger::log_error( 'PHPMailer error info: ' . $phpmailer->ErrorInfo );
			}

			return false;
		}
	}



	/**
	 * Handle email errors and log them.
	 *
	 * @param Exception $exception The exception thrown during email sending.
	 * @param string    $admin_email The admin email address.
	 */
	private static function handle_email_error( $exception, $admin_email ) {
		$error_message = $exception->getMessage();
		$log_message   = '';

		if ( strpos( $error_message, 'SMTP connect() failed' ) !== false ) {
			$log_message = 'SMTP Connection Failure: ' . $error_message;
			wp_mail( $admin_email, 'SMTP Connection Failure', $log_message );
		} elseif ( strpos( $error_message, 'Invalid address' ) !== false ) {
			$log_message = 'Invalid Email Address: ' . $error_message;
		} elseif ( strpos( $error_message, 'mailbox unavailable' ) !== false ) {
			$log_message = 'Recipient Rejection: ' . $error_message;
		} elseif ( strpos( $error_message, 'Authorization' ) !== false ) {
			$log_message = 'SMTP Authentication Failure: ' . $error_message;
			wp_mail( $admin_email, 'SMTP Authentication Failure', $log_message );
		} else {
			$log_message = 'Email Sending Error: ' . $error_message;
		}

		// Store errors in a custom option for admin review.
		$current_errors   = get_option( 'adas_quote_email_errors', array() );
		$current_errors[] = array(
			'time'  => current_time( 'mysql' ),
			'error' => $log_message,
		);
		update_option( 'adas_quote_email_errors', array_slice( $current_errors, -10 ) ); // Keep last 10 errors.
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
		// $company_logo_url = get_option( 'adas_user_logo' );
		$company_logo_url = get_option( 'adas_user_logo' );
		// Convert URL to file path
		$upload_dir = wp_upload_dir();
		$base_url   = $upload_dir['baseurl'];
		$base_dir   = $upload_dir['basedir'];
			// Replace the base URL with the base directory path
		$company_logo_path = str_replace( $base_url, $base_dir, $company_logo_url );

		// Ensure the file exists
		if ( file_exists( $company_logo_path ) ) {
			$logo_data   = file_get_contents( $company_logo_path );
			$base64_logo = base64_encode( $logo_data );
			$mime_type   = mime_content_type( $company_logo_path );
		} else {
			error_log( "Logo file not found: $company_logo_path" );
		}
		// Read the image file and encode it
		$logo_data   = file_get_contents( $company_logo_path );
		$base64_logo = base64_encode( $logo_data );
		$mime_type   = mime_content_type( $company_logo_path );
		// Log the base64 encoded image and MIME type
		error_log( 'Base64 Encoded Image: ' . substr( $base64_logo, 0, 100 ) . '...' );
		error_log( 'MIME Type: ' . $mime_type );

		?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php esc_html_e( 'Quote Request', 'adas_quote_request' ); ?></title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
		<?php if ( $base64_logo ) : ?>
	<div style="text-align: center; margin-bottom: 20px;">
		<img src="data:<?php echo esc_attr( $mime_type ); ?>;base64,<?php echo esc_attr( $base64_logo ); ?>"
			alt="<?php esc_attr_e( 'Company Logo', 'adas_quote_request' ); ?>" style="max-width: 200px; height: auto;">
	</div>
	<?php endif; ?>

	<h1 style="color: #0066cc; text-align: center;"><?php esc_html_e( 'Quote Request', 'adas_quote_request' ); ?></h1>

	<p style="font-size: 16px;">
		<?php esc_html_e( 'You have requested a quote for the following product:', 'adas_quote_request' ); ?>
	</p>

	<div style="width: 100%; margin: 20px auto; border: 1px solid #e5e5e5;">
		<table style="width: 100%; border-collapse: collapse;">
			<thead>
				<tr style="background-color: #f8f8f8;">
					<th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
						<?php esc_html_e( 'Product Title', 'adas_quote_request' ); ?>
					</th>
					<?php if ( ! empty( $data['variation_id'] ) ) : ?>
					<th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
						<?php esc_html_e( 'Product Variation', 'adas_quote_request' ); ?>
					</th>
					<?php endif; ?>
					<th style="width: 33.33%; text-align: left; border: 1px solid #e5e5e5; padding: 10px;">
						<?php esc_html_e( 'Product Quantity', 'adas_quote_request' ); ?>
					</th>
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
					<?php if ( ! empty( $data['variation_id'] ) ) : ?>
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
					<?php endif; ?>
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
		<?php esc_html_e( 'Thank you for your interest. We will review your quote request and get back to you shortly.', 'adas_quote_request' ); ?>
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