jQuery( function($) {

    $('#wpuf-ajax-reset-password').hide();

    $('a[href="#wpuf-ajax-login-url"]').click( function(e) {
        e.preventDefault();

        $('#wpuf-ajax-login').show();
        $('#wpuf-ajax-reset-password').hide();
    });

    $('a[href="#wpuf-ajax-lost-pw-url"]').click( function(e) {
        e.preventDefault();

        $('#wpuf-ajax-reset-password').show();
        $('#wpuf-ajax-login').hide();
    });

    // Post login form
    $('#wpuf_ajax_login_form').on('submit', function(e) {
        e.preventDefault();

        var button = $(this).find('submit');
        form_data = $('#wpuf_ajax_login_form').serialize() + '&action=ajax_login';

        $.ajax({
            url: wpuf_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: form_data
        })
        .done( function( response, textStatus, jqXHR ) {
            if ( response.success == false ) {
                $('.wpuf-ajax-login-form .wpuf-ajax-errors').append(response.data.message);
            } else {
                window.location.reload(true);
                button.hide();
            }
        } )
        .fail( function( jqXHR, textStatus, errorThrown ) {
            console.log( 'AJAX failed', errorThrown );
        } );
    });

    // Reset Password
    $('#wpuf_ajax_reset_pass_form').on('submit', function(e) {
        e.preventDefault();

        var button = $(this).find('submit');

        $.ajax({
            url: wpuf_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: { 
                'action': 'lost_password', 
                'user_login': $('#wpuf-user_login').val(), 
            }
        })
        .done( function( response, textStatus, jqXHR ) {
            $('.wpuf-ajax-reset-password-form .wpuf-ajax-errors').append(response.data.message);
        } )
        .fail( function( jqXHR, textStatus, errorThrown ) {
            console.log( 'AJAX failed', errorThrown );
        } );
    });

    // Logout
    $('[href="#logout"]').click( function(e) {
        e.preventDefault();

        $.ajax({
            url: wpuf_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ajax_logout',
            },
            success: function(data) {
                $('.wpuf-ajax-logout .wpuf-ajax-errors').html(data.message);
                window.location.reload(true);
            }
        });
    });  

});