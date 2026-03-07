import { useCallback } from '@wordpress/element';
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
