/**
 * The Dokan vendor registration behaviour of the classic form (frontend-form.js),
 * for a form with `dokan_store_name` and `shopurl` fields: leaving the shop name
 * fills the shop URL with its slug and moves the focus there, the shop URL takes
 * letters, digits and dashes only, `#url-alart` (in the field's help) follows the
 * slug, and leaving the shop URL asks Dokan whether it is free (`shop_url`).
 *
 * @since WPUF_SINCE
 */
import { useEffect } from '@wordpress/element';

/** frontend-form.js: lower case, dashes collapsed, spaces to dashes, nothing else. */
export const shopSlug = ( text ) => String( text || '' ).toLowerCase().replace( /-+/g, '' ).replace( /\s+/g, '-' ).replace( /[^a-z0-9-]/g, '' );

// The keys the classic keydown filter lets through besides letters and digits.
const ALLOWED = [ 46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190 ];

/**
 * @param {Object}      schema   Form schema.
 * @param {Function}    setValue Form value setter (name, value).
 * @param {HTMLElement} root     The form root.
 */
export default function useDokanShopUrl( schema, setValue, root ) {
    const names = ( schema.fields || [] ).map( ( field ) => field.name );
    const active = names.includes( 'dokan_store_name' ) && names.includes( 'shopurl' );

    useEffect( () => {
        if ( ! active || ! root ) {
            return undefined;
        }

        const alert = () => root.querySelector( '#url-alart' );
        const message = () => root.querySelector( '#url-alart-mgs' );
        const isShop = ( target ) => target && 'shopurl' === target.name;

        const onFocusOut = ( event ) => {
            const target = event.target;

            if ( target && 'dokan_store_name' === target.name ) {
                const slug = shopSlug( target.value );
                const shop = root.querySelector( 'input[name="shopurl"]' );

                setValue( 'shopurl', slug );

                if ( alert() ) {
                    alert().textContent = slug;
                }

                if ( shop ) {
                    window.setTimeout( () => shop.focus(), 0 );
                }

                return;
            }

            if ( ! isShop( target ) || '' === target.value ) {
                return;
            }

            const dokan = window.dokan;

            if ( ! dokan || ! dokan.ajaxurl ) {
                return;
            }

            const body = new window.URLSearchParams( { action: 'shop_url', url_slug: target.value, _nonce: dokan.nonce || '' } );

            window.fetch( dokan.ajaxurl, { method: 'POST', credentials: 'same-origin', body } )
                .then( ( response ) => response.text() )
                .then( ( text ) => {
                    const free = '0' !== String( text ).trim();
                    const seller = dokan.seller || {};

                    [ alert(), message() ].forEach( ( element ) => {
                        if ( element ) {
                            element.classList.toggle( 'text-success', free );
                            element.classList.toggle( 'text-danger', ! free );
                        }
                    } );

                    if ( message() ) {
                        message().textContent = free ? seller.available || '' : seller.notAvailable || '';
                    }
                } )
                .catch( () => {} );
        };

        const onKeyDown = ( event ) => {
            if ( ! isShop( event.target ) ) {
                return;
            }

            const code = event.keyCode;

            if ( ALLOWED.includes( code ) || ( 65 === code && event.ctrlKey ) || ( code >= 35 && code <= 39 ) ) {
                return;
            }

            if ( ( event.shiftKey || ( ( code < 65 || code > 90 ) && ( code < 48 || code > 57 ) ) ) && ( code < 96 || code > 105 ) ) {
                event.preventDefault();
            }
        };

        const onKeyUp = ( event ) => {
            if ( isShop( event.target ) && alert() ) {
                alert().textContent = event.target.value;
            }
        };

        root.addEventListener( 'focusout', onFocusOut );
        root.addEventListener( 'keydown', onKeyDown );
        root.addEventListener( 'keyup', onKeyUp );

        return () => {
            root.removeEventListener( 'focusout', onFocusOut );
            root.removeEventListener( 'keydown', onKeyDown );
            root.removeEventListener( 'keyup', onKeyUp );
        };
    }, [ active, root, setValue ] );
}
