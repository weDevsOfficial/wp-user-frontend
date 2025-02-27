;(function($, window) {

    $.fn.listautowidth = function() {
        return this.each(function() {
            var w = $(this).width();
            var liw = w / $(this).children('li').length;
            $(this).children('li').each(function(){
                var s = $(this).outerWidth(true)-$(this).width();
                $(this).width(liw-s);
            });
        });
    };

    window.WP_User_Frontend = {

        init: function() {

            //enable multistep
            this.enableMultistep(this);

            // clone and remove repeated field
            $('.wpuf-form').on('click', 'img.wpuf-clone-field', this.cloneField);
            $('.wpuf-form').on('click', 'img.wpuf-remove-field', this.removeField);
            $('.wpuf-form').on('click', 'a.wpuf-delete-avatar', this.deleteAvatar);
            $('.wpuf-form').on('click', 'a#wpuf-post-draft', this.draftPost);
            $('.wpuf-form').on('click', 'button#wpuf-account-update-profile', this.account_update_profile);

            $('.wpuf-form-add').on('submit', this.formSubmit);
            $('form#post').on('submit', this.adminPostSubmit);
            // $( '.wpuf-form').on('keyup', '#pass1', this.check_pass_strength );

            if ( window.matchMedia('(max-width: 600px)').matches ) {

                // Post toggle
                $('table.items-table tr td .post-edit-icon').click( function( e ) {
                    e.preventDefault();
                    $(this).parents('tr').find('.data-column').toggleClass('flex-column');
                    $(this).toggleClass('toggle-icon');
                });

                // Account page toggle mobile menu
                $('.wpuf-dashboard-navigation ul li:first-child').after().click( function(e) {
                    e.preventDefault();

                    $('.wpuf-dashboard-navigation ul li').not(':first').toggle();
                } );
            }

            // refresh pluploads on each step change (multistep form)
            $('.wpuf-form').on('step-change-fieldset', function(event, number, step) {
                if ( wpuf_plupload_items.length ) {
                    for (var i = wpuf_plupload_items.length - 1; i >= 0; i--) {
                        wpuf_plupload_items[i].refresh();
                    }
                }
                if ( wpuf_map_items.length ) {
                    for (var i = wpuf_map_items.length - 1; i >= 0; i--) {
                        google.maps.event.trigger(wpuf_map_items[i].map, 'resize');
                        wpuf_map_items[i].map.setCenter(wpuf_map_items[i].center);
                    }
                }
            });

            this.ajaxCategory();
            // image insert
            // this.insertImage();

            //comfirmation alert for canceling subscription
            $( ':submit[name="wpuf_user_subscription_cancel"]').click(function(e){
                e.preventDefault();

                Swal.fire({
                    text: wpuf_frontend.cancelSubMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d54e21',
                    confirmButtonText: wpuf_frontend.delete_it,
                    cancelButtonText: wpuf_frontend.cancel_it,
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    }
                }).then(function ( result ) {
                    if ( !result.isConfirmed ) {
                        return false;
                    }
                    $('#wpuf_cancel_subscription').submit();
                });
            });

            this.warningOnExit();
            this.handleReadOnly();
        },

        warningOnExit: function () {
            $('input[name="submit"], #wpuf-post-draft').on('click', function () {
                window.onbeforeunload = null;
                return;
            });

            $('.wpuf-form-add').on( 'change', function(){
                $('.wpuf-form-add input, .wpuf-form-add select, .wpuf-form-add textarea').each(function (index) {
                    if ( 'hidden' !== $(this).attr('type') || 'submit' !== $(this).attr('type') || -1 !== $(this).val() ){
                        window.onbeforeunload = function () {
                            return 'you have changes';//changing  return values doesnt have any impact due to security
                        }
                    }
                })
            });
        },

        handleReadOnly: function(){
            $('.wpuf-form .read-only').each( function () {
                $('input, select, textarea', this).each( function () {
                    $(this).attr( 'disabled', true );
                }) ;
            });
        },

        check_pass_strength : function() {
            var pass1 = $('#pass1').val(), strength;

            $('#pass-strength-result').show();

            $('#pass-strength-result').removeClass('short bad good strong');
            if ( ! pass1 ) {
                $('#pass-strength-result').html( '&nbsp;' );
                $('#pass-strength-result').hide();
                return;
            }

            if ( typeof wp.passwordStrength != 'undefined' ) {

                strength = wp.passwordStrength.meter( pass1, wp.passwordStrength.userInputDisallowedList(), pass1 );

                switch ( strength ) {
                    case 2:
                        $('#pass-strength-result').addClass('bad').html( pwsL10n.bad );
                        break;
                    case 3:
                        $('#pass-strength-result').addClass('good').html( pwsL10n.good );
                        break;
                    case 4:
                        $('#pass-strength-result').addClass('strong').html( pwsL10n.strong );
                        break;
                    case 5:
                        $('#pass-strength-result').addClass('short').html( pwsL10n.mismatch );
                        break;
                    default:
                        $('#pass-strength-result').addClass('short').html( pwsL10n['short'] );
                }

            }
        },

        enableMultistep: function(o) {

            var js_obj = this;
            var step_number = 0;
            var progressbar_type = $(':hidden[name="wpuf_multistep_type"]').val();

            if ( progressbar_type == null ) {
                return;
            }

            // first fieldset doesn't have prev button,
            // last fieldset doesn't have next button
            $('fieldset.wpuf-multistep-fieldset').find('.wpuf-multistep-prev-btn').first().remove();
            $('fieldset.wpuf-multistep-fieldset').find('.wpuf-multistep-next-btn').last().remove();

            // at first first fieldset will be shown, and others will be hidden
            $('.wpuf-form fieldset').removeClass('field-active').first().addClass('field-active');

            if ( progressbar_type == 'progressive' && $('.wpuf-form .wpuf-multistep-fieldset').length != 0 ) {

                var firstLegend = $('fieldset.wpuf-multistep-fieldset legend').first();
                $('.wpuf-multistep-progressbar').html('<div class="wpuf-progress-percentage"></div>' );

                var progressbar = $( ".wpuf-multistep-progressbar" ),
                    progressLabel = $( ".wpuf-progress-percentage" );

                $( ".wpuf-multistep-progressbar" ).progressbar({
                    change: function() {
                        progressLabel.text( progressbar.progressbar( "value" ) + "%" );
                    }
                });

                $('.wpuf-multistep-fieldset legend').hide();

            } else {
                $('.wpuf-form').each(function() {
                    var this_obj = $(this);
                    var progressbar = $('.wpuf-multistep-progressbar', this_obj);
                    var nav = '';

                    progressbar.addClass('wizard-steps');
                    nav += '<ul class="wpuf-step-wizard">';

                    $('.wpuf-multistep-fieldset', this).each(function(){
                        nav += '<li>' + $.trim( $('legend', this).text() ) + '</li>';
                        $('legend', this).hide();
                    });

                    nav += '</ul>';
                    progressbar.append( nav );

                    $('.wpuf-step-wizard li', progressbar).first().addClass('active-step');
                    $('.wpuf-step-wizard', progressbar).listautowidth();
                });
            }

            this.change_fieldset(step_number, progressbar_type);

            $('fieldset .wpuf-multistep-prev-btn, fieldset .wpuf-multistep-next-btn').click(function(e) {
                // js_obj.formSubmit();
                if ( $(this).hasClass('wpuf-multistep-next-btn') ) {
                    var result = js_obj.formStepCheck( '', $(this).closest('fieldset') );

                    if ( result != false ) {
                        o.change_fieldset(++step_number,progressbar_type);
                    }

                } else if ( $(this).hasClass('wpuf-multistep-prev-btn') ) {
                    o.change_fieldset( --step_number,progressbar_type );
                }

                var formDiv  = $( "form.wpuf-form-add" );
                var position = formDiv.offset().top;

                // this changes the scrolling behavior to "smooth"
                window.scrollTo({
                    top: position - 32,
                    behavior: "smooth"
                });

                return false;
            });
        },

        change_fieldset: function(step_number, progressbar_type) {
            var current_step = $('fieldset.wpuf-multistep-fieldset').eq(step_number);

            $('fieldset.wpuf-multistep-fieldset').removeClass('field-active').eq(step_number).addClass('field-active');

            $('.wpuf-step-wizard li').each(function(){
                if ( $(this).index() <= step_number ){
                    progressbar_type == 'step_by_step'? $(this).addClass('passed-wpuf-ms-bar') : $('.wpuf-ps-bar',this).addClass('passed-wpuf-ms-bar');
                } else {
                    progressbar_type == 'step_by_step'? $(this).removeClass('passed-wpuf-ms-bar') : $('.wpuf-ps-bar',this).removeClass('passed-wpuf-ms-bar');
                }
            });

            $('.wpuf-step-wizard li').removeClass('wpuf-ms-bar-active active-step completed-step');
            $('.passed-wpuf-ms-bar').addClass('completed-step').last().addClass('wpuf-ms-bar-active');
            $('.wpuf-ms-bar-active').addClass('active-step');

            var legend = $('fieldset.wpuf-multistep-fieldset').eq(step_number).find('legend').text();
            legend = $.trim( legend );

            if ( progressbar_type == 'progressive' && $('.wpuf-form .wpuf-multistep-fieldset').length != 0 ) {
                var progress_percent = ( step_number + 1 ) * 100 / $('fieldset.wpuf-multistep-fieldset').length ;
                var progress_percent = Number( progress_percent.toFixed(2) );
                $( ".wpuf-multistep-progressbar" ).progressbar({value: progress_percent });
                $( '.wpuf-progress-percentage' ).text( legend + ' (' + progress_percent + '%)');
            }

            // trigger a change event
            $('.wpuf-form').trigger('step-change-fieldset', [ step_number, current_step ]);
        },

        ajaxCategory: function () {

            var el = '.cat-ajax',
                wrap = '.category-wrap';

            $(wrap).on('change', el, function(){
                var form_id = $( this ).data( 'form-id' );
                currentLevel = parseInt( $(this).parent().attr('level') );
                WP_User_Frontend.getChildCats( $(this), currentLevel + 1, 'category', form_id );
            });
        },

        getChildCats: function ( dropdown, level, taxonomy, form_id ) {

            var cat = $(dropdown).val();
            var container_id = 'wpuf-category-dropdown-lvl-' + level;
            var taxonomy = typeof taxonomy !== 'undefined' ? taxonomy : 'category';
            var field_attr = $(dropdown).siblings('span').data('taxonomy');

            $.ajax({
                type: 'post',
                url: wpuf_frontend.ajaxurl,
                data: {
                    action: 'wpuf_get_child_cat',
                    catID: cat,
                    nonce: wpuf_frontend.nonce,
                    field_attr: field_attr,
                    form_id: form_id,
                },
                beforeSend: function() {
                    $(dropdown).parent().parent().next('.loading').addClass('wpuf-loading');
                },
                complete: function() {
                    $(dropdown).parent().parent().next('.loading').removeClass('wpuf-loading');
                },
                success: function(html) {
                    //console.log( html ); return;
                    $(dropdown).parent().nextAll().each(function(){
                        $(this).remove();
                    });

                    if(html != "") {
                        $(dropdown).parent().addClass('hasChild').parent().append('<div id="'+ container_id +'" level="'+level+'"></div>');
                        dropdown.parent().parent().find('#' + container_id ).html(html).slideDown('fast');
                    }

                    $( document ).trigger( 'wpuf-ajax-fetched-child-categories', container_id, level, taxonomy );
                }
            });
        },

        cloneField: function(e) {
            e.preventDefault();

            var $div = $(this).closest('tr');
            var $clone = $div.clone();
            // console.log($clone);

            //clear the inputs
            $clone.find('input').val('');
            $clone.find(':checked').attr('checked', '');
            $div.after($clone);
        },

        removeField: function() {
            //check if it's the only item
            var $parent = $(this).closest('tr');
            var items = $parent.siblings().addBack().length;

            if( items > 1 ) {
                $parent.remove();
            }
        },

        adminPostSubmit: function(e) {
            e.preventDefault();

            var form = $(this),
                form_data = WP_User_Frontend.validateForm(form);

            if (form_data) {
                return true;
            }
        },

        draftPost: function (e) {
            e.preventDefault();

            var self = $(this),
                form = $(this).closest('form'),
                form_data = form.serialize() + '&action=wpuf_draft_post',
                post_id = form.find('input[type="hidden"][name="post_id"]').val();

            var rich_texts = [],
                val;

            // grab rich texts from tinyMCE
            $('.wpuf-rich-validation').each(function (index, item) {
                var item      = $(item);
                var editor_id = item.data('id');
                var item_name = item.data('name');
                var val       = $.trim( tinyMCE.get(editor_id).getContent() );

                rich_texts.push(item_name + '=' + encodeURIComponent( val ) );
            });

            // append them to the form var
            form_data = form_data + '&' + rich_texts.join('&');


            self.after(' <span class="wpuf-loading"></span>');
            $.post(wpuf_frontend.ajaxurl, form_data, function(res) {
                // console.log(res, post_id);
                if ( typeof post_id === 'undefined') {
                    var html = '<input type="hidden" name="post_id" value="' + res.post_id +'">';
                    html += '<input type="hidden" name="post_date" value="' + res.date +'">';
                    html += '<input type="hidden" name="post_author" value="' + res.post_author +'">';
                    html += '<input type="hidden" name="comment_status" value="' + res.comment_status +'">';

                    form.append( html );
                }

                self.next('span.wpuf-loading').remove();

                self.after('<span class="wpuf-draft-saved">&nbsp; ' + res.message + '</span>');
                $('.wpuf-draft-saved').delay(2500).fadeOut('fast', function(){
                    $(this).remove();
                });
            })
        },

        // Frontend account dashboard update profile
        account_update_profile: function (e) {
            e.preventDefault();
            var form = $(this).closest('form');

            $.post(wpuf_frontend.ajaxurl, form.serialize(), function (res) {
                if (res.success) {
                    form.find('.wpuf-error').hide();
                    form.find('.wpuf-success').show();
                } else {
                    form.find('.wpuf-success').hide();
                    form.find('.wpuf-error').show();
                    form.find('.wpuf-error').text(res.data);
                }
            });
        },

        formStepCheck : function(e,fieldset) {
            var form = fieldset,
                submitButton = form.find('input[type=submit]'),
                form_data = WP_User_Frontend.validateForm(form);

            if ( form_data == false ) {
                WP_User_Frontend.addErrorNotice( self, 'bottom' );
            }
            return form_data;
        },

        formSubmit: function(e) {
            e.preventDefault();

            var form = $(this),
                submitButton = form.find('input[type=submit]')
            form_data = WP_User_Frontend.validateForm(form);

            if (form_data) {

                // send the request
                form.find('li.wpuf-submit').append('<span class="wpuf-loading"></span>');
                submitButton.attr('disabled', 'disabled').addClass('button-primary-disabled');

                $.post(wpuf_frontend.ajaxurl, form_data, function(res) {
                    // var res = $.parseJSON(res);

                    if ( res.success) {

                        // enable external plugins to use events
                        $('body').trigger('wpuf:postform:success', res);

                        if ( res.show_message == true) {
                            form.before( '<div class="wpuf-success">' + res.message + '</div>');
                            form.slideUp( 'fast', function() {
                                form.remove();
                            });

                            //focus
                            $('html, body').animate({
                                scrollTop: $('.wpuf-success').offset().top - 100
                            }, 'fast');

                        } else {
                            window.location = res.redirect_to;
                        }

                    } else {

                        if ( typeof res.type !== 'undefined' && res.type === 'login' ) {

                            if ( confirm(res.error) ) {
                                window.location = res.redirect_to;
                            } else {
                                submitButton.removeAttr('disabled');
                                submitButton.removeClass('button-primary-disabled');
                                form.find('span.wpuf-loading').remove();
                            }

                            return;
                        } else {
                            if ( form.find('.g-recaptcha').length > 0 ) {
                                grecaptcha.reset();
                            }

                            Swal.fire({
                                html: res.error,
                                icon: 'warning',
                                showCancelButton: false,
                                confirmButtonColor: '#d54e21',
                                confirmButtonText: 'OK',
                                customClass: {
                                    cancelButton: 'btn btn-danger',
                                }
                            });

                        }

                        submitButton.removeAttr('disabled');
                    }

                    submitButton.removeClass('button-primary-disabled');
                    form.find('span.wpuf-loading').remove();
                });
            }
        },

        isTelephoneField: function( item ) {
            return ( $(item).hasClass('wpuf_telephone') && $(item).data('show-list') === 'yes' );
        },

        validateForm: function( self ) {
            var richTexts = [];
            var errors = [];

            // remove all initial errors if any
            WP_User_Frontend.removeErrors( self );
            WP_User_Frontend.removeErrorNotice( self );

            // get all the visible fields
            var visibleFields = self.find( 'input:visible, textarea:visible, select:visible, div.br-widget:visible, .wpuf-rich-validation:visible, div.wpuf-attachment-upload-filelist:visible' );

            visibleFields.each( function( i, item ) {
                var container = '';
                var dataType = $( item ).data( 'type' );
                var required = $( item ).data( 'required' );

                switch( dataType ) {
                    case 'rich':
                        var editor_id = $( item ).data( 'id' );
                        var item_name = $( item ).data( 'name' );
                        val = $.trim( tinyMCE.get( editor_id ).getContent() );

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });
                        }

                        var isRestrictionFailed = WP_User_Frontend.editorLimit.isRestrictionFailed( item );

                        if ( isRestrictionFailed ) {
                            errors.push({
                                type: 'limit',
                                container: item
                            });

                            break;
                        }

                        var containingShortcode = WP_User_Frontend.editorContainingShortcode(item);

                        if ( containingShortcode.shortcodeFound ) {
                            errors.push({
                                type: 'custom',
                                container: item,
                                message: wpuf_frontend.protected_shortcodes_message
                                    .replace('%shortcode%', '[' + containingShortcode.shortcode + ']')
                            });

                            break;
                        }

                        richTexts.push( item_name + '=' + encodeURIComponent( val ) );

                        break;
                    case 'textarea':
                    case 'text':
                        val = $.trim( $( item ).val() );

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });

                            break;
                        }

                        var isRestrictionFailed = WP_User_Frontend.editorLimit.isRestrictionFailed( item );

                        if ( isRestrictionFailed ) {
                            errors.push({
                                type: 'limit',
                                container: item
                            });

                            break;
                        }

                        if ( WP_User_Frontend.isTelephoneField( item ) ) {
                            // Get intlTelInput instance
                            var iti = window.intlTelInputGlobals.getInstance( item );
                            if ( '' !== iti.getNumber() && ! iti.isValidNumber() ) {
                                errors.push({
                                    type: 'validation',
                                    container: item
                                });

                                break;
                            }

                            var name = $( item ).attr( 'name' );
                            var value = iti.getNumber();

                            // append the mobile number with country code
                            $( '<input>' )
                                .attr( 'type', 'hidden' )
                                .attr( 'name', name )
                                .attr( 'value', value )
                                .appendTo( self );

                            break;
                        }

                        break;
                    case 'url':
                        val = $.trim( $( item ).val() );

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });

                            break;
                        }

                        if ( val !== '' && ! WP_User_Frontend.isValidURL( val ) ) {
                            errors.push({
                                type: 'validation',
                                container: item
                            });

                            break;
                        }
                        break;
                    case 'password':
                    case 'confirm_password':
                        var hasRepeat = $(item).data('repeat');
                        var strength = $(item).data('strength');
                        var min_length = $(item).data('minimum-length');

                        val = $.trim( $(item).val() );

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });

                            break;
                        }

                        if ( val.length < min_length ) {
                            errors.push({
                                type: 'custom',
                                container: item,
                                message: 'Minimum ' + min_length + ' character is required'
                            });

                            break;
                        }

                        if ( hasRepeat === 'yes' ) {
                            var repeatItem = $(item).closest('.wpuf-form').find('[data-type="confirm_password"]').eq(0);

                            if ( repeatItem.val() !== val ) {
                                errors.push({
                                    error_type: 'mismatch',
                                    container: item
                                });
                            }
                        }

                        if ( strength ) {
                            var strengthMeter = wp.passwordStrength.meter(val, wp.passwordStrength.userInputDisallowedList());

                            if (strength === 'weak' && strengthMeter < 2) {
                                errors.push({
                                    type: 'custom',
                                    container: item,
                                    message: wpuf_frontend.password_warning_weak
                                });
                            } else if (strength === 'medium' && strengthMeter < 3) {
                                errors.push({
                                    type: 'custom',
                                    container: item,
                                    message: wpuf_frontend.password_warning_medium
                                });
                            } else if (strength === 'strong' && strengthMeter < 4) {
                                errors.push({
                                    type: 'custom',
                                    container: item,
                                    message: wpuf_frontend.password_warning_strong
                                });
                            }
                        }

                        break;
                    case 'select':
                        val = $(item).val();

                        if ( required === 'yes' && val === '-1' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });
                        }
                        break;
                    case 'multiselect':
                        val = $( item ).val();

                        if ( required === 'yes' && val.length === 0 ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });
                        }
                        break;
                    case 'tax-checkbox':
                        var length = $(item).children().find('input:checked').length;

                        if ( required === 'yes' && ! length ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });
                        }
                        break;
                    case 'radio':
                    case 'checkbox':
                        container = $( item ).parents( '.wpuf-fields' );
                        length = $( container ).find( 'input:checked' ).length;

                        if ( required === 'yes' && ! length ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });
                        }

                        break;
                    case 'file':
                        var length = $( item ).find('ul').children().length;

                        if ( required === 'yes' && ! length ) {
                            errors.push({
                                type: 'required',
                                container: $( item ).closest( '.wpuf-fields' )
                            });
                        }
                        break;
                    case 'email':
                        val = $( item ).val();

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'required',
                                container: item
                            });

                            break;
                        }

                        if ( val !== '' && ! WP_User_Frontend.isValidEmail( val ) ) {
                            errors.push({
                                type: 'validation',
                                container: item
                            });
                        }
                        break;
                    case 'url':
                        var val = $(item).val();

                        if ( required === 'yes' && val === '' ) {
                            errors.push({
                                type: 'validation',
                                container: item
                            });

                            break;
                        }

                        if ( val !== '' && ! WP_User_Frontend.isValidURL( val ) ) {
                            errors.push({
                                type: 'validation',
                                container: item
                            });
                        }
                        break;
                    default:    // Google map etc.
                        // if this is a Google map
                        if ( $( item ).attr( 'id' ) && $( item ).attr( 'id' ).startsWith( 'wpuf-map' ) ) {
                            var mapContainer = $( item ).parents( '.wpuf-form-google-map-container' );
                            mapField = $( mapContainer ).find( 'input[id^="wpuf-map-lat"]' );
                            required = $( mapField ).data( 'required' );
                            val = $( mapField ).val(); // needs to grab from parent to get all the data

                            if ( required === 'yes' && val === '' ) {
                                errors.push({
                                    type: 'required',
                                    container: mapContainer
                                });
                            }
                        }

                        // if rating field
                        if ( $( item ).hasClass( 'br-widget' ) ) {
                            container = $( item ).parents('.wpuf-fields');
                            required = $( container ).find( 'select.wpuf-ratings' ).data( 'required' );
                            length = $( item ).find('.br-selected').length;

                            if ( required === 'yes' && ! length ) {
                                errors.push({
                                    type: 'required',
                                    container: item
                                });
                            }
                        }
                }

            });

            if ( errors.length ) {
                var i;
                var length = errors.length;
                for ( i = 0; i < length; i++ ) {
                    WP_User_Frontend.markError( errors[i].container, errors[i].type, errors[i].message );
                }

                WP_User_Frontend.addErrorNotice( self,'end' );
                $( errors[0].container ).focus();

                return false;
            }

            // Fields that are conditionally hidden
            var conditionallyHiddenFields = self.find( 'li.wpuf-el[style*="display: none"] input' );

            conditionallyHiddenFields.each( function( i, item ) {
                var inputType = $( item ).attr( 'type' );

                switch ( inputType ) {
                    case 'url':
                    case 'email':
                        // removing conditionally hidden fields value
                        // so that default HTML validation don't restrict those fields
                        $( item ).val( '' );
                }
            });

            var disable_fields = self.find(":disabled").removeAttr("disabled");
            var formData = self.serialize();
            disable_fields.attr("disabled", "disabled");

            // append them to the form var
            formData = formData + '&' + richTexts.join( '&' );
            return formData;
        },

        /**
         *
         * @param form
         * @param position (value = bottom or end) end if form is onepare, bottom, if form is multistep
         */
        addErrorNotice: function( form, position ) {
            if( position == 'bottom' ) {
                $('.wpuf-multistep-fieldset:visible').append('<div class="wpuf-errors">' + wpuf_frontend.error_message + '</div>');
            } else {
                $(form).find('li.wpuf-submit').append('<div class="wpuf-errors">' + wpuf_frontend.error_message + '</div>');
            }

        },

        removeErrorNotice: function(form) {
            $(form).find('.wpuf-errors').remove();
        },

        markError: function(item, error_type, error_message) {
            $(item).closest('div').addClass('has-error');

            if ( ! error_type ) {
                return;
            }

            var error_string = '';
            var address_field_label = $( item ).data( 'label' );
            if ( address_field_label ) {
                error_string = address_field_label;
            } else {
                error_string = $(item).closest('li').data('label');
            }
            switch ( error_type ) {
                case 'required' :
                    error_string = error_string + ' ' + error_str_obj[error_type];
                    break;
                case 'mismatch' :
                    error_string = error_string + ' ' + error_str_obj[error_type];
                    break;
                case 'validation' :
                    error_string = error_string + ' ' + error_str_obj[error_type];
                    break;
                case 'limit':
                    error_string = '';
                    break;
                case 'custom' :
                    error_string = error_message;
                    break;
            }
            $(item).siblings('.wpuf-error-msg').remove();

            // for address fields
            if ( $( item ).closest( '.wpuf-fields.wpuf-fields-address' ).length ) {
                $( item ).closest( 'div.wpuf-sub-fields' ).find( 'div.wpuf-error-msg' ).remove();
                $( item ).closest( 'div.wpuf-sub-fields' ).append( '<div class="wpuf-error-msg">' + error_string + '</div>' );
            } else {
                $( item ).closest( 'div.wpuf-fields' ).find( 'div.wpuf-error-msg' ).remove();
                $( item ).closest( 'div.wpuf-fields' ).append( '<div class="wpuf-error-msg">' + error_string + '</div>' );
            }
        },

        removeErrors: function(item) {
            $(item).find('.has-error').removeClass('has-error');
            $('.wpuf-error-msg').remove();
        },

        isValidEmail: function( email ) {
            var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
            return pattern.test(email);
        },

        isValidURL: function(url) {
            var urlregex = new RegExp("^(http:\\/\\/www\\.|https:\\/\\/www\\.|http:\\/\\/|https:\\/\\/){1}[a-z0-9]+([\\-\\.]{1}[a-z0-9]+)*\\.[a-z]{2,20}(:[0-9]{1,20})?(\\/.*)?$");
            return urlregex.test(url);
        },

        insertImage: function(button, form_id) {

            var container = 'wpuf-insert-image-container';

            if ( ! $( '#' + button ).length ) {
                return;
            };

            var imageUploader = new plupload.Uploader({
                runtimes: 'html5,html4',
                browse_button: button,
                container: container,
                multipart: true,
                multipart_params: {
                    action: 'wpuf_insert_image',
                    form_id: $( '#' + button ).data('form_id')
                },
                multiple_queues: false,
                multi_selection: false,
                urlstream_upload: true,
                file_data_name: 'wpuf_file',
                max_file_size: wpuf_upload.max_filesize,
                url: wpuf_upload.plupload.url,
                flash_swf_url: wpuf_upload.flash_swf_url,
                filters: [{
                    title: 'Allowed Files',
                    extensions: 'jpg,jpeg,gif,png,bmp'
                }]
            });

            imageUploader.bind('Init', function(up, params) {
                // console.log("Current runtime environment: " + params.runtime);
            });

            imageUploader.bind('FilesAdded', function(up, files) {
                var $container = $('#' + container);

                $.each(files, function(i, file) {
                    $container.append(
                        '<div class="upload-item" id="' + file.id + '"><div class="progress progress-striped active"><div class="bar"></div></div></div>');
                });

                up.refresh();
                up.start();
            });

            imageUploader.bind('QueueChanged', function (uploader) {
                imageUploader.start();
            });

            imageUploader.bind('UploadProgress', function(up, file) {
                var item = $('#' + file.id);

                $('.bar', item).css({ width: file.percent + '%' });
                $('.percent', item).html( file.percent + '%' );
            });

            imageUploader.bind('Error', function(up, error) {
                alert('Error #' + error.code + ': ' + error.message);
            });

            imageUploader.bind('FileUploaded', function(up, file, response) {

                $('#' + file.id).remove();

                if ( response.response !== 'error' ) {
                    var success = false;

                    if ( typeof tinyMCE !== 'undefined' ) {

                        if ( typeof tinyMCE.execInstanceCommand !== 'function' ) {
                            // tinyMCE 4.x
                            var mce = tinyMCE.get( 'post_content_' + form_id );

                            if ( mce !== null ) {
                                mce.insertContent(response.response);
                            }
                        } else {
                            // tinyMCE 3.x
                            tinyMCE.execInstanceCommand( 'post_content_' + form_id, 'mceInsertContent', false, response.response);
                        }
                    }

                    // insert failed to the edit, perhaps insert into textarea
                    var post_content = $('#post_content_' + form_id);
                    post_content.val( post_content.val() + response.response );

                } else {
                    alert('Something went wrong');
                }
            });

            imageUploader.init();
        },

        deleteAvatar: function(e) {
            e.preventDefault();

            if ( confirm( $(this).data('confirm') ) ) {
                $.post(wpuf_frontend.ajaxurl, {action: 'wpuf_delete_avatar', _wpnonce: wpuf_frontend.nonce}, function() {
                    $(e.target).parent().remove();
                    $('[id^=wpuf-avatar]').css("display", "");
                });
            }
        },

        editorLimit: {

            bind: function(limit, field, type, limit_type, limit_to ) {
                if ( type === 'no' ) {
                    // it's a textarea
                    $('textarea#' +  field).keyup( function(event) {
                        WP_User_Frontend.editorLimit.textLimit.call(this, event, limit, limit_type, limit_to );
                    });

                    $('input#' +  field).keyup( function(event) {
                        WP_User_Frontend.editorLimit.textLimit.call(this, event, limit, limit_type, limit_to );
                    });

                    $('input#' +  field).change( function(event) {
                        WP_User_Frontend.editorLimit.textLimit.call(this, event, limit, limit_type, limit_to );
                    });

                    $('textarea#' +  field).on('paste', function(event) {
                        var self = $(this);

                        setTimeout(function() {
                            WP_User_Frontend.editorLimit.textLimit.call(self, event, limit, limit_type, limit_to );
                        }, 100);
                    });

                    $('input#' +  field).on('paste', function(event) {
                        var self = $(this);

                        setTimeout(function() {
                            WP_User_Frontend.editorLimit.textLimit.call(self, event, limit, limit_type, limit_to );
                        }, 100);
                    });

                } else {
                    // it's a rich textarea
                    setTimeout(function () {
                        tinyMCE.get(field).onKeyUp.add(function(ed, event) {
                            WP_User_Frontend.editorLimit.tinymce.onKeyUp(ed, event, limit, limit_type, limit_to );
                        } );

                        tinyMCE.get(field).onPaste.add(function(ed, event) {
                            setTimeout(function() {
                                WP_User_Frontend.editorLimit.tinymce.onPaste(ed, event, limit, limit_type, limit_to );
                            }, 100);
                        });

                    }, 1000);
                }
            },

            checkTinyMCERestriction: function(field, data, ed) {
                var numWords = 0;

                var limit = (typeof(data[0]) !== "undefined" && data[0] !== null) ? parseInt(data[0]) : 0;
                var fieldId = (typeof(data[1]) !== "undefined" && data[1] !== null) ? (data[1]).trim() : '';
                var fieldType = (typeof(data[2]) !== "undefined" && data[2] !== null) ? (data[2]).trim() : '';
                var limit_type = (typeof(data[3]) !== "undefined" && data[3] !== null) ? (data[3]).trim() : '';
                var limit_to = (typeof(data[4]) !== "undefined" && data[4] !== null) ? (data[4]).trim() : '';
                var limit_label = '';

                if ( 'word' === limit_type ) {
                    numWords = WP_User_Frontend.editorLimit.tinymce.getStats(ed).words;
                    limit_label = 'word_' + limit_to;
                } else {
                    numWords = WP_User_Frontend.editorLimit.tinymce.getStats(ed).chars;
                    limit_label = 'char_' + limit_to;
                }

                var errorMessage = wpuf_frontend['word_'+limit_to ] + ' ' + limit;

                // if blank field, no need to check for content restriction
                if ( numWords ===  0 ) {
                    return false;
                }

                if ( (numWords > limit) && ('max' === limit_to) ) {
                    WP_User_Frontend.markError( field, 'limit' );
                    jQuery('.mce-path-item.mce-last', ed.container).html( wpuf_frontend[ limit_label ] + ' ' + numWords +'/'+ limit );

                    return true;
                } else if ( (numWords < limit) && ('min' === limit_to) ) {
                    WP_User_Frontend.markError( field, 'limit' );
                    WP_User_Frontend.contentLimitMessage( $(field), limit_type, limit_to, limit );

                    return true;
                }
            },

            isRestrictionFailed: function( field ) {
                var fieldId = $( field ).data( 'id' );
                var fieldType = $( field ).data( 'type' );
                var isTinymce = false;
                var ed = null;
                var numChars = 0;
                var numWords = 0;
                var data = '';

                // clear previous style
                if ( $( field ).closest( 'div.wpuf-fields' ).hasClass( 'has-error' ) ) {
                    $( field ).closest( 'div.wpuf-fields' ).removeClass( 'has-error' )
                }

                if ( typeof tinyMCE !== 'undefined' && tinyMCE.get(fieldId) !== null ) {
                    isTinymce = true;
                    ed = tinyMCE.get( fieldId );

                    ed.focus();
                }

                // For content restriction, we are passing the information below the fields inside a <script> tag
                // For example: the field type, restriction type(word or character), min/max allowed etc.
                // Extracting required data from the script tag
                if ( $(field).closest('li.wpuf-el').find('script').html() ) {
                    var fieldScript = $(field).closest('li.wpuf-el').find('script').html();
                    data = fieldScript.match(/(?:bind\()(.*?(?=\)))/) ? fieldScript.match(/(?:bind\()(.*?(?=\)))/)[1].replace(/['"]/g, '').split(',') : '';
                }

                if ( isTinymce ) {
                    return WP_User_Frontend.editorLimit.checkTinyMCERestriction(field, data, ed);
                }

                var limit = (typeof(data[0]) !== "undefined" && data[0] !== null) ? parseInt(data[0]) : 0;
                var limit_type = (typeof(data[3]) !== "undefined" && data[3] !== null) ? (data[3]).trim() : '';
                var limit_to = (typeof(data[4]) !== "undefined" && data[4] !== null) ? (data[4]).trim() : '';
                var limit_label = ( 'word' === limit_type ) ? 'Word Limit : ' : 'Character Limit : ';

                numChars = $(field).val().trim().length;
                numWords = $(field).val().trim().split(' ').length;

                // if blank field, no need to check for content restriction
                if ( numChars ===  0 ) {
                    return false;
                }

                if ( limit_type === 'word' ) {
                    if ( ( numWords > limit ) && ( 'max' === limit_to ) ) {
                        WP_User_Frontend.markError( field, 'limit' );

                        return true;
                    } else if ( ( numWords < limit ) && ( 'min' === limit_to ) ) {
                        WP_User_Frontend.markError( field, 'limit' );

                        return true;
                    }
                } else {
                    if ( ( numChars > limit ) && ( 'max' === limit_to ) ) {
                        WP_User_Frontend.markError( field, 'limit' );

                        return true;
                    } else if ( ( numChars < limit ) && ( 'min' === limit_to ) ) {
                        WP_User_Frontend.markError( field, 'limit' );

                        return true;
                    }
                }

                return false;

            },

            tinymce: {

                getStats: function(ed) {
                    var text = ed.getContent({format: 'text'});

                    return {
                        chars: text.length,
                        words: text.split(' ').length
                    };
                },

                onKeyUp: function(ed, event, limit, limit_type, limit_to ) {
                    var field = $( '.wpuf-fields.wpuf_' + ed.id );

                    // remove previous errors
                    if ( $(field.closest('.wpuf-fields')).hasClass('has-error') ) {
                        field.removeClass('has-error');
                    }

                    var numChars = WP_User_Frontend.editorLimit.tinymce.getStats(ed).chars;

                    // if the fields is empty, no need to check for char/word limit
                    if ( ! numChars ) {
                        field.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');

                        return;
                    }

                    if ( 'word' === limit_type ) {
                        numChars = WP_User_Frontend.editorLimit.tinymce.getStats(ed).words;
                    }


                    if ( limit && numChars > limit && 'max' === limit_to ) {
                        WP_User_Frontend.markError( field, 'limit' );
                        WP_User_Frontend.contentLimitMessage( $(field), limit_type, limit_to, limit );
                    } else if ( limit && numChars < limit && 'min' === limit_to ) {
                        WP_User_Frontend.markError( field, 'limit' );
                        WP_User_Frontend.contentLimitMessage( $(field), limit_type, limit_to, limit );
                    } else {
                        jQuery('.mce-path-item.mce-last', ed.container).html('');
                        field.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');
                    }
                },

                onPaste: function(ed, event, limit, limit_type, limit_to) {
                    var editorContent = ed.getContent({format: 'text'});
                    var contentLength = editorContent.length;
                    var field = $( '.wpuf-fields.wpuf_' + ed.id );

                    // remove previous errors
                    if ( field.closest('.wpuf-fields').hasClass('has-error') ) {
                        field.closest('.wpuf-fields').removeClass('has-error');
                    }

                    // field is empty. no need to check for word/char limit
                    if ( ! contentLength ) {
                        return;
                    }

                    if ( 'word' === limit_type ) {
                        contentLength = editorContent.split(' ').length;
                    }

                    if ( limit && contentLength > limit && 'max' === limit_to ) {
                        WP_User_Frontend.contentLimitMessage( field, limit_type, limit_to, limit );
                        WP_User_Frontend.markError( field, 'limit' );
                    } else if ( limit && contentLength < limit && 'min' === limit_to ) {
                        WP_User_Frontend.contentLimitMessage( field, limit_type, limit_to, limit );
                        WP_User_Frontend.markError( field, 'limit' );
                    } else {
                        field.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');
                    }

                    WP_User_Frontend.editorLimit.make_media_embed_code(editorContent, ed);
                }
            },

            textLimit: function(event, limit, limit_type, limit_to ) {
                var self = $(this);
                var contentLength = self.val().length;

                // remove previous errors
                if ( self.closest('.wpuf-fields').hasClass('has-error') ) {
                    self.closest('.wpuf-fields').removeClass('has-error');
                }

                // field is empty. no need to check for word/char limit
                if ( ! contentLength ) {
                    return;
                }

                if ( 'word' === limit_type ) {
                    contentLength = self.val().split(' ').length;
                }

                if ( limit && contentLength > limit && 'max' === limit_to ) {
                    WP_User_Frontend.contentLimitMessage( self, limit_type, limit_to, limit );
                    WP_User_Frontend.markError( self, 'limit' );
                } else if ( limit && contentLength < limit && 'min' === limit_to ) {
                    WP_User_Frontend.contentLimitMessage( self, limit_type, limit_to, limit )
                    WP_User_Frontend.markError( self, 'limit' );
                } else {
                    self.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');
                }

                // handle the paste event
                if ( event.type === 'paste' ) {
                    if ( limit && contentLength > limit && 'max' === limit_to ) {
                        WP_User_Frontend.contentLimitMessage( self, limit_type, limit_to, limit );
                        WP_User_Frontend.markError( self, 'limit' );
                    } else if ( limit && contentLength < limit && 'min' === limit_to ) {
                        WP_User_Frontend.contentLimitMessage( self, limit_type, limit_to, limit );
                        WP_User_Frontend.markError( self, 'limit' );
                    } else {
                        self.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html('');
                    }
                }
            },

            blockTyping: function(event) {
                // Allow: backspace, delete, tab, escape, minus enter and . backspace = 8,delete=46,tab=9,enter=13,.=190,escape=27, minus = 189
                if ($.inArray(event.keyCode, [46, 8, 9, 27, 13, 110, 190, 189]) !== -1 ||
                    // Allow: Ctrl+A
                    (event.keyCode == 65 && event.ctrlKey === true) ||
                    // Allow: home, end, left, right, down, up
                    (event.keyCode >= 35 && event.keyCode <= 40)) {
                    // let it happen, don't do anything
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
            },

            make_media_embed_code: function(content, editor){
                $.post( ajaxurl, {
                        action:'wpuf_make_media_embed_code',
                        content: content
                    },
                    function(data){
                        editor.setContent(editor.getContent() + editor.setContent(data));
                    }
                )
            }
        },

        doUncheckRadioBtn: function ( el ) {
            el.checked = false;
        },

        contentLimitMessage: function( field, content_limit_type, limit_to, limit ) {
            var limitMessage = '';
            if ( 'word' === content_limit_type ){
                if ( limit_to === 'min' ) {
                    limitMessage = wpuf_frontend.word_min_title + '<br>';
                    limitMessage += wpuf_frontend.word_min_details.replace( '%number%', limit );
                } else {
                    limitMessage = wpuf_frontend.word_max_title + '<br>';
                    limitMessage += wpuf_frontend.word_max_details.replace( '%number%', limit );
                }
            } else {
                if ( limit_to === 'min' ) {
                    limitMessage = wpuf_frontend.char_min_title + '<br>';
                    limitMessage += wpuf_frontend.char_min_details.replace( '%number%', limit );
                } else {
                    limitMessage = wpuf_frontend.char_max_title + '<br>';
                    limitMessage += wpuf_frontend.char_max_details.replace( '%number%', limit );
                }
            }

            field.closest('.wpuf-fields').find('span.wpuf-wordlimit-message').html( limitMessage );
        },

        editorContainingShortcode: function( field ) {
            var item = $( field );
            var editor_id = item.data( 'id' );
            var postContent = $.trim( tinyMCE.get( editor_id ).getContent() ).toLowerCase();
            var shortcodes = wpuf_frontend.protected_shortcodes;

            if ( ! shortcodes) {
                return {
                    shortcodeFound: false,
                };
            }
            for ( var i = 0; i < shortcodes.length; i++) {
                var shortcode = shortcodes[i];
                var regex = new RegExp(shortcode);
                if (regex.test(postContent)) {
                    return {
                        shortcodeFound: true,
                        shortcode: shortcode,
                    };
                }
            }

            return {
                shortcodeFound: false,
            };
        }
    };

    $(function() {
        WP_User_Frontend.init();

        // payment gateway selection
        $('ul.wpuf-payment-gateways').on('click', 'input[type=radio]', function(e) {
            $('.wpuf-payment-instruction').slideUp(250);

            $(this).parents('li').find('.wpuf-payment-instruction').slideDown(250);
        });

        if( !$('ul.wpuf-payment-gateways li').find('input[type=radio]').is(':checked') ) {
            $('ul.wpuf-payment-gateways li').first().find('input[type=radio]').click()
        } else {
            var el = $('ul.wpuf-payment-gateways li').find('input[type=radio]:checked');
            el.parents('li').find('.wpuf-payment-instruction').slideDown(250);
        }
    });

    $(function() {
        $('input[name="first_name"], input[name="last_name"]').on('change keyup', function() {
            var myVal, newVal = $.makeArray($('input[name="first_name"], input[name="last_name"]').map(function(){
                if (myVal = $(this).val()) {
                    return(myVal);
                }
            })).join(' ');
            $('input[name="display_name"]').val(newVal);
        });
    });

    // script for Dokan vendor registration template
    $(function($) {

        $('.wpuf-form-add input[name="dokan_store_name"]').on('focusout', function() {
            var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            $('input[name="shopurl"]').val(value);
            $('#url-alart').text( value );
            $('input[name="shopurl"]').focus();
        });

        $('.wpuf-form-add input[name="shopurl"]').keydown(function(e) {
            var text = $(this).val();

            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }

            if ((e.shiftKey || (e.keyCode < 65 || e.keyCode > 90) && (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) ) {
                e.preventDefault();
            }
        });

        $('.wpuf-form-add input[name="shopurl"]').keyup(function(e) {
            $('#url-alart').text( $(this).val() );
        });

        $('.wpuf-form-add input[name="shopurl"]').on('focusout', function() {
            var self = $(this),
                data = {
                    action : 'shop_url',
                    url_slug : self.val(),
                    _nonce : dokan.nonce,
                };

            if ( self.val() === '' ) {
                return;
            }

            $.post( dokan.ajaxurl, data, function(resp) {

                if ( resp == 0){
                    $('#url-alart').removeClass('text-success').addClass('text-danger');
                    $('#url-alart-mgs').removeClass('text-success').addClass('text-danger').text(dokan.seller.notAvailable);
                } else {
                    $('#url-alart').removeClass('text-danger').addClass('text-success');
                    $('#url-alart-mgs').removeClass('text-danger').addClass('text-success').text(dokan.seller.available);
                }

            } );

        });

        // Set name attribute for google map search field
        $(".wpuf-form-add #wpuf-map-add-location").attr("name", "find_address");
    });

    $(function($) {
        // eye icon for password field
        $(document).on('click', '.wpuf-eye', function () {
            const input = $( this ).siblings( 'input' );

            if ( input.attr("type") === "password" ) {
                input.attr( "type", "text" );
                $( this ).attr( "src", wpuf_frontend.asset_url + '/images/eye-close.svg' );
            } else {
                input.attr( "type", "password" );
                $( this ).attr( "src", wpuf_frontend.asset_url + '/images/eye.svg' );
            }
        });
    });
})(jQuery, window);
