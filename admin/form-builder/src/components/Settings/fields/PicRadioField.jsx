import { useCallback } from '@wordpress/element';

/**
 * Picture radio field — matches Vue wpuf_render_settings_field() for type="pic-radio".
 */
export default function PicRadioField( { field, name, value, onChange } ) {
    const options = field.options || {};

    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    return (
        <>
            { field.label && (
                <div className="wpuf-flex wpuf-items-center">
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                </div>
            ) }
            <div className="wpuf-grid wpuf-grid-cols-4 wpuf-pic-radio" id={ name }>
                { Object.entries( options ).map( ( [ optKey, option ] ) => (
                    <div key={ optKey } className="wpuf-relative wpuf-text-center wpuf-p-3 wpuf-pl-0 wpuf-pt-0">
                        <label>
                            <input
                                type="radio"
                                name={ `wpuf_settings[${ name }]` }
                                value={ optKey }
                                checked={ value === optKey }
                                onChange={ handleChange }
                                className="wpuf-absolute wpuf-opacity-0 wpuf-peer"
                            />
                            { option.image && (
                                <img
                                    src={ option.image }
                                    alt={ optKey }
                                    className="hover:wpuf-cursor-pointer wpuf-border-transparent wpuf-border-2 wpuf-border-solid wpuf-rounded-lg hover:wpuf-border-primary peer-checked:wpuf-border-primary wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out wpuf-mb-2 wpuf-w-full"
                                />
                            ) }
                        </label>
                        <label className="wpuf-mr-2 wpuf-text-sm wpuf-text-gray-700">
                            { option.label }
                        </label>
                    </div>
                ) ) }
            </div>
        </>
    );
}
