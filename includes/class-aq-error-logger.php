<?php
/**
 * AQ_Error_Logger Class
 *
 * This file contains the AQ_Error_Logger class which handles logging, retrieving, and clearing email errors for the Adas Quote Plugin.
 *
 * @package   AdasQuoteForWc
 */

/**
 * Class AQ_Error_Logger
 *
 * This class handles logging, retrieving, and clearing email errors for the Adas Quote Plugin.
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
	 * @return array The formatted log data.
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

		// Format and return the log data.
		return self::format_log_data( $current_errors );
	}

	/**
	 * Format log data for display.
	 *
	 * @param array $log_data The log data to format.
	 * @return string The formatted log data.
	 */
	public static function format_log_data( $log_data ) {

		$formatted_output = '';
		foreach ( $log_data as $entry ) {
			$formatted_output .= "Time: {$entry['time']}\n";
			$formatted_output .= 'Error: ';

			// Check if the error message is JSON.
			$decoded_error = json_decode( $entry['error'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$formatted_output .= "\n" . self::format_json_error( $decoded_error );
			} else {
				$formatted_output .= $entry['error'] . "\n";
			}

			$formatted_output .= "\n";
		}
		return $formatted_output;
	}

	/**
	 * Format JSON error data for display.
	 *
	 * This function recursively formats JSON error data for display with indentation.
	 *
	 * @param array $error_data The JSON error data to format.
	 * @param int   $indent     The current indentation level (default is 0).
	 * @return string The formatted JSON error data.
	 */
	private static function format_json_error( $error_data, $indent = 0 ) {
		$output = '';
		foreach ( $error_data as $key => $value ) {
			$output .= str_repeat( '  ', $indent ) . "$key: ";
			if ( is_array( $value ) ) {
				$output .= "\n" . self::format_json_error( $value, $indent + 1 );
			} else {
				$output .= "$value\n";
			}
		}
		return $output;
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
