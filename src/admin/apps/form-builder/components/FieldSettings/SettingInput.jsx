import { useCallback, useEffect, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../../store';
import { getFieldSettingInput } from '../../extensions/registry';
import useSettingDependency from '../../hooks/useSettingDependency';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import TextInput from './inputs/TextInput';
import TextareaInput from './inputs/TextareaInput';
import CheckboxInput from './inputs/CheckboxInput';
import SelectInput from './inputs/SelectInput';
import RadioInput from './inputs/RadioInput';
import TextMetaInput from './inputs/TextMetaInput';
import OptionDataInput from './inputs/OptionDataInput';
import MultiSelectInput from './inputs/MultiSelectInput';
import RangeInput from './inputs/RangeInput';
import VisibilityInput from './inputs/VisibilityInput';
import HtmlHelpText from './inputs/HtmlHelpText';
import IconSelectorInput from './inputs/IconSelectorInput';
import ProFeatureAlert from './inputs/ProFeatureAlert';
import FieldConditionalLogic from '../ConditionalLogic/FieldConditionalLogic';
import { revealedDefault } from '../../utils/revealedDefault';

const INPUT_MAP = {
    text: TextInput,
    number: TextInput,
    textarea: TextareaInput,
    checkbox: CheckboxInput,
    select: SelectInput,
    radio: RadioInput,
    'text-meta': TextMetaInput,
    'option-data': OptionDataInput,
    multiselect: MultiSelectInput,
    range: RangeInput,
    visibility: VisibilityInput,
    html_help_text: HtmlHelpText,
    icon_selector: IconSelectorInput,
    'option-pro-feature-alert': ProFeatureAlert,
    // Pro declares this setting (Fields_Manager::add_conditional_field); free renders it (4.4d).
    'conditional-logic': FieldConditionalLogic,
};

/**
 * Renders a single setting input based on its type.
 * Handles dependency visibility and value dispatching.
 */
export default function SettingInput( { optionField, field } ) {
    const { updateField } = useDispatch( STORE_NAME );
    const isVisible = useSettingDependency( field, optionField.dependencies );
    const { builderClassNames } = useFieldClasses( field );

    const handleChange = useCallback( ( value ) => {
        updateField( field.id, optionField.name, value );
    }, [ field.id, optionField.name, updateField ] );

    // A setting revealed by the user (Show Icon = Yes shows Field Icon and Icon
    // Position) stores the default it shows when the field has no value: a field
    // saved before the setting existed showed "Left of Label" with nothing behind
    // it, so the label icon stayed hidden until the position was changed. Opening
    // a field writes nothing: only a hidden -> shown change of the same field does.
    const shown = useRef( { id: field.id, visible: isVisible } );

    useEffect( () => {
        const revealed = shown.current.id === field.id && ! shown.current.visible && isVisible;

        shown.current = { id: field.id, visible: isVisible };

        if ( ! revealed ) {
            return;
        }

        const value = revealedDefault( optionField, field );

        if ( undefined !== value ) {
            updateField( field.id, optionField.name, value );
        }
    }, [ isVisible, field.id ] ); // eslint-disable-line react-hooks/exhaustive-deps

    if ( ! isVisible ) {
        return null;
    }

    // Check registry for custom input component (Pro extension)
    const CustomInput = getFieldSettingInput( optionField.type );

    if ( CustomInput ) {
        return (
            <CustomInput
                optionField={ optionField }
                field={ field }
                value={ field[ optionField.name ] }
                onChange={ handleChange }
                builderClassNames={ builderClassNames }
            />
        );
    }

    // Fallback to built-in input types
    const InputComponent = INPUT_MAP[ optionField.type ];

    if ( ! InputComponent ) {
        return null;
    }

    return (
        <InputComponent
            optionField={ optionField }
            field={ field }
            value={ field[ optionField.name ] }
            onChange={ handleChange }
            builderClassNames={ builderClassNames }
        />
    );
}
