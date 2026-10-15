/**
 * Pick out what the user actually edited, so a save only writes those values.
 *
 * The legacy screen saved one section at a time; sending every section on each
 * save re-sanitized untouched values and rewrote own-option settings. Values
 * compare by their JSON form, which is how they travel to the server anyway.
 */
const same = ( a, b ) => JSON.stringify( a ) === JSON.stringify( b );

/**
 * Changed fields only, keyed by section id then field name.
 *
 * @param {Object} values Current section values.
 * @param {Object} saved  Last saved section values.
 *
 * @return {Object} Sections that hold at least one changed field.
 */
export function changedValues( values = {}, saved = {} ) {
    const changed = {};

    Object.keys( values ).forEach( ( sectionId ) => {
        const current = values[ sectionId ] || {};
        const before = saved[ sectionId ] || {};

        Object.keys( current ).forEach( ( name ) => {
            if ( ! same( current[ name ], before[ name ] ) ) {
                changed[ sectionId ] = changed[ sectionId ] || {};
                changed[ sectionId ][ name ] = current[ name ];
            }
        } );
    } );

    return changed;
}

/**
 * Changed `extra` keys only. Each key belongs to one own-option handler that
 * skips the write when its key is absent.
 *
 * @param {Object} extra Current extra channel.
 * @param {Object} saved Last saved extra channel.
 *
 * @return {Object} Extra keys whose value changed.
 */
export function changedExtra( extra = {}, saved = {} ) {
    const changed = {};

    Object.keys( extra ).forEach( ( key ) => {
        if ( ! same( extra[ key ], saved[ key ] ) ) {
            changed[ key ] = extra[ key ];
        }
    } );

    return changed;
}
