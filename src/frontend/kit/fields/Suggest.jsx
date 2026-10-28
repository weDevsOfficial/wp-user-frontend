/**
 * Comma separated term names with suggestions, as WordPress's jQuery
 * `suggest()` gives them to the classic tags field and the text / ajax
 * taxonomy field: after 2 characters and 500 ms the last name typed is
 * searched (`/forms/{id}/terms?search=`), `ul.ac_results` opens under the
 * input (inside `span.wpuf-suggest`, so the form's design reaches it; the
 * classic list was appended to `body`), the typed part is wrapped in
 * `span.ac_match`, the arrows move `li.ac_over`, Enter or Tab picks it,
 * Escape closes, a blur closes after 200 ms.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { searchTerms } from '../api';
import { useUid } from '../form/hooks';

const SEPARATOR = ',';

/** The name with the typed part wrapped like suggest.js parseTxt(). */
function Highlight( { name, query } ) {
    const at = name.toLowerCase().indexOf( query.toLowerCase() );

    if ( ! query || at < 0 ) {
        return name;
    }

    return (
        <>
            { name.slice( 0, at ) }
            <span className="ac_match">{ name.slice( at, at + query.length ) }</span>
            { name.slice( at + query.length ) }
        </>
    );
}

/**
 * @param {Object}   props
 * @param {string}   props.taxonomy  Taxonomy searched.
 * @param {number}   props.formId    Form id (the REST route checks the form).
 * @param {string}   props.value     Comma separated names.
 * @param {Function} props.onChange  Receives the new text.
 * @param {Object}   props.inputProps Attributes of the input (id, class, name, ...).
 */
export default function SuggestInput( { taxonomy, formId, value, onChange, onBlur, inputProps = {} } ) {
    const text = Array.isArray( value ) ? value.join( ', ' ) : String( value ?? '' );
    const [ open, setOpen ] = useState( false );
    const [ items, setItems ] = useState( [] );
    const [ active, setActive ] = useState( -1 );
    const [ loading, setLoading ] = useState( false );
    const input = useRef( null );
    const cache = useRef( {} );
    const listId = useUid( 'wpuf-suggest' );
    const query = text.split( SEPARATOR ).pop().trim();

    useEffect( () => {
        if ( query.length < 2 ) {
            setOpen( false );
            setLoading( false );

            return undefined;
        }

        const show = ( names ) => {
            setLoading( false );
            setItems( names );
            setActive( -1 );
            setOpen( names.length > 0 && document.activeElement === input.current );
        };

        if ( cache.current[ query ] ) {
            show( cache.current[ query ] );

            return undefined;
        }

        // The classic suggest() delay.
        const timer = window.setTimeout( () => {
            // A "Searching" row while the terms load.
            if ( document.activeElement === input.current ) {
                setItems( [] );
                setLoading( true );
                setOpen( true );
            }

            searchTerms( formId, taxonomy, query )
                .then( ( terms ) => {
                    const names = ( terms || [] ).map( ( term ) => term.name );

                    cache.current[ query ] = names;
                    show( names );
                } )
                .catch( () => {
                    setLoading( false );
                    setOpen( false );
                } );
        }, 500 );

        return () => window.clearTimeout( timer );
    }, [ query, formId, taxonomy ] );

    const choose = ( name ) => {
        const parts = text.split( SEPARATOR ).map( ( part ) => part.trim() ).filter( Boolean );

        parts.pop();
        parts.push( name );
        onChange( parts.join( ', ' ) + ', ' );
        setOpen( false );
    };

    const onKeyDown = ( event ) => {
        if ( ! open ) {
            return;
        }

        if ( 'ArrowDown' === event.key ) {
            event.preventDefault();
            setActive( ( index ) => ( index + 1 >= items.length ? 0 : index + 1 ) );
        } else if ( 'ArrowUp' === event.key ) {
            event.preventDefault();
            setActive( ( index ) => ( index <= 0 ? items.length - 1 : index - 1 ) );
        } else if ( ( 'Enter' === event.key || 'Tab' === event.key ) && active >= 0 ) {
            event.preventDefault();
            choose( items[ active ] );
        } else if ( 'Escape' === event.key ) {
            event.preventDefault();
            setOpen( false );
        }
    };

    return (
        <span className="wpuf-suggest">
            <input
                { ...inputProps }
                ref={ input }
                type="text"
                value={ text }
                autoComplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded={ open ? 'true' : 'false' }
                aria-busy={ loading ? 'true' : undefined }
                aria-controls={ listId }
                aria-activedescendant={ open && active >= 0 ? `${ listId }-${ active }` : undefined }
                onChange={ ( event ) => onChange( event.target.value ) }
                onKeyDown={ onKeyDown }
                onBlur={ () => {
                    window.setTimeout( () => setOpen( false ), 200 );

                    if ( onBlur ) {
                        onBlur();
                    }
                } }
            />
            { open && (
                <ul className="ac_results" id={ listId } role="listbox" aria-busy={ loading ? 'true' : undefined }>
                    { loading && (
                        <li className="wpuf-suggest-loading" role="presentation">
                            <span className="wpuf-suggest-spinner" aria-hidden="true" />
                            <span role="status">{ __( 'Searching…', 'wp-user-frontend' ) }</span>
                        </li>
                    ) }
                    { items.map( ( name, index ) => (
                        <li
                            key={ name }
                            id={ `${ listId }-${ index }` }
                            role="option"
                            aria-selected={ index === active }
                            className={ index === active ? 'ac_over' : undefined }
                            onMouseOver={ () => setActive( index ) }
                            onMouseDown={ ( event ) => {
                                event.preventDefault();
                                choose( name );
                            } }
                        >
                            <Highlight name={ name } query={ query } />
                        </li>
                    ) ) }
                </ul>
            ) }
        </span>
    );
}
