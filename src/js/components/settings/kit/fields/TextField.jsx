import { useCallback } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from '../SettingsField';
import HelpTextIcon from './HelpTextIcon';

/**
 * Text input field for form settings.
 * Matches Vue wpuf_render_settings_field() for type="text".
 */
export default function TextField( { field, name, value, onChange } ) {
    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
                { field.link && (
                    <a href={ field.link } target="_blank" rel="noopener noreferrer" title="Learn More" className="focus:wpuf-shadow-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" className="wpuf-size-5 wpuf-ml-1 wpuf-stroke-gray-50 hover:wpuf-stroke-gray-200">
                            <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                            <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                        </svg>
                    </a>
                ) }
            </div>
            <input
                type="text"
                id={ name }
                value={ value || field.default || '' }
                onChange={ handleChange }
                className={ SETTING_CLASS_NAMES.text }
                placeholder={ field.placeholder || '' }
            />
            { field.notice && (
                <div className="wpuf-bg-yellow-50 wpuf-border-l-4 wpuf-border-yellow-500 wpuf-text-yellow-700 wpuf-p-4">
                    <p className="wpuf-m-0">{ field.notice.text }</p>
                </div>
            ) }
            { field.long_help && (
                <div
                    className="wpuf-text-sm wpuf-mt-4 wpuf-long-help"
                    dangerouslySetInnerHTML={ { __html: field.long_help } }
                />
            ) }
        </>
    );
}
