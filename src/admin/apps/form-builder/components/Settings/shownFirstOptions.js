import { orderedOptions } from './fields/settingOptions';

/**
 * Selects develop always stored with their first option (its form post sent
 * every selectize select). The frontend reads a missing value differently
 * (Label Position: left; registration / profile redirects: no message;
 * MailPoet 3 list: no list), so these show develop's first option while nothing
 * is stored and the save stores it (owner decisions 2026-10-08, QA stories 11
 * and 15). Keyed by setting path; `FIRST` = the field's own first option (lists
 * that come from the site, e.g. MailPoet lists).
 */
const FIRST = true;

const SHOWN_FIRST_OPTIONS = {
    'expiration_settings.expiration_time_type': 'day',
    'expiration_settings.expired_post_status': 'draft',
    label_position: 'above',
    choose_payment_option: 'force_pack_purchase',
    reg_redirect_to: FIRST,
    profile_redirect_to: FIRST,
    mailpoet_3_list: FIRST,
};

/**
 * First non-empty option value of a select definition, or '' when it has none.
 *
 * @param {Object} fieldDef Field definition.
 * @return {string} Option value.
 */
export function firstOptionValue( fieldDef ) {
    if ( ! fieldDef || 'select' !== fieldDef.type ) {
        return '';
    }

    const first = orderedOptions( fieldDef.options ).find( ( option ) => option && '' !== String( option.value ) );

    return first ? String( first.value ) : '';
}

/**
 * The value shown for a setting path while nothing is stored, or undefined
 * when the path is not one of SHOWN_FIRST_OPTIONS.
 *
 * @param {string} path     Setting path ('key' or 'group.key').
 * @param {Object} fieldDef Field definition.
 * @return {string|undefined} Shown value.
 */
export function shownFirstOption( path, fieldDef ) {
    const shown = SHOWN_FIRST_OPTIONS[ path ];

    if ( undefined === shown ) {
        return undefined;
    }

    return FIRST === shown ? firstOptionValue( fieldDef ) || undefined : shown;
}

/**
 * Find a setting's definition by key anywhere in the builder's settings items.
 *
 * @param {Object} items Settings items (`wpuf_form_builder.settings_items`).
 * @param {string} key   Setting key.
 * @return {Object|null} Definition.
 */
export function findSettingDef( items, key ) {
    if ( ! items || 'object' !== typeof items ) {
        return null;
    }

    for ( const [ name, value ] of Object.entries( items ) ) {
        if ( name === key && value && 'object' === typeof value && value.type ) {
            return value;
        }

        const found = findSettingDef( value, key );

        if ( found ) {
            return found;
        }
    }

    return null;
}

/**
 * Settings for the save: top-level SHOWN_FIRST_OPTIONS keys that are missing get
 * the value the screen shows (stored values stay as they are).
 *
 * @param {Object} settings Store settings.
 * @param {Object} items    Settings items (`wpuf_form_builder.settings_items`).
 * @return {Object} Settings to save.
 */
export function withShownFirstOptions( settings, items ) {
    const out = { ...( settings || {} ) };

    Object.keys( SHOWN_FIRST_OPTIONS ).forEach( ( path ) => {
        if ( path.includes( '.' ) || ( undefined !== out[ path ] && null !== out[ path ] ) ) {
            return;
        }

        const def = findSettingDef( items, path );
        const value = def ? shownFirstOption( path, def ) : undefined;

        if ( undefined !== value ) {
            out[ path ] = value;
        }
    } );

    return out;
}
