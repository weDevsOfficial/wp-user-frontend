/**
 * The search box of the admin lists and screens: a search icon, the kit
 * TextInput and a clear button once something is typed. The caller decides
 * when to search (as typed, debounced or on Enter).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Search, X } from 'lucide-react';
import { cn } from '@wedevs/plugin-ui';

import TextInput from './TextInput';

/**
 * @param {Object}   props
 * @param {string}   props.value         Value.
 * @param {Function} props.onChange      ( value ) => void
 * @param {string}   [props.placeholder] Placeholder (also the accessible name).
 * @param {string}   [props.className]   Wrapper classes (width).
 * @param {Function} [props.onSubmit]    Enter pressed: ( value ) => void
 *
 * @return {JSX.Element} Search box.
 */
export default function SearchInput( { value, onChange, placeholder, className, onSubmit, ...rest } ) {
    const text = placeholder || __( 'Search', 'wp-user-frontend' );

    return (
        <div data-wpuf-ui="" className={ cn( 'relative w-60', className ) }>
            <Search size={ 16 } aria-hidden="true" className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <TextInput
                type="search"
                value={ value }
                onChange={ ( next ) => onChange( next ) }
                onKeyDown={ ( event ) => onSubmit && 'Enter' === event.key && onSubmit( event.target.value ) }
                placeholder={ text }
                aria-label={ rest[ 'aria-label' ] || text }
                className="h-9 w-full ps-9 pe-9 [&::-webkit-search-cancel-button]:hidden"
            />
            { value ? (
                <button
                    type="button"
                    onClick={ () => onChange( '' ) }
                    aria-label={ __( 'Clear search', 'wp-user-frontend' ) }
                    className="absolute end-2 top-1/2 inline-flex size-6 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                >
                    <X size={ 14 } aria-hidden="true" />
                </button>
            ) : null }
        </div>
    );
}
