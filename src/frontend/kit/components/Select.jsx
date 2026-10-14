/**
 * The single select of the React forms: a button with a list in place of
 * the browser's own dropdown, in the forms' design (the plugin-ui select
 * look of the admin). It follows the WAI-ARIA "select-only combobox":
 * the button keeps the focus, the arrows move the active option
 * (`aria-activedescendant`), Enter / Space / a click pick it, Escape and
 * an outside click close the list, typed letters jump to an option, Home /
 * End go to the ends. Long lists (more than SEARCH_FROM options) get a
 * search box on top.
 *
 * The classic `<select>` stays in the DOM, visually hidden and in sync
 * (`select.wpuf-select__native`, with the field's name and classes), for
 * code that reads the form's DOM and for tests; a change on it (a script
 * setting its value and firing `change`) reaches the field too.
 *
 * "Use Theme CSS" forms keep the browser's select: the theme styles it
 * (SelectModeContext, set by FormRenderer).
 *
 * @since WPUF_SINCE
 */
import { createContext, useContext, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useOutside, useUid } from '../form/hooks';

/** 'custom' (the list) or 'native' (the browser's select, "Use Theme CSS" forms). */
export const SelectModeContext = createContext( 'custom' );

/** Lists longer than this get a search box. */
const SEARCH_FROM = 12;

const CHEVRON = (
    <svg className="wpuf-select__chevron" width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
        <path d="M6 8l4 4 4-4" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const CHECK = (
    <svg className="wpuf-select__check" width="16" height="16" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
        <path d="M5 10.5l3 3 7-7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const fold = ( text ) => String( text || '' ).toLocaleLowerCase().normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' ).trim();

/**
 * @param {Object}   props
 * @param {string}   [props.id]          Id of the button (the row label points to it).
 * @param {string}   [props.name]        Name of the hidden select (what the classic form posts).
 * @param {string}   [props.className]   Classes of the hidden select (the classic field classes).
 * @param {string}   props.value         Selected value ('' or the placeholder value: none).
 * @param {Array}    props.options       { value, label, disabled?, depth? } list.
 * @param {string}   [props.placeholder] Label of the "nothing picked" option.
 * @param {string}   [props.placeholderValue] Its value (classic: -1).
 * @param {boolean}  [props.showPlaceholder] Offer the placeholder as an option (the classic "- Select -" row).
 * @param {Function} props.onChange      Called with the picked value.
 * @param {Function} [props.onBlur]
 * @param {boolean}  [props.disabled]
 * @param {boolean}  [props.invalid]
 * @param {boolean}  [props.required]
 * @param {string}   [props.label]       Accessible name when no row label points to the id.
 * @param {string}   [props.size]        'sm' for the compact look (calendar header).
 * @param {Object}   [props.data]        data-* attributes for the button (tests, e.g. data-handler).
 * @param {boolean}  [props.returnFocus] The button takes the focus back after a pick (off when onChange moves it, e.g. into an editor).
 */
export default function Select( {
    id,
    name,
    className = '',
    value,
    options,
    placeholder = '',
    placeholderValue = '-1',
    showPlaceholder = true,
    onChange,
    onBlur,
    disabled = false,
    invalid = false,
    required = false,
    label,
    size,
    data = {},
    returnFocus = true,
} ) {
    const mode = useContext( SelectModeContext );
    const uid = useUid( 'wpuf-select' );
    const listId = `${ uid }-list`;
    const wrap = useRef( null );
    const button = useRef( null );
    const search = useRef( null );
    const list = useRef( null );
    const typed = useRef( { text: '', at: 0 } );
    const [ open, setOpen ] = useState( false );
    const [ query, setQuery ] = useState( '' );
    const [ active, setActive ] = useState( -1 );
    const [ above, setAbove ] = useState( false );

    const current = String( value ?? '' );
    const empty = '' === current || placeholderValue === current;
    const all = useMemo( () => {
        const rows = ( options || [] ).map( ( option ) => ( { ...option, value: String( option.value ), label: String( option.label ?? option.value ) } ) );

        return showPlaceholder && placeholder ? [ { value: placeholderValue, label: placeholder, placeholder: true }, ...rows ] : rows;
    }, [ options, placeholder, placeholderValue, showPlaceholder ] );
    const searchable = all.length > SEARCH_FROM;
    const shown = useMemo( () => {
        const needle = fold( query );

        return needle ? all.filter( ( option ) => ! option.placeholder && fold( option.label ).includes( needle ) ) : all;
    }, [ all, query ] );
    const selected = all.find( ( option ) => option.value === current && ! ( option.placeholder && ! empty ) );
    const shownLabel = selected && ! selected.placeholder ? selected.label : placeholder;
    const optionId = ( index ) => `${ uid }-opt-${ index }`;

    const close = ( focusButton = true ) => {
        setOpen( false );
        setQuery( '' );

        if ( focusButton && button.current ) {
            button.current.focus();
        }
    };

    const pick = ( option ) => {
        if ( ! option || option.disabled ) {
            return;
        }

        close( returnFocus );

        if ( option.value !== current ) {
            onChange( option.placeholder ? '' : option.value );
        }
    };

    const firstEnabled = ( from, step ) => {
        for ( let i = from; i >= 0 && i < shown.length; i += step ) {
            if ( ! shown[ i ].disabled ) {
                return i;
            }
        }

        return -1;
    };

    const openList = ( at ) => {
        if ( disabled ) {
            return;
        }

        // Room below the button, else the list opens above it.
        if ( button.current ) {
            const rect = button.current.getBoundingClientRect();
            setAbove( window.innerHeight - rect.bottom < 300 && rect.top > window.innerHeight - rect.bottom );
        }

        const index = all.findIndex( ( option ) => option.value === current );

        setActive( 'first' === at ? firstEnabled( 0, 1 ) : ( 'last' === at ? firstEnabled( all.length - 1, -1 ) : ( index >= 0 ? index : firstEnabled( 0, 1 ) ) ) );
        setOpen( true );
    };

    useOutside( wrap, open, () => {
        setOpen( false );
        setQuery( '' );
        onBlur && onBlur();
    } );

    // The search box takes the focus when the list opens; the active option stays in view.
    useEffect( () => {
        if ( open && searchable && search.current ) {
            search.current.focus();
        }
    }, [ open, searchable ] );

    useEffect( () => {
        if ( ! open || active < 0 || ! list.current ) {
            return;
        }

        const node = list.current.querySelector( `#${ CSS.escape( optionId( active ) ) }` );

        if ( node && 'function' === typeof node.scrollIntoView ) {
            node.scrollIntoView( { block: 'nearest' } );
        }
    }, [ open, active ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // A new query starts on its first match.
    useEffect( () => {
        if ( open ) {
            setActive( firstEnabled( 0, 1 ) );
        }
    }, [ query ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // Typed letters (no search box): the next option whose label starts with them.
    const typeAhead = ( key ) => {
        const now = Date.now();
        const text = now - typed.current.at > 700 ? key : typed.current.text + key;

        typed.current = { text, at: now };

        const needle = fold( text );
        const start = open ? active : all.findIndex( ( option ) => option.value === current );
        const order = all.map( ( _, i ) => ( start + ( 1 === text.length ? 1 : 0 ) + i ) % all.length );
        const hit = order.find( ( i ) => ! all[ i ].disabled && ! all[ i ].placeholder && fold( all[ i ].label ).startsWith( needle ) );

        if ( undefined === hit ) {
            return;
        }

        if ( open ) {
            setActive( hit );
        } else {
            onChange( all[ hit ].value );
        }
    };

    const move = ( event ) => {
        const keys = {
            ArrowDown: () => firstEnabled( active + 1, 1 ),
            ArrowUp: () => firstEnabled( active - 1, -1 ),
            Home: () => firstEnabled( 0, 1 ),
            End: () => firstEnabled( shown.length - 1, -1 ),
            PageDown: () => firstEnabled( Math.min( shown.length - 1, active + 8 ), 1 ),
            PageUp: () => firstEnabled( Math.max( 0, active - 8 ), -1 ),
        };

        if ( keys[ event.key ] ) {
            event.preventDefault();
            const next = keys[ event.key ]();

            if ( next >= 0 ) {
                setActive( next );
            }

            return true;
        }

        if ( 'Enter' === event.key ) {
            event.preventDefault();
            pick( shown[ active ] );

            return true;
        }

        if ( 'Escape' === event.key ) {
            event.preventDefault();
            event.stopPropagation();
            close();

            return true;
        }

        if ( 'Tab' === event.key ) {
            // The APG pattern: Tab keeps the active option and moves on.
            if ( shown[ active ] && ! shown[ active ].disabled && shown[ active ].value !== current ) {
                onChange( shown[ active ].placeholder ? '' : shown[ active ].value );
            }

            setOpen( false );
            setQuery( '' );

            return true;
        }

        return false;
    };

    const onButtonKeyDown = ( event ) => {
        if ( disabled ) {
            return;
        }

        if ( ! open ) {
            if ( 'ArrowDown' === event.key || 'ArrowUp' === event.key || 'Enter' === event.key || ' ' === event.key ) {
                event.preventDefault();
                openList( 'ArrowUp' === event.key && ! current ? 'last' : undefined );
            } else if ( 'Home' === event.key || 'End' === event.key ) {
                event.preventDefault();
                openList( 'Home' === event.key ? 'first' : 'last' );
            } else if ( 1 === event.key.length && ! event.ctrlKey && ! event.metaKey && ! event.altKey ) {
                typeAhead( event.key );
            }

            return;
        }

        if ( move( event ) ) {
            return;
        }

        if ( ' ' === event.key ) {
            event.preventDefault();
            pick( shown[ active ] );
        } else if ( 1 === event.key.length && ! event.ctrlKey && ! event.metaKey && ! event.altKey ) {
            typeAhead( event.key );
        }
    };

    const native = (
        <select
            className={ `${ className } ${ 'native' === mode ? '' : 'wpuf-select__native' }`.trim() }
            id={ 'native' === mode ? id : undefined }
            name={ name }
            value={ empty && showPlaceholder && placeholder ? placeholderValue : current }
            disabled={ disabled || undefined }
            aria-hidden={ 'native' === mode ? undefined : 'true' }
            aria-invalid={ 'native' === mode && invalid ? 'true' : undefined }
            aria-required={ 'native' === mode && required ? 'true' : undefined }
            aria-label={ 'native' === mode && label ? label : undefined }
            tabIndex={ 'native' === mode ? undefined : -1 }
            onChange={ ( event ) => onChange( placeholderValue === event.target.value ? '' : event.target.value ) }
            onBlur={ 'native' === mode ? onBlur : undefined }
            { ...( 'native' === mode ? data : {} ) }
        >
            { showPlaceholder && placeholder && <option value={ placeholderValue }>{ placeholder }</option> }
            { ( options || [] ).map( ( option ) => (
                <option key={ String( option.value ) } value={ String( option.value ) } disabled={ option.disabled || undefined }>
                    { `${ '   '.repeat( option.depth || 0 ) }${ option.label ?? option.value }` }
                </option>
            ) ) }
        </select>
    );

    if ( 'native' === mode ) {
        return native;
    }

    return (
        <div ref={ wrap } className={ `wpuf-select${ open ? ' is-open' : '' }${ disabled ? ' is-disabled' : '' }${ size ? ` wpuf-select--${ size }` : '' }` }>
            { native }
            <button
                ref={ button }
                type="button"
                id={ id }
                className={ `wpuf-select__trigger${ empty ? ' is-placeholder' : '' }` }
                role="combobox"
                aria-haspopup="listbox"
                aria-expanded={ open ? 'true' : 'false' }
                aria-controls={ listId }
                aria-activedescendant={ open && ! searchable && active >= 0 ? optionId( active ) : undefined }
                aria-invalid={ invalid ? 'true' : undefined }
                aria-required={ required ? 'true' : undefined }
                aria-label={ label || undefined }
                disabled={ disabled || undefined }
                data-value={ current }
                onClick={ () => ( open ? close() : openList() ) }
                onKeyDown={ onButtonKeyDown }
                onBlur={ ( event ) => {
                    if ( ! open && onBlur && ! ( wrap.current && wrap.current.contains( event.relatedTarget ) ) ) {
                        onBlur();
                    }
                } }
                { ...data }
            >
                <span className="wpuf-select__value">{ shownLabel || ' ' }</span>
                { CHEVRON }
            </button>
            { open && (
                <div className={ `wpuf-select__popover${ above ? ' is-above' : '' }` }>
                    { searchable && (
                        <input
                            ref={ search }
                            type="text"
                            className="wpuf-select__search"
                            value={ query }
                            placeholder={ __( 'Search…', 'wp-user-frontend' ) }
                            aria-label={ __( 'Search options', 'wp-user-frontend' ) }
                            aria-controls={ listId }
                            aria-activedescendant={ active >= 0 ? optionId( active ) : undefined }
                            role="combobox"
                            aria-expanded="true"
                            aria-autocomplete="list"
                            autoComplete="off"
                            onChange={ ( event ) => setQuery( event.target.value ) }
                            onKeyDown={ move }
                        />
                    ) }
                    <ul ref={ list } id={ listId } className="wpuf-select__list" role="listbox" aria-labelledby={ id } tabIndex={ -1 }>
                        { shown.map( ( option, index ) => (
                            <li
                                key={ `${ option.placeholder ? 'ph' : 'o' }-${ option.value }` }
                                id={ optionId( index ) }
                                role="option"
                                data-value={ option.value }
                                aria-selected={ option.value === current && ! ( option.placeholder && ! empty ) ? 'true' : 'false' }
                                aria-disabled={ option.disabled ? 'true' : undefined }
                                className={ `wpuf-select__option${ index === active ? ' is-active' : '' }${ option.placeholder ? ' is-placeholder' : '' }` }
                                style={ option.depth ? { '--wpuf-select-depth': option.depth } : undefined }
                                onMouseMove={ () => index !== active && ! option.disabled && setActive( index ) }
                                onMouseDown={ ( event ) => event.preventDefault() }
                                onClick={ () => pick( option ) }
                            >
                                <span className="wpuf-select__option-label">{ option.label }</span>
                                { option.value === current && ! option.placeholder && CHECK }
                            </li>
                        ) ) }
                        { ! shown.length && <li className="wpuf-select__empty" role="presentation">{ __( 'No matches', 'wp-user-frontend' ) }</li> }
                    </ul>
                </div>
            ) }
        </div>
    );
}
