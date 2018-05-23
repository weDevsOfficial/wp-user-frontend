jQuery(function($){

    $('#wpuf-payment-gateway').submit(function (e) {
        wpuf_validate_address(e);
    });

    $('#wpuf-ajax-address-form').submit(function (e) {
        e.preventDefault();
        $.post(ajax_object.ajaxurl, {
            action: 'wpuf_address_ajax_action',
            data: $('#wpuf-ajax-address-form').serialize(),
        });
    });

    $( document.body ).on('change', 'select#wpuf_biiling_country', function() {
        var $this = $(this), $tr = $this.closest('tr');
        var data = {
            action: 'wpuf_get_shop_states',
            country: $(this).val(),
            field_name: $("#wpuf_biiling_state").attr("name"),
        };
        $.post(ajax_object.ajaxurl, data, function (response) {
            if( 'nostates' == response ) {
                var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
                $this.parent().next().find('select').replaceWith( text_field );
            } else {
                $this.parent().next().find('input,select').show();
                $this.parent().next().find('input,select').replaceWith(response);
                $this.parent().next().find('input,select').prepend("<option value='' selected='selected'>- select -</option>");
            }
        });
    });

    $(document.body).on('change', 'select#wpuf_biiling_state', function () {
        wpuf_calculate_tax();
    });

    var ajax_tax_count = 0;

    function wpuf_validate_address(e) {
        var country = $("td.bill_required").find('.wpuf_biiling_country, .input');
        var state   = $("td.bill_required").find('.wpuf_biiling_state, .input');
        var add_1   = $("#wpuf_biiling_add_line_1");
        var add_2   = $("#wpuf_biiling_add_line_2");
        var city    = $("#wpuf_biiling_city");
        var zip     = $("#wpuf_biiling_zip_code");

        if ( ( country.val() === '' || state.val() === '' ) || ( add_1.hasClass('bill_required') && add_1.val() === "" ) ||
            ( add_1.hasClass('bill_required') && add_1.val() === "" ) || ( add_2.hasClass('bill_required') && add_2.val() === "" ) ||
            ( city.hasClass('bill_required') && city.val() === "" ) || ( zip.hasClass('bill_required') && zip.val() === "" ) ) {
            alert( ajax_object.fill_notice );
            e.preventDefault();
        }
    }

    function wpuf_calculate_tax() {

        var $wpuf_cc_address = jQuery('#wpuf-address-country-state');
        var $payment_form = jQuery('#wpuf-payment-gateway');

        var postData = {
            action: 'wpuf_update_billing_address',
            billing_country: $wpuf_cc_address.find('#wpuf_biiling_country').val(),
            billing_state: $wpuf_cc_address.find('#wpuf_biiling_state').val(),
            billing_add_line1: $wpuf_cc_address.find('#wpuf_biiling_add_line_1').val(),
            billing_add_line2: $wpuf_cc_address.find('#wpuf_biiling_add_line_2').val(),
            billing_city: $wpuf_cc_address.find('#wpuf_biiling_city').val(),
            billing_zip: $wpuf_cc_address.find('#wpuf_biiling_zip_code').val(),
            type: $payment_form.find('#wpuf_type').html(),
            id: $payment_form.find('#wpuf_id').html(),
        };

        var current_ajax_count = ++ajax_tax_count;
        jQuery.ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: ajax_object.ajaxurl,
            success: function (tax_response) {
                // Only update tax info if this response is the most recent ajax call. This avoids bug with form autocomplete firing multiple ajax calls at the same time
                if ((current_ajax_count === ajax_tax_count) && tax_response) {
                    jQuery('#wpuf_pay_page_tax').html(tax_response.tax);
                    jQuery('#wpuf_pay_page_total').html(tax_response.cost);
                    var tax_data = new Object();
                    tax_data.postdata = postData;
                    tax_data.response = tax_response;
                }
            }
        }).fail(function (data) {
            if ( window.console && window.console.log ) {
                console.log( data );
            }
        });
    }
});
