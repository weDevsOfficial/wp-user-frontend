import { useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../store';
import SettingsField from './SettingsField';
import { orderedOptions } from './fields/settingOptions';
import ProPreviewWrapper from './ProPreviewWrapper';
import { useFieldVisibility, MUTUAL_EXCLUSIONS } from './useFieldDependencies';
import { shownFirstOption } from './shownFirstOptions';
import { builderData } from '../../utils/boot';


/**
 * The input name a settings row posts (develop's views).
 *
 * @param {string} fieldName Field key.
 * @param {Object} fieldDef  Field definition.
 * @return {string} Input name.
 */
function settingName( fieldName, fieldDef ) {
    // Develop's rich-text bodies are wp_editor()s posting
    // wpuf_settings[notification][<key>] even when the definition has no name
    // (Welcome Email Body).
    if ( fieldDef && ! fieldDef.name && 'rich-text' === fieldDef.type ) {
        return `wpuf_settings[notification][${ fieldName }]`;
    }

    return ( fieldDef && fieldDef.name ) || fieldName;
}

/**
 * Where a setting is stored, from its input `name` as PHP parses the posted
 * form (develop): `wpuf_settings[key]` -> [ key ], `wpuf_settings[group][key]`
 * -> [ group, key ]; any other name is the key itself.
 *
 * @param {string} name Input name (`field.name`) or the field key.
 * @return {Array<string>} [ key ] or [ group, key ].
 */
function settingPath( name ) {
    const match = /^wpuf_settings\[(\w+)\](?:\[(\w+)\])?(?:\[\])?$/.exec( name || '' );

    if ( ! match ) {
        return [ name ];
    }

    return match[ 2 ] ? [ match[ 1 ], match[ 2 ] ] : [ match[ 1 ] ];
}

/**
 * The value a settings row shows (develop's wpuf_render_settings_field): the
 * stored value when it is set (`''` included), else the field's `value`, else
 * its `default`, else `''`. A key switched off in this session (set to
 * undefined, left out of the save) shows as off, not as its default.
 *
 * @param {Object} settings  Store settings.
 * @param {string} fieldName Field key.
 * @param {Object} fieldDef  Field definition.
 * @return {*} Value to show.
 */
export function resolveSettingValue( settings, fieldName, fieldDef ) {
    const path = settingPath( settingName( fieldName, fieldDef ) );
    const owner = 2 === path.length ? settings[ path[ 0 ] ] : settings;
    const key = path[ path.length - 1 ];

    if ( owner && 'object' === typeof owner && Object.prototype.hasOwnProperty.call( owner, key ) && null !== owner[ key ] ) {
        return owner[ key ];
    }

    // Registration "Required Approval After Registration": the frontend reads
    // `wpuf_user_status`; forms that only have it (registration templates store
    // 'pending') show the toggle on (develop showed it off and rewrote it).
    if ( 'user_status' === fieldName && settings && 'pending' === settings.wpuf_user_status ) {
        return 'on';
    }

    // Selects develop always stored with their first option (Day(s), Draft,
    // Above Element, Mandatory Subscription, Same page, the first MailPoet
    // list): show that, not "- Select -" (shownFirstOptions.js).
    const shownFirst = shownFirstOption( path.join( '.' ), fieldDef );

    if ( shownFirst ) {
        return shownFirst;
    }

    return fieldDef.value || fieldDef.default || '';
}

/**
 * Renders a settings section content area — mirrors post-form-settings.php Vue template.
 *
 * Handles conditional field visibility via useFieldVisibility hook,
 * mirroring Vue's FormDependencyHandler class.
 */
export default function SettingsSection( { sectionKey, sectionData } ) {
    const settings = useSelect( ( select ) => select( STORE_NAME ).getSettings(), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );

    // Build a merged settings view that includes flattened nested values
    // so field dependencies (e.g. new_to depends on "new") can resolve correctly.
    const resolvedSettings = useMemo( () => {
        const merged = { ...settings };

        for ( const key of Object.keys( settings ) ) {
            const val = settings[ key ];

            if ( val && typeof val === 'object' && ! Array.isArray( val ) ) {
                for ( const subKey of Object.keys( val ) ) {
                    if ( merged[ subKey ] === undefined ) {
                        merged[ subKey ] = val[ subKey ];
                    }
                }
            }
        }

        return merged;
    }, [ settings ] );

    // Conditions read the value a row shows: a key never stored counts as its
    // default (develop's DOM select showed it, e.g. "Same page" revealing the
    // success message); a key switched off in this session stays off.
    const visibilitySettings = useMemo( () => {
        const merged = { ...resolvedSettings };
        const visit = ( fields ) => Object.entries( fields || {} ).forEach( ( [ key, def ] ) => {
            if ( ! def || 'object' !== typeof def ) {
                return;
            }
            if ( def.fields && ( 'inline_fields' === def.type || ! def.type ) ) {
                visit( def.fields );
                return;
            }
            const path = settingPath( settingName( key, def ) );
            const flatKey = path[ path.length - 1 ];
            if ( ! Object.prototype.hasOwnProperty.call( merged, flatKey ) && def.type ) {
                let shown = resolveSettingValue( settings, key, def );
                // A select with nothing stored and no default: develop's DOM select
                // stood on its first option (the branch shows "- Select -", Q6).
                if ( '' === shown && 'select' === def.type ) {
                    const first = orderedOptions( def.options )[ 0 ];
                    shown = first ? first.value : '';
                }
                merged[ flatKey ] = shown;
            }
        } );
        const data = sectionData || {};
        if ( data.section ) {
            Object.values( data.section ).forEach( ( sub ) => visit( sub && sub.fields ) );
        } else {
            visit( data );
        }
        return merged;
    }, [ resolvedSettings, settings, sectionData ] );

    const isFieldVisible = useFieldVisibility( visibilitySettings );

    // An inline group (guest Name / E-Mail labels, schedule From / To) shares
    // one row: develop hid that row whenever a sub-field with a rule failed
    // (sub-fields without a rule never touch it).
    const isVisible = ( fieldName, fieldDef ) => {
        if ( ! isFieldVisible( fieldName ) ) {
            return false;
        }

        const inner = fieldDef && ( 'inline_fields' === fieldDef.type || ( ! fieldDef.type && fieldDef.fields ) ) ? Object.keys( fieldDef.fields || {} ) : [];

        return inner.every( ( key ) => isFieldVisible( key ) );
    };

    // Stores under the key the input name points at (develop: PHP parses
    // `wpuf_settings[key]` / `wpuf_settings[group][key]` from the posted form).
    const handleChange = useCallback( ( name, value ) => {
        const path = settingPath( name );
        const key = path[ path.length - 1 ];

        if ( 2 === path.length ) {
            const currentGroup = settings[ path[ 0 ] ] || {};
            updateFormSetting( path[ 0 ], { ...currentGroup, [ key ]: value } );
        } else {
            updateFormSetting( key, value );
        }

        // Develop's hidden `wpuf_user_status` followed this toggle (pending when
        // approval is required, else approved); written on a change only (Q6).
        if ( 'user_status' === key && 'wpuf_profile' === builderData().form_type ) {
            updateFormSetting( 'wpuf_user_status', 'on' === value ? 'pending' : 'approved' );
        }

        // Handle mutual exclusivity (e.g. payment_options <-> enable_pricing_payment)
        const exclusion = MUTUAL_EXCLUSIONS[ key ];
        if ( exclusion ) {
            // If turning ON this toggle, turn OFF the other
            const isOn = value === 'on' || value === 'yes' || value === true || value === 'true';
            if ( isOn ) {
                updateFormSetting( exclusion, 'off' );
            }
        }
    }, [ updateFormSetting, settings ] );

    if ( ! sectionData ) {
        return null;
    }

    // Apply wp.hooks filter for Pro extensions to inject/modify fields
    const filteredData = applyFilters( 'wpuf.formBuilder.settingsFields', sectionData, sectionKey );

    // Check if section has sub-sections (section.before_post_settings, etc.)
    if ( filteredData.section ) {
        const sectionEntries = Object.entries( filteredData.section );

        return (
            <div className="wpuf-settings-section">
                { sectionEntries.map( ( [ subKey, subSection ] ) => {
                    // One card per sub-section: header strip + body (FlyHR settings).
                    const classList = 'wpuf-settings-body mb-6 rounded-[10px] border border-gray-200 bg-white shadow-sm';

                    return (
                        <div
                            key={ subKey }
                            className={ classList }
                            data-settings-body={ sectionKey }
                        >
                            { ( subSection.label || subSection.desc ) && (
                                <div className="border-0 border-b border-solid border-gray-200 px-6 py-4">
                                    { subSection.label && (
                                        <p className="text-base font-semibold text-gray-900 m-0 leading-6">
                                            { subSection.label }
                                        </p>
                                    ) }
                                    { subSection.desc && (
                                        <p className="text-gray-500 text-sm leading-5 mb-0! mt-1!">
                                            { subSection.desc }
                                        </p>
                                    ) }
                                </div>
                            ) }
                            <div className="px-6 pb-6 [&>.wpuf-input-container:first-child]:mt-6">
                                { subSection.fields && Object.entries( subSection.fields ).map( ( [ fieldName, fieldDef ] ) => {
                                    const inputName = settingName( fieldName, fieldDef );
                                    const settingValue = resolveSettingValue( settings, fieldName, fieldDef );

                                    return (
                                        <SettingsField
                                            key={ fieldName }
                                            slotKey={ fieldName }
                                            hideControl={ ! isVisible( fieldName, fieldDef ) }
                                            field={ fieldDef }
                                            name={ inputName }
                                            value={ settingValue }
                                            onChange={ handleChange }
                                            settings={ resolvedSettings }
                                            resolveValue={ ( subName, subDef ) => resolveSettingValue( settings, subName, subDef ) }
                                        />
                                    );
                                } ) }
                                { subSection.pro_preview && (
                                    <ProPreviewWrapper
                                        proPreview={ subSection.pro_preview }
                                        onChange={ handleChange }
                                        settings={ settings }
                                    />
                                ) }
                            </div>
                        </div>
                    );
                } ) }
            </div>
        );
    }

    // Direct fields at top level (no section prop) — Vue uses wpuf-settings-body -mt-6
    return (
        <div className="wpuf-settings-section">
            <div
                className="wpuf-settings-body mb-6 rounded-[10px] border border-gray-200 bg-white px-6 pb-6 shadow-sm"
                data-settings-body={ sectionKey }
            >
                { Object.entries( filteredData ).map( ( [ fieldName, fieldDef ] ) => {
                    if ( fieldName === 'pro_preview' ) {
                        return null;
                    }
                    if ( ! fieldDef || typeof fieldDef !== 'object' ) {
                        return null;
                    }
                    const inputName = settingName( fieldName, fieldDef );
                    const settingValue = resolveSettingValue( settings, fieldName, fieldDef );

                    return (
                        <SettingsField
                            key={ fieldName }
                            slotKey={ fieldName }
                            hideControl={ ! isVisible( fieldName, fieldDef ) }
                            field={ fieldDef }
                            name={ inputName }
                            value={ settingValue }
                            onChange={ handleChange }
                            settings={ resolvedSettings }
                            resolveValue={ ( subName, subDef ) => resolveSettingValue( settings, subName, subDef ) }
                        />
                    );
                } ) }
                { filteredData.pro_preview && (
                    <ProPreviewWrapper
                        proPreview={ filteredData.pro_preview }
                        onChange={ handleChange }
                        settings={ settings }
                    />
                ) }
            </div>
        </div>
    );
}
