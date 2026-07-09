import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import HelpTextIcon from './HelpTextIcon';

/**
 * Multi-select with a dropdown + searchable list + removable chips.
 * Ported from the User Directory free module (src/js/user-directory/components/
 * common/MultiSelect.js) so settings multi-selects match that design.
 *
 * Stores an ARRAY of the selected option keys — same shape the legacy
 * `wpuf_settings_multiselect` callback stored.
 */
export default function MultiSelectChips( { field, name, value, onChange } ) {
    const options = field.options || {};
    const selected = Array.isArray( value )
        ? value
        : ( value ? Object.values( value ) : ( field.default || [] ) );

    const [ isOpen, setIsOpen ] = useState( false );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const dropdownRef = useRef( null );
    const searchInputRef = useRef( null );

    const displayText = ( key ) => {
        const opt = options[ key ];
        if ( typeof opt === 'string' ) {
            return opt;
        }
        if ( opt && typeof opt === 'object' ) {
            return opt.label || opt.name || key;
        }
        return key;
    };

    useEffect( () => {
        const handleClick = ( e ) => {
            if ( dropdownRef.current && ! dropdownRef.current.contains( e.target ) ) {
                setIsOpen( false );
                setSearchTerm( '' );
            }
        };
        document.addEventListener( 'mousedown', handleClick );
        return () => document.removeEventListener( 'mousedown', handleClick );
    }, [] );

    useEffect( () => {
        if ( isOpen && searchInputRef.current ) {
            setTimeout( () => searchInputRef.current && searchInputRef.current.focus(), 100 );
        }
    }, [ isOpen ] );

    const emit = ( next ) => onChange( name, next );

    const handleSelect = ( key ) => {
        if ( ! selected.includes( key ) ) {
            emit( [ ...selected, key ] );
        }
        setIsOpen( false );
        setSearchTerm( '' );
    };

    const handleRemove = ( key ) => emit( selected.filter( ( k ) => k !== key ) );

    const available = Object.keys( options )
        .filter( ( key ) => ! selected.includes( key ) )
        .filter( ( key ) =>
            ! searchTerm.trim() || displayText( key ).toLowerCase().includes( searchTerm.toLowerCase() )
        );

    // Trigger label: placeholder when empty, "N selected" past this many, else
    // the comma-joined names.
    const MAX_NAMES_IN_SUMMARY = 3;
    let summary;
    if ( selected.length === 0 ) {
        summary = field.placeholder || __( 'Select…', 'wp-user-frontend' );
    } else if ( selected.length > MAX_NAMES_IN_SUMMARY ) {
        summary = `${ selected.length } ${ __( 'selected', 'wp-user-frontend' ) }`;
    } else {
        summary = selected.map( displayText ).join( ', ' );
    }

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ field.label }</label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>

            <div className="wpuf-relative wpuf-mt-1" ref={ dropdownRef }>
                <button
                    type="button"
                    aria-haspopup="listbox"
                    aria-expanded={ isOpen }
                    onClick={ () => setIsOpen( ( o ) => ! o ) }
                    className="wpuf-flex wpuf-h-[42px] wpuf-w-full wpuf-items-center wpuf-justify-between wpuf-rounded-[6px] wpuf-border !wpuf-border-slate-300 wpuf-bg-white wpuf-px-[13px] wpuf-py-[9px] wpuf-text-left focus:wpuf-ring-transparent"
                >
                    <span className={ `wpuf-truncate wpuf-text-base ${ selected.length === 0 ? 'wpuf-text-gray-400' : 'wpuf-text-gray-700' }` }>
                        { summary }
                    </span>
                    <svg className="wpuf-ml-2 wpuf-h-4 wpuf-w-4 wpuf-text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                { isOpen && (
                    <ul className="wpuf-absolute wpuf-z-50 wpuf-mt-1 wpuf-max-h-60 wpuf-w-full wpuf-overflow-auto wpuf-rounded-md wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-lg" role="listbox">
                        <li className="wpuf-sticky wpuf-top-0 wpuf-border-b wpuf-border-gray-200 wpuf-bg-white wpuf-p-2">
                            <input
                                ref={ searchInputRef }
                                type="text"
                                value={ searchTerm }
                                onChange={ ( e ) => setSearchTerm( e.target.value ) }
                                onKeyDown={ ( e ) => {
                                    if ( e.key === 'Escape' ) {
                                        setIsOpen( false );
                                        setSearchTerm( '' );
                                    } else if ( e.key === 'Enter' && available.length > 0 ) {
                                        e.preventDefault();
                                        handleSelect( available[ 0 ] );
                                    }
                                } }
                                placeholder={ __( 'Search options…', 'wp-user-frontend' ) }
                                className="wpuf-ms-search wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-border wpuf-border-gray-300 wpuf-rounded-[4px] focus:wpuf-outline-none focus:wpuf-border-transparent"
                            />
                        </li>
                        { available.length === 0 && (
                            <li className="!wpuf-mb-0 wpuf-p-3 wpuf-text-sm wpuf-text-gray-400">
                                { searchTerm.trim()
                                    ? __( 'No matching options', 'wp-user-frontend' )
                                    : __( 'No more options', 'wp-user-frontend' ) }
                            </li>
                        ) }
                        { available.map( ( key ) => (
                            <li
                                key={ key }
                                role="option"
                                aria-selected={ false }
                                tabIndex={ 0 }
                                onClick={ () => handleSelect( key ) }
                                onKeyDown={ ( e ) => {
                                    if ( e.key === 'Enter' || e.key === ' ' ) {
                                        handleSelect( key );
                                    }
                                } }
                                className="!wpuf-mb-0 wpuf-cursor-pointer wpuf-p-3 wpuf-text-base hover:wpuf-bg-gray-100"
                            >
                                { displayText( key ) }
                            </li>
                        ) ) }
                    </ul>
                ) }

                <div className="wpuf-mt-3 wpuf-flex wpuf-flex-wrap wpuf-gap-2">
                    { selected.map( ( key ) => (
                        <div
                            key={ key }
                            className="wpuf-group/item wpuf-flex wpuf-items-center wpuf-rounded-[5px] wpuf-border wpuf-border-gray-200 wpuf-bg-gray-50 wpuf-px-3 wpuf-py-1 wpuf-text-base wpuf-shadow-sm wpuf-transition-colors hover:wpuf-border-emerald-600 hover:wpuf-bg-emerald-50"
                        >
                            <span className="wpuf-text-gray-800">{ displayText( key ) }</span>
                            <button
                                type="button"
                                aria-label={ __( 'Remove', 'wp-user-frontend' ) }
                                onClick={ () => handleRemove( key ) }
                                className="wpuf-ml-1 wpuf-flex wpuf-h-4 wpuf-w-4 wpuf-items-center wpuf-justify-center wpuf-text-emerald-600 hover:wpuf-bg-emerald-100"
                            >
                                ×
                            </button>
                        </div>
                    ) ) }
                </div>
            </div>
        </>
    );
}
