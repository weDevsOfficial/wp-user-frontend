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
 * The request body of a submit (create or update) or a draft.
 *
 * @param {Object}      schema  The form schema.
 * @param {Object}      values  name => value
 * @param {Set<string>} visible Names of the visible fields (hidden fields post nothing).
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
        // Hidden by conditional logic, passive, or read-only (the classic form disables those inputs, so they post nothing).
        if ( ! field.name || PASSIVE_TEMPLATES.includes( field.template ) || ! visible.has( field.name ) || 'yes' === field.read_only ) {
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

                if ( text.trim() ) {
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
