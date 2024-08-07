<?php
/**
 * Adas Admin subpage
 *
 * @package AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Adas_Quote_Form_Details
 */
class Adas_Quote_Form_Details {


	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $product_id;



	/**
	 * Constructor start subpage
	 */
	public function __construct() {
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		$nonce_verified = isset( $_GET['_wpnonce'] ) ? wp_verify_nonce( $nonce, 'adas_list_nonce' ) : false;

		if ( ! $nonce_verified ) {
			wp_die( 'Security verification failed' );
		}

			$this->product_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';

			// create page.
			$this->adas_table_page();
	}

	/**
	 * Create the page to display the form details.
	 *
	 * @return void
	 */
	public function adas_table_page() {
		$product = wc_get_product( $this->product_id );

		$list_table = new ADASQT_Wp_Sub_Page();
		$list_table->prepare_items();
		?>
<div class="wrap">
	<h2>Quotes submitted for : <b><?php echo esc_html( $product->get_title() ); ?></b></h2>

	</h2>
	<form method="post" action="">
		<?php $list_table->display(); ?>
	</form>
</div>
		<?php
	}
}

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * WPFormsDB_Wp_List_Table class will create the page to load the table.
 */
class ADASQT_Wp_Sub_Page extends WP_List_Table {

	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $product_id;
	/**
	 * Page number.
	 *
	 * @var int
	 */
	private $page;

	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $form_id;

	/**
	 * Constructor start subpage
	 */
	public function __construct() {

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		$nonce_verified = isset( $_GET['_wpnonce'] ) ? wp_verify_nonce( $nonce, 'adas_list_nonce' ) : false;

		if ( ! $nonce_verified ) {
			wp_die( 'Security verification failed' );
		}

		$this->product_id = isset( $_GET['fid'] ) ? sanitize_text_field( wp_unslash( $_GET['fid'] ) ) : '';
		$this->page       = isset( $_REQUEST['page'] ) ? sanitize_title_with_dashes( wp_unslash( $_REQUEST['page'] ) ) : '';

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'Quote-form',     // Singular name of the listed records.
				'plural'   => 'Quotes-forms',    // Plural name of the listed records.
				'ajax'     => false,       // Does this table support ajax?
			)
		);
	}


	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'page_id'
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information.
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />', // Render a checkbox instead of text.
			'id'             => _x( 'id', 'Column label', 'adasdividb' ),
			'page_id'        => _x( 'page_id', 'Column label', 'adasdividb' ),
			// 'page_name'      => _x( 'page_name', 'Column label', 'adasdividb' ),
			'date_submitted' => _x( 'date_submitted', 'Column label', 'adasdividb' ),
			'read_status'    => _x( 'Read Status', 'Column label', 'adasdividb' ),
		);

		return $columns;
	}


	/**
	 * Get a list of sortable columns.
	 *
	 * @return array An associative array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'id'             => array( 'id', false ),
			'date_submitted' => array( 'date_submitted', false ),
			'read_status'    => array( 'read_status', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Get default column value.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */

	/**
	 * Get default column value.
	 *
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'read_status':
				$read_status = $item['read_status'];

				// Output the cell content as "Read" if read_status is 1, or "Unread" otherwise.
				return ( '1' === $read_status ) ? 'Read' : 'Unread';

			case 'id':
			case 'page_id':
			case 'page_name':
			case 'date_submitted':
				return $item[ $column_name ];
			default:
		}
	}

	/**
	 * Get value for checkbox column.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_cb( $item ) {
		if ( isset( $item['id'] ) ) {
			return sprintf(
				'<input type="checkbox" name="id[]" value="%1$s"/>',
				$item['id']                // The value of the checkbox should be the record's ID.
			);
		}
	}

	/**
	 * Get page_id column value.
	 *
	 * @param object $item A singular item (one full row's worth of data).
	 * @return string Text to be placed inside the column <td>.
	 */
	protected function column_id( $item ) {
		$view_nonce = wp_create_nonce( 'view_action' );

		// Build view row action.
		$view_query_args = array(
			'page'   => $this->page,
			'action' => 'view',
			'ufid'   => $item['id'],
			'fid'    => $this->product_id,
		);

		$actions['view'] = sprintf(
			'<a href="%1$s&view_nonce=%2$s">%3$s</a>',
			esc_url( add_query_arg( $view_query_args, 'admin.php' ) ),
			esc_attr( $view_nonce ),
			_x( 'Details', 'List table row action', 'adasdividb' )
		);

		// Return the page_id contents.
		return sprintf(
			'%2$s <span style="color:silver;">entry</span>%3$s',
			$item['page_id'],
			$item['id'],
			$this->row_actions( $actions )
		);
	}

	/**
	 * Get an associative array ( option_name => option_page_id ) with the list
	 * of bulk actions available on this table.
	 *
	 * @return array An associative array containing all the bulk actions.
	 */
	protected function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'text-domain' ),
		);

		// Add nonce to delete action.
		$delete_nonce       = wp_create_nonce( 'deletentry' );
		$actions['delete'] .= sprintf(
			'<input type="hidden" name="delete_nonce" value="%s" />',
			esc_attr( $delete_nonce )
		);

		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 */
	protected function process_bulk_action() {
		global $wpdb;
		$product_id = $this->product_id;
		$ids        = isset( $_REQUEST['id'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['id'] ) ) : array();

		if ( empty( $ids ) ) {
			return;
		}

		if ( 'delete' !== $this->current_action() ) {
			return;
		}

		if ( ! wp_verify_nonce( isset( $_REQUEST['delete_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['delete_nonce'] ) ) : '', 'deletentry' ) ) {
			wp_die( 'Security verification failed' );
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		// phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kh_woo WHERE id IN({$placeholders})", $ids ) );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @global $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	public function prepare_items() {
		global $wpdb;
		$form_id      = $this->form_id;
		$per_page     = 10;
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		// Calculate the total number of items before calling the entries_data().
		$total_items = $wpdb->get_var(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}kh_woo WHERE product_id = %s",
				$form_id
			)
		);

		$data = $this->entries_data( $current_page, $per_page );

		usort( $data, array( $this, 'usort_reorder' ) );

		$this->items = $data;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,                   // WE have to calculate the total number of items.
				'per_page'    => ( $per_page ),                         // WE have to determine how many items to show on a page.
			)
		);
	}

	/**
	 * Custom sorting function for the list table.
	 *
	 * @param array $a First item to compare.
	 * @param array $b Second item to compare.
	 * @return int Comparison result.
	 */
	protected function usort_reorder( $a, $b ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'read_status';
		$order   = ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'asc';

		switch ( $orderby ) {
			case 'read_status':
				$result = strcmp( $a['read_status'], $b['read_status'] );
				break;
			case 'id':
				$result = $a['id'] - $b['id'];
				break;
			case 'date_submitted':
				$result = strcmp( $a['date_submitted'], $b['date_submitted'] );
				break;
			// Add other column cases here if needed.
			default:
				return 0; // Return 0 for no sorting.
		}

		return ( 'asc' === $order ) ? $result : -$result;
	}

	/**
	 * Retrieve the entries data for the list table.
	 *
	 * @param int $page The current page number.
	 * @param int $items_per_page The number of items to display per page.
	 * @return array The entries data.
	 */
	public function entries_data( $page, $items_per_page ) {

		global $wpdb;
		$offset = ( intval( $page ) - 1 ) * intval( $items_per_page );

		global $wpdb;
		$results = array();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) ? 'date_submitted' : 'date_submitted';

		$order = isset( $_GET['order'] ) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

		$product_id = $this->product_id;

		$results = $wpdb->get_results(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}kh_woo WHERE product_id = %s ORDER BY %s %s LIMIT %d OFFSET %d",
				$product_id,
				$orderby,
				$order,
				$items_per_page,
				$offset
			),
			ARRAY_A
		);

		return $results;
	}
}