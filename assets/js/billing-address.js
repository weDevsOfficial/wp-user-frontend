jQuery(function($){

    // Billing_Address::script_data(): `wpufBillingAddress` (`ajax_object` before WPUF_SINCE).
    var billingData = window.wpufBillingAddress || window.ajax_object || {};

    window.wpuf_validate_address = function(e) {
        var country = $("td.bill_required").find('.wpuf_biiling_country, .input');
        var state   = $("td.bill_required").find('.wpuf_biiling_state, .input');
        var add_1   = $("#wpuf_biiling_add_line_1");
        var add_2   = $("#wpuf_biiling_add_line_2");
        var city    = $("#wpuf_biiling_city");
        var zip     = $("#wpuf_biiling_zip_code");
        var isValid = true;

        if ( ( country.val() === '' || state.val() === '' ) ||
            ( add_1.hasClass('bill_required') && add_1.val() === "" ) ||
            ( add_2.hasClass('bill_required') && add_2.val() === "" ) ||
            ( city.hasClass('bill_required') && city.val() === "" ) ||
            ( zip.hasClass('bill_required') && zip.val() === "" ) )
        {
            isValid = false;
        }
        return isValid;
    };

    function wpuf_payment_gateway_submit(e) {
        // Stripe intercepts and processes the payment itself via AJAX — skip this handler entirely.
        if ( $( this ).find( "input[name='wpuf_payment_method']:checked" ).val() === 'stripe' ) {
            return;
        }

        let form_data = WP_User_Frontend.validateForm($(this));

        if (!form_data) {
            e.preventDefault();
        }

        var billing_address_form = $( '#wpuf-ajax-address-form' );

        if ( billing_address_form.length )  {
            var inputs = [
                { id: '#wpuf_biiling_country', name: 'billing_country' },
                { id: '#wpuf_biiling_state', name: 'billing_state' },
                { id: '#wpuf_biiling_add_line_1', name: 'billing_add_line1' },
                { id: '#wpuf_biiling_add_line_2', name: 'billing_add_line2' },
                { id: '#wpuf_biiling_city', name: 'billing_city' },
                { id: '#wpuf_biiling_zip_code', name: 'billing_zip' },
            ];

            inputs.map( function ( input ) {
                $( '<input type="hidden" name="billing_address[' + input.name + ']" />' )
                    .appendTo( '#wpuf-payment-gateway' )
                    .val( $( input.id ).val() );
            } );
        }
    }

    // Bound on load and again when the React account inserts a section
    // (wpuf:frontend:html), so the form it brings in is handled too.
    function wpuf_bind_billing_forms() {
        $( '#wpuf-payment-gateway' ).off( 'submit.wpufBilling' ).on( 'submit.wpufBilling', wpuf_payment_gateway_submit );

        $( '#wpuf-ajax-address-form' ).off( 'submit.wpufBilling' ).on( 'submit.wpufBilling', function (e) {
            e.preventDefault();
            $( '#wpuf-payment-gateway' ).trigger( 'submit' );
        });
    }

    wpuf_bind_billing_forms();
    $( document ).on( 'wpuf:frontend:html', wpuf_bind_billing_forms );

    $( document.body ).on('change', 'select#wpuf_biiling_country', function() {
        var $this = $(this), $tr = $this.closest('tr');
        var data = {
            country: $(this).val(),
            field_name: $("#wpuf_biiling_state").attr("name")
        };
        window.wpufRest.request( billingData.rest, 'account/billing-address/states', { data: data } ).then( function ( result ) {
            var response = result && result.states ? result.html : 'nostates';
            var $old = $this.parent().next().find('input,select').first();
            // The new state field keeps the old one's id, classes and data, so it
            // looks the same and the next country change still finds it.
            var keep = function ( $field ) {
                if ( ! $old.length ) {
                    return $field;
                }
                $.each( [ 'id', 'class', 'data-required', 'data-type' ], function ( i, attr ) {
                    if ( $old.attr( attr ) !== undefined ) {
                        $field.attr( attr, $old.attr( attr ) );
                    }
                } );
                $field.removeClass( 'wpuf-select' );
                if ( $field.is( 'select' ) && $old.is( 'select' ) && $old.hasClass( 'wpuf-select' ) ) {
                    $field.addClass( 'wpuf-select' );
                }
                return $field;
            };

            if( 'nostates' == response ) {
                var $text = keep( $( '<input type="text" value=""/>' ).attr( 'name', data.field_name ) );
                $old.replaceWith( $text );
            } else {
                var $select = keep( $( response ).filter( 'select' ).add( $( response ).find( 'select' ) ).first() );
                $select.show();
                $old.replaceWith( $select );
                $select.prepend("<option value='' selected='selected'>- select -</option>");
            }
        }).catch( function ( error ) {
            if ( window.console && window.console.log ) {
                console.log( error );
            }
        });
    });

    $(document).ready( function () {
        wpuf_calculate_tax();
    });

    // The checkout renders the fields in a div (a form inside the payment form is dropped).
    $(document.body).on('change', '#wpuf-ajax-address-form', function () {
        wpuf_calculate_tax();
    });

    var ajax_tax_count = 0;

    function wpuf_calculate_tax() {

        var $wpuf_cc_address = jQuery('#wpuf-address-country-state');
        var $payment_form = jQuery('#wpuf-payment-gateway');
        var $address_form = jQuery('#wpuf-ajax-address-form');

        if ( ! $address_form.length ) {
            return;
        }

        var postData = {
            billing_country: $wpuf_cc_address.find('#wpuf_biiling_country').val(),
            billing_state: $wpuf_cc_address.find('#wpuf_biiling_state').val(),
            billing_add_line1: $wpuf_cc_address.find('#wpuf_biiling_add_line_1').val(),
            billing_add_line2: $wpuf_cc_address.find('#wpuf_biiling_add_line_2').val(),
            billing_city: $wpuf_cc_address.find('#wpuf_biiling_city').val(),
            billing_zip: $wpuf_cc_address.find('#wpuf_biiling_zip_code').val(),
            type: $payment_form.find('#wpuf_type').html(),
            id: $payment_form.find('#wpuf_id').html(),
        };

        // The REST body is JSON: drop the fields the page does not have.
        $.each( postData, function ( key, value ) {
            if ( undefined === value || null === value ) {
                delete postData[ key ];
            }
        } );

        var current_ajax_count = ++ajax_tax_count;

        window.wpufRest.request( billingData.rest, 'account/billing-address', { method: 'PUT', data: postData } )
            .then( function ( tax_response ) {
                // Only update tax info if this response is the most recent call. This avoids bug with form autocomplete firing multiple calls at the same time
                if ( ( current_ajax_count === ajax_tax_count ) && tax_response && undefined !== tax_response.tax ) {
                    jQuery('#wpuf_pay_page_tax').html(tax_response.tax);
                    jQuery('#wpuf_pay_page_total').html(tax_response.cost);
                }
            } )
            .catch( function ( error ) {
                if ( window.console && window.console.log ) {
                    console.log( error );
                }
            } );
    }
});
