;(function($){
    $( '.button-upgrade-to-pro' ).click( function () {
        $( '.wpuf-popup-window' ).addClass( 'state-show' );
    } );

    $( '.popup-close-button' ).click( function() {
        $( '.wpuf-popup-window' ).removeClass( 'state-show' );
    } );

    $('.wpuf-popup-window').click( function( event ) {
        if ( ! $(event.target).is( '.modal-window' ) ) {
            $( '.wpuf-popup-window' ).removeClass( 'state-show' );
        }
    } );


})(jQuery);
