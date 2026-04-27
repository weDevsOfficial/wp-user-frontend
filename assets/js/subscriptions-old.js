(function($) {

	var subscription = {

		init: function() {

			$('input#wpuf-recuring-pay').on('click', this.showSubscriptionRecurring );

			$('input#wpuf-trial-status').on('click', this.showSubscriptionPack );

            $('.wpuf-order-summary').on( 'click', 'a.wpuf-apply-coupon', this.couponApply );

            $('.wpuf-order-summary').on( 'click', 'a.wpuf-coupon-show', this.couponShow );

            $('.wpuf-order-summary').on( 'click', 'a.wpuf-coupon-cancel', this.couponCancel );

            $('.wpuf-order-summary').on( 'click', '.wpuf-coupon-remove', this.couponRemove );

            $( '#wpuf-payment-gateway' ).on( 'submit', this.checkoutSubmit );

            $('.wpuf-assing-pack-btn').on( 'click', this.showPackDropdown );

            $('.wpuf-delete-pack-btn').on( 'click', this.deletePack );

            $('.wpuf-disabled-link').click( this.packAlert );

            //on change enable expiration check status
            this.changeExpirationFieldVisibility(':checkbox#wpuf-enable_post_expiration');

            $('.wpuf-metabox-post_expiration').on('change',':checkbox#wpuf-enable_post_expiration',this.changeExpirationFieldVisibility);
            //on change expiration type drop down
            //this.setTimeExpiration('select#wpuf-expiration_time_type');
            $('.wpuf-metabox-post_expiration').on('change','select#wpuf-expiration_time_type',this.setTimeExpiration);

            // warn the user before updating the package
            $( 'body.post-type-wpuf_subscription #post' ).submit( function( event ) {
                if ( document.activeElement.value === 'Update' ) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure to update the subscription?',
                        text: 'The changes you made will be applied only to the new subscriptions and pending recurring payments.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Update'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $( this ).unbind('submit').submit()
                        }
                    });
                }
            } );

		},

        packAlert : function () {
            alert( wpuf_subscription.pack_notice );
        },

        showPackDropdown: function(e) {
            e.preventDefault();
            var self = $(this),
                wrap = self.parents('.wpuf-user-subscription'),
                sub_dropdown = wrap.find('.wpuf-pack-dropdown'),
                sub_details = wrap.find('.wpuf-user-sub-info'),
                cancel_btn = wrap.find('.wpuf-cancel-pack'),
                add_btn = wrap.find('.wpuf-add-pack');

            if ( sub_dropdown.attr( 'disabled' ) === 'disabled' ) {
                sub_dropdown.show().removeAttr('disabled');
                sub_details.hide().attr('disabled', true );
                cancel_btn.show();
                add_btn.hide();
            } else {
                sub_details.show().removeAttr('disabled');
                sub_dropdown.hide().attr('disabled', true );
                cancel_btn.hide();
                add_btn.show();
            }

        },

        deletePack: function(e){
            var self = $(this),
                wrap = self.parents('.wpuf-user-subscription'),
                sub_dropdown = wrap.find('.wpuf-pack-dropdown'),
                selected_sub = wrap.find( '#wpuf_sub_pack' ),
                userid = $(e.target).attr('data-userid'),
                packid = $(e.target).attr('data-packid');

            wrap.find('.wpuf-delete-pack-btn').attr('disabled', true);
            wrap.css('opacity', 0.5);
            $.post(
                ajaxurl,
                {
                    'action' : 'wpuf_delete_user_package',
                    'userid' : userid,
                    'packid' : packid,
                    'wpuf_subscription_delete_nonce': wpuf_subs_vars.wpuf_subscription_delete_nonce
                },
                function(data){
                    if(data){
                        wrap.css( 'opacity', 1 );
                        $('.wpuf-user-sub-info').remove();
                        $(e.target).remove();
                        selected_sub.val(-1);
                        sub_dropdown.show();
                    }
                }
            );

        },

        couponCancel: function(e) {

            e.preventDefault();

            var self = $(this),

                data = {

                    action: 'wpuf_coupon_cancel',

                    _wpnonce: wpuf_frontend.nonce,

                    pack_id: self.data('pack_id'),

                    type: $( '#wpuf_type' ).text() || 'pack'

                },

                coupon_field = self.closest('.wpuf-order-summary').find('input.wpuf-coupon-field');



            coupon_field.addClass('wpuf-coupon-field-spinner');

            $.post( wpuf_frontend.ajaxurl, data, function( res ) {

                coupon_field.removeClass('wpuf-coupon-field-spinner');

                if ( res.success ) {
                    var orderSummary = self.closest('.wpuf-order-summary');
                    orderSummary.find('.wpuf-pack-inner').html( res.data.append_data );
                    orderSummary.find('.wpuf-coupon-id-field').val('');
                    orderSummary.find('.wpuf-coupon-applied').remove();
                    orderSummary.find('.wpuf-coupon-show').show();
                }

            });
        },

        couponRemove: function(e) {

            e.preventDefault();

            var self = $(this),
                orderSummary = self.closest('.wpuf-order-summary'),
                data = {
                    action: 'wpuf_coupon_cancel',
                    _wpnonce: wpuf_frontend.nonce,
                    pack_id: self.data('pack_id'),
                    type: $( '#wpuf_type' ).text() || 'pack'
                };

            $.post( wpuf_frontend.ajaxurl, data, function( res ) {
                if ( res.success ) {
                    orderSummary.find('.wpuf-pack-inner').html( res.data.append_data );
                    orderSummary.find('.wpuf-coupon-id-field').val('');
                    orderSummary.find('.wpuf-coupon-applied').remove();
                    orderSummary.find('.wpuf-coupon-show').show();
                }
            });

        },

        couponShow: function(e) {

            e.preventDefault();

            var self = $(this);

            self.hide();

            self.siblings('.wpuf-coupon-wrap').show();

        },

        couponApply: function(e) {

            e.preventDefault();

            var self = $(this),

                orderSummary = self.closest('.wpuf-order-summary'),

                coupon_field = orderSummary.find('input.wpuf-coupon-field'),

                coupon = coupon_field.val();


            if ( coupon === '' ) {

                orderSummary.find('.wpuf-coupon-error').html( wpuf_frontend.coupon_error );
                return;

            }

            var data = {

                    action: 'wpuf_coupon_apply',

                    _wpnonce: wpuf_frontend.nonce,

                    coupon: coupon,

                    pack_id: self.data('pack_id'),

                    type: $( '#wpuf_type' ).text() || 'pack'

                };


            if ( self.attr('disabled') === 'disabled' ) {

                //return;

            }

            self.attr( 'disabled', true );

            coupon_field.addClass('wpuf-coupon-field-spinner');

            $.post( wpuf_frontend.ajaxurl, data, function( res ) {
                coupon_field.removeClass('wpuf-coupon-field-spinner');

                if ( res.success ) {
                    orderSummary.find('.wpuf-pack-inner').html( res.data.append_data );
                    orderSummary.find('.wpuf-coupon-id-field').val( res.data.coupon_id );
                    orderSummary.find('.wpuf-coupon-error').html('');

                    if ( res.data.amount <= 0 ) {
                        $('.wpuf-nullamount-hide').hide();
                    }

                    // Hide the coupon input wrap and show the applied bar
                    var coponWrap = orderSummary.find('.wpuf-coupon-wrap');
                    coponWrap.hide();
                    orderSummary.find('.wpuf-coupon-applied').remove();
                    orderSummary.append(
                        '<div class="wpuf-coupon-applied">' +
                            '<span class="wpuf-coupon-applied-label">Coupon code applied</span>' +
                            '<button type="button" class="wpuf-coupon-remove" data-pack_id="' + self.data('pack_id') + '">&times;</button>' +
                        '</div>'
                    );
                } else {
                    self.attr( 'disabled', false );
                    orderSummary.find('.wpuf-coupon-error').html( res.data.message );
                }

            });

        },

		showSubscriptionRecurring: function() {

            var self = $(this),

                wrap = self.parents('table.form-table'),
                pack_child = wrap.find('.wpuf-recurring-child'),
                trial_checkbox = wrap.find('input#wpuf-trial-status'),
                trial_child = wrap.find('.wpuf-trial-child'),
                expire_field = wrap.find('.wpuf-subcription-expire');

            if ( self.is(':checked') ) {

            	if ( trial_checkbox.is(':checked') ) {

            		trial_child.show();

            	}

                pack_child.show();

                expire_field.hide();

            } else {

            	trial_child.hide();

                pack_child.hide();

                expire_field.show();

            }

        },

        showSubscriptionPack: function() {

            var self = $(this),

                pack_status = self.closest('table.form-table').find('.wpuf-trial-child');

            if ( self.is(':checked') ) {

                pack_status.show();

            } else {

                pack_status.hide();

            }

        },

        setTimeExpiration: function(e){
            var timeArray = {
                'day' : 30,
                'month' : 12,
                'year': 100
            };
            $('#wpuf-expiration_time_value').html('');
            var timeVal = e.target?$(e.target).val():$(e).val();
            for(var time = 1; time <= timeArray[timeVal]; time++){
                $('#wpuf-expiration_time_value').append('<option>'+ time +'</option>');
            }
        },

        changeExpirationFieldVisibility : function(e){

            var checkbox_obj = e.target? $(e.target) : $(e);

            if ( checkbox_obj.is(':checked') ) {
                $('.wpuf_subscription_expiration_field').show();
            } else {
                $('.wpuf_subscription_expiration_field').hide();
            }
        },

        checkoutSubmit: function( e ) {
            var form            = $( '#wpuf-payment-gateway' );
            var selectedGateway = form.find( "input[name='wpuf_payment_method']:checked" ).val();
            var btn             = form.find( '.wpuf-checkout-btn' );
            var originalLabel   = btn.data( 'original-label' ) || btn.text().trim();

            // Show loading immediately so the user gets instant feedback.
            btn.data( 'original-label', originalLabel )
               .prop( 'disabled', true )
               .addClass( 'wpuf-checkout-btn--loading' )
               .html(
                   '<span class="wpuf-btn-spinner"></span>' +
                   '<span class="wpuf-btn-text">Processing&hellip;</span>'
               );

            // Helper to roll back the button if validation fails.
            function resetBtn() {
                btn.prop( 'disabled', false )
                   .removeClass( 'wpuf-checkout-btn--loading' )
                   .text( originalLabel );
            }

            // Stripe intercepts and processes everything itself (no real form POST).
            // Keep the button disabled so the user can't double-submit while Stripe's
            // modal/redirect runs; Pro Stripe handler is responsible for its own error UI.
            if ( selectedGateway === 'stripe' ) {
                return;
            }

            // validateForm() returns false on error, serialized string on success.
            if ( typeof WP_User_Frontend !== 'undefined' ) {
                if ( WP_User_Frontend.validateForm( form ) === false ) {
                    resetBtn();
                    return;
                }
            }

            // Billing address validation when the address form is present.
            if ( $( '#wpuf-ajax-address-form' ).length && typeof wpuf_validate_address === 'function' ) {
                if ( ! wpuf_validate_address() ) {
                    resetBtn();
                    return;
                }
            }
        },

	};

    if ( typeof datepicker === 'function') {
        $('.wpuf-date-picker').datepicker({ dateFormat: "yy-mm-dd" });
    }

	subscription.init();

})(jQuery);
