import { useState, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import { filterFieldSettings } from '../../extensions/hooks';
import SettingInput from './SettingInput';

function SettingSection( { title, settings, field, defaultOpen } ) {
    const [ isOpen, setIsOpen ] = useState( defaultOpen );

    if ( ! settings.length ) {
        return null;
    }

    return (
        <div className="option-fields-section">
            <h3
                className={ `wpuf-flex wpuf-mt-0 wpuf-mb-6 wpuf-justify-between hover:wpuf-cursor-pointer wpuf-font-medium wpuf-text-lg ${ isOpen ? 'wpuf-text-primary' : 'wpuf-text-gray-500' }` }
                onClick={ () => setIsOpen( ! isOpen ) }
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && setIsOpen( ! isOpen ) }
            >
                { title }
                <i className={ `wpuf-text-2xl ${ isOpen ? 'fa fa-angle-down wpuf-text-primary' : 'fa fa-angle-right wpuf-text-gray-500' }` } />
            </h3>
            { isOpen && (
                <div className="option-field-section-fields">
                    { settings.map( ( setting ) => (
                        <SettingInput
                            key={ setting.name }
                            optionField={ setting }
                            field={ field }
                        />
                    ) ) }
                </div>
            ) }
        </div>
    );
}

export default function FieldOptionsPanel() {
    const { editingField, editingFieldConfig, fieldSettings, i18n } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            editingField: store.getEditingField(),
            editingFieldConfig: store.getEditingFieldConfig(),
            fieldSettings: store.getFieldSettings(),
            i18n: store.getI18n(),
        };
    }, [] );

    // Get settings sorted by priority
    const { basicSettings, advancedSettings, quizSettings, fieldTitle } = useMemo( () => {
        if ( ! editingField || ! editingFieldConfig ) {
            return { basicSettings: [], advancedSettings: [], quizSettings: [], fieldTitle: '' };
        }

        let settings = editingFieldConfig.settings || [];

        // Apply Pro extension filter
        settings = filterFieldSettings( settings, editingField.template );

        // Sort by priority
        settings = [ ...settings ].sort( ( a, b ) => parseInt( a.priority || 0 ) - parseInt( b.priority || 0 ) );

        return {
            basicSettings: settings.filter( ( s ) => s.section === 'basic' ),
            advancedSettings: settings.filter( ( s ) => s.section === 'advanced' ),
            quizSettings: settings.filter( ( s ) => s.section === 'quiz' ),
            fieldTitle: editingFieldConfig.title || editingField.template,
        };
    }, [ editingField, editingFieldConfig ] );

    if ( ! editingField ) {
        return (
            <div className="wpuf-form-builder-field-options">
                <div className="options-fileds-section text-center">
                    <p className="wpuf-text-gray-500 wpuf-text-lg wpuf-font-medium">
                        { i18n.empty_field_options_msg || __( 'Click on a field to edit its options.', 'wp-user-frontend' ) }
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="wpuf-form-builder-field-options">
            <SettingSection
                title={ fieldTitle }
                settings={ basicSettings }
                field={ editingField }
                defaultOpen={ true }
            />

            <SettingSection
                title={ i18n.advanced_options || __( 'Advanced Options', 'wp-user-frontend' ) }
                settings={ advancedSettings }
                field={ editingField }
                defaultOpen={ false }
            />

            <SettingSection
                title={ __( 'Quiz Options', 'wp-user-frontend' ) }
                settings={ quizSettings }
                field={ editingField }
                defaultOpen={ false }
            />
        </div>
    );
}
