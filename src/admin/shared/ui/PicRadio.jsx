/**
 * Picture radio (settings type="pic-radio"): form style templates, login
 * layouts, SMS gateways. Compact cards (thumbnail + label) in a wrapping row;
 * the chosen one has the primary border, a soft ring and a check badge.
 *
 * The choice is drawn from the value, not from a hidden radio's `:checked`
 * state (plugin-ui's `.pui-root .opacity-0 { opacity: 0 !important }` kept a
 * `peer-checked` badge hidden, and invisible absolute radios took clicks
 * meant for the next card). The cards are a radio group: click, or arrow
 * keys to move the choice.
 *
 * @since WPUF_SINCE
 */
import { useRef } from '@wordpress/element';
import { Check } from 'lucide-react';

/**
 * @param {Object}   props
 * @param {string}   props.id       Group id (also labels the group).
 * @param {string}   [props.label]  Visible label.
 * @param {Object}   props.options  key => { label, image }.
 * @param {string}   props.value    Chosen key (the first one when unknown).
 * @param {Function} props.onChange ( key ) => void
 */
export default function PicRadio( { id, label, options = {}, value, onChange } ) {
    const keys = Object.keys( options );
    const refs = useRef( {} );
    const current = keys.includes( value ) ? value : keys[ 0 ];

    const move = ( event, index ) => {
        const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[ event.key ];

        if ( ! step ) {
            return;
        }

        event.preventDefault();
        const next = keys[ ( index + step + keys.length ) % keys.length ];
        onChange( next );

        if ( refs.current[ next ] ) {
            refs.current[ next ].focus();
        }
    };

    return (
        <div data-wpuf-ui="" className="wpuf-pic-radio">
            { label && (
                <span id={ `${ id }-label` } className="mb-2 block text-sm font-medium leading-5 text-gray-700">
                    { label }
                </span>
            ) }
            <div id={ id } role="radiogroup" aria-labelledby={ label ? `${ id }-label` : undefined } className="flex flex-wrap gap-3">
                { keys.map( ( key, index ) => {
                    const option = options[ key ] || {};
                    const selected = key === current;

                    return (
                        <button
                            key={ key }
                            type="button"
                            role="radio"
                            aria-checked={ selected }
                            tabIndex={ selected ? 0 : -1 }
                            ref={ ( node ) => {
                                refs.current[ key ] = node;
                            } }
                            onClick={ () => onChange( key ) }
                            onKeyDown={ ( event ) => move( event, index ) }
                            className={ `group relative flex w-28 cursor-pointer flex-col items-stretch overflow-hidden rounded-lg border border-solid bg-white p-0 text-left transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 ${ selected ? 'border-primary ring-2 ring-primary/15' : 'border-gray-200 hover:border-gray-300' }` }
                        >
                            <span className="flex h-20 items-center justify-center border-0 border-b border-solid border-gray-100 bg-gray-50 p-1.5">
                                { option.image ? (
                                    <img src={ option.image } alt="" className="block max-h-full max-w-full object-contain" />
                                ) : null }
                            </span>
                            <span title={ option.label || key } className={ `line-clamp-2 px-2 py-1.5 text-xs font-medium leading-4 ${ selected ? 'text-gray-900' : 'text-gray-600' }` }>
                                { option.label || key }
                            </span>
                            { selected && (
                                <span className="absolute right-1.5 top-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-white shadow-sm" aria-hidden="true">
                                    <Check size={ 10 } strokeWidth={ 3 } />
                                </span>
                            ) }
                        </button>
                    );
                } ) }
            </div>
        </div>
    );
}
