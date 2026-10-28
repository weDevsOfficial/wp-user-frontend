/**
 * Client-side validation from the schema: required, type (email, url,
 * number), content restriction (characters or words, min or max), file
 * counts. Messages are the ones the classic form shows; the server
 * validates everything again (frontend-react-architecture.md 5.3).
 *
 * @since WPUF_SINCE
 */
import { applyFilters } from '@wordpress/hooks';
import { __, sprintf } from '@wordpress/i18n';
import { LIMIT_ERROR } from '../components/Field';
import { limitState } from '../fields/basic';

const on = ( value ) => 'yes' === value || 'on' === value || true === value || 'true' === value;

// The fields whose "- Select -" choice posts "-1" (frontend-form.js: `select` val === '-1').
const SELECT_TEMPLATES = [ 'dropdown_field', 'taxonomy', 'country_list_field', 'time_field', 'gender_field', 'multiple_select' ];

const isEmpty = ( value, field = {} ) => {
    const none = ( v ) => '' === String( v ?? '' ).trim() || ( SELECT_TEMPLATES.includes( field.template ) && '-1' === String( v ) );

    if ( Array.isArray( value ) ) {
        return 0 === value.filter( ( v ) => null !== v && undefined !== v && ! none( v ) ).length;
    }

    return null === value || undefined === value || none( value );
};

const words = ( text ) => String( text || '' ).trim().split( /\s+/ ).filter( Boolean ).length;
const chars = ( text ) => String( text || '' ).trim().length;

// frontend-form.js isValidEmail() / isValidURL(), as they are.
// eslint-disable-next-line no-control-regex
const CLASSIC_EMAIL = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
const CLASSIC_URL = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/){1}[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,20}(:[0-9]{1,20})?(\/.*)?$/;

/** The fields the classic form checks as e-mail (`data-type="email"`) and URL (`data-type="url"`). */
export const EMAIL_TEMPLATES = [ 'email_address', 'secondary_email' ];
export const URL_TEMPLATES = [ 'website_url' ];

/** frontend-form.js editorContainingShortcode(): the first protected shortcode the content uses. */
export const protectedShortcode = ( html, shortcodes ) => {
    const content = String( html || '' ).trim().toLowerCase();

    return ( Array.isArray( shortcodes ) ? shortcodes : [] ).find( ( shortcode ) => {
        try {
            return new RegExp( shortcode ).test( content );
        } catch ( e ) {
            return false;
        }
    } ) || '';
};

/** Templates whose value is a list of attachment ids. */
export const UPLOAD_TEMPLATES = [ 'image_upload', 'featured_image', 'file_upload', 'profile_photo', 'cover_photo', 'avatar' ];

/** Templates that never carry a value. */
export const PASSIVE_TEMPLATES = [ 'section_break', 'custom_html', 'column_field', 'step_start', 'action_hook', 'shortcode', 'toc' ];

/**
 * @param {Object} field Field settings.
 * @param {*}      value Current value.
 * @return {string} An error message, or ''.
 */
export function validateField( field, value, context = {} ) {
    // Server-rendered fields (captcha plugins, map, signature) keep their classic markup and
    // script: React holds no value for them, so the server and that script check them.
    if ( PASSIVE_TEMPLATES.includes( field.template ) || field.server_rendered ) {
        return '';
    }

    const required = on( field.required );

    // The classic messages: "<label> is required" / "<label> is not valid".
    const label = field.label || __( 'This field', 'wp-user-frontend' );

    if ( required && isEmpty( value, field ) ) {
        /* translators: %s: field label */
        return sprintf( __( '%s is required', 'wp-user-frontend' ), label );
    }

    if ( isEmpty( value, field ) ) {
        return '';
    }

    const text = Array.isArray( value ) ? value.join( ' ' ) : String( value );

    if ( EMAIL_TEMPLATES.includes( field.template ) && ! CLASSIC_EMAIL.test( text ) ) {
        /* translators: %s: field label */
        return sprintf( __( '%s is not valid', 'wp-user-frontend' ), label );
    }

    if ( URL_TEMPLATES.includes( field.template ) && ! CLASSIC_URL.test( text.trim() ) ) {
        /* translators: %s: field label */
        return sprintf( __( '%s is not valid', 'wp-user-frontend' ), label );
    }

    // Numbers: the input's own min / max stop a one-page form in the browser, but on a
    // multi-step form the field is hidden by the time Submit runs and the browser blocks
    // the submit without showing why. Check the range here, with the same rules as the
    // input (an empty or 0 max means no maximum).
    if ( 'numeric_text_field' === field.template && '' !== String( value ?? '' ).trim() ) {
        const number = Number( value );
        const min = '' !== ( field.min_value_field ?? '' ) ? Number( field.min_value_field ) : null;
        const max = '' !== ( field.max_value_field ?? '' ) && 0 !== Number( field.max_value_field ) ? Number( field.max_value_field ) : null;

        if ( Number.isNaN( number ) ) {
            /* translators: %s: field label */
            return sprintf( __( '%s is not valid', 'wp-user-frontend' ), label );
        }

        if ( null !== min && number < min ) {
            /* translators: 1: field label, 2: minimum */
            return sprintf( __( '%1$s must be at least %2$s', 'wp-user-frontend' ), label, min );
        }

        if ( null !== max && number > max ) {
            /* translators: 1: field label, 2: maximum */
            return sprintf( __( '%1$s must be at most %2$s', 'wp-user-frontend' ), label, max );
        }
    }

    // A rich editor using a protected shortcode (classic: "Using [shortcode] is restricted").
    if ( ( 'post_content' === field.template || ( 'textarea_field' === field.template && field.rich && 'no' !== field.rich ) ) ) {
        const found = protectedShortcode( text, context.schema?.protected_shortcodes );

        if ( found ) {
            /* translators: %shortcode% is the shortcode name */
            return __( 'Using %shortcode% is restricted', 'wp-user-frontend' ).replace( '%shortcode%', `[${ found }]` );
        }
    }

        // A broken content limit: the word-limit span shows the classic message, the row only turns red.
    if ( limitState( field, Array.isArray( value ) ? value.join( ' ' ) : value ).violated ) {
        return LIMIT_ERROR;
    }

    if ( UPLOAD_TEMPLATES.includes( field.template ) && Array.isArray( value ) ) {
        const max = Number( field.count || field.max_files || 0 );

        if ( max > 0 && value.length > max ) {
            /* translators: %d: number of files */
            return sprintf( __( 'You can upload at most %d files.', 'wp-user-frontend' ), max );
        }
    }

    return '';
}

/**
 * Validate every visible field.
 *
 * @param {Array}       fields  Fields (flat, visible ones).
 * @param {Object}      values  name => value
 * @param {Object}      [context] `{ schema }`
 * @return {Object} name => message
 */
export function validateAll( fields, values, context = {} ) {
    const errors = {};

    fields.forEach( ( field ) => {
        /**
         * Filters the message of one field's validation ('' = valid). Pro adds
         * its own rules here (password length / match, phone format).
         *
         * @param {string} message The kit's message.
         * @param {Object} field   Field settings.
         * @param {*}      value   Value.
         * @param {Object} context `{ schema, values }`.
         */
        const error = applyFilters( 'wpuf.frontend.form.validateField', validateField( field, values[ field.name ], context ), field, values[ field.name ], { ...context, values } );

        if ( error ) {
            errors[ field.name ] = error;
        }
    } );

    return errors;
}
