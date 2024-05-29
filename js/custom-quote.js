jQuery(document).ready(function ($) {

    // Select the WooCommerce quantity input
    const wooCommerceQuantityInput = $('.single-product').find('form.cart').find('.quantity').find('input[type="number"]');

    // Select the custom input field
    const customQuantityInput = $('.product_quantity');
  
    // Function to update the custom input field value
    function updateCustomQuantityInput() {
      const selectedQuantity = wooCommerceQuantityInput.val();
      customQuantityInput.val(selectedQuantity);
    }
  
    // Attach the update function to the 'change' event of the quantity input
    wooCommerceQuantityInput.on('change', updateCustomQuantityInput);

    // Event listener for variation change
    $('form.variations_form').on('found_variation', function (event, variation) {
        let selectedVariations = {};
        $('.variations_form .variations select').each(function () {
            let attributeName = $(this).attr('name').replace('attribute_pa_', '');

            let attributeValue = $(this).val();
            selectedVariations[attributeName] = attributeValue;
        });

        // Convert variations data to JSON string
        let variationsJSON = JSON.stringify(selectedVariations);

        // Set the value of the hidden input field
        $('#variationsAttr').val(variationsJSON);

        // Set the variation ID in the hidden input field
        $('#quote_variation_id').val(variation.variation_id);
    });

    // Handle form submission via AJAX
    $('#custom-quote-form').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission
//alert($('#custom-quote-form textarea[name="message_quote"]').val());
        //var $product_quantity = $('.single-product').find('form.cart').find('.quantity').find('input[type="number"]').val();
        //$('.single-product').find('form.custom-quote-form').find('input[name="product_quantity"]').val($product_quantity).change();


        let formData = {
            action: 'handle_quote_request',
            product_quantity : $('#custom-quote-form input[name="product_quantity"]').val(),
            product_type : $('#custom-quote-form input[name="product_type"]').val(),
            product_image : $('#custom-quote-form input[name="product_image"]').val(),
            product_id: $('#custom-quote-form input[name="product_id"]').val(),
            product_name: $('#custom-quote-form input[name="product_name"]').val(),
            message_quote: $('#custom-quote-form textarea[name="message_quote"]').val(),
            variation_id: $('#quote_variation_id').val(),
            variations_attr: $('#variationsAttr').val(),
            useremail: $('#custom-quote-form input[name="adas_user_email"]').val(),
            //current_url: window.location.href // Pass the current URL

            custom_quote_request_nonce: $('#custom-quote-form input[name="adas_quote_nonce"]').val() // Include nonce
        };

       // console.log('Form Data:', formData); // Debugging: Inspect form data

        $.ajax({
            url: custom_quote_params.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                console.log('AJAX Response:', response); // Debugging: Inspect response

                if (response.success) {
                   //alert('Quote request sent successfully!');
                    console.log(response.data);

                      // Trigger the modal with JavaScript
                      $('#quoteSuccessModal').modal('show');

                } else {
                    alert('Failed to send quote request: ' + response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('AJAX Error:', textStatus, errorThrown); // Debugging: Inspect error
                alert('An error occurred while sending the quote request.');
            }
        });
    });
});