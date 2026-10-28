/**
 * The column field, in Form_Field_Column's markup: `li.wpuf-el.column_field
 * > div.wpuf-field-columns.has-columns-N > div.wpuf-column-field-inner-columns
 * > div.wpuf-column > div.column-i.items-of-column-N.wpuf-column-inner-fields`
 * (width from `inner_columns_size`, padding-right from `column_space`)
 * `> ul.wpuf-column-fields`, each inner field rendered through the same
 * dispatch as the top level.
 *
 * @since WPUF_SINCE
 */
import { cx } from '../components/primitives';

/**
 * @param {Object}   props
 * @param {Object}   props.field       The column field.
 * @param {number}   props.formId      Form id.
 * @param {Function} props.renderField Renders one inner field (given by FormRenderer).
 */
export default function ColumnField( { field, formId, renderField } ) {
    const count = Math.max( 1, Math.min( 3, Number( field.columns ) || 1 ) );
    const sizes = field.inner_columns_size || {};
    const inner = field.inner_fields || {};
    const space = '' !== String( field.column_space ?? '' ) ? `${ field.column_space }px` : undefined;

    return (
        <li className={ cx( 'wpuf-el', 'column_field', field.css, `wpuf_column_field_${ field.id || 0 }_${ formId }` ) } data-label="wpuf-column-field">
            <div className={ `wpuf-field-columns has-columns-${ count }` }>
                <div className="wpuf-column-field-inner-columns">
                    <div className="wpuf-column">
                        { Array.from( { length: count } ).map( ( _, index ) => {
                            const key = `column-${ index + 1 }`;

                            return (
                                <div key={ key } className={ `${ key } items-of-column-${ count } wpuf-column-inner-fields` } style={ { width: sizes[ key ] || `${ ( 100 / count ).toFixed( 2 ) }%`, paddingRight: space } }>
                                    <ul className="wpuf-column-fields">
                                        { ( inner[ key ] || [] ).map( ( child ) => renderField( child ) ) }
                                    </ul>
                                </div>
                            );
                        } ) }
                    </div>
                </div>
            </div>
        </li>
    );
}
