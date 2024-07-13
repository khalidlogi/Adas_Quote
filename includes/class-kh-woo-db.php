<?php
/**
 * This file contains the KH_Woo_DB class which is responsible for creating the kh_woo table in the database.
 *
 * @package   AdasQuoteForWC
 */

/**
 * Class KH_Woo_DB
 */
class KH_Woo_DB {

		/**
		 * Delete the kh_woo table.
		 */
	public static function delete_kh_woo_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'kh_woo';

		// Use prepared statements for security.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS `%s`', $table_name ) );

			// Check for errors.
			if ( '' !== $wpdb->last_error ) {
				// Add admin notice instead of logging.
				add_action(
					'admin_notices',
					function () use ( $wpdb ) {
						$class   = 'notice notice-error';
						$message = sprintf( 'Error deleting kh_woo table: %s', $wpdb->last_error );
						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}
				);
			}
		}

		// Remove options.
		self::adas_quote_remove_options();
	}


	public static function adas_quote_remove_options() {
		// Remove the specified options
		delete_option( 'adas_quote_hide_add_to_cart' );
		delete_option( 'adas_quote_hide_price' );
		delete_option( 'adas_quote_custom_email_message' );
		delete_option( 'adas_quote_admin_email' );
		delete_option( 'adas_quote_selected_products' );
		delete_option( 'adas_quote_selected_categories' );
		delete_option( 'adas_quote_gmail_smtp_username' );
		delete_option( 'adas_quote_gmail_smtp_password' );
		delete_option( 'adas_quote_email_subject' );
		delete_option( 'adas_quote_display_email_errors' );
		delete_option( 'adas_quote_enable_recaptcha' );
		delete_option( 'adas_quote_recaptcha_secret_key' );
		delete_option( 'adas_quote_recaptcha_site_key' );
		delete_option( 'adas_quote_custom_button_label' );
		delete_option( 'adas_user_logo' );
	}
	/**
	 * Create the kh_woo table if it doesn't exist.
	 */
	public static function create_kh_woo_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'kh_woo';

		// Check if the table already exists.
        /// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                product_id mediumint(9) NOT NULL,
                product_name text NOT NULL,
                product_quantity mediumint(9) NULL,
                product_type text NULL,
                product_image text NULL,
                user_email text NOT NULL,
                phone_number text NULL,
                variation_id mediumint(9) NULL,
                variations_attr text NULL,
                message_quote text NULL,
                date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_status INT DEFAULT 0,
                read_date TIMESTAMP NULL,                
                page_id varchar(255) NOT NULL,
                PRIMARY KEY (id),
                KEY product_id (product_id),
                KEY variation_id (variation_id),
                KEY date_submitted (date_submitted)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

		}
	}
}