<?php

/**
 * Class KH_Woo_DB
 */
class KH_Woo_DB {
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

			// Check for errors during table creation
			if ( $wpdb->last_error !== '' ) {
				error_log( 'Error creating table: ' . $wpdb->last_error );
			}
		}
	}
}
