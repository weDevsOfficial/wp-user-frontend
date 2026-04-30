/*
 * WPUF — 2FA login method-picker auto-advance
 *
 * Loaded conditionally by Login_Controller::render_2fa_field() only on
 * the picker stage. Submits the form when the user clicks / keys a
 * radio so a single interaction advances to the code-entry screen.
 *
 * Gated by a real user gesture (pointerdown / keydown) so we don't
 * submit on page load — themes / autofill / late-loaded scripts can
 * otherwise fire `change` on the pre-checked radio and trigger an
 * unwanted submit. The Continue button remains the no-JS fallback.
 */

( function () {
    'use strict';

    var radios = document.querySelectorAll( '.wpuf-2fa-method-picker__radio' );

    if ( ! radios.length ) {
        return;
    }

    var userInteracted = false;

    Array.prototype.forEach.call( radios, function ( radio ) {
        radio.addEventListener( 'pointerdown', function () {
            userInteracted = true;
        } );

        radio.addEventListener( 'keydown', function () {
            userInteracted = true;
        } );

        radio.addEventListener( 'change', function () {
            if ( ! userInteracted ) {
                return;
            }

            var form = radio.closest( 'form' );

            if ( form ) {
                form.submit();
            }
        } );
    } );
} )();
