/**
 * WPUF Premium (Upgrade to Pro) page interactions.
 *
 * Annual / Lifetime pricing toggle: switches the active pill and swaps each
 * plan's amount + period suffix from data attributes.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */
( function () {
    'use strict';

    document.addEventListener( 'DOMContentLoaded', function () {
        var toggle = document.querySelector( '.wpuf-pp-toggle' );

        if ( ! toggle ) {
            return;
        }

        var buttons = toggle.querySelectorAll( 'button[data-period]' );
        var amounts = document.querySelectorAll( '.wpuf-pp-plan__amount' );
        var periods = document.querySelectorAll( '.wpuf-pp-plan__period' );

        var discounts = document.querySelectorAll( '.wpuf-pp-plan__discount' );

        function applyPeriod( period, suffix ) {
            amounts.forEach( function ( el ) {
                var value = el.getAttribute( 'data-' + period );

                if ( value ) {
                    el.textContent = value;
                }
            } );

            periods.forEach( function ( el ) {
                el.textContent = suffix;
            } );

            // Strikethrough original price + discount chip only show for periods
            // that carry discount data (lifetime).
            discounts.forEach( function ( row ) {
                var orig = row.querySelector( '.wpuf-pp-plan__orig' );
                var chip = row.querySelector( '.wpuf-pp-plan__chip' );
                var origValue = orig ? orig.getAttribute( 'data-' + period ) : '';
                var chipValue = chip ? chip.getAttribute( 'data-' + period ) : '';

                if ( origValue && chipValue ) {
                    orig.textContent = origValue;
                    chip.textContent = chipValue;
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            } );
        }

        buttons.forEach( function ( button ) {
            button.addEventListener( 'click', function () {
                buttons.forEach( function ( b ) {
                    b.classList.remove( 'is-active' );
                } );
                button.classList.add( 'is-active' );

                applyPeriod(
                    button.getAttribute( 'data-period' ),
                    button.getAttribute( 'data-suffix' ) || ''
                );
            } );
        } );
    } );
}() );
