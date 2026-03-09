import { useCallback } from '@wordpress/element';

/**
 * Get the asset URL for images.
 *
 * @return {string}
 */
function getAssetUrl() {
    return ( window.wpuf_admin_script || window.wpuf_form_builder || {} ).asset_url || '';
}

/**
 * Picture radio field — matches wpuf_render_settings_field() for type="pic-radio".
 * Includes the green checkmark overlay on the selected option.
 */
export default function PicRadioField( { field, name, value, onChange } ) {
    const options = field.options || {};
    const checkedIcon = getAssetUrl() + '/images/checked-green.svg';

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
                            <img
                                className="wpuf-absolute wpuf-opacity-0 peer-checked:wpuf-opacity-100 wpuf-top-[7%] wpuf-right-[12%] wpuf-transition-all wpuf-duration-200 wpuf-ease-in-out"
                                src={ checkedIcon }
                                alt=""
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
