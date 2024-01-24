jQuery(function($) {
    // Collapsable email settings field
    group = [
        '.email-setting',
        '.guest-email-setting',
        '.reset-email-setting',
        '.confirmation-email-setting',
        '.subscription-setting',
        '.admin-new-user-email',
        '.pending-user-email',
        '.denied-user-email',
        '.approved-user-email',
        '.approved-post-email',
        '.account-activated-user-email'
    ]
    group.forEach(function(header, index) {
        $(header).addClass("heading");
        $(header+"-option").addClass("hide");

        $("#wpuf_mails "+header).click(function() {
            $(header+"-option").toggleClass("hide");
        });
    })

    // Checked layout radio input field after clicking image
    $(".wpuf-form-layouts li").click(function() {
        $(this.children[0]).attr("checked", "checked");
        $(".wpuf-form-layouts li").removeClass('active');
        $(this).toggleClass('active');
    });

    // Clear schedule lock
    $('#wpuf_clear_schedule_lock').on('click', function(e) {
        e.preventDefault();
        var post_id = $(this).attr('data');

        $.ajax({
            url: wpuf_admin_script.ajaxurl,
            type: 'POST',
            data: {
                'action'    : 'wpuf_clear_schedule_lock',
                'nonce'     : wpuf_admin_script.nonce,
                'post_id'   : post_id
            },
            success:function(data) {
                Swal.fire({
                    icon: 'success',
                    title: wpuf_admin_script.cleared_schedule_lock,
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
        $(this).closest("p").hide();
    });

    // override settings tab preventDefault action on lib/class.settings-api.php for tooltip link
    $('.button-upgrade-to-pro').click(function(e) {
        e.stopPropagation();
    });

    // show tooltips on crown icons
    $('th span.pro-icon, td label span.pro-icon-title, th label span.pro-icon-title, a span.pro-icon-title').on('mouseover', function() {
        let tooltip = $( '.wpuf-pro-field-tooltip' );
        let windowWidth = $( window ).width();
        let windowHeight = $( window ).height();
        let iconBounding = $( this )[0].getBoundingClientRect();
        let spaceTop = iconBounding.y;
        let iconBoundingRight = iconBounding.right;
        let iconBoundingBottom = iconBounding.bottom;
        let spaceRight = windowWidth - iconBoundingRight;
        let spaceBottom = windowHeight - iconBoundingBottom;
        let tooltipHeight = tooltip.outerHeight();
        let tooltipWidth = tooltip.outerWidth();

        if ( spaceTop > tooltipHeight ) {
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '50%' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '100%' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'initial' );
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '50%' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '0' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '-1em' );
            $( 'span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'top', '-1em' );
            $( 'tr.wpuf-subscription-recurring span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'top', '-.5em' );
        } else if ( spaceTop < tooltipHeight && spaceRight > tooltipWidth ) {
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '-5px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '22px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'rotate(90deg)' );
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '185px' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '310px' );
            $( 'a span.pro-icon-title .wpuf-pro-field-tooltip' ).css( 'left', '175px' );
            $( 'a span.pro-icon-title .wpuf-pro-field-tooltip i' ).css( 'top', '15px' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'left', '175px' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '295px' );
        } else if ( spaceBottom > tooltipHeight ) {
            $( '.wpuf-pro-field-tooltip' ).css( 'left', '10px' );
            $( '.wpuf-pro-field-tooltip' ).css( 'top', '360px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'top', '-10px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'left', '150px' );
            $( '.wpuf-pro-field-tooltip i' ).css( 'transform', 'rotate(180deg)' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'left', '0' );
            $( 'nav.subscription-nav-tab .wpuf-pro-field-tooltip' ).css( 'top', '0' );
        }

        tooltip.appendTo( this );
    });

    // check for restricted shortcodes
    var shortcodes = wpuf_admin_script.protected_shortcodes;

    if ( ! shortcodes ) {
        return;
    }

    // first get the body tag with 'post-type-*' class.
    // post type can be `post`
    // or any other custom post type like WooCommerce product, Events from event calendar etc.
    var body = $('body').filter(function() {
        var classes = $( this ).attr( 'class' ).split( ' ' );
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].indexOf( 'post-type-' ) === 0) {
                return true;
            }
        }
        return false;
    });

    var publishBtn = body.find('#post input#publish');

    // warn the admin before updating a post if it's contains a restricted shortcode
    setTimeout(function() {

        if (body.length === 0) {
            body = $('body.block-editor-page');
        }

        var postButton = body.find('button.editor-post-publish-button, button.editor-post-publish-button__button');

        var checkForShortcodes = function(event) {
            event.stopPropagation();

            var { select } = wp.data;
            var postContent = select("core/editor").getEditedPostAttribute( 'content' );
            var shortcodesFound = [];

            for ( var i = 0; i < shortcodes.length; i++) {
                var shortcode = shortcodes[i];
                var regex = new RegExp(shortcode);
                if (!regex.test( postContent )) {
                    continue;
                }

                shortcodesFound.push(shortcode);
            }

            // no shortcodes found
            if ( ! shortcodesFound.length ) {
                $(this).off('click', checkForShortcodes).click();

                // Rebind the event listener after the initial removalq
                setTimeout(function() {
                    postButton.on('click', checkForShortcodes);
                }, 500);

                return;
            }

            Swal.fire({
                title: 'Are you sure to update the post?',
                html: wpuf_admin_script.protected_shortcodes_message,
                icon: 'warning',
                padding: '0px 2em 2em',
                width: '35em',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Proceed with Update',
                cancelButtonText: 'Remove Shortcode & Publish'
            } ).then( ( result ) => {
                if (result.isConfirmed) {
                    $(this).off('click', checkForShortcodes).click();

                    // Rebind the event listener after the initial removal
                    setTimeout(function() {
                        postButton.on('click', checkForShortcodes);
                    }, 500);
                }
            });
        };

        // for gutenberg
        postButton.on('click', checkForShortcodes);
    }, 500);

    // align with WooCommerce _downloadable meta key
    var downloadableRadio = $('.wpuf-fields input[type="radio"][name="_downloadable"]');
    var downloadableCheckbox = $('#woocommerce-product-data input[type="checkbox"][name="_downloadable"]');
    downloadableRadio.click(function() {
        var downloadable = $(this).val();

        if ($(this).is(':checked') && downloadable === 'yes') {
            downloadableCheckbox.prop('checked', true);
        } else {
            downloadableCheckbox.prop('checked', false);
        }
    });

    downloadableCheckbox.change(function() {
        if ($(this).is(':checked')) {
            $('.wpuf-fields input[type="radio"][name="_downloadable"][value="yes"]').prop('checked', true);
            $('.wpuf-fields input[type="radio"][name="_downloadable"][value="no"]').prop('checked', false);
        } else {
            $('.wpuf-fields input[type="radio"][name="_downloadable"][value="yes"]').prop('checked', false);
            $('.wpuf-fields input[type="radio"][name="_downloadable"][value="no"]').prop('checked', true);
        }
    });
});
