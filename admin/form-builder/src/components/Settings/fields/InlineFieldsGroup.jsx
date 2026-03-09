import { SETTING_CLASS_NAMES } from '../SettingsField';
import HelpTextIcon from './HelpTextIcon';

/**
 * Renders inline fields in a horizontal row — matches Vue inline_fields rendering.
 *
 * Vue structure: <div class="wpuf-mt-6 wpuf-flex wpuf-input-container">
 *   For each sub-field: <div class="wpuf-w-1/2 [wpuf-mr-6 for first]">
 *     <label> + <input :class="setting_class_names(type)" class="!wpuf-mt-2">
 */
export default function InlineFieldsGroup( { field, settings, onChange } ) {
    const subFields = field.fields || {};
    const entries = Object.entries( subFields );

    return (
        <>
            { entries.map( ( [ subName, subField ], index ) => {
                const classes = `wpuf-w-1/2${ index === 0 ? ' wpuf-mr-6' : '' }`;
                const subValue = settings[ subName ];
                const inputClasses = SETTING_CLASS_NAMES[ subField.type ] || SETTING_CLASS_NAMES.text;

                return (
                    <div key={ subName } className={ classes }>
                        { subField.label && (
                            <label htmlFor={ subName } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                                { subField.label }
                            </label>
                        ) }
                        { subField.help_text && <HelpTextIcon text={ subField.help_text } /> }
                        { ( subField.type === 'text' || subField.type === 'number' ) && (
                            <input
                                type={ subField.type }
                                id={ subName }
                                value={ subValue !== undefined && subValue !== null ? subValue : ( subField.default || '' ) }
                                onChange={ ( e ) => onChange( subName, e.target.value ) }
                                className={ `!wpuf-mt-2 ${ inputClasses }` }
                                placeholder={ subField.placeholder || '' }
                            />
                        ) }
                        { subField.type === 'date' && (
                            <input
                                type="text"
                                id={ subName }
                                value={ subValue || '' }
                                onChange={ ( e ) => onChange( subName, e.target.value ) }
                                className={ `datepicker !wpuf-mt-2 ${ SETTING_CLASS_NAMES.text }` }
                            />
                        ) }
                        { subField.type === 'select' && (
                            <select
                                id={ subName }
                                value={ subValue || subField.default || '' }
                                onChange={ ( e ) => onChange( subName, e.target.value ) }
                                className={ `!wpuf-mt-2 ${ SETTING_CLASS_NAMES.dropdown }` }
                            >
                                { Object.entries( subField.options || {} ).map( ( [ optValue, optLabel ] ) => (
                                    <option key={ optValue } value={ optValue }>
                                        { optLabel }
                                    </option>
                                ) ) }
                            </select>
                        ) }
                    </div>
                );
            } ) }
            { field.long_help && (
                <div
                    className="wpuf-text-sm wpuf-mt-4 wpuf-long-help"
                    dangerouslySetInnerHTML={ { __html: field.long_help } }
                />
            ) }
        </>
    );
}
