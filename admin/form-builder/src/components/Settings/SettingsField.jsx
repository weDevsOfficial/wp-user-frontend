import ToggleField from './fields/ToggleField';
import TextField from './fields/TextField';
import NumberField from './fields/NumberField';
import TextareaField from './fields/TextareaField';
import SelectField from './fields/SelectField';
import MultiSelectField from './fields/MultiSelectField';
import CheckboxField from './fields/CheckboxField';
import TrailingTextField from './fields/TrailingTextField';
import DateField from './fields/DateField';
import ColorPickerField from './fields/ColorPickerField';
import PicRadioField from './fields/PicRadioField';
import InlineFieldsGroup from './fields/InlineFieldsGroup';
import SubmitConditionalLogic from '../ConditionalLogic/SubmitConditionalLogic';

const FIELD_MAP = {
    toggle: ToggleField,
    text: TextField,
    number: NumberField,
    textarea: TextareaField,
    select: SelectField,
    'multi-select': MultiSelectField,
    checkbox: CheckboxField,
    'trailing-text': TrailingTextField,
    date: DateField,
    'color-picker': ColorPickerField,
    'pic-radio': PicRadioField,
};

/**
 * Vue setting_class_names() equivalents.
 * Matches admin/form-builder/assets/js/form-builder.js:1036
 */
export const SETTING_CLASS_NAMES = {
    text: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    number: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    textarea: 'wpuf-block wpuf-min-w-full wpuf-my-0 wpuf-mb-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
    dropdown: 'wpuf-block wpuf-w-full wpuf-min-w-full wpuf-text-gray-700 wpuf-font-normal !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 !wpuf-text-base !leading-6',
    checkbox: '!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded',
};

/**
 * Dispatches to the appropriate field component based on field type.
 *
 * Wraps each field in Vue's `wpuf-mt-6 wpuf-input-container` div
 * matching the wpuf_render_settings_field() PHP function.
 */
export default function SettingsField( { field, name, value, onChange, settings } ) {
    // inline_fields is a special container type — Vue uses wpuf-mt-6 wpuf-flex wpuf-input-container
    if ( field.type === 'inline_fields' || ( ! field.type && field.fields ) ) {
        return (
            <div className="wpuf-mt-6 wpuf-flex wpuf-input-container">
                <InlineFieldsGroup
                    field={ field }
                    settings={ settings || {} }
                    onChange={ onChange }
                />
            </div>
        );
    }

    // submit-button-conditional-logics is a special Vue component — render React equivalent
    if ( field.type === 'submit-button-conditional-logics' ) {
        return (
            <div className="wpuf-mt-6 wpuf-input-container">
                <SubmitConditionalLogic label={ field.label } />
            </div>
        );
    }

    const FieldComponent = FIELD_MAP[ field.type ];

    if ( ! FieldComponent ) {
        return null;
    }

    // Vue wraps every field in <div class="wpuf-mt-6 wpuf-input-container">
    return (
        <div className="wpuf-mt-6 wpuf-input-container">
            <FieldComponent
                field={ field }
                name={ name }
                value={ value }
                onChange={ onChange }
            />
        </div>
    );
}
