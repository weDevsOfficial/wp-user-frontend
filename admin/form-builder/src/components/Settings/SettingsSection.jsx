import { useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../store';
import SettingsField from './SettingsField';
import ProPreviewWrapper from './ProPreviewWrapper';
import { useFieldVisibility, MUTUAL_EXCLUSIONS } from './useFieldDependencies';

/**
 * Parse a PHP-style nested name like wpuf_settings[notification][new]
 * and return [groupKey, subKey] or null if not a nested pattern.
 */
function parseNestedName( name ) {
    if ( ! name ) {
        return null;
    }
    const match = name.match( /wpuf_settings\[(\w+)\]\[(\w+)\]/ );
    return match ? [ match[ 1 ], match[ 2 ] ] : null;
}

/**
 * Resolve a setting value, handling nested field.name patterns.
 * e.g. field.name = "wpuf_settings[notification][new]" → settings.notification.new
 */
function resolveSettingValue( settings, fieldName, fieldDef ) {
    const nested = parseNestedName( fieldDef.name );

    if ( nested ) {
        const [ group, key ] = nested;
        const groupObj = settings[ group ];

        if ( groupObj && typeof groupObj === 'object' && groupObj[ key ] !== undefined ) {
            return groupObj[ key ];
        }
    }

    if ( settings[ fieldName ] !== undefined ) {
        return settings[ fieldName ];
    }

    // Fall back to the PHP-defined default value
    return fieldDef.value;
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

    const isVisible = useFieldVisibility( resolvedSettings );

    const handleChange = useCallback( ( name, value ) => {
        // Check if this is a nested settings key (e.g. wpuf_settings[notification][new])
        const nested = parseNestedName( name );

        if ( nested ) {
            const [ group, key ] = nested;
            const currentGroup = settings[ group ] || {};
            updateFormSetting( group, { ...currentGroup, [ key ]: value } );
        } else {
            updateFormSetting( name, value );
        }

        // Handle mutual exclusivity (e.g. payment_options <-> enable_pricing_payment)
        const exclusion = MUTUAL_EXCLUSIONS[ name ];
        if ( exclusion ) {
            // If turning ON this toggle, turn OFF the other
            const isOn = value === 'on' || value === 'yes' || value === true;
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
                { sectionEntries.map( ( [ subKey, subSection ], index ) => {
                    const isFirst = index === 0;
                    const isLast = index === sectionEntries.length - 1 && sectionEntries.length > 1;

                    let classList = 'wpuf-settings-body wpuf-pb-8';
                    if ( ! isFirst ) {
                        classList = 'wpuf-settings-body wpuf-pb-8 wpuf-pt-6 wpuf-border-t wpuf-border-gray-200';
                    }
                    if ( isLast ) {
                        classList = 'wpuf-settings-body wpuf-pt-6 wpuf-border-t wpuf-border-gray-200';
                    }

                    return (
                        <div
                            key={ subKey }
                            className={ classList }
                            data-settings-body={ sectionKey }
                        >
                            { subSection.label && (
                                <p className="wpuf-text-lg wpuf-font-medium wpuf-mb-3 wpuf-mt-0 wpuf-leading-none">
                                    { subSection.label }
                                </p>
                            ) }
                            { subSection.desc && (
                                <p className="wpuf-text-gray-500 wpuf-text-[13px] wpuf-leading-5 !wpuf-mb-4 !wpuf-mt-0">
                                    { subSection.desc }
                                </p>
                            ) }
                            { subSection.fields && Object.entries( subSection.fields ).map( ( [ fieldName, fieldDef ] ) => {
                                if ( ! isVisible( fieldName ) ) {
                                    return null;
                                }

                                const settingName = fieldDef.name || fieldName;
                                const settingValue = resolveSettingValue( settings, fieldName, fieldDef );

                                return (
                                    <SettingsField
                                        key={ fieldName }
                                        field={ fieldDef }
                                        name={ settingName }
                                        value={ settingValue }
                                        onChange={ handleChange }
                                        settings={ resolvedSettings }
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
                    );
                } ) }
            </div>
        );
    }

    // Direct fields at top level (no section prop) — Vue uses wpuf-settings-body wpuf--mt-6
    return (
        <div className="wpuf-settings-section">
            <div
                className="wpuf-settings-body wpuf--mt-6"
                data-settings-body={ sectionKey }
            >
                { Object.entries( filteredData ).map( ( [ fieldName, fieldDef ] ) => {
                    if ( fieldName === 'pro_preview' ) {
                        return null;
                    }
                    if ( ! fieldDef || typeof fieldDef !== 'object' ) {
                        return null;
                    }
                    if ( ! isVisible( fieldName ) ) {
                        return null;
                    }

                    const settingName = fieldDef.name || fieldName;
                    const settingValue = resolveSettingValue( settings, fieldName, fieldDef );

                    return (
                        <SettingsField
                            key={ fieldName }
                            field={ fieldDef }
                            name={ settingName }
                            value={ settingValue }
                            onChange={ handleChange }
                            settings={ resolvedSettings }
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
