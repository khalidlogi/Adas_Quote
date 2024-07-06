<?php
/**
 * Class ADAS_Quote_Plugin
 *
 * This class handles the initialization and settings of the Adas Quote Plugin.
 *
 *  @package   AdasQuoteForWC
 */
class ADAS_Quote_Plugin {
	/**
	 * The single instance of the class.
	 *
	 * @var ADAS_Quote_Plugin|null The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return ADAS_Quote_Plugin The instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Initializes the plugin by adding necessary actions and filters.
	 */
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
		$settings_link = '<a href="options-general.php?page=adas-quote-settings">' . __( 'Settings', 'adas_quote_request' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

		/**
		 * Check if WooCommerce is installed and active.
		 *
		 * @return bool True if WooCommerce is active, false otherwise.
		 */
	public function check_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check if there are any published products.
	 *
	 * @return bool True if there are published products, false otherwise.
	 */
	public function check_products_exist() {
		$args     = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		);
		$products = get_posts( $args );
		return ! empty( $products );
	}

	/**
	 * Display a notice if no products are found.
	 */
	public function no_products_notice() {
		?>
<div class="notice notice-warning">
	<p>
		<?php
		esc_html_e(
			'No products found. Please create at least one product to use Adas Quote Plugin effectively.',
			'adas_quote_request'
		);
		?>
	</p>
</div>
		<?php
	}

	/**
	 * Display a notice if WooCommerce is not installed or active.
	 */
	public function woocommerce_missing_notice() {
		?>
<div class="notice notice-error">
	<p><?php esc_html_e( 'Adas Quote Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.', 'adas_quote_request' ); ?>
	</p>
</div>
		<?php
	}

	/**
	 * Create the admin page for the plugin settings.
	 */
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

		// Display email errors if the checkbox is checked.
		$display_errors = get_option( 'adas_quote_display_email_errors' );
		if ( ! empty( $display_errors ) ) {
			$this->render_email_errors();
		}

		?>
</div>
		<?php
	}

	/**
	 * Display email errors from the logs.
	 */
	/**
	 * Display email errors from the logs.
	 */
	// private function display_email_errors() {
	// $errors           = get_option( 'adas_quote_email_errors', array() );
	// $phpmailer_errors = array();

	// if ( ! empty( $errors ) ) {
	// Reverse the order of errors.
	// $errors = array_reverse( $errors );

	// foreach ( $errors as $error ) {
	// $message = $this->extract_phpmailer_message( $error['error'] );
	// if ( $message ) {
	// $phpmailer_errors[] = array(
	// 'time'    => $error['time'],
	// 'message' => $message,
	// );
	// }
	// }
	// }

	// echo '<h2>Email Logs</h2>';

	// if ( ! empty( $phpmailer_errors ) ) {
	// echo '<ul>';
	// foreach ( $phpmailer_errors as $error ) {
	// echo '<li><strong>Time:</strong> ' . esc_html( $error['time'] ) . ' - <strong>PHPMailer error:</strong> ' . esc_html( $error['message'] ) . '</li>';
	// }
	// echo '</ul>';
	// } else {
	// echo '<p>' . esc_html__( 'No PHPMailer errors found.', 'adas_quote_request' ) . '</p>';     }
	// }

	private function display_email_errors() {
		$errors           = get_option( 'adas_quote_email_errors', array() );
		$phpmailer_errors = array();
		$general_errors   = array();
		$max_errors       = 5; // Limit to 5 most recent errors for each category

		if ( ! empty( $errors ) ) {
			$errors = array_reverse( $errors );

			foreach ( $errors as $error ) {
				$time          = sanitize_text_field( $error['time'] );
				$error_message = esc_html( $error['error'] );

				$phpmailer_message = $this->extract_phpmailer_message( $error['error'] );

				if ( $phpmailer_message ) {
					if ( count( $phpmailer_errors ) < $max_errors ) {
						$phpmailer_errors[] = array(
							'time'    => $time,
							'message' => esc_html( $phpmailer_message ),
						);
					}
				} elseif ( count( $general_errors ) < $max_errors ) {
						$general_errors[] = array(
							'time'    => $time,
							'message' => $error_message,
						);
				}

				if ( count( $phpmailer_errors ) >= $max_errors && count( $general_errors ) >= $max_errors ) {
					break;
				}
			}
		}

		return array(
			'phpmailer_errors' => $phpmailer_errors,
			'general_errors'   => $general_errors,
		);
	}

	public function render_email_errors() {
		$errors = $this->display_email_errors();

		echo '<h2>' . esc_html__( 'Email Logs', 'adas_quote_request' ) . '</h2>';

		// Display general errors
		if ( ! empty( $errors['general_errors'] ) ) {
			echo '<ul>';
			foreach ( $errors['general_errors'] as $error ) {
				echo '<li><strong>' . esc_html__( 'Time:', 'adas_quote_request' ) . '</strong> ' . esc_html( $error['time'] ) . ' - <strong>' . esc_html__( 'Log:', 'adas_quote_request' ) . '</strong> ' . esc_html( $error['message'] ) . '</li>';
			}
			echo '</ul>';
		}

		// Display PHPMailer errors
		// echo '<h3>' . esc_html__( 'PHPMailer Errors', 'adas_quote_request' ) . '</h3>';
		if ( ! empty( $errors['phpmailer_errors'] ) ) {
			echo '<ul>';
			foreach ( $errors['phpmailer_errors'] as $error ) {
				echo '<li><strong>' . esc_html__( 'Time:', 'adas_quote_request' ) . '</strong> ' . esc_html( $error['time'] ) . ' - <strong>' . esc_html__( 'PHPMailer Log:', 'adas_quote_request' ) . '</strong> ' . esc_html( $error['message'] ) . '</li>';
			}
			echo '</ul>';
		} //else {
		// echo '<p>' . esc_html__( 'No PHPMailer errors found.', 'adas_quote_request' ) . '</p>';
		// }
	}

	/**
	 * Extract PHPMailer error message.
	 */
	private function extract_phpmailer_message( $error_string ) {
		if ( strpos( $error_string, 'PHPMailer log:' ) !== false ) {
			$json_start = strpos( $error_string, '{' );
			if ( $json_start !== false ) {
				$json_string = substr( $error_string, $json_start );
				$error_data  = json_decode( $json_string, true );
				if ( json_last_error() === JSON_ERROR_NONE && isset( $error_data['error']['message'] ) ) {
					return $error_data['error']['message'];
				}
			}
		}
		return null;
	}






	/**
	 * Add the plugin settings page to the admin menu.
	 */
	public function add_plugin_page() {
		add_options_page(
			'Adas Quote Settings',
			'Adas Quote',
			'manage_options',
			'adas-quote-settings',
			array( $this, 'create_admin_page' )
		);
	}



	/**
	 * Initialize the settings page.
	 */
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
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_email_subject'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_display_email_errors'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_enable_recaptcha'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_recaptcha_secret_key'
		);
		register_setting(
			'adas_quote_settings_group',
			'adas_quote_recaptcha_site_key'
		);

		add_settings_section(
			'adas_quote_settings_section',
			__( 'Adas Quote Settings', 'adas_quote_request' ),
			array( $this, 'settings_section_callback' ),
			'adas-quote-settings'
		);
		add_settings_section(
			'adas_smtp_settings_section',
			__( 'SMTP Settings', 'adas_quote_request' ),
			array( $this, 'smtp_settings_section_callback' ),
			'adas-quote-settings'
		);
		add_settings_section(
			'adas_recaptcha_settings_section',
			__( 'reCAPTCHA Settings', 'adas_quote_request' ),
			array( $this, 'recaptcha_settings_section_callback' ),
			'adas-quote-settings'
		);

		add_settings_field(
			'adas_quote_gmail_smtp_username',
			__( 'Gmail SMTP Username', 'adas_quote_request' ),
			array( $this, 'gmail_smtp_username_callback' ),
			'adas-quote-settings',
			'adas_smtp_settings_section'
		);
		add_settings_field(
			'adas_quote_gmail_smtp_password',
			__( 'Gmail SMTP Password', 'adas_quote_request' ),
			array( $this, 'gmail_smtp_password_callback' ),
			'adas-quote-settings',
			'adas_smtp_settings_section'
		);
		add_settings_field(
			'adas_quote_display_email_errors',
			__( 'Display Email Errors', 'adas_quote_request' ),
			array( $this, 'display_email_errors_callback' ),
			'adas-quote-settings',
			'adas_smtp_settings_section'
		);
		add_settings_field(
			'adas_quote_selected_categories',
			__( 'Select Categories for Quote Button', 'adas_quote_request' ),
			array( $this, 'selected_categories_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_selected_products',
			__( 'Select Products for Quote Button', 'adas_quote_request' ),
			array( $this, 'selected_products_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_hide_add_to_cart',
			__( 'Hide Add To Cart Button', 'adas_quote_request' ),
			array( $this, 'hide_add_to_cart_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_hide_price',
			__( 'Hide Price', 'adas_quote_request' ),
			array( $this, 'adas_quote_show_price_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_custom_email_message',
			__( 'Custom Email Message', 'adas_quote_request' ),
			array( $this, 'adas_quote_custom_email_message_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_email_subject',
			__( 'Email Subject', 'adas_quote_request' ),
			array( $this, 'email_subject_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_admin_email',
			__( 'Admin Email', 'adas_quote_request' ),
			array( $this, 'admin_email_callback' ),
			'adas-quote-settings',
			'adas_quote_settings_section'
		);
		add_settings_field(
			'adas_quote_enable_recaptcha',
			__( 'Enable reCAPTCHA', 'adas_quote_request' ),
			array( $this, 'enable_recaptcha_callback' ),
			'adas-quote-settings',
			'adas_recaptcha_settings_section'
		);
		add_settings_field(
			'adas_quote_recaptcha_secret_key',
			__( 'reCAPTCHA Secret Key', 'adas_quote_request' ),
			array( $this, 'recaptcha_secret_key_callback' ),
			'adas-quote-settings',
			'adas_recaptcha_settings_section',
			array( 'class' => 'recaptcha-field' )
		);
		add_settings_field(
			'adas_quote_recaptcha_site_key',
			__( 'reCAPTCHA Site Key', 'adas_quote_request' ),
			array( $this, 'recaptcha_site_key_callback' ),
			'adas-quote-settings',
			'adas_recaptcha_settings_section',
			array( 'class' => 'recaptcha-field' )
		);

			// Add JavaScript to hide/show reCAPTCHA fields based on the checkbox state.
			add_action(
				'admin_footer',
				function () {
					?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {




	const recaptchaCheckbox = document.querySelector('input[name="adas_quote_enable_recaptcha"]');
	const recaptchaFields = document.querySelectorAll('.recaptcha-field');

	function toggleRecaptchaFields() {
		recaptchaFields.forEach(field => {
			field.closest('tr').style.display = recaptchaCheckbox.checked ? '' : 'none';
		});
	}

	recaptchaCheckbox.addEventListener('change', toggleRecaptchaFields);
	toggleRecaptchaFields(); // Initial call to set the correct state on page load
});
</script>
					<?php
				}
			);
	}

		/**
		 * Callback function for displaying the reCAPTCHA Site Key input field.
		 */
	public function recaptcha_site_key_callback() {
		$option = get_option( 'adas_quote_recaptcha_site_key' );
		echo '<input type="text" name="adas_quote_recaptcha_site_key" value="' . esc_attr( $option ) . '" />';
	}


		/**
		 * Callback function for the reCAPTCHA settings section.
		 */
	public function recaptcha_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the reCAPTCHA settings for the quote request form.', 'adas_quote_request' ) . '</p>';
	}

	/**
	 * Callback function for displaying the reCAPTCHA Secret Key input field.
	 */
	public function recaptcha_secret_key_callback() {
		$option = get_option( 'adas_quote_recaptcha_secret_key' );
		echo '<input type="text" name="adas_quote_recaptcha_secret_key" value="' . esc_attr( $option ) . '" />';
	}

	/**
	 * Callback function for displaying the Enable reCAPTCHA checkbox.
	 */
	public function enable_recaptcha_callback() {
		$option = get_option( 'adas_quote_enable_recaptcha' );
		echo '<input type="checkbox" name="adas_quote_enable_recaptcha" value="1" ' . checked( 1, $option, false ) . '>';
	}


	/**
	 * Callback function for displaying the email errors checkbox.
	 */
	public function display_email_errors_callback() {
		$option = get_option( 'adas_quote_display_email_errors' );
		echo '<input type="checkbox" name="adas_quote_display_email_errors" value="1" ' . checked( 1, $option, false ) . '>';
	}
	/**
	 * Callback function for displaying the email subject input field.
	 */
	public function email_subject_callback() {
		$option = get_option( 'adas_quote_email_subject' );
		echo '<input style="width: 300px;" type="text" name="adas_quote_email_subject" value="' . esc_attr( $option ) . '">';
	}

	/**
	 * Callback function for displaying the Gmail SMTP Username input field.
	 */
	public function gmail_smtp_username_callback() {
		$option = get_option( 'adas_quote_gmail_smtp_username' );
		echo '<input type="text" name="adas_quote_gmail_smtp_username" value="' . esc_attr( $option ) . '" />';
	}

	/**
	 * Callback function for displaying the Gmail SMTP Password input field.
	 */
	public function gmail_smtp_password_callback() {
		$option = get_option( 'adas_quote_gmail_smtp_password' );
		echo '<input type="text" name="adas_quote_gmail_smtp_password" value="' . esc_attr( $option ) . '" />';
	}

	/**
	 * Sanitize the input values.
	 *
	 * @param array $input The input values.
	 * @return array The sanitized input values.
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['enable_quote'] ) ) {
			$new_input['enable_quote'] = sanitize_text_field( $input['enable_quote'] );
		}
		return $new_input;
	}

		/**
		 * Display the section info.
		 */
	public function section_info() {
		// translators: %s: Plugin name.
		printf(
			'<p>%s</p>',
			esc_html__(
				'Configure settings for the Adas Quote plugin.',
				'Adas Quote Plugin requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.

'
			)
		);
	}

	/**
	 * Callback function for displaying the enable quote checkbox.
	 */
	public function enable_quote_callback() {
		$options = get_option( 'adas_quote_options' );
		$checked = isset( $options['enable_quote'] ) ? 'checked' : '';
		printf(
			'<input type="checkbox" id="enable_quote" name="adas_quote_options[enable_quote]" value="1" %s />',
			esc_attr( $checked )
		);
	}

	/**
	 * Callback function for displaying the settings section description.
	 */
	public function settings_section_callback() {
		echo '';
	}

	/**
	 * Callback function for displaying the SMTP settings section description.
	 */
	public function smtp_settings_section_callback() {
		echo '<p> </p>';
	}

	/**
	 * Callback function for displaying the selected categories dropdown.
	 */
	public function selected_categories_callback() {
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
	public function admin_email_callback() {
		$option = get_option( 'adas_quote_admin_email' );
		if ( empty( $option ) ) {
			$option = get_option( 'admin_email' );
		}
		echo '<input style="width: 300px;" type="email" name="adas_quote_admin_email" value="' . esc_attr( $option ) . '">';
	}

		/**
		 * Callback function for displaying the selected products in a multi-select dropdown.
		 */
	public function selected_products_callback() {
		$selected_products_option = get_option( 'adas_quote_selected_products', array() );

		// Ensure $selected_products is always an array.
		$selected_products = is_array( $selected_products_option ) ? $selected_products_option : array();

		// Only apply array_map if $selected_products is not empty.
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
			echo esc_html__( 'No products found.', 'adas_quote_request' );
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
				esc_attr( $product_id ),
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
					_n( '1 product', '%s products', count( $total_products ), 'adas_quote_request' ),
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

		$pagination_links = paginate_links( $pagination_args );
		if ( $pagination_links !== null ) {
			echo '<span class="pagination-links">' . wp_kses_post( $pagination_links ) . '</span>';
		}   }


	/**
	 * Callback function for displaying the hide add to cart checkbox.
	 */
	public function hide_add_to_cart_callback() {
		$option = get_option( 'adas_quote_hide_add_to_cart' );
		echo '<input type="checkbox" name="adas_quote_hide_add_to_cart" value="1" ' . checked( 1, $option, false ) . '>';
	}

	/**
	 * Callback function for displaying the hide price checkbox.
	 */
	public function adas_quote_show_price_callback() {
		$option = get_option( 'adas_quote_hide_price' );
		echo '<input type="checkbox" name="adas_quote_hide_price" value="1" ' . checked( 1, $option, false ) . '>';
	}


	/**
	 * Callback function for displaying the custom email message textarea.
	 */
	public function adas_quote_custom_email_message_callback() {
		$option = get_option( 'adas_quote_custom_email_message' );
		echo '<textarea name="adas_quote_custom_email_message" rows="5" cols="50">' . esc_textarea( $option ) . '</textarea>';
	}
	/**
	 * Callback function for displaying the custom email subject.
	 */
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