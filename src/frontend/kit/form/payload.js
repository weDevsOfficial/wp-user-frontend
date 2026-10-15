/**
 * What a submit sends and what happens after it (frontend-react-architecture.md 6):
 * the same keys the classic form posts, the same JSON answer handled the
 * same way (message in place of the form, or a redirect).
 *
 * @since WPUF_SINCE
 */
import { applyFilters, doAction } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { UPLOAD_TEMPLATES, PASSIVE_TEMPLATES } from './validation';

/**
 * What a field hidden by conditional logic posts in the classic form, where
 * the row is hidden but its inputs still post. Most fields post their form
 * value through the usual path (null); an address
 * posts its parts as its inputs hold them (stored or default value, "-1" on
 * an empty country / state select) and a server-rendered field its stored
 * value (its markup is not on the page while hidden).
 *
 * @since WPUF_SINCE
 *
 * @param {Object} field Field.
 * @param {*}      value Form value.
 * @return {*} The value to post, null for the usual path, undefined for nothing.
 */
export function hiddenValue( field, value ) {
    if ( 'address_field' === field.template ) {
        const current = value && 'object' === typeof value ? value : {};
        const out = {};

        Object.entries( field.address || {} ).forEach( ( [ key, sub ] ) => {
            if ( ! sub || ! sub.checked ) {
                return;
            }

            const part = current[ key ] ?? sub.value ?? '';
            const asInput = [ 'text', 'hidden', 'email', 'password' ].includes( sub.type );
            const select = ( 'country_select' === key || 'state' === key ) && ! asInput;

            out[ key ] = select && '' === String( part ) ? '-1' : String( part );
        } );

        return out;
    }

    if ( field.server_rendered ) {
        return undefined === field.value || null === field.value ? undefined : field.value;
    }

    return null;
}

/**
 * The request body of a submit (create or update) or a draft.
 *
 * @param {Object}      schema  The form schema.
 * @param {Object}      values  name => value
 * @param {Set<string>} visible Names of the visible fields (hidden ones post what they hold, see hiddenValue()).
 * @param {Array}       fields  Flat list of the fields.
 * @param {Object}      extra   Extra keys (captcha tokens, guest fields, is_featured_item).
 * @return {Object} Body
 */
export function buildPayload( schema, values, visible, fields, extra = {} ) {
    const body = {
        form_id: schema.id,
        page_id: schema.page_id || 0,
        wpuf_nonce: schema.nonces?.submit || '',
        wpuf_files: {},
        delete_attachments: extra.delete_attachments || [],
    };

    if ( 'edit' === schema.mode && schema.post ) {
        body.post_id = schema.post.id;
        body.post_date = schema.post.date;
        body.comment_status = schema.post.comment_status;
        body.post_author = schema.post.author;
    }

    fields.forEach( ( field ) => {
        // Passive or read-only: the classic form disables those inputs, so they post nothing.
        if ( ! field.name || PASSIVE_TEMPLATES.includes( field.template ) || 'yes' === field.read_only ) {
            return;
        }

        // A field hidden by conditional logic posts what it holds, as the classic form
        // (it only hides the row; its inputs stay enabled). The server stores '' for a
        // meta field that posts nothing, so leaving it out would empty a saved value
        // on edit. It is not rendered here: no input of its own posts it.
        if ( ! visible.has( field.name ) ) {
            const held = hiddenValue( field, values[ field.name ] );

            if ( undefined === held ) {
                return;
            }

            if ( 'address_field' === field.template || field.server_rendered ) {
                body[ field.name ] = held;

                return;
            }
        }

        // A server-rendered field (map, signature, QR code, hook output) posts through its own
        // inputs, which mergeFormInputs() adds as the classic form posted them; a key here
        // would shadow them.
        if ( field.server_rendered ) {
            return;
        }

        const value = values[ field.name ];

        if ( UPLOAD_TEMPLATES.includes( field.template ) ) {
            body.wpuf_files[ field.name ] = ( value || [] ).map( String );

            return;
        }

        if ( 'taxonomy' === field.template ) {
            // Text / ajax: the comma separated names, as the classic input posts them.
            if ( 'text' === field.type || 'ajax' === field.type ) {
                const text = Array.isArray( value ) ? value.join( ', ' ) : String( value || '' );

                // The classic text input always posts (empty clears the terms on edit);
                // the ajax selects post their own levels.
                if ( text.trim() || 'text' === field.type ) {
                    body[ field.name ] = text;
                }

                return;
            }

            const list = ( Array.isArray( value ) ? value : ( value ? [ value ] : [] ) ).filter( ( v ) => '' !== String( v ) && '-1' !== String( v ) );

            // A single select posts one value (`category=12`, `-1` for "- select -"), as the
            // classic `<select name="category">` does; woo_attribute() reads it as a string.
            if ( 'select' === ( field.type || 'select' ) ) {
                body[ field.name ] = list.length ? String( list[ 0 ] ) : '-1';

                return;
            }

            if ( list.length ) {
                body[ field.name ] = list;
            } else if ( 'checkbox' === field.type ) {
                // The classic checklist carries a hidden `name=0`: nothing ticked posts 0,
                // which clears the terms (an edit that unticks everything).
                body[ field.name ] = '0';
            }

            return;
        }

        if ( 'post_tags' === field.template ) {
            body.tags = value || '';

            return;
        }

        // An empty choice list posts nothing, as an unchecked checkbox group or
        // an untouched multiselect posts nothing from the classic form; the
        // legacy meta code expects a string there.
        if ( Array.isArray( value ) ) {
            if ( value.length ) {
                body[ field.name ] = value;
            }

            return;
        }

        // An untouched address has no value yet: its inputs post as they stand
        // (`name[street_address]` ... `name[country_select]=-1`), as the classic
        // form posted them (mergeFormInputs() adds them), not one empty string.
        if ( 'address_field' === field.template && ( undefined === value || null === value || '' === value ) ) {
            return;
        }

        body[ field.name ] = undefined === value || null === value ? '' : value;
    } );

    Object.entries( extra ).forEach( ( [ key, value ] ) => {
        if ( 'delete_attachments' !== key ) {
            body[ key ] = value;
        }
    } );

    /**
     * Filters the body of a frontend form submit before it is sent.
     *
     * @param {Object} body   Body
     * @param {Object} schema Schema
     * @param {Object} values Values
     */
    return applyFilters( 'wpuf.frontend.form.payload', body, schema, values );
}

/**
 * The classic response handling: `show_message` replaces the form with the
 * notice and scrolls to it; otherwise the browser follows `redirect_to`.
 *
 * @param {Object}   response The JSON the routes answer (success, redirect_to, show_message, message).
 * @param {Object}   schema   Schema
 * @param {Function} showMessage Called with the message HTML when the form is replaced.
 */
export function afterSubmit( response, schema, showMessage ) {
    doAction( 'wpuf.frontend.form.submitted', response, schema );

    if ( window.jQuery ) {
        window.jQuery( 'body' ).trigger( 'wpuf:postform:success', response );
    }

    if ( response.show_message ) {
        showMessage( response.message || __( 'Thank you.', 'wp-user-frontend' ) );

        return;
    }

    // The classic form sets `window.location = redirect_to`: an empty target reloads the page.
    window.location.assign( response.redirect_to || window.location.href );
}
