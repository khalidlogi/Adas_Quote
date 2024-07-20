<?php
/**
 * This file contains the modal for displaying a success message when a quote request is submitted.
 *
 * @package AdasQuoteForWC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="modal fade" id="quoteSuccessModal" tabindex="-1" role="dialog" aria-labelledby="quoteSuccessModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="quoteSuccessModalLabel">Quote Request Submitted!</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="alert alert-success" role="alert">
					<i class="fas fa-check-circle"></i>
					<?php echo esc_html( get_option( 'adas_quote_confirmation_message', 'Your quote request has been sent successfully! A confirmation email has been sent to your email.' ) ); ?>
				</div>
			</div>
			<div class="modal-footer">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=adas_list' ) ); ?>"
					class="btn btn-primary">Manage
					quotes
				</a>
			</div>
		</div>
	</div>
</div>

<?php