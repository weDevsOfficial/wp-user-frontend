import { __ } from '@wordpress/i18n';

export const RULE_OPTIONS = [
    { label: __( 'All', 'wp-user-frontend' ), value: 'all' },
    { label: __( 'Any', 'wp-user-frontend' ), value: 'any' },
];

export const OPERATORS = {
    radio: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'any selection', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'no selection', 'wp-user-frontend' ), value: '==empty' },
    ],
    text: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'contains', 'wp-user-frontend' ), value: '==contains' },
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
    ],
    number: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'contains', 'wp-user-frontend' ), value: '==contains' },
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
        { label: __( 'value is greater than', 'wp-user-frontend' ), value: 'greater' },
        { label: __( 'value is less than', 'wp-user-frontend' ), value: 'less' },
    ],
    others: [
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
    ],
};

/**
 * Get operators for a given input type.
 *
 * @param {string} inputType
 * @return {Array}
 */
export function getOperatorsForType( inputType ) {
    switch ( inputType ) {
        case 'select':
        case 'radio':
        case 'category':
        case 'taxonomy':
        case 'checkbox':
            return OPERATORS.radio;
        case 'text':
        case 'textarea':
        case 'email':
        case 'url':
        case 'password':
            return OPERATORS.text;
        case 'numeric_text':
            return OPERATORS.number;
        case null:
            return [];
        default:
            return OPERATORS.others;
    }
}

/**
 * Map field template to input type string.
 *
 * @param {string} template
 * @return {string}
 */
function templateToInputType( template ) {
    switch ( template ) {
        case 'radio_field':
            return 'radio';
        case 'checkbox_field':
            return 'checkbox';
        case 'dropdown_field':
            return 'select';
        case 'text_field':
            return 'text';
        case 'textarea_field':
            return 'textarea';
        case 'email_address':
            return 'email';
        case 'numeric_text_field':
            return 'numeric_text';
        case 'website_url':
            return 'url';
        case 'password':
            return 'password';
        default:
            return template ? template.replace( '_field', '' ) : '';
    }
}

/**
 * Whether the operator makes the value input unnecessary.
 *
 * @param {string} operator
 * @return {boolean}
 */
export function isEmptyOperator( operator ) {
    return operator === '==empty' || operator === '!=empty';
}

/**
 * Whether the input type should show a dropdown (has discrete options).
 *
 * @param {string} inputType
 * @return {boolean}
 */
export function isDropdownType( inputType ) {
    return [ 'select', 'radio', 'category', 'taxonomy', 'checkbox' ].includes( inputType );
}

/**
 * Input type a condition stores for a dependency field: the field's own
 * `input_type`, as the Vue builder stored it, else one derived from the template.
 *
 * @param {Object} field
 * @return {string}
 */
export function conditionInputType( field ) {
    if ( ! field ) {
        return '';
    }

    return field.input_type || templateToInputType( field.template );
}

/**
 * Field type a condition stores: a rich textarea stores its `rich` value, else
 * the field's `type`, else the input type (same order as the Vue builder).
 *
 * @param {Object} field
 * @return {string}
 */
export function conditionFieldType( field ) {
    const inputType = conditionInputType( field );

    if ( inputType === 'textarea' && field && Object.prototype.hasOwnProperty.call( field, 'rich' ) ) {
        return field.rich;
    }

    return field && field.type !== undefined ? field.type : inputType;
}

/**
 * Hierarchical taxonomy names from `wpuf_form_builder.wp_post_types`.
 *
 * @param {Object} wpPostTypes
 * @return {Array<string>}
 */
export function hierarchicalTaxonomies( wpPostTypes = {} ) {
    const names = [];

    Object.values( wpPostTypes || {} ).forEach( ( taxonomies ) => {
        Object.entries( taxonomies || {} ).forEach( ( [ taxonomy, props ] ) => {
            if ( props && props.hierarchical ) {
                names.push( taxonomy );
            }
        } );
    } );

    return names;
}

/**
 * Options a condition can match for a field (develop get_cond_options): the
 * terms of a hierarchical taxonomy (by field name; `raw` = the term id as
 * stored, a number), else the field's own options (`raw` = option key).
 * `value` is the string form for the Select wrapper.
 *
 * @param {Object} field
 * @param {Object} wpPostTypes `wpuf_form_builder.wp_post_types`.
 * @param {Array}  [taxonomies] Hierarchical taxonomy names (default: from wpPostTypes).
 * @return {Array<{value: string, label: string, raw: *}>}
 */
export function getFieldOptions( field, wpPostTypes = {}, taxonomies = null ) {
    if ( ! field ) {
        return [];
    }

    const options = [];
    const hierarchical = taxonomies || hierarchicalTaxonomies( wpPostTypes );

    if ( hierarchical.includes( field.name ) ) {
        // Two post types never share a taxonomy, so the first match is the one.
        const owner = Object.values( wpPostTypes || {} ).find( ( items ) =>
            items && Object.prototype.hasOwnProperty.call( items, field.name )
        );
        const terms = owner && owner[ field.name ] ? owner[ field.name ].terms : null;

        ( Array.isArray( terms ) ? terms : [] ).forEach( ( term ) => options.push( { value: String( term.term_id ), label: term.name, raw: term.term_id } ) );

        return options;
    }

    if ( field.options && typeof field.options === 'object' && ! Array.isArray( field.options ) ) {
        for ( const key in field.options ) {
            if ( Object.prototype.hasOwnProperty.call( field.options, key ) ) {
                options.push( { value: key, label: field.options[ key ], raw: key } );
            }
        }
    }

    return options;
}

/**
 * Fields a rule can depend on (develop's `dependencies`): supported fields
 * with a name and label at the top level and inside column cells (not repeat
 * fields), taxonomies by name, never the field being edited.
 *
 * @param {Array}  formFields
 * @param {Array}  supported   `wpuf_cond_supported_fields` + hierarchical taxonomies.
 * @param {string} editingName Name of the field being edited.
 * @return {Array}
 */
export function condDependencies( formFields, supported, editingName ) {
    const deps = [];
    const accept = ( field ) => {
        if ( ! field || editingName === field.name || ! field.label ) {
            return;
        }

        if ( 'taxonomy' === field.template ? supported.includes( field.name ) : ( supported.includes( field.template ) && field.name ) ) {
            deps.push( field );
        }
    };

    ( formFields || [] ).forEach( ( field ) => {
        if ( 'column_field' === field.template ) {
            Object.values( field.inner_fields || {} ).forEach( ( cell ) => ( Array.isArray( cell ) ? cell : [] ).forEach( accept ) );
        } else {
            accept( field );
        }
    } );

    return deps;
}

/**
 * Rule rows shown for a stored `wpuf_cond` (develop `created`): stored rows
 * with a field and an operator, else one blank row. Conditions saved before
 * `input_type` existed take the input type of the top-level field.
 *
 * @param {Object} cond       Stored wpuf_cond.
 * @param {Array}  formFields
 * @return {Array<Object>}
 */
export function condRowsFromStored( cond, formFields = [] ) {
    const rows = [];
    const at = ( list, i, fallback ) => ( Array.isArray( list ) && list[ i ] !== undefined ? list[ i ] : fallback );

    ( ( cond && cond.cond_field ) || [] ).forEach( ( name, i ) => {
        const operator = at( cond.cond_operator, i );

        if ( ! name || ! operator ) {
            return;
        }

        let inputType;

        if ( cond.input_type === undefined ) {
            const top = ( formFields || [] ).find( ( field ) => field.name === name );
            inputType = top && top.input_type !== undefined ? top.input_type : '';
        } else {
            inputType = at( cond.input_type, i, '' );
        }

        rows.push( {
            name,
            operator,
            option: at( cond.cond_option, i ),
            option_title: at( cond.option_title, i, '' ),
            input_type: inputType,
            field_type: at( cond.field_type, i, '' ),
        } );
    } );

    return rows.length ? rows : [ { name: '', operator: '', option: '' } ];
}

/**
 * `wpuf_cond` with its six arrays rebuilt from the rule rows (develop's
 * conditions watcher): other keys kept; a missing wpuf_cond starts as
 * `condition_status: 'no'`, `cond_logic: 'all'`; "has any / no value" rules
 * store an empty value. Missing row keys stay `undefined` (saved as null, as
 * develop).
 *
 * @param {Object} cond Stored wpuf_cond.
 * @param {Array}  rows Rule rows.
 * @return {Object}
 */
export function buildCondArrays( cond, rows ) {
    const next = cond ? { ...cond } : { condition_status: 'no', cond_logic: 'all' };

    next.cond_field = rows.map( ( row ) => row.name );
    next.cond_operator = rows.map( ( row ) => row.operator );
    next.cond_option = rows.map( ( row ) => ( isEmptyOperator( row.operator ) ? '' : row.option ) );
    next.option_title = rows.map( ( row ) => row.option_title );
    next.input_type = rows.map( ( row ) => row.input_type );
    next.field_type = rows.map( ( row ) => row.field_type );

    return next;
}

/**
 * Value stored at a dot path of the form settings.
 *
 * @param {Object} settings Form settings.
 * @param {string} path     e.g. `integrations.mailchimp.wpuf_cond`.
 * @return {*} Value or undefined.
 */
export function readPath( settings, path ) {
    return path.split( '.' ).reduce( ( node, key ) => ( node && 'object' === typeof node ? node[ key ] : undefined ), settings );
}

/**
 * Copy of the top-level setting a dot path starts with, with the value set
 * at the path (sibling keys kept, missing levels created as develop's $set did).
 *
 * @param {Object} settings Form settings.
 * @param {string} path     Dot path.
 * @param {*}      value    Value.
 * @return {Array} [ top-level key, new top-level value ].
 */
export function writePath( settings, path, value ) {
    const keys = path.split( '.' );
    const top = keys[ 0 ];

    if ( 1 === keys.length ) {
        return [ top, value ];
    }

    const copy = ( node ) => ( node && 'object' === typeof node && ! Array.isArray( node ) ? { ...node } : {} );
    const root = copy( settings[ top ] );
    let node = root;

    keys.slice( 1, -1 ).forEach( ( key ) => {
        node[ key ] = copy( node[ key ] );
        node = node[ key ];
    } );
    node[ keys[ keys.length - 1 ] ] = value;

    return [ top, root ];
}
