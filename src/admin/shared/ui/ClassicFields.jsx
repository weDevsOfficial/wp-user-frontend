/**
 * Fields other plugins print through a classic edit screen's PHP form hooks
 * (coupon, subscription pack), already sanitized with wp_kses on the server
 * (Platform\Classic\Hook_Fields). Table rows and free markup sit in one form
 * so classicFieldValues() reads them back as the classic form posted them.
 *
 * @since WPUF_SINCE
 */
import { forwardRef } from '@wordpress/element';

/**
 * Values of the fields' inputs (`name[]` gathers a list).
 *
 * @param {HTMLFormElement|null} form The ClassicFields form.
 *
 * @return {Object} Values.
 */
export const classicFieldValues = ( form ) => {
    const values = {};

    if ( ! form ) {
        return values;
    }

    new window.FormData( form ).forEach( ( value, name ) => {
        if ( name.endsWith( '[]' ) ) {
            const key = name.slice( 0, -2 );

            values[ key ] = [ ...( values[ key ] || [] ), value ];
        } else {
            values[ name ] = value;
        }
    } );

    return values;
};

/**
 * @param {Object} props
 * @param {string} [props.rows]      Table rows markup.
 * @param {string} [props.html]      Other markup.
 * @param {string} [props.className] Extra classes.
 * @param {Function} [props.onChange] Called when one of the fields changes.
 */
const ClassicFields = forwardRef( function ClassicFields( { rows = '', html = '', className = '', onChange }, ref ) {
    if ( ! rows && ! html ) {
        return null;
    }

    /* eslint-disable react/no-danger -- sanitized with wp_kses on the server (Hook_Fields::render()). */
    return (
        <form ref={ ref } onSubmit={ ( event ) => event.preventDefault() } onInput={ onChange } onChange={ onChange } className={ `wpuf-classic-fields text-sm text-gray-700 ${ className }`.trim() }>
            { rows && <table className="w-full border-collapse"><tbody dangerouslySetInnerHTML={ { __html: rows } } /></table> }
            { html && <div dangerouslySetInnerHTML={ { __html: html } } /> }
        </form>
    );
    /* eslint-enable react/no-danger */
} );

export default ClassicFields;
