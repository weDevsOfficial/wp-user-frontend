import { useCallback, useRef } from '@wordpress/element';

/**
 * Get the asset URL for images.
 *
 * @return {string}
 */
function getAssetUrl() {
    return ( window.wpuf_admin_script || window.wpuf_form_builder || {} ).asset_url || '';
}

/**
 * Picture radio field (type="pic-radio", the login form layout picker): one
 * card per option, the selected one with the green border and checkmark.
 *
 * The selection is drawn from the value, not from a hidden radio's
 * `:checked` state: plugin-ui's `.pui-root .opacity-0 { opacity: 0 !important }`
 * kept the `peer-checked:opacity-100` checkmark hidden, and the invisible
 * absolutely placed radios made clicks land on the wrong card. Cards are a
 * radio group (arrow keys move the choice).
 */
export default function PicRadioField( { field, name, value, onChange } ) {
    const options = field.options || {};
    const keys = Object.keys( options );
    const checkedIcon = getAssetUrl() + '/images/checked-green.svg';
    const refs = useRef( {} );
    const current = keys.includes( value ) ? value : keys[ 0 ];

    const select = useCallback( ( key ) => onChange( name, key ), [ name, onChange ] );

    const onKeyDown = ( event, index ) => {
        const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[ event.key ];

        if ( ! step ) {
            return;
        }

        event.preventDefault();
        const next = keys[ ( index + step + keys.length ) % keys.length ];
        select( next );

        if ( refs.current[ next ] ) {
            refs.current[ next ].focus();
        }
    };

    return (
        <>
            { field.label && (
                <div className="flex items-center">
                    <span id={ `${ name }-label` } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                        { field.label }
                    </span>
                </div>
            ) }
            <div className="grid grid-cols-4 wpuf-pic-radio" id={ name } role="radiogroup" aria-labelledby={ field.label ? `${ name }-label` : undefined }>
                { keys.map( ( optKey, index ) => {
                    const option = options[ optKey ] || {};
                    const selected = optKey === current;

                    return (
                        <div key={ optKey } className="text-center p-3 pl-0 pt-0">
                            <button
                                type="button"
                                role="radio"
                                aria-checked={ selected }
                                aria-label={ option.label || optKey }
                                tabIndex={ selected ? 0 : -1 }
                                ref={ ( node ) => {
                                    refs.current[ optKey ] = node;
                                } }
                                onClick={ () => select( optKey ) }
                                onKeyDown={ ( event ) => onKeyDown( event, index ) }
                                className={ `relative block w-full p-0 bg-transparent cursor-pointer rounded-lg border-2 border-solid transition-all duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-primary ${ selected ? 'border-primary' : 'border-transparent hover:border-primary' }` }
                            >
                                { option.image && <img src={ option.image } alt="" className="block w-full rounded-md" /> }
                                { selected && (
                                    <img src={ checkedIcon } alt="" aria-hidden="true" style={ { position: 'absolute', top: '7%', right: '7%', width: 20, height: 20, opacity: 1 } } />
                                ) }
                            </button>
                            <span className="block mt-2 text-sm text-gray-700">{ option.label }</span>
                        </div>
                    );
                } ) }
            </div>
        </>
    );
}
