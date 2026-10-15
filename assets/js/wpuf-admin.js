jQuery(function($) {
    // Clear schedule lock
    $('#wpuf_clear_schedule_lock').on('click', function(e) {
        e.preventDefault();
        var post_id = $(this).attr('data');

        // REST DELETE wpuf/v1/admin/posts/{id}/lock.
        window.wpufRest.request( wpuf_admin_script.rest, 'admin/posts/' + parseInt( post_id, 10 ) + '/lock', { method: 'DELETE' } ).then( function() {
            Swal.fire({
                icon: 'success',
                title: wpuf_admin_script.cleared_schedule_lock,
                showConfirmButton: false,
                timer: 1500
            });
        } );
        $(this).closest("p").hide();
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
