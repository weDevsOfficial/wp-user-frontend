/**
 * Form settings that must not be saved empty while they are shown: button
 * texts, the page / URL / message of each redirect and the registration
 * e-mails. An empty one gave a blank button, a redirect to nowhere or a blank
 * e-mail on the frontend.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

const REQUIRED = {
    wpuf_forms: [ 'submit_text', 'update_text', 'message', 'page_id', 'url', 'update_message', 'edit_page_id', 'edit_url' ],
    wpuf_profile: [
        'submit_text', 'update_text', 'message', 'reg_page_id', 'registration_url', 'update_message', 'profile_page_id', 'profile_url',
        'verification_subject', 'verification_body', 'welcome_email_subject', 'welcome_email_body',
    ],
};

const PAGE_KEYS = [ 'page_id', 'edit_page_id', 'reg_page_id', 'profile_page_id' ];
const URL_KEYS = [ 'url', 'edit_url', 'registration_url', 'profile_url' ];

/**
 * The required settings of a form type (filter `wpuf.formBuilder.requiredSettings`).
 *
 * @param {string} formType 'wpuf_forms' or 'wpuf_profile'.
 *
 * @return {string[]} Setting keys.
 */
export function requiredSettings( formType ) {
    return applyFilters( 'wpuf.formBuilder.requiredSettings', REQUIRED[ formType ] || [], formType );
}

/**
 * What is wrong with a required setting's value.
 *
 * @param {string} key   Setting key.
 * @param {*}      value Value.
 *
 * @return {string} '' when fine, else the message for the field.
 */
export function settingProblem( key, value ) {
    const text = null === value || undefined === value ? '' : String( value ).trim();

    if ( PAGE_KEYS.includes( key ) ) {
        return ! text || '0' === text || '-1' === text ? __( 'Choose a page.', 'wp-user-frontend' ) : '';
    }

    if ( ! text || '<p></p>' === text.replace( /\s/g, '' ) ) {
        return __( 'This field is required.', 'wp-user-frontend' );
    }

    if ( URL_KEYS.includes( key ) && ! /^https?:\/\/\S+$/i.test( text ) ) {
        return __( 'Enter a full URL starting with http:// or https://.', 'wp-user-frontend' );
    }

    return '';
}

/**
 * A setting's definition and the label of the section it sits in.
 *
 * @param {Object} items Builder settings items (tabs > sections > fields).
 * @param {string} key   Setting key.
 *
 * @return {Object|null} { def, section }
 */
export function findSettingDef( items, key ) {
    const walk = ( node, section ) => {
        if ( ! node || 'object' !== typeof node ) {
            return null;
        }

        if ( node.fields && 'object' === typeof node.fields && node.fields[ key ] ) {
            return { def: node.fields[ key ], section: node.label || section };
        }

        for ( const child of Object.values( node ) ) {
            const found = walk( child, node.label || section );

            if ( found ) {
                return found;
            }
        }

        return null;
    };

    return walk( items, '' );
}

/**
 * The required settings that are shown and wrong.
 *
 * @param {Object}   args
 * @param {Object}   args.settings  Form settings (unsaved state).
 * @param {string}   args.formType  Form type.
 * @param {Object}   args.items     Builder settings items.
 * @param {Function} args.isVisible ( key ) => boolean
 *
 * @return {Array<Object>} [ { key, label, section, problem } ]
 */
export function invalidRequiredSettings( { settings, formType, items, isVisible } ) {
    const found = [];

    requiredSettings( formType ).forEach( ( key ) => {
        const located = findSettingDef( items, key );

        // A setting this form does not offer (module off, other form type) is not required.
        if ( ! located || ! isVisible( key ) ) {
            return;
        }

        const value = Object.prototype.hasOwnProperty.call( settings, key ) ? settings[ key ] : ( located.def.value || located.def.default || '' );
        const problem = settingProblem( key, value );

        if ( problem ) {
            found.push( { key, label: located.def.label || key, section: located.section, problem } );
        }
    } );

    return found;
}

/**
 * The save dialog's message for the invalid settings (HTML, escaped).
 *
 * @param {Array<Object>} invalid invalidRequiredSettings()
 *
 * @return {string} HTML
 */
export function invalidSettingsMessage( invalid ) {
    const escape = ( text ) => String( text ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ] ) );
    const rows = invalid.map( ( item ) => '<li><b>' + escape( item.label ) + '</b>' + ( item.section ? ' (' + escape( item.section ) + ')' : '' ) + ': ' + escape( item.problem ) + '</li>' );

    return '<p>' + escape(
        /* translators: %d: number of settings */
        sprintf( __( 'Fix %d setting(s) in the Settings tab before saving:', 'wp-user-frontend' ), invalid.length )
    ) + '</p><ul style="list-style:disc;padding-left:1.25rem;margin:0">' + rows.join( '' ) + '</ul>';
}
