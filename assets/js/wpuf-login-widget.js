jQuery( function($) {

    $('.wpuf-ajax-reset-password-form').hide();

    var login_widget  = $('.login-widget-container');

    /**
     * The message of a failed REST call (`{ code, message }`).
     */
    function errorMessage( error ) {
        return error && error.message ? error.message : '';
    }

    login_widget.on('click', '#wpuf-ajax-login-url', function(e) {
        e.preventDefault();

        $('.wpuf-ajax-login-form').show();
        $('.wpuf-ajax-reset-password-form').hide();
    });

    login_widget.on('click', '#wpuf-ajax-lost-pw-url', function(e) {
        e.preventDefault();

        $('.wpuf-ajax-reset-password-form').show();
        $('.wpuf-ajax-login-form').hide();
    });

    // Login
    login_widget.find( '#wpuf_ajax_login_form' ).on('submit', function(e) {
        e.preventDefault();

        var errors = $('.wpuf-ajax-login-form .wpuf-ajax-errors');
        var body   = window.wpufRest.formBody( $(this).serialize() ) + '&wpuf_nonce=' + encodeURIComponent( wpuf_ajax.nonce );

        errors.empty();

        window.wpufRest.request( wpuf_ajax.rest, 'auth/login', { method: 'POST', data: body } )
            .then( function() {
                window.location.reload();
            } )
            .catch( function( error ) {
                errors.html( errorMessage( error ) );
            } );
    });

    // Reset Password
    login_widget.find( '#wpuf_ajax_reset_pass_form' ).on('submit', function(e) {
        e.preventDefault();

        var message = $('.wpuf-ajax-reset-password-form .wpuf-ajax-message');

        function show( text ) {
            if ( ! message.find('p').length ) {
                message.html('<p></p>');
            }

            message.find('p').html( text );
            message.addClass('wpuf-message');
        }

        window.wpufRest.request( wpuf_ajax.rest, 'auth/lost-password', { method: 'POST', data: window.wpufRest.formBody( $(this).serialize() ) } )
            .then( function( response ) {
                show( response.message );
            } )
            .catch( function( error ) {
                show( errorMessage( error ) );
            } );
    });

    // Logout
    login_widget.on('click', '#logout-url', function(e) {
        e.preventDefault();

        window.wpufRest.request( wpuf_ajax.rest, 'auth/logout', { method: 'POST' } )
            .then( function( response ) {
                $('.wpuf-ajax-logout .wpuf-ajax-errors').html( response.message );
                window.location.reload();
            } )
            .catch( function( error ) {
                $('.wpuf-ajax-logout').append( '<div class="wpuf-ajax-errors">' + $('<div>').text( errorMessage( error ) ).html() + '</div>' );
            } );
    });

});
