<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}


add_action( 'admin_menu', 'adas_add_menu_items' );
/**
 * REGISTER ADMIN PAGE
 */
function adas_add_menu_items() {
	add_menu_page(
		__( 'Manage Quotes', 'adas_quote_request' ), // Page title.
		__( 'Adas Quotes Manager', 'adas_quote_request' ),        // Menu title.
		'activate_plugins',                                         // Capability.
		'adas_list',                                             // Menu slug.
		'adas_quote_render_list_page',                                       // Callback function.
		'dashicons-list-view' // Dashicon class.
	);
}

/**
 * CALLBACK TO RENDER THE EXAMPLE ADMIN PAGE
 */
function adas_quote_render_list_page() {

	// Getting crasy with this shit.
	// this function is the switch board that will call the correct class.
	// based on the action parameter.

	// See what page we are in right now.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$fid  = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
	$ufid = isset( $_GET['ufid'] ) ? (int) $_GET['ufid'] : '';

	if ( ! empty( $fid ) && empty( $ufid ) ) {
		new Adas_Quote_Form_Details();
		return;
	}

	if ( ! empty( $ufid ) && ! empty( $fid ) ) {

		new Adas_Quote_Form_Details_Ufd();
		return;
	}

	// Create an instance of our package class.
	$test_list_table = new Adas_Main_List_Table();
	$test_list_table->prepare_items();

	// Include the view markup.
	include __DIR__ . '/views/page.php';
}




/**
 * Example List Table Child Class
 * Our topic for this list table is going to be movies.
 *
 * @package WPListTableExample
 * @author  Matt van Andel
 */

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Custom List Table Class
 */
class Adas_Main_List_Table extends WP_List_Table {


	/**
	 * How many items to show on a single page.
	 *
	 * @var int|string
	 */
	private $per_page = 10;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'contact-form',     // Singular name of the listed records.
				'plural'   => 'contact-forms',    // Plural name of the listed records.
				'ajax'     => false,       // Does this table support ajax?
			)
		);
	}

	/**
	 * This is a custom get_columns function
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information.
	 */
	public function get_columns() {

		$columns = array(
			'product_id'   => __( 'PRODUCT ID', 'adas_quote_request' ),
			'product_name' => 'Product Name',
			'count'        => __( 'Count', 'adas_quote_request' ),
		);

		return $columns;
	}



	/**
	 * Get default column value.
	 *
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Get value for checkbox column.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%1$s"/>',
			$item['id']                // The value of the checkbox should be the record's ID.
		);
	}



	/**
	 * Prepare items for the list table
	 *
	 * @global wpdb $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	public function prepare_items() {

		$columns = $this->get_columns();

		$hidden = array();

		$this->_column_headers = array( $columns, $hidden );

		/*
		 * GET THE DATA!
		 */

		$data = $this->entries_data();

		if ( ! $data ) {
			return;
		}

		$current_page = $this->get_pagenum();

		$total_items = count( $data );

		/*
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to do that.
		 */
		$data = array_slice( $data, ( ( $current_page - 1 ) * $this->per_page ), $this->per_page );

		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                   // WE have to calculate the total number of items.
				'per_page'    => ( $this->per_page ),                         // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $this->per_page ), // WE have to calculate the total number of pages.
			)
		);
	}

	/**
	 * Get entries data
	 *
	 * @return array|bool
	 */
	public function entries_data() {
		global $wpdb;

		$title = 'title';

		$results = $wpdb->get_results(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT id,product_id,product_name, COUNT(*) as count
		FROM {$wpdb->prefix}kh_woo 
		GROUP BY product_name",
			ARRAY_A
		);

		if ( ! $results ) {
			return false;
		}

		foreach ( $results as $result ) {
			$product_name = $result['product_name'];

			// get the id of the form.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}kh_woo WHERE product_name = %s",
					$product_name
				)
			);

			$nonce = wp_create_nonce( 'adas_list_nonce' );

			$product_id   = $result['product_id'];
			$product_name = $result['product_name'];
			$link         = "<a class='row-title' href='admin.php?page=adas_list&fid=" . esc_attr( $product_id ) . '&_wpnonce=' . esc_attr( $nonce ) . "'>%s</a>";

			$data_value['product_id']   = sprintf( $link, $product_id );
			$data_value['count']        = sprintf( $link, $count );
			$data_value['product_name'] = sprintf( $link, stripslashes( $product_name ) );
			$data[]                     = $data_value;

		}

		return $data;
	}
}
