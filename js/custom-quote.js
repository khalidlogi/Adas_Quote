jQuery(document).ready(function ($) {
    const wooCommerceQuantityInput = $('.single-product form.cart .quantity input[type="number"]');
    const customQuantityInput = $('.product_quantity');

    function updateCustomQuantityInput() {
        const selectedQuantity = wooCommerceQuantityInput.val();
        customQuantityInput.val(selectedQuantity);
    }

    wooCommerceQuantityInput.on('change', updateCustomQuantityInput);

    $('form.variations_form').on('found_variation', function (event, variation) {
        let selectedVariations = {};
        $('.variations_form .variations select').each(function () {
            let attributeName = $(this).attr('name').replace('attribute_pa_', '');
            let attributeValue = $(this).val();
            selectedVariations[attributeName] = attributeValue;
        });

        let variationsJSON = JSON.stringify(selectedVariations);
        $('#variationsAttr').val(variationsJSON);
        $('#quote_variation_id').val(variation.variation_id);
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhoneNumber(phone) {
        const phoneRegex = /^[0-9\-\+\(\) ]{7,15}$/;
        return phoneRegex.test(phone);
    }

    function validateForm(formData) {
        let errors = [];

        if (!formData.product_quantity || isNaN(formData.product_quantity) || formData.product_quantity <= 0) {
            errors.push({ field: 'product_quantity', message: "Please enter a valid product quantity." });
        }
        if (!formData.product_name) {
            errors.push({ field: 'product_name', message: "Product name is required." });
        }
        if (!formData.useremail || !isValidEmail(formData.useremail)) {
            errors.push({ field: 'adas_user_email', message: "Please enter a valid email address." });
        }
        if (!formData.phone_number || !isValidPhoneNumber(formData.phone_number)) {
            errors.push({ field: 'phone_number', message: "Please enter a valid phone number." });
        }
        if (!formData.message_quote) {
            errors.push({ field: 'message_quote', message: "Please enter a message." });
        }

        return errors;
    }

    function highlightErrorFields(errors) {
        errors.forEach(error => {
            const field = $(`#custom-quote-form [name="${error.field}"]`);
            field.addClass('error-highlight');
            field.after(`<span class="error-message">${error.message}</span>`);
        });
    }

    function clearErrorHighlights() {
        $('#custom-quote-form .error-highlight').removeClass('error-highlight');
        $('#custom-quote-form .error-message').remove();
    }

    $('#custom-quote-form').on('submit', function (event) {
        event.preventDefault();

        clearErrorHighlights();

        let formData = {
            action: 'handle_quote_request',
            product_quantity: $('#custom-quote-form input[name="product_quantity"]').val(),
            product_type: $('#custom-quote-form input[name="product_type"]').val(),
            product_image: $('#custom-quote-form input[name="product_image"]').val(),
            product_id: $('#custom-quote-form input[name="product_id"]').val(),
            product_name: $('#custom-quote-form input[name="product_name"]').val(),
            message_quote: $('#custom-quote-form textarea[name="message_quote"]').val(),
            useremail: $('#custom-quote-form input[name="adas_user_email"]').val(),
            phone_number: $('#custom-quote-form input[name="phone_number"]').val(),
            variation_id: $('#quote_variation_id').val(),
            variations_attr: $('#variationsAttr').val(),
            custom_quote_request_nonce: $('#custom-quote-form input[name="adas_quote_nonce"]').val()
        };

        const errors = validateForm(formData);
        if (errors.length > 0) {
            highlightErrorFields(errors);
            return;
        }

        $.ajax({
            url: custom_quote_params.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                console.log('AJAX Response:', response);

                if (response.success) {
                    console.log(response.data);
                    $('#quoteSuccessModal').modal('show');
                } else {
                    alert('Failed to send quote request: ' + response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('AJAX Error:', textStatus, errorThrown);
                alert('An error occurred while sending the quote request.');
            }
        });
    });

    $('#custom-quote-form').on('focus', '.error-highlight', function () {
        $(this).removeClass('error-highlight');
        $(this).next('.error-message').remove();
    });
});