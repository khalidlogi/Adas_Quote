<?php
class ADAS_Quote_Plugin {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) . '/adas-wc-quote.php' ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings link to the plugin's entry on the plugins page.
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=adas-quote-settings">' . __( 'Settings', 'AQ' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function check_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	public function check_products_exist() {
		$args     = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		);
		$products = get_posts( $args );
		return ! empty( $products );
	}

	public function no_products_notice() {
		?>
<div class="notice notice-warning">
    <p>
        <?php
		esc_html_e(
			'No products found. Please create at least one product to use ADAS Quote Plugin effectively.',
			'ADAS Quote Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.

'
		);
		?>
    </p>
</div>
<?php
	}

	public function woocommerce_missing_notice() {
		?>
<div class="notice notice-error">
    <p><?php esc_html_e( 'ADAS Quote Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.', 'AQ' ); ?>
    </p>
</div>
<?php
	}








	public function create_admin_page() {
		?>
<div class="wrap">
    <?php
		if ( ! $this->check_woocommerce() ) {
			$this->woocommerce_missing_notice();
		} elseif ( ! $this->check_products_exist() ) {
			$this->no_products_notice();
			$this->display_settings_form();
		} else {
			$this->display_settings_form();
		}
		?>
</div>
<?php
	}


	public function add_plugin_page() {
		add_options_page(
			'ADAS Quote Settings',
			'ADAS Quote',
			'manage_options',
			'adas-quote-settings',
			array( $this, 'create_admin_page' )
		);
	}

	/*
	public function create_admin_page() {
		?>
<div class="wrap">
    <h2><?php _e( 'ADAS Quote Settings', 'adas-quote' ); ?></h2>
    <form method="post" action="options.php">
        <?php
				settings_fields( 'adas_quote_option_group' );
				do_settings_sections( 'adas-quote-settings' );
				submit_button();
		?>
    </form>
</div>
<?php
	}*/

	public function page_init() {
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_hide_add_to_cart'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_hide_price'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_custom_email_message'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_admin_email'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_selected_products'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_selected_categories'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_gmail_smtp_username'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_gmail_smtp_password'
		);

		add_settings_section(
			'adas_quote_settings_section',
			'ADAS Quote Settings',
			array( $this, 'settings_section_callback' ),
			'adas-quote-settings'
		);
		add_settings_section(
			'adas_smtp_settings_section',
			'SMTP Settings',
			array( $this, 'smtp_settings_section_callback' ),
			'adas-quote-settings'
		);

		add_settings_field(
			'adas_quote_gmail_smtp_username',
			'Gmail SMTP Username',
			array( $this, 'gmail_smtp_username_callback' ),
			'adas-quote-settings',
			'adas_smtp_settings_section'
		);
		add_settings_field(
			'adas_quote_gmail_smtp_password',
			'Gmail SMTP Password',
			array( $this, 'gmail_smtp_password_callback' ),
			'adas-quote-settings',
			'adas_smtp_settings_section'
		);

		add_settings_field(
			'adas_quote_selected_categories',
			'Select Categories for Quote Button',
			array( $this, 'selected_categories_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);

		add_settings_field(
			'adas_quote_hide_add_to_cart',
			'Hide Add To Cart Button',
			array( $this, 'hide_add_to_cart_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);

		add_settings_field(
			'adas_quote_hide_price',
			'Hide Price',
			array( $this, 'adas_quote_show_price_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_custom_email_message',
			'Custom Email Message',
			array( $this, 'adas_quote_custom_email_message_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);

		add_settings_field(
			'adas_quote_selected_products',
			'Select Products for Quote Button',
			array( $this, 'selected_products_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_admin_email',
			'Admin Email',
			array( $this, 'admin_email_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
	}

	/**
	 * Callback function for displaying the Gmail SMTP Username input field.
	 */
	function gmail_smtp_username_callback() {
		$option = get_option( 'adas_quote_gmail_smtp_username' );
		echo '<input type="text" name="adas_quote_gmail_smtp_username" value="' . esc_attr( $option ) . '" />';
	}

	/**
	 * Callback function for displaying the Gmail SMTP Password input field.
	 */
	function gmail_smtp_password_callback() {
		$option = get_option( 'adas_quote_gmail_smtp_password' );
		echo '<input type="text" name="adas_quote_gmail_smtp_password" value="' . esc_attr( $option ) . '" />';
	}

	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['enable_quote'] ) ) {
			$new_input['enable_quote'] = sanitize_text_field( $input['enable_quote'] );
		}
		return $new_input;
	}

	public function section_info() {
		// translators: %s: Plugin name
		printf(
			'<p>%s</p>',
			esc_html__(
				'Configure settings for the ADAS Quote plugin.',
				'ADAS Quote Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.

'
			)
		);
	}

	public function enable_quote_callback() {
		$options = get_option( 'adas_quote_options' );
		$checked = isset( $options['enable_quote'] ) ? 'checked' : '';
		printf(
			'<input type="checkbox" id="enable_quote" name="adas_quote_options[enable_quote]" value="1" %s />',
			esc_attr( $checked )
		);
	}

	public function settings_section_callback() {
		echo '<p>Configure settings for ADAS Quote plugin.</p>';
	}

	/**
	 * Callback function for displaying the SMTP settings section description.
	 */
	public function smtp_settings_section_callback() {
		echo '<p>Configure SMTP settings for sending emails.</p>';
	}

	function selected_categories_callback() {
		global $wp_query;

		$current_product_cat = isset( $wp_query->query['product_cat'] ) ? $wp_query->query['product_cat'] : '';

		$defaults = array(
			'pad_counts'         => 1,
			'show_count'         => 1,
			'hierarchical'       => 1,
			'hide_empty'         => 1,
			'show_uncategorized' => 1,
			'orderby'            => 'name',
			'selected'           => $current_product_cat,
			'menu_order'         => false,
		);

		$args  = wp_parse_args( array(), $defaults );
		$terms = get_terms( array_merge( array( 'taxonomy' => 'product_cat' ), $args ) );

		$saved_product_cat = get_option( 'adas_quote_selected_categories', '' );

		$output  = "<select name='adas_quote_selected_categories' class='dropdown_product_cat'>";
		$output .= '<option value="" ' . selected( $saved_product_cat, '', false ) . '>' . __( 'Select a category', 'woocommerce' ) . '</option>';
		$output .= wc_walk_category_dropdown_tree( $terms, 0, array_merge( $args, array( 'selected' => $saved_product_cat ) ) );

		$output      .= '</select>';
		$allowed_html = array(
			'select' => array(
				'name'  => true,
				'class' => true,
			),
			'option' => array(
				'value'    => true,
				'selected' => true,
			),
		);
		echo wp_kses( $output, $allowed_html );}

	/**
	 * Callback function for displaying the admin email input field.
	 */
	function admin_email_callback() {
		$option = get_option( 'adas_quote_admin_email' );
		if ( empty( $option ) ) {
			$option = get_option( 'admin_email' );
		}
		echo '<input style="width: 300px;" type="email" name="adas_quote_admin_email" value="' . esc_attr( $option ) . '">';
	}

		/**
		 * Callback function for displaying the selected products in a multi-select dropdown.
		 */
	function selected_products_callback() {
		// $selected_products = get_option( 'adas_quote_selected_products', array() );
		$selected_products_option = get_option( 'adas_quote_selected_products', array() );

		// Ensure $selected_products is always an array
		$selected_products = is_array( $selected_products_option ) ? $selected_products_option : array();

		// Only apply array_map if $selected_products is not empty
		$selected_products = ! empty( $selected_products ) ? array_flip( array_map( 'intval', $selected_products ) ) : array();

		error_log( 'Selected products: ' . print_r( $selected_products, true ) );

		$paged    = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
		$per_page = 50;
		$products = wc_get_products(
			array(
				'limit'   => $per_page,
				'page'    => $paged,
				'status'  => 'publish',
				'orderby' => 'title',
				'order'   => 'ASC',
			)
		);

		if ( ! $products ) {
			echo esc_html__( 'No products found.', 'AQ' );
			error_log( 'No products found.' );
			return;
		}

		echo '<select multiple name="adas_quote_selected_products[]" style="width: 300px;">';
		foreach ( $products as $product ) {
			$product_id  = (int) $product->get_id();
			$is_selected = isset( $selected_products[ $product_id ] );
			error_log( "Product ID: $product_id, Is Selected: " . ( $is_selected ? 'Yes' : 'No' ) );
			printf(
				'<option value="%d" %s>%s</option>',
				$product_id,
				$is_selected ? 'selected' : '',
				esc_html( $product->get_name() )
			);
		}
		echo '</select>';

		$total_products = wc_get_products(
			array(
				'limit'  => -1,
				'status' => 'publish',
				'return' => 'ids',
			)
		);
		$total_pages    = ceil( count( $total_products ) / $per_page );

		printf(
			'<span class="displaying-num">%s</span>',
			esc_html(
				sprintf(
					// Translators: 1: Number of products.
					_n( '1 product', '%s products', count( $total_products ), 'AQ' ),
					count( $total_products )
				)
			)
		);

		$pagination_args = array(
			'base'      => add_query_arg( 'paged', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;', 'text-domain' ),
			'next_text' => __( '&raquo;', 'text-domain' ),
			'total'     => $total_pages,
			'current'   => $paged,
		);

		echo '<span class="pagination-links">' . paginate_links( $pagination_args ) . '</span>';
	}


	/**
	 * Callback function for displaying the hide add to cart checkbox.
	 */
	function hide_add_to_cart_callback() {
		$option = get_option( 'adas_quote_hide_add_to_cart' );
		echo '<input type="checkbox" name="adas_quote_hide_add_to_cart" value="1" ' . checked( 1, $option, false ) . '>';
	}

	/**
	 * Callback function for displaying the hide price checkbox.
	 */
	function adas_quote_show_price_callback() {
		$option = get_option( 'adas_quote_hide_price' );
		echo '<input type="checkbox" name="adas_quote_hide_price" value="1" ' . checked( 1, $option, false ) . '>';
	}


	/**
	 * Callback function for displaying the custom email message textarea.
	 */
	function adas_quote_custom_email_message_callback() {
		$option = get_option( 'adas_quote_custom_email_message' );
		echo '<textarea name="adas_quote_custom_email_message" rows="5" cols="50">' . esc_textarea( $option ) . '</textarea>';
	}
	private function display_settings_form() {
		?>
<form method="post" action="options.php">
    <?php
			settings_fields( 'adas_quote_settings_group' );
			do_settings_sections( 'adas-quote-settings' );
			submit_button();
		?>
</form>
<?php
	}
}