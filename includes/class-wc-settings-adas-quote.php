<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'admin_menu', 'adas_quote_settings_menu' );

/**
 * Add options page to the admin menu.
 */
function adas_quote_settings_menu() {
	add_options_page(
		'ADAS Quote Settings',
		'ADAS Quote',
		'manage_options',
		'adas-quote-settings',
		'adas_quote_settings_page'
	);
}

add_action( 'admin_init', 'adas_quote_settings_init' );

/**
 * Initialize settings.
 */
function adas_quote_settings_init() {
	register_setting( 'adas_quote_settings_group', 'adas_quote_hide_add_to_cart' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_hide_price' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_custom_email_message' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_admin_email' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_selected_products' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_selected_categories' );

	add_settings_section(
		'adas_quote_settings_section',
		'ADAS Quote Settings',
		'adas_quote_settings_section_callback',
		'adas-quote-settings'
	);

	add_settings_field(
		'adas_quote_selected_categories',
		'Select Categories for Quote Button',
		'adas_quote_selected_categories_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_field(
		'adas_quote_hide_add_to_cart',
		'Hide Add To Cart Button',
		'adas_quote_hide_add_to_cart_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_field(
		'adas_quote_hide_price',
		'Hide Price',
		'adas_quote_show_price_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_field(
		'adas_quote_custom_email_message',
		'Custom Email Message',
		'adas_quote_custom_email_message_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_field(
		'adas_quote_selected_products',
		'Select Products for Quote Button',
		'adas_quote_selected_products_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_field(
		'adas_quote_admin_email',
		'Admin Email',
		'adas_quote_admin_email_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);
}

/**
 * Callback function for displaying the selected product categories in a multi-select dropdown.
 */
function adas_quote_selected_categories_callback() {
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
 * Callback function for displaying the selected products in a multi-select dropdown.
 */
function adas_quote_selected_products_callback() {
	$selected_products = get_option( 'adas_quote_selected_products', array() );
	$paged             = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;//phpcs:ignore WordPress.Security.NonceVerification
	$per_page          = 50;
	$products          = wc_get_products(
		array(
			'limit'   => $per_page,
			'page'    => $paged,
			'status'  => 'publish',
			'orderby' => 'title',
			'order'   => 'ASC',
		)
	);

	if ( ! $products ) {
		esc_html__( 'No products found.', 'AQ' );
		return;
	}

	echo '<select multiple name="adas_quote_selected_products[]" style="width: 300px;">';
	foreach ( $products as $product ) {
		$selected = in_array( $product->get_id(), $selected_products ) ? 'selected' : '';
		echo '<option value="' . esc_attr( $product->get_id() ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $product->get_name() ) . '</option>';
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

	echo '<span class="displaying-num">' . esc_html(
		sprintf(
		// Translators: 1: Number of products.
			    // phpcs:ignore WordPress.WP.I18n
			_n( '1 product', '%s products', count( $total_products ), 'AQ' ),
			count( $total_products )
		)
	) . '</span>';

	$pagination_args = array(
		'base'      => add_query_arg( 'paged', '%#%' ),
		'format'    => '',
		'prev_text' => __( '&laquo;', 'text-domain' ),
		'next_text' => __( '&raquo;', 'text-domain' ),
		'total'     => $total_pages,
		'current'   => $paged,
	);

	echo '<span class="pagination-links">' . esc_html( paginate_links( $pagination_args ) ) . '</span>';
}

/**
 * Callback function for displaying the admin email input field.
 */
function adas_quote_admin_email_callback() {
	$option = get_option( 'adas_quote_admin_email' );
	if ( empty( $option ) ) {
		$option = get_option( 'admin_email' );
	}
	echo '<input style="width: 300px;" type="email" name="adas_quote_admin_email" value="' . esc_attr( $option ) . '">';
}

/**
 * Callback function for displaying the custom email message textarea.
 */
function adas_quote_custom_email_message_callback() {
	$option = get_option( 'adas_quote_custom_email_message' );
	echo '<textarea name="adas_quote_custom_email_message" rows="5" cols="50">' . esc_textarea( $option ) . '</textarea>';
}

/**
 * Callback function for displaying the hide price checkbox.
 */
function adas_quote_show_price_callback() {
	$option = get_option( 'adas_quote_hide_price' );
	echo '<input type="checkbox" name="adas_quote_hide_price" value="1" ' . checked( 1, $option, false ) . '>';
}

/**
 * Callback function for displaying the settings section description.
 */
function adas_quote_settings_section_callback() {
	echo 'Configure the ADAS Quote settings below:';
}

/**
 * Callback function for displaying the hide add to cart checkbox.
 */
function adas_quote_hide_add_to_cart_callback() {
	$option = get_option( 'adas_quote_hide_add_to_cart' );
	echo '<input type="checkbox" name="adas_quote_hide_add_to_cart" value="1" ' . checked( 1, $option, false ) . '>';
}

/**
 * Display the settings page.
 */
function adas_quote_settings_page() {
	?>
<div class="wrap custom-quote-settings-page">
	<h1>ADAS Quote Settings</h1>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'adas_quote_settings_group' );
			do_settings_sections( 'adas-quote-settings' );
			submit_button();
		?>
	</form>
</div>
	<?php
}
