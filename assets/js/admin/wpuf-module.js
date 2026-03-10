;(function($){
    $( '.button-upgrade-to-pro' ).click( function () {
        let popupWindow = $( '.wpuf-popup-window' );
        // append the popup window to the body for styling
        $( 'body' ).css( 'position', 'relative' );
        $( 'body' ).append( popupWindow );
        $( '.wpuf-popup-window' ).addClass( 'state-show' );
    } );

    $( '.popup-close-button' ).click( function () {
        closePopUp();
    } );

    // init swiffyslider
    $( window ).on( 'load', function () {
        swiffyslider.initSlider( document.getElementById( 'wpuf-slider' ) );
    } );

    // show the overlay on hovering over to each pro modules preview
    $( '.wp-list-table.wpuf-pro-modules-preview .plugin-card' ).on( 'mouseover', function () {
        let overlay = $( '.form-create-overlay' );
        overlay.appendTo( this );
    } );

    // Handle free module toggle
    $( '.wpuf-toggle-free-module' ).on( 'change', function () {
        var $toggle = $( this );
        var moduleId = $toggle.data( 'module' );
        var isActive = $toggle.is( ':checked' );
        var status = isActive ? 'active' : 'inactive';

        // Disable the toggle while processing
        $toggle.prop( 'disabled', true );

        $.ajax({
            url: wpuf_free_modules.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpuf_toggle_free_module',
                module: moduleId,
                status: status,
                nonce: wpuf_free_modules.nonce
            },
            success: function( response ) {
                if ( response.success ) {
                    // Re-enable the toggle
                    $toggle.prop( 'disabled', false );
                } else {
                    // Revert the toggle state on error
                    $toggle.prop( 'checked', ! isActive );
                    $toggle.prop( 'disabled', false );
                    alert( response.data.message || 'An error occurred' );
                }
            },
            error: function() {
                // Revert the toggle state on error
                $toggle.prop( 'checked', ! isActive );
                $toggle.prop( 'disabled', false );
                alert( 'An error occurred while toggling the module' );
            }
        });
    });

    $( '#wpuf-upgrade-popup' ).on( 'click', function( e ) {
        let modal = $( '.modal-window' );

        // clicking outside the popup modal
        if ( ! modal.is( e.target ) && modal.has( e.target ).length === 0) {
            closePopUp();
        }
    } );

    // close the 'upgrade to pro' popup on the module page
    function closePopUp() {
        $( '.wpuf-popup-window' ).removeClass( 'state-show' );
        $( 'body' ).css( 'position', 'initial' );
    }
})(jQuery);
