jQuery(document).ready(function($) {
    // Function to display an alert when the variation changes
    function watq_quote_submission_() {
       // alert('Variation has been changed.');
    }

    // Event listener for variation change
    $('form.variations_form').on('found_variation', function(event, variation) {
        watq_quote_submission_();
           // Set the variation ID in the hidden input field
           $('#quote_variation_id').val(variation.variation_id);
            // You can access the variation details here if needed
            console.log(variation);
    });
});