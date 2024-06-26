jQuery(document).ready(function ($) {
    const wooCommerceQuantityInput = $('.single-product form.cart .quantity input[type="number"]');
    const customQuantityInput = $('.product_quantity');
    const customQuoteForm = $('#custom-quote-form');
    const quoteSuccessModal = $('#quoteSuccessModal');

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
        event.preventDefault();
        clearErrorHighlights();

        const formData = {
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
            custom_quote_request_nonce: customQuoteForm.find('input[name="adas_quote_nonce"]').val()
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
                    quoteSuccessModal.modal('show');
                } else {
                    alert('Failed to send quote request: ' + response.data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                alert('An error occurred while sending the quote request.');
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