import { useCallback } from '@wordpress/element';
import HelpTextIcon from './HelpTextIcon';

/**
 * Color picker field — matches Vue wpuf_render_settings_field() for type="color-picker".
 */
export default function ColorPickerField( { field, name, value, onChange } ) {
    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    const currentValue = value || field.default || '#000000';

    return (
        <div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-2/5">
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-relative wpuf-ml-2 wpuf-flex wpuf-gap-2.5">
                <div className="wpuf-flex wpuf-justify-center wpuf-items-center wpuf-space-x-1 wpuf-px-2 wpuf-py-1.5 wpuf-rounded-md wpuf-bg-white wpuf-border wpuf-cursor-pointer wpuf-relative">
                    <div className="wpuf-w-6 wpuf-h-6 wpuf-overflow-hidden wpuf-border wpuf-border-gray-200 wpuf-rounded-full wpuf-flex wpuf-justify-center wpuf-items-center">
                        <input
                            type="color"
                            id={ name }
                            value={ currentValue }
                            onChange={ handleChange }
                            className="wpuf-w-8 wpuf-h-12 !wpuf-border-gray-50 !wpuf--m-4 hover:!wpuf-cursor-pointer"
                            style={ { background: field.default || '' } }
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}
