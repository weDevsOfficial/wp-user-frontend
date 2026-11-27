/**
 * Add members to `window.wpuf` without replacing anything already there
 * (the builder's flat registry members, or a member another script set).
 * Object members are merged key by key the same way.
 *
 * @param {Object} members Members to add.
 *
 * @return {Object} window.wpuf
 */
export default function publish( members ) {
    const wpuf = ( window.wpuf = window.wpuf || {} );

    Object.keys( members ).forEach( ( key ) => {
        const value = members[ key ];

        if ( undefined === wpuf[ key ] ) {
            wpuf[ key ] = value;
            return;
        }

        const isPlain = ( item ) => item && 'object' === typeof item && ! Array.isArray( item );

        if ( isPlain( wpuf[ key ] ) && isPlain( value ) && ! Object.isFrozen( wpuf[ key ] ) ) {
            Object.keys( value ).forEach( ( inner ) => {
                if ( undefined === wpuf[ key ][ inner ] ) {
                    wpuf[ key ][ inner ] = value[ inner ];
                }
            } );
        }
    } );

    return wpuf;
}
