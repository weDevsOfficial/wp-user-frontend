/**
 * Form builder date settings (form scheduling From / To) <-> the shared
 * date picker's `YYYY-MM-DD HH:mm:ss`.
 *
 * @since WPUF_SINCE
 */

const pad = ( n ) => String( n ).padStart( 2, '0' );

/**
 * The stored text -> the picker's `YYYY-MM-DD HH:mm:ss`. Develop's builder
 * picker (jQuery UI, `yy-mm-dd`, no time) wrote `YYYY-MM-DD`; typed values may
 * use `/` or carry a time.
 *
 * @param {string} value Stored setting.
 *
 * @return {string} Picker value, '' when it does not read as a date.
 */
export function toPicker( value ) {
    const match = /^(\d{4})[/-](\d{1,2})[/-](\d{1,2})(?:[ T](\d{1,2}):(\d{2}))?/.exec( String( value || '' ).trim() );

    return match ? `${ match[ 1 ] }-${ pad( match[ 2 ] ) }-${ pad( match[ 3 ] ) } ${ pad( match[ 4 ] || 0 ) }:${ match[ 5 ] || '00' }:00` : '';
}

/**
 * The picker's value -> the stored text, read by strtotime(): `YYYY-MM-DD`
 * exactly as develop stored it when no time is set (midnight, develop's only
 * time), `YYYY-MM-DD HH:mm` when a time is picked (new: develop had no time).
 *
 * @param {string} value Picker value.
 *
 * @return {string} Setting value.
 */
export function fromPicker( value ) {
    const match = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/.exec( String( value || '' ) );

    if ( ! match ) {
        return '';
    }

    const day = `${ match[ 1 ] }-${ match[ 2 ] }-${ match[ 3 ] }`;

    return '00' === match[ 4 ] && '00' === match[ 5 ] ? day : `${ day } ${ match[ 4 ] }:${ match[ 5 ] }`;
}
