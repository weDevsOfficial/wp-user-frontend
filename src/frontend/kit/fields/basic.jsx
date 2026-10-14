/**
 * The free text, choice and static fields, in the classic markup of
 * `includes/Fields/Form_Field_*.php` so the template sheets style them
 * exactly as the PHP form. Each component gets the stored field settings,
 * the current value, `onChange( value )`, `onBlur()`, the form id and the
 * error, and renders one `li.wpuf-el` row (Field). Controlled inputs only.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import Field, { isReadOnly } from '../components/Field';
import ServerHtml from '../components/ServerHtml';
import SuggestInput from './Suggest';
import { useOutside, useUid } from '../form/hooks';

const controlId = ( field, formId ) => `${ field.name }_${ formId }`;
const fieldClass = ( field, formId ) => `wpuf_${ field.name }_${ formId }`;

/** Options stored as { value: label } or as a list of { value, label }. The "- Select -" placeholder (-1) is dropped. */
export const optionsOf = ( field ) => {
    const options = field.options || {};
    const list = Array.isArray( options )
        ? options.map( ( option ) => ( 'object' === typeof option ? { value: String( option.value ), label: String( option.label ) } : { value: String( option ), label: String( option ) } ) )
        : Object.entries( options ).map( ( [ value, label ] ) => ( { value: String( value ), label: String( label ) } ) );

    return list.filter( ( option ) => '-1' !== option.value );
};

/**
 * The classic content limit (frontend-form.js textLimit / contentLimitMessage):
 * nothing while the field is empty or inside the limit; past it, the title
 * and details in the word-limit span and the box marked `has-error`.
 *
 * @param {Object} field Field settings.
 * @param {string} value Text.
 * @return {{ violated: boolean, title: string, details: string }} State
 */
export const limitState = ( field, value ) => {
    const limit = Number( field.content_restriction );
    const text = String( value || '' );

    if ( ! ( limit > 0 ) || ! text.length ) {
        return { violated: false, title: '', details: '' };
    }

    const words = 'word' === field.restriction_type;
    const count = words ? text.split( ' ' ).length : text.length;
    const max = 'min' !== field.restriction_to;
    const violated = max ? count > limit : count < limit;

    if ( ! violated ) {
        return { violated: false, title: '', details: '' };
    }

    const strings = words
        ? ( max
            ? [ __( 'Maximum word limit reached. Please shorten your texts.', 'wp-user-frontend' ), __( 'This field supports a maximum of %number% words, and the limit is reached. Remove a few words to reach the acceptable limit of the field.', 'wp-user-frontend' ) ]
            : [ __( 'Minimum word required.', 'wp-user-frontend' ), __( 'This field requires minimum %number% words. Please add some more text.', 'wp-user-frontend' ) ] )
        : ( max
            ? [ __( 'Maximum character limit reached. Please shorten your texts.', 'wp-user-frontend' ), __( 'This field supports a maximum of %number% characters, and the limit is reached. Remove a few characters to reach the acceptable limit of the field.', 'wp-user-frontend' ) ]
            : [ __( 'Minimum character required.', 'wp-user-frontend' ), __( 'This field requires minimum %number% characters. Please add some more character.', 'wp-user-frontend' ) ] );

    return { violated: true, title: strings[ 0 ], details: strings[ 1 ].replace( '%number%', String( limit ) ) };
};

/** The classic word-limit span (`span.wpuf-wordlimit-message[.wpuf-help]`), empty unless the limit is broken. */
export function Counter( { field, value, help = true } ) {
    const state = limitState( field, value );

    return (
        <span className={ help ? 'wpuf-wordlimit-message wpuf-help' : 'wpuf-wordlimit-message' } aria-live="polite">
            { state.violated && <>{ state.title }<br />{ state.details }</> }
        </span>
    );
}

/** Templates whose classic markup carries the word-limit span. */
const LIMIT_SPAN = [ 'text_field', 'post_title', 'post_tags', 'first_name', 'last_name', 'display_name', 'nickname', 'user_url', 'embed' ];

const INPUT_TYPES = {
    email_address: [ 'email', 'email' ],
    website_url: [ 'url', 'url' ],
    numeric_text_field: [ 'number', 'textfield' ],
};

export function TextField( { field, value, onChange, onBlur, formId, error, type = 'text', inputClass = '' } ) {
    const id = controlId( field, formId );
    const [ inputType, baseClass ] = INPUT_TYPES[ field.template ] || [ type, 'textfield' ];
    const number = 'number' === inputType;

    return (
        <Field field={ field } id={ id } error={ error } invalid={ limitState( field, value ).violated } fieldsClass={ number ? 'wpuf-numeric_text_holder' : '' }>
            <input
                id={ id }
                className={ `${ baseClass } ${ fieldClass( field, formId ) } ${ inputClass }`.trim() }
                type={ inputType }
                name={ field.name }
                value={ value ?? '' }
                placeholder={ field.placeholder || '' }
                size={ number ? undefined : field.size || 40 }
                min={ number && '' !== ( field.min_value_field ?? '' ) ? field.min_value_field : undefined }
                max={ number && '' !== ( field.max_value_field ?? '' ) && 0 !== Number( field.max_value_field ) ? field.max_value_field : undefined }
                step={ number && Number( field.step_text_field ) > 0 ? field.step_text_field : undefined }
                disabled={ isReadOnly( field ) || undefined }
                aria-required={ 'yes' === field.required || undefined }
                aria-invalid={ error ? 'true' : undefined }
                onChange={ ( event ) => onChange( event.target.value ) }
                onBlur={ onBlur }
            />
            { LIMIT_SPAN.includes( field.template ) && <Counter field={ field } value={ value } /> }
        </Field>
    );
}

export function HiddenField( { field, value } ) {
    return <input type="hidden" name={ field.name } value={ value ?? field.meta_value ?? '' } />;
}

export function TextareaField( { field, value, onChange, onBlur, formId, error } ) {
    const id = controlId( field, formId );

    return (
        <Field field={ field } id={ id } error={ error } invalid={ limitState( field, value ).violated }>
            <textarea
                id={ id }
                className={ `textareafield ${ fieldClass( field, formId ) }` }
                name={ field.name }
                value={ value ?? '' }
                placeholder={ field.placeholder || '' }
                rows={ field.rows || 5 }
                cols={ field.cols || 25 }
                disabled={ isReadOnly( field ) || undefined }
                aria-required={ 'yes' === field.required || undefined }
                aria-invalid={ error ? 'true' : undefined }
                onChange={ ( event ) => onChange( event.target.value ) }
                onBlur={ onBlur }
            />
            <Counter field={ field } value={ value } help={ 'post_excerpt' === field.template } />
        </Field>
    );
}

export function SelectField( { field, value, onChange, onBlur, formId, error, selectClass = '' } ) {
    const id = controlId( field, formId );

    return (
        <Field field={ field } id={ id } error={ error }>
            <select
                id={ id }
                className={ `${ selectClass } ${ fieldClass( field, formId ) }`.trim() }
                name={ field.name }
                value={ '' === ( value ?? '' ) ? '-1' : value }
                disabled={ isReadOnly( field ) || undefined }
                aria-required={ 'yes' === field.required || undefined }
                aria-invalid={ error ? 'true' : undefined }
                onChange={ ( event ) => onChange( event.target.value ) }
                onBlur={ onBlur }
            >
                { field.first && <option value="-1">{ field.first }</option> }
                { optionsOf( field ).map( ( option ) => (
                    <option key={ option.value } value={ option.value }>{ option.label }</option>
                ) ) }
            </select>
        </Field>
    );
}

const ARROW = (
    <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M13.25 0.750001L7 7L0.75 0.750001" stroke="#4B5563" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

/**
 * The multiselect as Pro's custom-multiselect.js builds it
 * (`div.custom-multiselect > div.multiselect-input[role=combobox]` with
 * pills, placeholder and arrow, `div.multiselect-dropdown[role=listbox]`
 * with a checkbox per option): click or Enter / Space toggles it, Escape
 * and an outside click close it, a pill's × removes its value. On top of
 * the classic keys, ArrowDown opens it on the first option and the arrows
 * (Home / End) move between the options; Escape there goes back to the box.
 *
 * @param {Object} props
 * @param {Array}  props.options  { value, label } list.
 * @param {Array}  props.selected Selected values.
 * @param {string} props.placeholder
 * @param {Function} props.onChange Called with the new list.
 * @param {Function} [props.onBlur]
 * @param {string} [props.id]      Id for the combobox (the row label points to it).
 * @param {boolean} [props.invalid]
 */
export function MultiSelect( { options, selected, placeholder, onChange, onBlur, id, invalid = false } ) {
    const [ open, setOpen ] = useState( false );
    const wrap = useRef( null );
    const uid = useUid( 'wpuf-ms' );
    const listId = `${ uid }-list`;

    useOutside( wrap, open, () => {
        setOpen( false );
        onBlur && onBlur();
    } );

    // In the options' order, as the classic select serializes its selected options.
    const toggle = ( value ) => {
        const next = selected.includes( value ) ? selected.filter( ( v ) => v !== value ) : [ ...selected, value ];

        onChange( options.map( ( option ) => option.value ).filter( ( v ) => next.includes( v ) ) );
    };
    const labelOf = ( value ) => ( options.find( ( option ) => option.value === value ) || { label: value } ).label;

    // The template sheets hide the option checkboxes, so the option rows take the keyboard focus.
    const boxes = () => ( wrap.current ? Array.from( wrap.current.querySelectorAll( '.multiselect-option' ) ) : [] );
    const focusOption = ( index ) => {
        const list = boxes();

        if ( list.length ) {
            list[ Math.max( 0, Math.min( list.length - 1, index ) ) ].focus();
        }
    };

    const onKeyDown = ( event ) => {
        if ( event.target !== event.currentTarget ) {
            return;
        }

        if ( 'Enter' === event.key || ' ' === event.key ) {
            event.preventDefault();
            setOpen( ( current ) => ! current );
        } else if ( 'ArrowDown' === event.key || 'ArrowUp' === event.key ) {
            event.preventDefault();
            setOpen( true );
            // The list renders open first, then the option takes the focus.
            window.setTimeout( () => focusOption( 'ArrowDown' === event.key ? 0 : boxes().length - 1 ), 0 );
        } else if ( 'Escape' === event.key ) {
            setOpen( false );
        }
    };

    const onListKeyDown = ( event ) => {
        const list = boxes();
        const at = list.indexOf( event.target );

        if ( at < 0 ) {
            return;
        }

        if ( 'ArrowDown' === event.key || 'ArrowUp' === event.key || 'Home' === event.key || 'End' === event.key ) {
            event.preventDefault();
            focusOption( { ArrowDown: at + 1, ArrowUp: at - 1, Home: 0, End: list.length - 1 }[ event.key ] );
        } else if ( 'Enter' === event.key || ' ' === event.key ) {
            event.preventDefault();
            toggle( list[ at ].getAttribute( 'data-value' ) );
        } else if ( 'Escape' === event.key ) {
            event.preventDefault();
            setOpen( false );
            wrap.current.querySelector( '.multiselect-input' ).focus();
        }
    };

    return (
        <div className="custom-multiselect" ref={ wrap }>
            <div
                id={ id }
                className="multiselect-input"
                tabIndex={ 0 }
                role="combobox"
                aria-expanded={ open ? 'true' : 'false' }
                aria-haspopup="listbox"
                aria-controls={ listId }
                aria-invalid={ invalid ? 'true' : undefined }
                onClick={ () => setOpen( ( current ) => ! current ) }
                onKeyDown={ onKeyDown }
            >
                <div className="selected-items">
                    { selected.map( ( value ) => (
                        <div key={ value } className="selected-item" data-value={ value }>
                            <span>{ labelOf( value ) }</span>
                            <button
                                type="button"
                                className="remove-item"
                                /* translators: %s: option label */
                                aria-label={ sprintf( __( 'Remove %s', 'wp-user-frontend' ), labelOf( value ) ) }
                                onClick={ ( event ) => {
                                    event.stopPropagation();
                                    toggle( value );
                                } }
                            >
                                ×
                            </button>
                        </div>
                    ) ) }
                </div>
                <span className="multiselect-placeholder" style={ { display: selected.length ? 'none' : 'block' } }>{ placeholder }</span>
                <span className="dropdown-arrow" style={ { transform: open ? 'rotate(180deg)' : 'rotate(0deg)' } }>{ ARROW }</span>
            </div>
            <div className="multiselect-dropdown" role="listbox" id={ listId } aria-multiselectable="true" style={ { display: open ? 'block' : 'none' } } onKeyDown={ onListKeyDown }>
                { options.map( ( option, index ) => (
                    <div key={ option.value } className="multiselect-option" role="option" tabIndex={ -1 } aria-selected={ selected.includes( option.value ) } data-value={ option.value }>
                        <input type="checkbox" id={ `${ uid }-${ index }` } checked={ selected.includes( option.value ) } onChange={ () => toggle( option.value ) } />
                        <label htmlFor={ `${ uid }-${ index }` }>{ option.label }</label>
                    </div>
                ) ) }
            </div>
        </div>
    );
}

export function MultiSelectField( { field, value, onChange, onBlur, formId, error } ) {
    const id = controlId( field, formId );
    const selected = Array.isArray( value ) ? value.map( String ).filter( ( v ) => '-1' !== v ) : [];

    return (
        <Field field={ field } id={ id } error={ error }>
            <MultiSelect
                id={ id }
                options={ optionsOf( field ) }
                selected={ selected }
                placeholder={ field.first || __( '- Select -', 'wp-user-frontend' ) }
                onChange={ onChange }
                onBlur={ onBlur }
                invalid={ !! error }
            />
        </Field>
    );
}

/** Radio and checkbox labels: `label.wpuf-radio-block|wpuf-radio-inline > input + text`, as Form_Field_Radio prints them. */
function Choices( { field, formId, type, isChecked, onToggle, onBlur, name, onDoubleClick } ) {
    const inline = 'yes' === field.inline;
    const labelClass = `wpuf-${ type }-${ inline ? 'inline' : 'block' }`;

    return optionsOf( field ).map( ( option, index ) => (
        <label key={ option.value } className={ labelClass }>
            <input
                type={ type }
                id={ `${ field.name }_${ formId }_${ index }` }
                className={ fieldClass( field, formId ) }
                name={ name }
                value={ option.value }
                checked={ isChecked( option.value ) }
                disabled={ isReadOnly( field ) || undefined }
                onChange={ () => onToggle( option.value ) }
                onDoubleClick={ onDoubleClick }
                onBlur={ onBlur }
            />
            { ' ' }{ option.label }
        </label>
    ) );
}

export function RadioField( { field, value, onChange, onBlur, formId, error } ) {
    const current = String( value ?? '' );

    return (
        <Field field={ field } id={ `${ field.name }_${ formId }_0` } error={ error } role="radiogroup" aria-label={ field.label }>
            <Choices
                field={ field }
                formId={ formId }
                type="radio"
                name={ field.name }
                isChecked={ ( option ) => current === option }
                onToggle={ ( option ) => onChange( option ) }
                // A double click clears the choice (the classic doUncheckRadioBtn).
                onDoubleClick={ () => onChange( '' ) }
                onBlur={ onBlur }
            />
        </Field>
    );
}

export function CheckboxField( { field, value, onChange, onBlur, formId, error } ) {
    const selected = Array.isArray( value ) ? value.map( String ) : [];

    return (
        <Field field={ field } id={ `${ field.name }_${ formId }_0` } error={ error } role="group" aria-label={ field.label }>
            <Choices
                field={ field }
                formId={ formId }
                type="checkbox"
                name={ `${ field.name }[]` }
                isChecked={ ( option ) => selected.includes( option ) }
                // The values in the options' order, as the classic checkboxes serialize.
                onToggle={ ( option ) => {
                    const next = selected.includes( option ) ? selected.filter( ( v ) => v !== option ) : [ ...selected, option ];

                    onChange( optionsOf( field ).map( ( item ) => item.value ).filter( ( v ) => next.includes( v ) ) );
                } }
                onBlur={ onBlur }
            />
        </Field>
    );
}

/**
 * The HTML field: the builder's markup (admin-authored, sanitised with
 * wp_kses_post by the schema) in `div.wpuf-fields.html_<form>`, no label,
 * as Form_Field_HTML prints it.
 */
export function HtmlField( { field, formId } ) {
    return (
        <li className={ `wpuf-el ${ field.name || '' } ${ field.css || '' }`.trim() } data-label={ field.label }>
            <ServerHtml html={ field.html || '' } className={ `wpuf-fields html_${ formId } ${ fieldClass( field, formId ) }` } />
        </li>
    );
}

/** The section break, as Form_Field_SectionBreak prints it. */
export function SectionBreakField( { field, formId } ) {
    return (
        <li className={ `wpuf-el ${ field.name || '' } ${ field.css || '' }`.trim() } data-label={ field.label }>
            <div className={ `wpuf-section-wrap wpuf-fields section_${ formId } ${ fieldClass( field, formId ) }` } style={ { width: '100%', textAlign: 'center' } }>
                <h2 className="wpuf-section-title">{ field.label }</h2>
                <div className="wpuf-section-details">{ field.description }</div>
            </div>
        </li>
    );
}

/** Post tags: a comma-separated list (the classic field posts `tags`). */
/** Post tags (Form_Field_Post_Tags): the text input with the classic `suggest()` term search. */
export function TagsField( { field, value, onChange, onBlur, formId, error } ) {
    const id = controlId( field, formId );

    return (
        <Field field={ field } id={ id } error={ error } invalid={ limitState( field, value ).violated }>
            <SuggestInput
                taxonomy="post_tag"
                formId={ formId }
                value={ value }
                onChange={ onChange }
                onBlur={ onBlur }
                inputProps={ {
                    id,
                    name: field.name,
                    className: `textfield ${ fieldClass( field, formId ) }`,
                    placeholder: field.placeholder || '',
                    size: field.size || 40,
                    disabled: isReadOnly( field ) || undefined,
                    'aria-required': 'yes' === field.required || undefined,
                    'aria-invalid': error ? 'true' : undefined,
                } }
            />
            <Counter field={ field } value={ value } />
        </Field>
    );
}
