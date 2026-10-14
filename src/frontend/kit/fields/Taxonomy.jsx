/**
 * The taxonomy field in the classic markup of Form_Field_Post_Taxonomy
 * (`div.wpuf-fields.wpuf_<name>_<type>_<id>_<form>`):
 *
 * - select: `select.<tax>.wpuf_<name>_<form>` with `level-N` options (wp_dropdown_categories)
 * - multiselect: the same, `multiple`, class `multiselect`
 * - checkbox: `ul.wpuf-category-checklist > li[.wpuf-checkbox-inline] > label.selectit > input`
 *   (WPUF_Walker_Category_Checklist, children nested in `ul.children`)
 * - text / ajax: `input.textfield` of comma separated names with the term
 *   suggestions the classic `suggest()` gives (`/forms/{id}/terms?search=`)
 *
 * Terms come from the schema (`choices`, the field's exclusions applied
 * server-side). Value: a list of term ids (names for text / ajax).
 *
 * @since WPUF_SINCE
 */
import Field from '../components/Field';
import SuggestInput from './Suggest';

const asList = ( value ) => ( Array.isArray( value ) ? value.map( String ) : ( value ? [ String( value ) ] : [] ) ).filter( ( v ) => '' !== v && '-1' !== v && '0' !== v );

/** Nested terms in display order with their depth. */
const flatTree = ( choices, parent = 0, depth = 0 ) => choices
    .filter( ( term ) => Number( term.parent || 0 ) === Number( parent ) )
    .flatMap( ( term ) => [ { ...term, depth }, ...flatTree( choices, term.id, depth + 1 ) ] );

const indent = ( depth ) => '   '.repeat( depth );

/** The checklist rows, children nested like the classic walker prints them. */
function Checklist( { choices, parent, field, formId, chosen, toggle, onBlur } ) {
    const rows = choices.filter( ( term ) => Number( term.parent || 0 ) === Number( parent ) );

    if ( ! rows.length ) {
        return null;
    }

    return rows.map( ( term ) => {
        const children = choices.some( ( child ) => Number( child.parent || 0 ) === Number( term.id ) );

        return (
            <li key={ term.id } className={ 'yes' === field.show_inline ? 'wpuf-checkbox-inline' : '' } id={ `${ field.name }-${ term.id }` } data-label={ field.label }>
                <label className="selectit">
                    <input
                        className={ `wpuf_${ field.name }_${ formId }` }
                        value={ term.id }
                        type="checkbox"
                        name={ `${ field.name }[]` }
                        id={ `in-${ field.name }-${ term.id }` }
                        checked={ chosen.includes( String( term.id ) ) }
                        onChange={ () => toggle( String( term.id ) ) }
                        onBlur={ onBlur }
                    />
                    { ' ' }{ term.name }
                </label>
                { children && (
                    <ul className="children">
                        <Checklist choices={ choices } parent={ term.id } field={ field } formId={ formId } chosen={ chosen } toggle={ toggle } onBlur={ onBlur } />
                    </ul>
                ) }
            </li>
        );
    } );
}

export default function TaxonomyField( { field, value, onChange, onBlur, formId, error } ) {
    const id = `${ field.name }_${ formId }`;
    const type = field.type || 'select';
    const choices = field.choices || [];
    const options = flatTree( choices );
    const chosen = asList( value );
    const fieldsClass = `wpuf_${ field.name }_${ type }_${ field.id || 0 }_${ formId }`;
    const selectClass = `${ field.name } wpuf_${ field.name }_${ formId }`;
    const toggle = ( termId ) => onChange( chosen.includes( termId ) ? chosen.filter( ( item ) => item !== termId ) : [ ...chosen, termId ] );

    let control;

    if ( 'checkbox' === type ) {
        control = (
            <ul className="wpuf-category-checklist">
                <Checklist choices={ choices } parent={ 0 } field={ field } formId={ formId } chosen={ chosen } toggle={ toggle } onBlur={ onBlur } />
            </ul>
        );
    } else if ( 'text' === type || 'ajax' === type ) {
        control = (
            <SuggestInput
                taxonomy={ field.name }
                formId={ formId }
                value={ value }
                onChange={ onChange }
                onBlur={ onBlur }
                inputProps={ { id, name: field.name, className: `textfield wpuf_${ field.name }_${ formId }`, size: 40 } }
            />
        );
    } else if ( 'multiselect' === type ) {
        control = (
            <select
                id={ id }
                name={ `${ field.name }[]` }
                className={ `${ field.name } multiselect wpuf_${ field.name }_${ formId }` }
                multiple
                value={ chosen }
                onChange={ ( event ) => onChange( Array.from( event.target.selectedOptions ).map( ( option ) => option.value ) ) }
                onBlur={ onBlur }
            >
                { options.map( ( term ) => (
                    <option key={ term.id } className={ `level-${ term.depth }` } value={ String( term.id ) }>{ indent( term.depth ) + term.name }</option>
                ) ) }
            </select>
        );
    } else {
        control = (
            <select
                id={ id }
                name={ field.name }
                className={ selectClass }
                value={ chosen[ 0 ] || '-1' }
                onChange={ ( event ) => onChange( '-1' === event.target.value ? [] : [ event.target.value ] ) }
                onBlur={ onBlur }
            >
                <option value="-1">{ field.first || '--select--' }</option>
                { options.map( ( term ) => (
                    <option key={ term.id } className={ `level-${ term.depth }` } value={ String( term.id ) }>{ indent( term.depth ) + term.name }</option>
                ) ) }
            </select>
        );
    }

    return (
        <Field field={ field } id={ id } error={ error } fieldsClass={ fieldsClass } data-type={ 'checkbox' === type ? 'tax-checkbox' : undefined }>
            { control }
            <span className="wpuf-wordlimit-message wpuf-help" />
        </Field>
    );
}
