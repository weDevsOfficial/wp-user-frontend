/**
 * The row of one form field, in the exact classic markup
 * (`li.wpuf-el.<name>.field-size-<size>[data-label] > div.wpuf-label > label`
 * + `div.wpuf-fields`, `span.required`, `span.wpuf-help`,
 * `div.wpuf-error-msg`, `has-error` on the fields box), so the classic
 * template sheets (layout1-5, Pro reg-template-*) style the React form the
 * way they style the PHP one, and CSS written for the PHP markup keeps
 * matching.
 *
 * @since WPUF_SINCE
 */
import { cx } from './primitives';

/** The error of a broken content limit: the row turns red, the message is in the word-limit span. */
export const LIMIT_ERROR = 'wpuf-content-limit';

/** Whether a field setting says "required" (Field_Contract::is_required(): exactly "yes"). */
export const isRequired = ( field ) => 'yes' === field.required;

/** Whether the field is read-only (its inputs are disabled and post nothing, as the classic form does). */
export const isReadOnly = ( field ) => 'yes' === field.read_only;

/** The label icon of Field_Contract::print_label(): an image URL or an icon class, left or right. */
function LabelIcon( { field, side } ) {
    if ( 'yes' !== field.show_icon || ! field.field_icon || `${ side }_label` !== field.icon_position ) {
        return null;
    }

    const value = String( field.field_icon );
    const image = /^https?:\/\//i.test( value ) || value.startsWith( '/' );

    return image
        ? <img src={ value } alt="" className={ `wpuf-field-icon wpuf-field-icon-img wpuf-field-icon-${ side }` } style={ { width: '1em', height: '1em', objectFit: 'contain', verticalAlign: 'middle', display: 'inline-block' } } />
        : <i className={ `${ value } wpuf-field-icon wpuf-field-icon-${ side }` } aria-hidden="true" />;
}

/**
 * The classic label box: `div.wpuf-label > label[for] Label <span.required>*</span>`.
 *
 * @param {Object} props
 * @param {Object} props.field Field settings.
 * @param {string} [props.id]  Control id the label points to.
 */
export function FieldLabel( { field, id } ) {
    return (
        <div className="wpuf-label">
            <label htmlFor={ id }>
                <LabelIcon field={ field } side="left" />
                { field.label_html ? <span dangerouslySetInnerHTML={ { __html: field.label_html } } /> : field.label }
                { isRequired( field ) && <> <span className="required">*</span></> }
                <LabelIcon field={ field } side="right" />
            </label>
        </div>
    );
}

/**
 * @param {Object}  props
 * @param {Object}  props.field          The field settings from the schema.
 * @param {string}  [props.id]           Control id the label points to.
 * @param {string}  [props.error]        Validation message.
 * @param {boolean} [props.noLabel]      Rows without a label box (HTML, section break).
 * @param {*}       [props.label]        A label node replacing the classic label box (social fields).
 * @param {string}  [props.className]    Extra row classes.
 * @param {string}  [props.fieldsClass]  Extra classes on `div.wpuf-fields`.
 * @param {boolean} [props.noHelp]       The component prints the help itself.
 * @param {*}       [props.after]        Rendered after the fields box, inside the row.
 * @param {string}  [props.fieldsBase]   The fields box class (`wpuf-fields`; the address field uses `wpuf-fields-address`).
 * @param {boolean} [props.invalid]      Mark the box `has-error` without a message (the classic content limit).
 */
export default function Field( { field, id, error, children, noLabel = false, label = null, className = '', fieldsClass = '', noHelp = false, after = null, fieldsBase = 'wpuf-fields', invalid = false, ...rest } ) {
    // print_list_attributes(): the size class only when the field has a width; `row_class`
    // when the field prints its row under another name (Pro's profile fields: `wpuf_<name>`).
    const size = field.width ? `field-size-${ field.width }` : '';

    return (
        <li
            className={ cx( 'wpuf-el', field.row_class || field.name, size, field.css, isReadOnly( field ) && 'read-only', className ) }
            data-label={ field.label }
            { ...rest }
        >
            { label }
            { ! label && ! noLabel && <FieldLabel field={ field } id={ id } /> }
            <div className={ cx( fieldsBase, fieldsClass, ( error || invalid ) && 'has-error' ) }>
                { children }
                { ! noHelp && field.help && <span className="wpuf-help" dangerouslySetInnerHTML={ { __html: field.help } } /> }
                { error && LIMIT_ERROR !== error && (
                    <div className="wpuf-error-msg" role="alert" aria-live="assertive">
                        { error }
                    </div>
                ) }
            </div>
            { after }
        </li>
    );
}
