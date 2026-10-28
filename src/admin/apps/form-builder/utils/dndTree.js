/**
 * Field lists of the builder canvas as drag-and-drop containers (design.md D16).
 *
 * A container id names one list:
 * - `top`: the form's own fields;
 * - `column:<fieldId>:<column-n>`: one cell of a column field (`inner_fields`
 *   is an object of cells);
 * - `repeat:<fieldId>`: a repeat field's inner fields (`inner_fields` is an array).
 *
 * Moves keep each field object as it is (develop's stored shape: an inner field
 * is the same field object inside its parent's `inner_fields`).
 *
 * @since WPUF_SINCE
 */

export const TOP = 'top';

// Develop: form-column_field isAllowedInColumnField.
const RESTRICTED_IN_COLUMN = [ 'column_field', 'custom_hidden_field', 'step_start' ];

// Develop: form-repeat_field isAllowedInRepeatField.
export const ALLOWED_IN_REPEAT = [
    'text_field',
    'textarea_field',
    'dropdown_field',
    'multiple_select',
    'radio_field',
    'checkbox_field',
    'website_url',
    'date_field',
    'phone_field',
    'numeric_text_field',
    'email_address',
];

/**
 * @param {number|string} fieldId Column field id.
 * @param {string}        column  Cell key (`column-1`).
 *
 * @return {string} Container id.
 */
export const columnContainer = ( fieldId, column ) => `column:${ fieldId }:${ column }`;

/**
 * @param {number|string} fieldId Repeat field id.
 *
 * @return {string} Container id.
 */
export const repeatContainer = ( fieldId ) => `repeat:${ fieldId }`;

/**
 * @param {string} id Container id.
 *
 * @return {Object} `{ type: 'top' }`, `{ type: 'column', fieldId, column }` or `{ type: 'repeat', fieldId }`.
 */
export function parseContainer( id ) {
    const parts = String( id ).split( ':' );

    if ( 'column' === parts[ 0 ] ) {
        return { type: 'column', fieldId: parts[ 1 ], column: parts[ 2 ] };
    }

    if ( 'repeat' === parts[ 0 ] ) {
        return { type: 'repeat', fieldId: parts[ 1 ] };
    }

    return { type: 'top' };
}

const sameId = ( a, b ) => String( a ) === String( b );

/**
 * Replace one container field (top level or inside a column cell) with
 * `update( field )`; every other object is kept.
 *
 * @param {Array}         fields  Form fields.
 * @param {number|string} fieldId Container field id.
 * @param {Function}      update  ( field ) => new field.
 *
 * @return {Array} New form fields.
 */
function updateContainerField( fields, fieldId, update ) {
    return fields.map( ( field ) => {
        if ( sameId( field.id, fieldId ) ) {
            return update( field );
        }

        if ( 'column_field' === field.template && field.inner_fields && ! Array.isArray( field.inner_fields ) ) {
            let changed = false;
            const cells = {};

            Object.keys( field.inner_fields ).forEach( ( cell ) => {
                cells[ cell ] = ( field.inner_fields[ cell ] || [] ).map( ( inner ) => {
                    if ( sameId( inner.id, fieldId ) ) {
                        changed = true;
                        return update( inner );
                    }

                    return inner;
                } );
            } );

            return changed ? { ...field, inner_fields: cells } : field;
        }

        return field;
    } );
}

/**
 * Find a container field anywhere it can live (top level or a column cell).
 *
 * @param {Array}         fields  Form fields.
 * @param {number|string} fieldId Field id.
 *
 * @return {Object|null} Field.
 */
function findContainerField( fields, fieldId ) {
    for ( const field of fields ) {
        if ( sameId( field.id, fieldId ) ) {
            return field;
        }

        if ( 'column_field' === field.template && field.inner_fields && ! Array.isArray( field.inner_fields ) ) {
            for ( const cell of Object.keys( field.inner_fields ) ) {
                const inner = ( field.inner_fields[ cell ] || [] ).find( ( item ) => sameId( item.id, fieldId ) );

                if ( inner ) {
                    return inner;
                }
            }
        }
    }

    return null;
}

/**
 * @param {Array}  fields      Form fields.
 * @param {string} containerId Container id.
 *
 * @return {Array} The container's list ([] when it does not exist).
 */
export function getList( fields, containerId ) {
    const target = parseContainer( containerId );

    if ( 'top' === target.type ) {
        return fields;
    }

    const owner = findContainerField( fields, target.fieldId );

    if ( ! owner ) {
        return [];
    }

    if ( 'repeat' === target.type ) {
        return Array.isArray( owner.inner_fields ) ? owner.inner_fields : [];
    }

    return ( owner.inner_fields && owner.inner_fields[ target.column ] ) || [];
}

/**
 * @param {Array}  fields      Form fields.
 * @param {string} containerId Container id.
 * @param {Array}  list        New list.
 *
 * @return {Array} New form fields.
 */
export function setList( fields, containerId, list ) {
    const target = parseContainer( containerId );

    if ( 'top' === target.type ) {
        return list;
    }

    return updateContainerField( fields, target.fieldId, ( owner ) => {
        if ( 'repeat' === target.type ) {
            return { ...owner, inner_fields: list };
        }

        return { ...owner, inner_fields: { ...( owner.inner_fields || {} ), [ target.column ]: list } };
    } );
}

/**
 * Whether a move changes nothing (same list, same place).
 *
 * @param {Object} from `{ container, index }`
 * @param {Object} to   `{ container, index }` (insertion index before removal)
 *
 * @return {boolean} No-op.
 */
export function isNoopMove( from, to ) {
    return from.container === to.container && ( to.index === from.index || to.index === from.index + 1 );
}

// Keys the stage adds to its fields (ADD_FIELD, "only missing values"); develop's
// column and repeat inner fields do not have them.
const STAGE_DEFAULTS = { show_icon: 'no', field_icon: '', icon_position: 'left_label' };

/**
 * A moved field in its new list's stored shape (develop's): into a column or
 * repeat, the stage-only icon keys go when they still hold the defaults (a
 * field showing an icon keeps all three); onto the stage, missing ones get the defaults, as a
 * field added to the stage. The name is never changed (it is the meta key of
 * the data already submitted with the field).
 *
 * @param {Object} field Field.
 * @param {string} from  Source container id.
 * @param {string} to    Target container id.
 *
 * @return {Object} Field (the same object when nothing changes).
 */
function reshapeForList( field, from, to ) {
    const fromTop = 'top' === parseContainer( from ).type;
    const toTop = 'top' === parseContainer( to ).type;

    // Same kind of list, or an icon in use (its keys are real settings then).
    if ( fromTop === toTop || ( ! toTop && field.show_icon && 'no' !== field.show_icon ) ) {
        return field;
    }

    const keys = Object.keys( STAGE_DEFAULTS ).filter( ( key ) => ( toTop
        ? undefined === field[ key ] || null === field[ key ]
        : field[ key ] === STAGE_DEFAULTS[ key ] ) );

    if ( ! keys.length ) {
        return field;
    }

    const next = { ...field };

    keys.forEach( ( key ) => {
        if ( toTop ) {
            next[ key ] = STAGE_DEFAULTS[ key ];
        } else {
            delete next[ key ];
        }
    } );

    return next;
}

/**
 * Move one field between (or inside) lists.
 *
 * @param {Array}  fields Form fields.
 * @param {Object} from   `{ container, index }`
 * @param {Object} to     `{ container, index }`: insertion index in the target
 *                        list as it is before the field leaves its old place.
 *
 * @return {Array} New form fields (the same array for a no-op or a bad move).
 *                 Between the stage and a column the field takes its new
 *                 list's stored shape (reshapeForList).
 */
export function moveField( fields, from, to ) {
    if ( isNoopMove( from, to ) ) {
        return fields;
    }

    const source = getList( fields, from.container );
    const moved = source[ from.index ];

    if ( ! moved ) {
        return fields;
    }

    let toIndex = to.index;

    if ( from.container === to.container && to.index > from.index ) {
        toIndex -= 1;
    }

    const without = setList( fields, from.container, source.filter( ( _, index ) => index !== from.index ) );
    const target = [ ...getList( without, to.container ) ];

    target.splice( Math.max( 0, Math.min( toIndex, target.length ) ), 0, reshapeForList( moved, from.container, to.container ) );

    return setList( without, to.container, target );
}

/**
 * Where a field lives.
 *
 * @param {Array}         fields  Form fields.
 * @param {number|string} fieldId Field id.
 *
 * @return {Object|null} `{ container, index }`
 */
export function locateField( fields, fieldId ) {
    const topIndex = fields.findIndex( ( field ) => sameId( field.id, fieldId ) );

    if ( -1 !== topIndex ) {
        return { container: TOP, index: topIndex };
    }

    for ( const field of fields ) {
        if ( Array.isArray( field.inner_fields ) ) {
            const index = field.inner_fields.findIndex( ( inner ) => sameId( inner.id, fieldId ) );

            if ( -1 !== index ) {
                return { container: repeatContainer( field.id ), index };
            }
        } else if ( field.inner_fields && 'object' === typeof field.inner_fields ) {
            for ( const cell of Object.keys( field.inner_fields ) ) {
                const list = field.inner_fields[ cell ] || [];
                const index = list.findIndex( ( inner ) => sameId( inner.id, fieldId ) );

                if ( -1 !== index ) {
                    return { container: columnContainer( field.id, cell ), index };
                }

                // A repeat field inside a cell.
                for ( const inner of list ) {
                    if ( Array.isArray( inner.inner_fields ) ) {
                        const innerIndex = inner.inner_fields.findIndex( ( item ) => sameId( item.id, fieldId ) );

                        if ( -1 !== innerIndex ) {
                            return { container: repeatContainer( inner.id ), index: innerIndex };
                        }
                    }
                }
            }
        }
    }

    return null;
}

/**
 * Whether a dragged item may go into a list.
 *
 * - palette field: anywhere on the stage; into a column cell unless it is a
 *   column / hidden / step field; into a repeat when the repeat accepts its
 *   type (develop's rules; refused drops get develop's message);
 * - field already on the canvas: reorder on the stage; column cell <-> stage
 *   both ways and between cells (owner 2026-10-02, D16); never column / hidden /
 *   step / repeat fields into a cell, nor into its own cells; repeat inner
 *   fields only inside their repeat; nothing else enters a repeat (develop).
 *
 * @param {Object} item        `{ kind: 'palette', template }` or `{ kind: 'field', template, fieldId, container }`
 * @param {string} containerId Target container id.
 *
 * @return {boolean} Allowed.
 */
export function canDrop( item, containerId ) {
    const target = parseContainer( containerId );

    if ( 'palette' === item.kind ) {
        if ( 'column' === target.type ) {
            return ! RESTRICTED_IN_COLUMN.includes( item.template );
        }

        if ( 'repeat' === target.type ) {
            return ALLOWED_IN_REPEAT.includes( item.template );
        }

        return true;
    }

    const source = parseContainer( item.container );

    if ( 'repeat' === source.type ) {
        return 'repeat' === target.type && sameId( target.fieldId, source.fieldId );
    }

    if ( 'repeat' === target.type ) {
        return false;
    }

    if ( 'column' === target.type ) {
        return ! RESTRICTED_IN_COLUMN.includes( item.template )
            && 'repeat_field' !== item.template
            && ! sameId( target.fieldId, item.fieldId );
    }

    return true;
}
