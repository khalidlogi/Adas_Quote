<?php
/**
 * AQ_Error_Logger Class
 *
 * This file contains the AQ_Error_Logger class which handles logging, retrieving, and clearing email errors for the ADAS Quote Plugin.
 *
 * @package   AdasQuoteForWc
 */

/**
 * Class AQ_Error_Logger
 *
 * This class handles logging, retrieving, and clearing email errors for the ADAS Quote Plugin.
 */
class AQ_Error_Logger {
	/**
	 *  * This class handles logging, retrieving, and clearing email errors for the
	 *
	 * @var string $option_name The name of the WordPress option where errors are stored.
	 */
	private static $option_name = 'adas_quote_email_errors';

	/**
	 * Log an error message.
	 *
	 * This function logs an error message with the current timestamp. It keeps only the most recent 5 errors.
	 *
	 * @param string $error_message The error message to log.
	 */
	public static function log_error( $error_message ) {
		// Retrieve the current errors from the WordPress options table.
		$current_errors = get_option( self::$option_name, array() );

		// Add the new error message to the beginning of the array.
		array_unshift(
			$current_errors,
			array(
				'time'  => current_time( 'mysql' ),
				'error' => $error_message,
			)
		);

		// Keep only the most recent 5 errors.
		$current_errors = array_slice( $current_errors, 0, 5 );

		// Update the WordPress option with the new list of errors.
		update_option( self::$option_name, $current_errors );
	}

	/**
	 * Retrieve the logged errors.
	 *
	 * This function retrieves the logged errors from the WordPress options table.
	 *
	 * @return array The array of logged errors.
	 */
	public static function get_errors() {
		return get_option( self::$option_name, array() );
	}

	/**
	 * Clear all logged errors.
	 *
	 * This function clears all logged errors by deleting the WordPress option.
	 */
	public static function clear_errors() {
		delete_option( self::$option_name );
	}
}
