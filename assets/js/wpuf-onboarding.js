/**
 * Onboarding wizard behaviour.
 *
 * One file for every step. Each block looks for the elements it needs and returns
 * quietly when they are absent, so the same script is safe on all six screens.
 */
( function () {
    'use strict';

    var settings = window.wpufOnboarding || {};

    /**
     * Keep a card's selected styling in step with its checkbox.
     *
     * @param {string} selector Checkboxes to bind.
     */
    function bindCardToggles( selector ) {
        document.querySelectorAll( selector ).forEach( function ( input ) {
            if ( 'INPUT' !== input.tagName ) {
                return;
            }

            input.addEventListener( 'change', function () {
                var card = input.closest( '.wpuf-onboarding-card' );

                if ( card ) {
                    card.classList.toggle( 'is-selected', input.checked );
                }
            } );
        } );
    }

    // Step: what you need, and the companion plugins step.
    bindCardToggles( '.wpuf-onboarding-card input[name="features[]"]' );
    bindCardToggles( '.wpuf-onboarding-card input[name="plugins[]"]' );

    // Step: post form. Describe whichever template is selected.
    ( function () {
        var select = document.getElementById( 'wpuf-onboarding-template' );
        var desc   = document.getElementById( 'wpuf-onboarding-template-desc' );

        if ( ! select || ! desc ) {
            return;
        }

        function render() {
            var option = select.options[ select.selectedIndex ];

            desc.textContent = option ? ( option.getAttribute( 'data-desc' ) || '' ) : '';
        }

        select.addEventListener( 'change', render );
        render();
    } )();

    // Step: settings. The gateway picker only means anything while payments are on.
    ( function () {
        var enable  = document.getElementById( 'wpuf-onboarding-enable-payment' );
        var gateway = document.getElementById( 'wpuf-onboarding-gateway-field' );

        if ( ! enable || ! gateway ) {
            return;
        }

        function render() {
            gateway.style.display = enable.checked ? '' : 'none';
        }

        enable.addEventListener( 'change', render );
        render();

        bindCardToggles( '#wpuf-onboarding-gateway-field input[type="checkbox"]' );
    } )();

    // Step: registration. Picking a layout folds the picker away again.
    ( function () {
        var preview = document.getElementById( 'wpuf-onboarding-layout-preview' );
        var name    = document.getElementById( 'wpuf-onboarding-layout-name' );
        var layouts = document.querySelectorAll( '.wpuf-onboarding-layout input[type="radio"]' );

        if ( ! preview || ! name || ! layouts.length ) {
            return;
        }

        layouts.forEach( function ( input ) {
            input.addEventListener( 'change', function () {
                document.querySelectorAll( '.wpuf-onboarding-layout' ).forEach( function ( card ) {
                    card.classList.remove( 'is-selected' );
                } );

                var card = input.closest( '.wpuf-onboarding-layout' );

                if ( card ) {
                    card.classList.add( 'is-selected' );
                }

                preview.src      = input.getAttribute( 'data-image' );
                name.textContent = input.getAttribute( 'data-label' );

                var box = input.closest( 'details' );

                if ( box ) {
                    box.open = false;
                }
            } );
        } );
    } )();

    // Step: ready. Each footer button says where it wants to land.
    ( function () {
        var redirect = document.getElementById( 'wpuf-onboarding-redirect' );

        if ( ! redirect ) {
            return;
        }

        document.querySelectorAll( '.wpuf-onboarding-footer button[data-redirect]' ).forEach( function ( button ) {
            button.addEventListener( 'click', function () {
                redirect.value = button.getAttribute( 'data-redirect' );
            } );
        } );
    } )();

    // Step: ready. A short burst of confetti for a finished run.
    ( function () {
        var canvas = document.getElementById( 'wpuf-onboarding-confetti' );

        if ( ! canvas || ! canvas.getContext ) {
            return;
        }

        // Respect people who asked the OS for less movement.
        if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
            canvas.remove();

            return;
        }

        var ctx    = canvas.getContext( '2d' );
        var icon   = new Image();
        var pieces = [];
        var start  = null;
        var LIFE   = 4000;
        var COLORS = [ '#059669', '#34d399', '#6d28d9', '#f59e0b', '#0ea5e9', '#ec4899' ];

        function resize() {
            canvas.width  = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        function build() {
            var count = Math.min( 120, Math.round( window.innerWidth / 10 ) );

            for ( var i = 0; i < count; i++ ) {
                // Every third piece is the plugin icon, the rest is paper.
                var isIcon = i % 3 === 0;

                pieces.push( {
                    icon: isIcon,
                    color: COLORS[ Math.floor( Math.random() * COLORS.length ) ],
                    x: Math.random() * window.innerWidth,
                    y: -40 - ( Math.random() * window.innerHeight * 0.6 ),
                    size: isIcon ? 16 + Math.random() * 16 : 6 + Math.random() * 7,
                    ratio: 0.4 + Math.random() * 0.6,
                    vx: -1.2 + Math.random() * 2.4,
                    vy: 2 + Math.random() * 3.5,
                    rot: Math.random() * Math.PI * 2,
                    vr: -0.12 + Math.random() * 0.24,
                    sway: Math.random() * Math.PI * 2
                } );
            }
        }

        function frame( now ) {
            if ( ! start ) {
                start = now;
            }

            var elapsed = now - start;
            var fade    = elapsed > LIFE - 900 ? Math.max( 0, ( LIFE - elapsed ) / 900 ) : 1;

            ctx.clearRect( 0, 0, canvas.width, canvas.height );
            ctx.globalAlpha = fade;

            pieces.forEach( function ( p ) {
                p.sway += 0.03;
                p.x    += p.vx + Math.sin( p.sway ) * 0.7;
                p.y    += p.vy;
                p.rot  += p.vr;

                ctx.save();
                ctx.translate( p.x, p.y );
                ctx.rotate( p.rot );

                if ( p.icon ) {
                    // Clip to a circle so the icon falls as a round chip.
                    ctx.beginPath();
                    ctx.arc( 0, 0, p.size / 2, 0, Math.PI * 2 );
                    ctx.closePath();
                    ctx.clip();

                    ctx.fillStyle = '#ffffff';
                    ctx.fill();
                    ctx.drawImage( icon, -p.size / 2, -p.size / 2, p.size, p.size );
                } else {
                    ctx.fillStyle = p.color;
                    ctx.fillRect( -p.size / 2, -( p.size * p.ratio ) / 2, p.size, p.size * p.ratio );
                }

                ctx.restore();
            } );

            if ( elapsed < LIFE ) {
                window.requestAnimationFrame( frame );
            } else {
                canvas.remove();
            }
        }

        icon.onload = function () {
            resize();
            build();
            window.addEventListener( 'resize', resize );
            window.requestAnimationFrame( frame );
        };

        // Without an icon there is still confetti, just paper rather than chips.
        icon.onerror = function () {
            resize();
            build();
            window.addEventListener( 'resize', resize );
            window.requestAnimationFrame( frame );
        };

        icon.src = settings.confettiIcon || '';
    } )();
} )();
