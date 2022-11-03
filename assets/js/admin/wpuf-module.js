;(function($){
    $( '.button-upgrade-to-pro' ).click( function () {
        $( '.wpuf-popup-window' ).addClass( 'state-show' );
    } );

    $( '.popup-close-button' ).click( function () {
        $( '.wpuf-popup-window' ).removeClass( 'state-show' );
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
})(jQuery);
