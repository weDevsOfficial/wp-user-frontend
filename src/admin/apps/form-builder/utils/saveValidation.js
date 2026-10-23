/**
 * The builder's save checks (develop's refusals before a save): required
 * fields per form type and the payment cost rules. Pure, so they are tested
 * without the builder.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Check if a toggle/checkbox value is considered "on" (wpuf_is_checkbox_or_toggle_on():
 * the React toggle's `true` is stored as the string "true").
 */
export function isOn( val ) {
    return val === 'on' || val === 'yes' || val === true || val === 'true' || val === '1';
}

/**
 * Check if a field template exists in the form fields list,
 * including inside the inner fields of container fields.
 *
 * @param {Array}  formFields List of form field objects.
 * @param {Array}  templates  Template names to look for.
 * @param {RegExp} containers Container templates whose inner fields count.
 * @return {boolean}
 */
function hasFieldTemplate( formFields, templates, containers = /^(column|repeat)_field$/ ) {
    for ( const field of formFields ) {
        if ( ! field || ! field.template ) {
            continue;
        }

        if ( templates.includes( field.template ) ) {
            return true;
        }

        // Check inside column/repeat fields
        if ( containers.test( field.template ) && field.inner_fields ) {
            for ( const colKey of Object.keys( field.inner_fields ) ) {
                const innerFields = field.inner_fields[ colKey ];

                if ( Array.isArray( innerFields ) ) {
                    for ( const inner of innerFields ) {
                        if ( inner && inner.template && templates.includes( inner.template ) ) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Validate that required fields exist in the form.
 *
 * Post forms must have post_title, post_content, or post_excerpt.
 * Profile forms must have user_email.
 *
 * @param {Array}  formFields Form fields array.
 * @param {string} formType   Form type ('wpuf_forms' or 'wpuf_profile').
 * @return {string|null} Error message or null if valid.
 */
export function validateRequiredFields( formFields, formType ) {
    // Develop's messages: the builder's localized strings (markup).
    const i18n = ( window.wpuf_form_builder || {} ).i18n || {};

    if ( formType === 'wpuf_forms' ) {
        if ( ! hasFieldTemplate( formFields, [ 'post_title', 'post_content', 'post_excerpt' ] ) ) {
            return i18n.any_of_three_needed || __( 'Form must contain at least a Post Title, Post Content, or Post Excerpt field.', 'wp-user-frontend' );
        }
    }

    if ( formType === 'wpuf_profile' ) {
        // Develop's registration check looked inside column fields only.
        if ( ! hasFieldTemplate( formFields, [ 'user_email' ], /^column_field$/ ) ) {
            return i18n.email_needed || __( 'Form must contain a User Email field.', 'wp-user-frontend' );
        }
    }

    return null;
}

/**
 * Validate payment settings before save.
 *
 * @param {Object} settings Form settings.
 * @return {string|null} Error message or null if valid.
 */
export function validatePaymentSettings( settings ) {
    if ( ! isOn( settings.payment_options ) ) {
        return null;
    }

    const paymentOption = settings.choose_payment_option;

    if ( paymentOption === 'force_pack_purchase' && isOn( settings.fallback_ppp_enable ) ) {
        const cost = parseFloat( settings.fallback_ppp_cost );

        if ( ! cost || cost <= 0 ) {
            return __( 'Cost for each additional post after pack limit is reached is required when Pay-per-post billing when limit exceeds is enabled.', 'wp-user-frontend' );
        }
    }

    if ( paymentOption === 'enable_pay_per_post' ) {
        const cost = parseFloat( settings.pay_per_post_cost );

        if ( ! cost || cost <= 0 ) {
            return __( 'Charge for each post is required when Pay as you post is selected.', 'wp-user-frontend' );
        }
    }

    return null;
}

/**
 * Custom fields need a meta key, and two fields of one form must not share one
 * (the second overwrites the first's value; keys match without case). Fields of a repeat field are their
 * own group; column fields belong to the form.
 *
 * @since WPUF_SINCE
 *
 * @param {Array} formFields Form fields.
 * @return {string|null} Error message (HTML, escaped) or null if valid.
 */
export function validateFieldMetaKeys( formFields ) {
    const escape = ( text ) => String( text ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ] ) );
    const missing = [];
    const groups = { form: {} };

    const collect = ( fields, group ) => {
        ( Array.isArray( fields ) ? fields : [] ).forEach( ( field ) => {
            if ( ! field || 'object' !== typeof field ) {
                return;
            }

            if ( field.inner_fields && 'object' === typeof field.inner_fields ) {
                const inner = Array.isArray( field.inner_fields ) ? [ field.inner_fields ] : Object.values( field.inner_fields );
                const innerGroup = 'repeat_field' === field.template ? 'repeat:' + ( field.name || field.id ) : group;

                groups[ innerGroup ] = groups[ innerGroup ] || {};
                inner.forEach( ( list ) => collect( list, innerGroup ) );
            }

            if ( 'yes' !== field.is_meta ) {
                return;
            }

            const name = String( field.name || '' ).trim();
            const label = field.label || field.template || __( 'A field', 'wp-user-frontend' );

            if ( ! name ) {
                missing.push( label );
                return;
            }

            // WordPress matches meta keys without case (the meta_key column's collation).
            ( groups[ group ][ name.toLowerCase() ] = groups[ group ][ name.toLowerCase() ] || [] ).push( label );
        } );
    };

    collect( formFields, 'form' );

    const rows = missing.map( ( label ) => '<li>' + escape(
        /* translators: %s: field label */
        sprintf( __( '%s has no meta key.', 'wp-user-frontend' ), label )
    ) + '</li>' );

    Object.values( groups ).forEach( ( names ) => Object.entries( names ).forEach( ( [ name, labels ] ) => {
        if ( labels.length > 1 ) {
            rows.push( '<li>' + escape(
                /* translators: 1: field labels, 2: meta key */
                sprintf( __( '%1$s use the same meta key "%2$s".', 'wp-user-frontend' ), labels.join( ', ' ), name )
            ) + '</li>' );
        }
    } ) );

    if ( ! rows.length ) {
        return null;
    }

    return '<p>' + escape( __( 'Each custom field needs its own meta key (field options, Meta Key):', 'wp-user-frontend' ) ) + '</p><ul style="list-style:disc;padding-left:1.25rem;margin:0">' + rows.join( '' ) + '</ul>';
}

/**
 * Warnings for option values that would lose options when saved (the field
 * stores its options keyed by value).
 *
 * @since WPUF_SINCE
 *
 * @param {Array<Object>} options [ { label, value } ]
 * @return {string[]} Messages.
 */
export function optionProblems( options ) {
    const problems = [];
    const seen = new Set();
    const repeated = new Set();

    options.forEach( ( opt ) => {
        const value = String( opt.value ?? '' ).trim();

        if ( seen.has( value ) ) {
            repeated.add( value );
        }

        seen.add( value );
    } );

    if ( seen.has( '' ) ) {
        problems.push( __( 'An option has no value. Give every option a value: options without one are not saved properly.', 'wp-user-frontend' ) );
    }

    repeated.forEach( ( value ) => {
        if ( '' !== value ) {
            /* translators: %s: option value */
            problems.push( sprintf( __( 'Options share the value "%s": only the last one is kept when you save. Give each option its own value.', 'wp-user-frontend' ), value ) );
        }
    } );

    return problems;
}
