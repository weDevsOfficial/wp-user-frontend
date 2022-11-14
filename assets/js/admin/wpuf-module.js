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

    // show the overlay on hovering over to each modules
    $( '.wp-list-table.wpuf-modules .plugin-card' ).on( 'mouseover', function () {
        let overlay = $( '.form-create-overlay' );
        overlay.appendTo( this );
    } );

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
