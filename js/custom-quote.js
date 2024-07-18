jQuery(document).ready(function ($) {
    const wooCommerceQuantityInput = $('.single-product form.cart .quantity input[type="number"]');
    const customQuantityInput = $('.product_quantity');
    const customQuoteForm = $('#custom-quote-form');
    const quoteSuccessModal = $('#quoteSuccessModal');
    $('#quoteSuccessModal').modal({show: false});
    
 // Handle the close button click
 $('.modal .close, .modal .btn-close, [data-dismiss="modal"]').on('click', function() {
    $(this).closest('.modal').modal('hide');
});

    function updateCustomQuantityInput() {
        customQuantityInput.val(wooCommerceQuantityInput.val());
    }

    function handleVariationChange(event, variation) {
        const selectedVariations = {};
        $('.variations_form .variations select').each(function () {
            const attributeName = $(this).attr('name').replace('attribute_pa_', '');
            selectedVariations[attributeName] = $(this).val();
        });

        $('#variationsAttr').val(JSON.stringify(selectedVariations));
        $('#quote_variation_id').val(variation.variation_id);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhoneNumber(phone) {
        return /^[0-9\-\+\(\) ]{7,15}$/.test(phone);
    }

    function validateForm(formData) {
        const errors = [];
        const validations = [
            { field: 'product_quantity', condition: !formData.product_quantity || isNaN(formData.product_quantity) || formData.product_quantity <= 0, message: "Please enter a valid product quantity." },
            { field: 'product_name', condition: !formData.product_name, message: "Product name is required." },
            { field: 'adas_user_email', condition: !formData.useremail || !isValidEmail(formData.useremail), message: "Please enter a valid email address." },
            { field: 'phone_number', condition: !formData.phone_number || !isValidPhoneNumber(formData.phone_number), message: "Please enter a valid phone number." }
        ];

        validations.forEach(({ field, condition, message }) => {
            if (condition) errors.push({ field, message });
        });

        return errors;
    }

    function highlightErrorFields(errors) {
        errors.forEach(({ field, message }) => {
            const fieldElement = customQuoteForm.find(`[name="${field}"]`);
            fieldElement.addClass('error-highlight').after(`<span class="error-message">${message}</span>`);
        });
    }

    function clearErrorHighlights() {
        customQuoteForm.find('.error-highlight').removeClass('error-highlight');
        customQuoteForm.find('.error-message').remove();
    }

    function handleFormSubmit(event) {
        event.preventDefault(); // Always prevent default form submission
        console.log('Form submission initiated');
    
        clearErrorHighlights();
    
        var formData = {
            action: 'handle_quote_request',
            product_quantity: customQuoteForm.find('input[name="product_quantity"]').val(),
            product_type: customQuoteForm.find('input[name="product_type"]').val(),
            product_image: customQuoteForm.find('input[name="product_image"]').val(),
            product_id: customQuoteForm.find('input[name="product_id"]').val(),
            product_name: customQuoteForm.find('input[name="product_name"]').val(),
            message_quote: customQuoteForm.find('textarea[name="message_quote"]').val(),
            useremail: customQuoteForm.find('input[name="adas_user_email"]').val(),
            phone_number: customQuoteForm.find('input[name="phone_number"]').val(),
            variation_id: $('#quote_variation_id').val(),
            variations_attr: $('#variationsAttr').val(),
            custom_quote_request_nonce: customQuoteForm.find('input[name="adas_quote_nonce"]').val(),
            'g-recaptcha-response': recaptchaResponse
        };
    
       // Check if reCAPTCHA is enabled
    if (typeof grecaptcha !== 'undefined' && $('.g-recaptcha').length > 0) {
        var recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
            alert('Please complete the CAPTCHA');
            return;
        }
        formData['g-recaptcha-response'] = recaptchaResponse;
    }

        console.log('Form data:', formData);
    
        const errors = validateForm(formData);
        if (errors.length > 0) {
            console.log('Form validation errors:', errors);
            highlightErrorFields(errors);
            return;
        }
    
        console.log('AJAX URL:', custom_quote_params.ajax_url);
    
        // Show loading indicator
        $('#loadingIndicator').show();
    
        $.ajax({
            url: custom_quote_params.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    console.log('Quote request successful:', response.data);
                    quoteSuccessModal.modal('show');
                    customQuoteForm[0].reset();  // Reset the form.
                } else {
                    console.error('Quote request failed:', response.data);
                    alert('Failed to send quote request: ' + response.data);
                }
                $('#loadingIndicator').hide();

                if (typeof grecaptcha !== 'undefined' && $('.g-recaptcha').length > 0) {
                    grecaptcha.reset();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    textStatus: textStatus,
                    errorThrown: errorThrown
                });
                if (typeof grecaptcha !== 'undefined' && $('.g-recaptcha').length > 0) {
                    grecaptcha.reset();
                }
                alert('An error occurred while sending the quote request. Please check the console for more details.');
            },
            complete: function() {
                // Hide loading indicator
                $('#loadingIndicator').hide();
                if (typeof grecaptcha !== 'undefined' && $('.g-recaptcha').length > 0) {
                    grecaptcha.reset();
                }
            }
        });
    }

    wooCommerceQuantityInput.on('change', updateCustomQuantityInput);
    $('form.variations_form').on('found_variation', handleVariationChange);
    customQuoteForm.on('submit', handleFormSubmit);
    customQuoteForm.on('focus', '.error-highlight', function () {
        $(this).removeClass('error-highlight').next('.error-message').remove();
    });
});