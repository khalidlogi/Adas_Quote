<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_action( 'admin_menu', 'adas_quote_settings_menu' );

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

// add_action( 'admin_init', 'adas_quote_handle_clear_settings' );

// function adas_quote_handle_clear_settings() {
// if ( isset( $_POST['adas_quote_clear_settings'] ) && $_POST['adas_quote_clear_settings'] == '1' ) {
// delete_option( 'adas_quote_hide_add_to_cart' );
// delete_option( 'adas_quote_hide_price' );
// delete_option( 'adas_quote_custom_email_message' );
// delete_option( 'adas_quote_admin_email' );
// delete_option( 'adas_quote_selected_products' );
// delete_option( 'adas_quote_selected_categories' );
// }
// }
function adas_quote_settings_init() {
	register_setting( 'adas_quote_settings_group', 'adas_quote_hide_add_to_cart' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_hide_price' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_custom_email_message' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_admin_email' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_selected_products' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_selected_categories' );
	register_setting( 'adas_quote_settings_group', 'adas_quote_display_categories' );

	add_settings_field(
		'adas_quote_display_categories',
		'Display All Categories',
		'adas_quote_display_categories_callback',
		'adas-quote-settings',
		'adas_quote_settings_section'
	);

	add_settings_section(
		'adas_quote_settings_section',
		'ADAS Quote Settings',
		'adas_quote_settings_section_callback',
		'adas-quote-settings'
	);

	// Add settings field for selecting categories
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

function adas_quote_display_categories_callback() {
	$selected_products = get_option( 'adas_quote_selected_products', array() );

	PluginToolbox::displayAllProductCategories();
	$cat = PluginToolbox::getAdasQuoteSelectedCategories();
	print_r( $cat );
	echo '<br>';
	echo 'selected_products: ';
	print_r( $selected_products );
}
/**
 * Callback function for displaying the selected product categories in a multi-select dropdown.
 *
 * This function retrieves the selected product categories from the WordPress options, and then displays a multi-select dropdown
 * with all the available product categories. The selected categories are pre-selected in the dropdown.
 *
 * The function also handles the case where a category has child categories. In such cases, the child category IDs are added to the
 * selected categories array to ensure that the child categories are also selected.
 *
 * @return void
 */
function adas_quote_selected_categories_callback() {
	// Retrieve the selected product categories from the WordPress options
	$selected_categories = get_option( 'adas_quote_selected_categories', array() );

	// Retrieve all the product categories
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	// Debug output for the selected categories
	echo 'selected_categories';
	print_r( $selected_categories );

	// Start the multi-select dropdown
	echo '<select multiple name="adas_quote_selected_categories[]" style="width: 300px;">';

	// Loop through the product categories and display them in the dropdown
	foreach ( $categories as $category ) {

		// Check if the category has children
		$children = get_term_children( $category->term_id, 'product_cat' );
		if ( ! empty( $children ) ) {
			// Add the child category IDs to the selected categories
			$selected_categories = array_merge( $selected_categories, $children );
			// error_log( '$children : ' . print_r( $children, true ) );
			// error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
		}

		// Check if the current category is selected
		$selected = in_array( $category->term_id, explode( ',', $selected_categories[0] ) ) ? 'selected' : '';

		// Combine the category ID and its children IDs into a comma-separated string
		$ids = array( $category->term_id );
		if ( ! empty( $children ) ) {
			$ids = array_merge( $ids, $children );
		}
		$ids = implode( ',', $ids );

		// Display the category in the dropdown, with the selected status
		echo '<option value="' . esc_attr( $ids ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . ' id: ' . esc_html( ( $category->term_id ) ) . '</option>';

		// Debug output for the category ID
		error_log( '$category->term_id: ' . print_r( $category->term_id, true ) );
		error_log( 'in ' . __FILE__ . ' on line ' . __LINE__ );
	}

	// Close the multi-select dropdown
	echo '</select>';
}

function adas_quote_selected_products_callback() {
	$selected_products = get_option( 'adas_quote_selected_products', array() );
	$paged             = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
	$per_page          = 50; // Number of products to display per page
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
		_e( 'No products found.', 'text-domain' );
		return;
	}

	echo '<select multiple name="adas_quote_selected_products[]" style="width: 300px;">';
	foreach ( $products as $product ) {
		$selected = in_array( $product->get_id(), $selected_products ) ? 'selected' : '';
		echo '<option value="' . esc_attr( $product->get_id() ) . '" ' . $selected . '>' . esc_html( $product->get_name() ) . '</option>';
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

	echo '<span class="displaying-num">' . sprintf( _n( '1 product', '%s products', count( $total_products ), 'text-domain' ), count( $total_products ) ) . '</span>';

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

// function adas_quote_selected_categories_callback() {
// $selected_categories = get_option( 'adas_quote_selected_categories', array() );
// $categories          = get_terms(
// array(
// 'taxonomy'   => 'product_cat',
// 'hide_empty' => false,
// )
// );

// echo '<select multiple name="adas_quote_selected_categories[]" style="width: 300px;">';
// foreach ( $categories as $category ) {
// $selected = in_array( $category->term_id, $selected_categories ) ? 'selected' : '';
// echo '<option value="' . esc_attr( $category->term_id ) . '" ' . $selected . '>' . esc_html( $category->name ) . '</option>';
// }
// echo '</select>';
// }

function adas_quote_admin_email_callback() {
	$option = get_option( 'adas_quote_admin_email' );
	if ( empty( $option ) ) {
		$option = get_option( 'admin_email' );
	}
	echo '<input style="width: 300px;" type="email" name="adas_quote_admin_email" value="' . esc_attr( $option ) . '">';
}

function adas_quote_custom_email_message_callback() {
	$option = get_option( 'adas_quote_custom_email_message' );
	echo '<textarea name="adas_quote_custom_email_message" rows="5" cols="50">' . esc_textarea( $option ) . '</textarea>';
}

function adas_quote_show_price_callback() {
	$option = get_option( 'adas_quote_hide_price' );
	echo '<input type="checkbox" name="adas_quote_hide_price" value="1" ' . checked( 1, $option, false ) . '>';
}

function adas_quote_settings_section_callback() {
	echo 'Configure the ADAS Quote settings below:';
}

function adas_quote_hide_add_to_cart_callback() {
	$option = get_option( 'adas_quote_hide_add_to_cart' );
	echo '<input type="checkbox" name="adas_quote_hide_add_to_cart" value="1" ' . checked( 1, $option, false ) . '>';
}

function adas_quote_settings_page() {
	?>

<div class="wrap custom-quote-settings-page">

    <div class="wrap">
        <h1>ADAS Quote Settings</h1>
        <form method="post" action="options.php">
            <?php
			settings_fields( 'adas_quote_settings_group' );
			do_settings_sections( 'adas-quote-settings' );
			submit_button();
			?>

        </form>
    </div>
</div>
<?php
}