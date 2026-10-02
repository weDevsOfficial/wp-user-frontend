import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import HelpTextIcon from './HelpTextIcon';

/**
 * Decode HTML entities in option labels (e.g. currency labels store `&#36;`
 * for `$`). The legacy `<select>` let the browser decode them; React renders
 * labels as plain text, so decode here. Returns the input unchanged when there
 * are no entities.
 */
const decodeEntities = ( str ) => {
    if ( typeof str !== 'string' || str.indexOf( '&' ) === -1 ) {
        return str;
    }
    const el = document.createElement( 'textarea' );
    el.innerHTML = str;
    return el.value;
};

/**
 * Single-select dropdown — custom trigger + searchable list, matching the User
 * Directory free module's MultiSelect look (button h-42, border, white). Stores
 * a single string value, exactly like the legacy `<select>` field.
 */
export default function SelectDropdown( { field, name, value, onChange } ) {
    const options = field.options || {};
    const current = value !== undefined && value !== '' ? value : ( field.default || '' );

    const [ isOpen, setIsOpen ] = useState( false );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const dropdownRef = useRef( null );
    const searchInputRef = useRef( null );

    const displayText = ( key ) => {
        const opt = options[ key ];
        if ( typeof opt === 'string' ) {
            return decodeEntities( opt );
        }
        if ( opt && typeof opt === 'object' ) {
            return decodeEntities( opt.label || opt.name || key );
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

    const select = ( key ) => {
        onChange( name, key );
        setIsOpen( false );
        setSearchTerm( '' );
    };

    const keys = Object.keys( options );
    const showSearch = keys.length > 7;
    const available = keys.filter(
        ( key ) => ! searchTerm.trim() || displayText( key ).toLowerCase().includes( searchTerm.toLowerCase() )
    );

    const hasValue = current !== '' && options[ current ] !== undefined;

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
                    className="wpuf-flex wpuf-h-[42px] wpuf-w-full wpuf-items-center wpuf-justify-between wpuf-rounded-md wpuf-border !wpuf-border-gray-300 wpuf-bg-white wpuf-px-3.5 wpuf-py-2 wpuf-text-left"
                >
                    <span className={ `wpuf-truncate wpuf-text-base ${ hasValue ? 'wpuf-text-gray-700' : 'wpuf-text-gray-400' }` }>
                        { hasValue ? displayText( current ) : ( field.placeholder || __( 'Select…', 'wp-user-frontend' ) ) }
                    </span>
                    <svg className={ `wpuf-ml-2 wpuf-h-4 wpuf-w-4 wpuf-text-gray-500 wpuf-transition-transform ${ isOpen ? 'wpuf-rotate-180' : '' }` } fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                { isOpen && (
                    <ul className="wpuf-absolute wpuf-z-50 wpuf-mt-1 wpuf-max-h-60 wpuf-w-full wpuf-overflow-auto wpuf-rounded-md wpuf-border wpuf-border-gray-200 wpuf-bg-white wpuf-shadow-lg" role="listbox">
                        { showSearch && (
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
                                            select( available[ 0 ] );
                                        }
                                    } }
                                    placeholder={ __( 'Search options…', 'wp-user-frontend' ) }
                                    className="wpuf-ms-search wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-border wpuf-border-gray-300 wpuf-rounded-[4px] focus:wpuf-outline-none focus:wpuf-border-transparent"
                                />
                            </li>
                        ) }
                        { available.length === 0 && (
                            <li className="!wpuf-mb-0 wpuf-p-3 wpuf-text-sm wpuf-text-gray-400">
                                { __( 'No matching options', 'wp-user-frontend' ) }
                            </li>
                        ) }
                        { available.map( ( key ) => (
                            <li
                                key={ key }
                                role="option"
                                aria-selected={ key === current }
                                tabIndex={ 0 }
                                onClick={ () => select( key ) }
                                onKeyDown={ ( e ) => {
                                    if ( e.key === 'Enter' || e.key === ' ' ) {
                                        select( key );
                                    }
                                } }
                                className={ `!wpuf-mb-0 wpuf-cursor-pointer wpuf-p-3 wpuf-text-base hover:wpuf-bg-gray-100 ${ key === current ? 'wpuf-bg-emerald-50 wpuf-text-emerald-700' : '' }` }
                            >
                                { displayText( key ) }
                            </li>
                        ) ) }
                    </ul>
                ) }
            </div>
        </>
    );
}
