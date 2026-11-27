import { useState, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { ChevronDown, ChevronRight, MousePointerClick } from 'lucide-react';
import { STORE_NAME } from '../../store';
import { filterFieldSettings } from '../../extensions/hooks';
import SettingInput from './SettingInput';
import { BUILDER_SLOTS, BuilderSlot } from '../../slots';

function SettingSection( { title, settings, field, defaultOpen, isBasic = false } ) {
    const [ isOpen, setIsOpen ] = useState( defaultOpen );

    // Develop always prints the basic section; the others only with settings.
    if ( ! isBasic && ! settings.length ) {
        return null;
    }

    return (
        <div className={ isBasic ? 'option-fields-section mt-2' : 'option-fields-section mt-2' }>
            <h3
                className={ `flex items-center mt-0 mb-4 justify-between hover:cursor-pointer font-semibold text-sm! ${ isOpen ? 'text-primary' : 'text-gray-500' }` }
                onClick={ () => setIsOpen( ! isOpen ) }
                role="button"
                tabIndex={ 0 }
                onKeyDown={ ( e ) => e.key === 'Enter' && setIsOpen( ! isOpen ) }
            >
                { title }
                { isOpen
                    ? <ChevronDown className="size-4 shrink-0 text-primary" aria-hidden="true" />
                    : <ChevronRight className="size-4 shrink-0 text-gray-500" aria-hidden="true" /> }
            </h3>
            { /* Hidden, not unmounted, when closed (develop's v-show): inputs keep their state. */ }
            <div className="option-field-section-fields" style={ isOpen ? undefined : { display: 'none' } }>
                { settings.map( ( setting ) => (
                    <SettingInput
                        key={ setting.name }
                        optionField={ setting }
                        field={ field }
                    />
                ) ) }
            </div>
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
                <div className="options-fileds-section mt-2 flex flex-col items-center rounded-[10px] border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center">
                    <span className="mb-4 flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <MousePointerClick className="size-6" aria-hidden="true" />
                    </span>
                    <p className="m-0! mb-1! text-base font-semibold text-gray-900">
                        { __( 'No field selected', 'wp-user-frontend' ) }
                    </p>
                    <p className="m-0! max-w-[220px] text-sm leading-5 text-gray-500">
                        { i18n.empty_field_options_msg || __( 'Click on a field to edit its options.', 'wp-user-frontend' ) }
                    </p>
                </div>
            </div>
        );
    }

    // Keyed by field: inputs keep local state (option rows, open sections), so a
    // different field must start fresh, as develop remounted the panel.
    return (
        <div key={ editingField.id } className="wpuf-form-builder-field-options">
            <SettingSection
                title={ fieldTitle }
                settings={ basicSettings }
                field={ editingField }
                defaultOpen={ true }
                isBasic
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

            <BuilderSlot name={ BUILDER_SLOTS.FIELD_OPTIONS_AFTER } fillProps={ { field: editingField } } />
        </div>
    );
}
