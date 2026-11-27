/**
 * The classic form posted every named input inside it (jQuery serialize),
 * so inputs printed by hooks (`wpuf_form_fields_top`, action-hook fields,
 * server-rendered fields such as Pro's pricing fields) reached the
 * handlers. The React payload is built from state; this adds the named
 * inputs of the form's DOM that the payload does not already carry, with
 * PHP's bracket names turned into nested objects / lists.
 *
 * @since WPUF_SINCE
 */

/** `a[b][]` => [ 'a', 'b', '' ] */
const pathOf = ( name ) => {
    const match = /^([^[\]]+)((?:\[[^\]]*\])*)$/.exec( name );

    if ( ! match ) {
        return [ name ];
    }

    const rest = match[ 2 ] ? match[ 2 ].slice( 1, -1 ).split( '][' ) : [];

    return [ match[ 1 ], ...rest ];
};

const assign = ( target, path, value ) => {
    let node = target;

    path.forEach( ( key, index ) => {
        const last = index === path.length - 1;
        const nextIsList = ! last && '' === path[ index + 1 ];

        if ( '' === key ) {
            if ( last ) {
                node.push( value );
            } else {
                const child = nextIsList ? [] : {};

                node.push( child );
                node = child;
            }

            return;
        }

        if ( last ) {
            node[ key ] = value;

            return;
        }

        if ( undefined === node[ key ] || 'object' !== typeof node[ key ] ) {
            node[ key ] = nextIsList ? [] : {};
        }

        node = node[ key ];
    } );
};

/**
 * @param {Object}          body The payload built from state.
 * @param {HTMLFormElement} form The rendered form.
 * @return {Object} The payload with the unmanaged inputs added.
 */
export function mergeFormInputs( body, form ) {
    if ( ! form || 'function' !== typeof window.FormData ) {
        return body;
    }

    const extra = {};

    new window.FormData( form ).forEach( ( value, name ) => {
        if ( ! name || 'string' !== typeof value ) {
            return;
        }

        const path = pathOf( name );

        // The payload owns every key it already has (React-managed fields).
        if ( Object.prototype.hasOwnProperty.call( body, path[ 0 ] ) || 'submit' === path[ 0 ] ) {
            return;
        }

        assign( extra, path, value );
    } );

    return { ...extra, ...body };
}
