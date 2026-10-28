import { __ } from '@wordpress/i18n';
import { Select } from '@wpuf/components';
import { SELECT_SIZE } from '../controlSize';
import SettingLabel from './SettingLabel';

// Lists longer than this get a search box (pages, currencies, roles).
const SEARCH_FROM = 8;

/**
 * Option labels can carry HTML entities (currency labels store `&#36;` for
 * `$`); the legacy `<select>` let the browser decode them.
 *
 * @param {*} text Label.
 * @return {*} Decoded label.
 */
const decodeEntities = ( text ) => {
    if ( 'string' !== typeof text || -1 === text.indexOf( '&' ) ) {
        return text;
    }
    const area = document.createElement( 'textarea' );
    area.innerHTML = text;

    return area.value;
};

/**
 * Options in the order PHP sent them where JavaScript keeps it; the empty key
 * ("- Select -") goes first again (JS lists integer-like keys first).
 *
 * @param {Object} options { value: label | { label, name } }.
 * @return {Array} [ { value, label } ].
 */
export function selectOptions( options ) {
    const list = Object.keys( options || {} ).map( ( key ) => {
        const option = options[ key ];
        const label = option && 'object' === typeof option ? ( option.label || option.name || key ) : option;

        return { value: key, label: String( decodeEntities( label ) ) };
    } );

    return [ ...list.filter( ( item ) => '' === item.value ), ...list.filter( ( item ) => '' !== item.value ) ];
}

/**
 * Single select setting on the shared Select (4.6a); stores one string like
 * the legacy `<select>`. Shows the stored value, else the default, else the
 * placeholder; nothing is written until a pick.
 */
export default function SelectDropdown( { field, name, value, onChange, disabled = false } ) {
    const options = selectOptions( field.options );
    const current = value !== undefined && value !== '' ? value : ( field.default || '' );

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <div className="mt-1">
                <Select
                    id={ name }
                    className={ SELECT_SIZE }
                    options={ options }
                    value={ current }
                    searchable={ options.length >= SEARCH_FROM }
                    disabled={ disabled }
                    placeholder={ field.placeholder || __( 'Select…', 'wp-user-frontend' ) }
                    onChange={ ( next ) => onChange( name, next ) }
                />
            </div>
        </>
    );
}
