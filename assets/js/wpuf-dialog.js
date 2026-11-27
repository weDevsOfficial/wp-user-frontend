/**
 * The WPUF dialog of the classic scripts and the frontend React apps
 * (replaces SweetAlert2): a native `<dialog>` opened with showModal(), in the
 * plugin-ui dialog design (assets/css/wpuf-dialog.css). It is placed inside
 * the WPUF root that asked for it when there is one, removed when closed,
 * and resolves a promise. No dependencies.
 *
 *   wpufDialog.confirm( { title, text, html, confirmText, cancelText, tone } ) -> Promise<boolean>
 *   wpufDialog.alert( { title, text, html, okText, tone } )                   -> Promise<void>
 *   wpufDialog.toast( { title, tone, timeout } )                              -> Promise<void>
 *
 * `tone`: danger (red action, trash icon), warning, success, info.
 * `html` is shown as markup: pass server messages the routes escaped only.
 *
 * @since WPUF_SINCE
 */
( function ( window, document ) {
    'use strict';

    var ICONS = {
        danger: '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
        warning: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        success: '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    };

    var CLOSE = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';

    var seq = 0;

    function el( tag, className, text ) {
        var node = document.createElement( tag );

        if ( className ) {
            node.className = className;
        }

        if ( text ) {
            node.textContent = text;
        }

        return node;
    }

    /**
     * Where the dialog goes: the WPUF root of the focused element, else <body>.
     */
    function hostOf( root ) {
        if ( root && root.appendChild ) {
            return root;
        }

        var active = document.activeElement;
        var found = active && active.closest ? active.closest( '[data-wpuf-mount], .wpuf-frontend, .wpuf-form-add, .login-widget-container' ) : null;

        return found || document.body;
    }

    /**
     * Open a dialog.
     *
     * @param {Object}  options
     * @param {string}  [options.title]
     * @param {string}  [options.text]    Plain text.
     * @param {string}  [options.html]    Markup (escaped by the server).
     * @param {string}  [options.tone]    danger|warning|success|info
     * @param {Array}   options.buttons   [ { label, value, variant: primary|danger|outline } ]
     * @param {boolean} [options.close]   Corner close button.
     * @param {number}  [options.timeout] Close after ms (toast).
     * @param {Element} [options.root]    Container.
     * @return {Promise<*>} Value of the pressed button; false on Escape / backdrop / close
     */
    function open( options ) {
        var settings = options || {};

        ensureStyles();

        return new Promise( function ( resolve ) {
            var dialog = el( 'dialog', 'wpuf-dialog wpuf-dialog--' + ( settings.tone || 'warning' ) );
            var titleId = 'wpuf-dialog-title-' + ( ++seq );
            var descId = 'wpuf-dialog-desc-' + seq;
            var result = false;
            var header = el( 'div', 'wpuf-dialog__header' );
            var icon = ICONS[ settings.tone ];

            if ( icon ) {
                var media = el( 'span', 'wpuf-dialog__media' );

                media.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icon + '</svg>';
                header.appendChild( media );
            }

            var content = el( 'div', 'wpuf-dialog__content' );
            var heading = el( 'h2', 'wpuf-dialog__title', settings.title || settings.text || '' );

            heading.id = titleId;
            dialog.setAttribute( 'aria-labelledby', titleId );

            if ( ! settings.title && ! settings.text && settings.html ) {
                heading.className = 'wpuf-dialog__title wpuf-dialog__sr';
            }

            content.appendChild( heading );
            header.appendChild( content );

            var desc = null;

            if ( settings.title && settings.text ) {
                desc = el( 'p', 'wpuf-dialog__desc', settings.text );
            } else if ( settings.html ) {
                desc = el( 'div', 'wpuf-dialog__desc' );
                desc.innerHTML = settings.html;

                if ( ! settings.title && ! settings.text ) {
                    heading.textContent = desc.textContent;
                }
            }

            if ( desc ) {
                desc.id = descId;
                dialog.setAttribute( 'aria-describedby', descId );
                content.appendChild( desc );
            }

            dialog.appendChild( header );

            if ( settings.close ) {
                var x = el( 'button', 'wpuf-dialog__close' );

                dialog.className += ' wpuf-dialog--closable';

                x.type = 'button';
                x.setAttribute( 'aria-label', settings.closeLabel || 'Close' );
                x.innerHTML = CLOSE;
                x.addEventListener( 'click', function () {
                    dialog.close();
                } );
                dialog.appendChild( x );
            }

            var buttons = settings.buttons || [];
            var focus = null;

            if ( buttons.length ) {
                var footer = el( 'div', 'wpuf-dialog__footer' );

                buttons.forEach( function ( button ) {
                    var b = el( 'button', 'wpuf-dialog__btn wpuf-dialog__btn--' + ( button.variant || 'outline' ), button.label );

                    b.type = 'button';
                    b.addEventListener( 'click', function () {
                        result = button.value;
                        dialog.close();
                    } );
                    footer.appendChild( b );

                    // The action (the last button) has the focus unless one asks for it.
                    if ( button.focus || ! focus || ! focus.dataset.wpufFocus ) {
                        focus = b;

                        if ( button.focus ) {
                            b.dataset.wpufFocus = '1';
                        }
                    }
                } );

                dialog.appendChild( footer );
            }

            dialog.addEventListener( 'click', function ( event ) {
                if ( event.target === dialog ) {
                    dialog.close();
                }
            } );

            dialog.addEventListener( 'close', function () {
                if ( dialog.parentNode ) {
                    dialog.parentNode.removeChild( dialog );
                }

                resolve( result );
            } );

            hostOf( settings.root ).appendChild( dialog );

            if ( 'function' === typeof dialog.showModal ) {
                dialog.showModal();
            } else {
                dialog.setAttribute( 'open', '' );
            }

            if ( focus ) {
                focus.focus();
            } else {
                // A toast: focus the dialog, not its close button.
                dialog.tabIndex = -1;
                dialog.focus();
            }

            if ( settings.timeout ) {
                window.setTimeout( function () {
                    if ( dialog.open ) {
                        dialog.close();
                    }
                }, settings.timeout );
            }
        } );
    }

    function labels() {
        return window.wpufDialogL10n || {};
    }

    // The stylesheet comes with the script: WordPress prints it as #wpuf-dialog-css
    // when a screen enqueued it, otherwise it is linked on first use.
    function ensureStyles() {
        if ( document.getElementById( 'wpuf-dialog-css' ) || ! labels().css ) {
            return;
        }

        var link = document.createElement( 'link' );

        link.id = 'wpuf-dialog-css';
        link.rel = 'stylesheet';
        link.href = labels().css;
        document.head.appendChild( link );
    }

    window.wpufDialog = {
        open: open,

        confirm: function ( options ) {
            var o = options || {};
            var danger = 'danger' === ( o.tone || 'danger' );

            return open( {
                title: o.title,
                text: o.text,
                html: o.html,
                root: o.root,
                tone: o.tone || 'danger',
                buttons: [
                    // A destructive question starts on Cancel: Enter pressed by reflex keeps the data.
                    { label: o.cancelText || labels().cancel || 'Cancel', value: false, variant: 'outline', focus: danger },
                    { label: o.confirmText || labels().confirm || 'Yes', value: true, variant: danger ? 'danger' : 'primary' },
                ],
            } ).then( Boolean );
        },

        alert: function ( options ) {
            var o = options || {};

            return open( {
                title: o.title,
                text: o.text,
                html: o.html,
                root: o.root,
                tone: o.tone || 'warning',
                buttons: [ { label: o.okText || labels().ok || 'OK', value: true, variant: 'primary' } ],
            } ).then( function () {} );
        },

        toast: function ( options ) {
            var o = options || {};

            return open( {
                title: o.title,
                text: o.text,
                root: o.root,
                tone: o.tone || 'success',
                close: true,
                closeLabel: labels().close,
                timeout: o.timeout || 1500,
            } ).then( function () {} );
        },
    };
} )( window, document );
