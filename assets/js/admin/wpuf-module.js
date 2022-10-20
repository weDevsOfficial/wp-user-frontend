;(function($){
    $( '.button-upgrade-to-pro' ).click( function () {
        $( '.wpuf-popup-window' ).addClass( 'state-show' );
    } );

    $( '.popup-close-button' ).click( function() {
        $( '.wpuf-popup-window' ).removeClass( 'state-show' );
    } );

    $(window).on('load', function() {
        swiffyslider.initSlider(document.getElementById('wpuf-slider'));
    });
})(jQuery);
