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
			wp_die( 'No action taken' );
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
		if ( ! $product ) {
			wp_die( 'Product not found.' );
		}

		$list_table = new ADASQT_Wp_Sub_Page();
		$list_table->prepare_items();
		?>
<div class="wrap">
    <h2>Quotes submitted for : <b><?php echo esc_html( $product->get_title() ); ?></b></h2>
    <?php $list_table->display(); ?>
    <div class="tablenav bottom">
        <?php echo wp_kses_post( $list_table->get_views() ); // Display pagination links. ?>
    </div>
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
	 * Constructor start subpage
	 */
	public function __construct() {
		$nonce = isset( $_REQUEST['adas_list_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['adas_list_nonce'] ) ) : '';

		if ( wp_verify_nonce( $nonce, 'adas_list_nonce' ) ) {
			wp_die( 'No action taken' );
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
			'id'             => _x( 'id', 'Column label', 'AQ' ),
			'date_submitted' => _x( 'Date', 'Column label', 'AQ' ),
			'read_status'    => _x( 'Read Status', 'Column label', 'AQ' ),
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
	 * @param object $item        A singular item (one full row's worth of data).
	 * @param string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>.
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'read_status':
				$read_status = $item['read_status'];
				return ( '1' === $read_status ) ? 'Read' : 'Unread';

			case 'id':
			case 'date_submitted':
				return $item[ $column_name ];
			default:
				// return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
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
				$item['id']
			);
		}
	}

	/**
	 * Get id column value.
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
			_x( 'Details', 'List table row action', 'AQ' )
		);

		// Return the id contents.
		return sprintf(
			'%1$s <span style="color:silver;">entry</span>%2$s',
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
			wp_die( 'No action taken' );
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        // phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}kh_woo WHERE id IN({$placeholders})", $ids ) );
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		global $wpdb;
		$per_page     = 2;
		$product_id   = $this->product_id;
		$columns      = $this->get_columns();
		$hidden       = array();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		// Calculate the total number of items before calling the entries_data().
		$total_items = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}kh_woo WHERE product_id = %s",
				$product_id
			)
		);

		$data = $this->entries_data( $current_page, $per_page );

		usort( $data, array( $this, 'usort_reorder' ) );

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Get the pagination views for the list table.
	 *
	 * @return string HTML output for the pagination views.
	 */
	public function get_views() {
		$current_page = $this->get_pagenum();
		$total_items  = $this->_pagination_args['total_items'];
		$total_pages  = ceil( $total_items / $this->_pagination_args['per_page'] );

		$output = '<div class="tablenav-pages">';
		// translators: %s: Number of items.
		$output .= sprintf(
			'<span class="displaying-num">' . _n( '%s item', '%s items', $total_items ) . '</span>',
			number_format_i18n( $total_items )
		);

		if ( $total_pages > 1 ) {
			$page_links = paginate_links(
				array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
					'total'     => $total_pages,
					'current'   => $current_page,
				)
			);

			if ( $page_links ) {
				$output .= "\n<span class='pagination-links'>$page_links</span>";
			}
		}

		$output .= '</div>';

		return $output;
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

		// Sanitize input parameters
		$page           = absint( $page );
		$items_per_page = absint( $items_per_page );
		$offset         = ( $page - 1 ) * $items_per_page;

		// Sanitize and validate orderby
		$valid_orderby_columns = array( 'date_submitted', 'id', 'product_id' ); // Add all valid column names
		$orderby               = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], $valid_orderby_columns )
			? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) )
			: 'date_submitted';

		// Sanitize and validate order.
		$order = isset( $_GET['order'] ) && strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) === 'asc'
			? 'ASC'
			: 'DESC';

		$product_id = absint( $this->product_id );

		// Prepare the query.
		$query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}kh_woo 
            WHERE product_id = %d 
            ORDER BY {$orderby} {$order} 
            LIMIT %d OFFSET %d",
			$product_id,
			$items_per_page,
			$offset
		);

		// Execute the query.
        // phpcs:ignore.
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}
}