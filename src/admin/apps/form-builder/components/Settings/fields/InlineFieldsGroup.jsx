import { RawHTML } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { NumberInput, Select, TextInput } from '@wpuf/components';
import { HelpTextIcon } from '@wpuf/components';
import { hasEmptyOption, orderedOptions } from './settingOptions';
import { DateInput } from './DateField';

/**
 * Settings shown side by side (develop `inline_fields`: e.g. schedule start /
 * end, expiration time value / unit), on the shared wrappers (4.4e). Each
 * sub-field reads and writes its own key: `subField.name`
 * (`wpuf_settings[group][key]`) when it has one, else its key.
 *
 * @param {Object}   props
 * @param {Object}   props.field        The inline_fields definition.
 * @param {Function} props.resolveValue ( subKey, subField ) => shown value.
 * @param {Function} props.onChange     ( name, value ) => void
 */
export default function InlineFieldsGroup( { field, resolveValue, onChange } ) {
    const entries = Object.entries( field.fields || {} );

    return (
        <>
            { entries.map( ( [ subKey, subField ], index ) => {
                const name = subField.name || subKey;
                const value = resolveValue ? resolveValue( subKey, subField ) : '';
                const change = ( next ) => onChange( name, next );

                return (
                    <div key={ subKey } className={ `w-1/2${ 0 === index ? ' mr-6' : '' }` }>
                        { subField.label && (
                            <label htmlFor={ subKey } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                                { subField.label }
                            </label>
                        ) }
                        { subField.help_text && <HelpTextIcon text={ subField.help_text } /> }
                        <div className="mt-2">
                            { 'text' === subField.type && (
                                <TextInput id={ subKey } className="w-full" value={ value ?? '' } onChange={ change } placeholder={ subField.placeholder || '' } />
                            ) }
                            { 'number' === subField.type && (
                                <NumberInput id={ subKey } className="w-full" value={ value ?? '' } onChange={ change } allowNegative />
                            ) }
                            { 'date' === subField.type && <DateInput id={ subKey } value={ value } onChange={ change } /> }
                            { 'select' === subField.type && (
                                <Select
                                    id={ subKey }
                                    options={ orderedOptions( subField.options ) }
                                    value={ '' === value && ! hasEmptyOption( subField.options ) ? undefined : value }
                                    placeholder={ __( '- Select -', 'wp-user-frontend' ) }
                                    onChange={ change }
                                />
                            ) }
                        </div>
                    </div>
                );
            } ) }
            { field.long_help && <RawHTML className="text-sm mt-4 wpuf-long-help">{ field.long_help }</RawHTML> }
        </>
    );
}
