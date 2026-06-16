// DESCRIPTION: Searchable multi-select dropdown with pill-style selected items.
// Used in the subscription packs block for include/exclude pack selection.

import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const MultiSelect = ( { options, value, onChange, placeholder } ) => {
    const [ isOpen, setIsOpen ] = useState( false );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const dropdownRef = useRef( null );
    const searchInputRef = useRef( null );

    // Close dropdown on outside click
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

    // Focus search input when dropdown opens
    useEffect( () => {
        if ( isOpen && searchInputRef.current ) {
            setTimeout( () => {
                searchInputRef.current?.focus();
            }, 100 );
        }
    }, [ isOpen ] );

    const getOptionLabel = ( id ) => {
        const option = options.find( ( opt ) => opt.id === id );
        return option ? option.title : `#${ id }`;
    };

    const handleSelect = ( id ) => {
        if ( ! value.includes( id ) ) {
            onChange( [ ...value, id ] );
        }
        setIsOpen( false );
        setSearchTerm( '' );
    };

    const handleRemove = ( id ) => {
        onChange( value.filter( ( v ) => v !== id ) );
    };

    const getFilteredOptions = () => {
        let filtered = options.filter( ( opt ) => ! value.includes( opt.id ) );

        if ( searchTerm.trim() ) {
            const searchLower = searchTerm.toLowerCase();
            filtered = filtered.filter( ( opt ) =>
                opt.title.toLowerCase().includes( searchLower )
            );
        }

        return filtered;
    };

    const availableOptions = getFilteredOptions();

    return (
        <div className="wpuf-block-multiselect" ref={ dropdownRef }>
            <button
                type="button"
                className="components-button wpuf-block-multiselect__trigger"
                aria-haspopup="listbox"
                aria-expanded={ isOpen }
                onClick={ () => setIsOpen( ( open ) => ! open ) }
            >
                <span className={ value.length === 0 ? 'wpuf-block-multiselect__placeholder' : '' }>
                    { value.length === 0
                        ? placeholder
                        : value.length > 3
                            ? `${ value.length } packs selected`
                            : value.map( ( id ) => getOptionLabel( id ) ).join( ', ' )
                    }
                </span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            { isOpen && (
                <ul className="wpuf-block-multiselect__dropdown" role="listbox">
                    <li className="wpuf-block-multiselect__search-wrap">
                        <input
                            ref={ searchInputRef }
                            type="text"
                            value={ searchTerm }
                            onChange={ ( e ) => setSearchTerm( e.target.value ) }
                            onKeyDown={ ( e ) => {
                                if ( e.key === 'Escape' ) {
                                    setIsOpen( false );
                                    setSearchTerm( '' );
                                } else if ( e.key === 'Enter' && availableOptions.length > 0 ) {
                                    handleSelect( availableOptions[ 0 ].id );
                                }
                            } }
                            placeholder={ __( 'Search packs...', 'wp-user-frontend' ) }
                            className="wpuf-block-multiselect__search"
                        />
                    </li>
                    { availableOptions.length === 0 && (
                        <li className="wpuf-block-multiselect__empty">
                            { searchTerm.trim()
                                ? __( 'No matching packs', 'wp-user-frontend' )
                                : __( 'No more packs', 'wp-user-frontend' )
                            }
                        </li>
                    ) }
                    { availableOptions.map( ( opt ) => (
                        <li
                            key={ opt.id }
                            className="wpuf-block-multiselect__option"
                            role="option"
                            aria-selected={ false }
                            tabIndex={ 0 }
                            onClick={ () => handleSelect( opt.id ) }
                            onKeyDown={ ( e ) => {
                                if ( e.key === 'Enter' || e.key === ' ' ) {
                                    handleSelect( opt.id );
                                }
                            } }
                        >
                            { opt.title }
                        </li>
                    ) ) }
                </ul>
            ) }

            { value.length > 0 && (
                <div className="wpuf-block-multiselect__pills">
                    { value.map( ( id ) => (
                        <span key={ id } className="wpuf-block-multiselect__pill">
                            { getOptionLabel( id ) }
                            <button
                                type="button"
                                className="wpuf-block-multiselect__pill-remove"
                                aria-label={ __( 'Remove', 'wp-user-frontend' ) }
                                onClick={ () => handleRemove( id ) }
                            >
                                ×
                            </button>
                        </span>
                    ) ) }
                </div>
            ) }
        </div>
    );
};

export default MultiSelect;
