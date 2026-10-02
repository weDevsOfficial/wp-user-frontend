import { useCallback } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from '../SettingsField';
import HelpTextIcon from './HelpTextIcon';

/**
 * Input with trailing text — matches Vue wpuf_render_settings_field() for type="trailing-text".
 */
export default function TrailingTextField( { field, name, value, onChange } ) {
    const inputType = field.trailing_type || 'text';

    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    const inputClasses = SETTING_CLASS_NAMES[ inputType ] || SETTING_CLASS_NAMES.text;

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-relative">
                <input
                    type={ inputType }
                    id={ name }
                    value={ value || field.default || '' }
                    onChange={ handleChange }
                    className={ inputClasses }
                />
                { field.trailing_text && (
                    <span className="wpuf-absolute wpuf-top-0 wpuf--right-px wpuf-h-full wpuf-bg-gray-50 wpuf-rounded-r-md wpuf-text-gray-700 wpuf-border wpuf-border-gray-300 wpuf-text-base wpuf-py-1.75 wpuf-px-3.75">
                        { field.trailing_text }
                    </span>
                ) }
            </div>
            { field.long_help && (
                <div
                    className="wpuf-text-sm wpuf-mt-4 wpuf-long-help"
                    dangerouslySetInnerHTML={ { __html: field.long_help } }
                />
            ) }
        </>
    );
}
