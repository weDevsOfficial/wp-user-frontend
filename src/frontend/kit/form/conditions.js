/**
 * Conditional logic from the stored `wpuf_cond` setting, evaluated on every
 * value change with the rules of Pro's conditional-logic.js:
 *
 * - The builder stores parallel lists `cond_field[]`, `cond_operator[]`,
 *   `cond_option[]` (a `conditions` list of `{ name, operator, option }` is
 *   read too).
 * - With the logic on, every rule is checked; a rule without a field never
 *   matches. `all`: visible when no rule fails. `any`: visible when one
 *   rule matches. No rules at all: visible.
 * - Operators: `=`, `!=`, `==empty`, `!=empty`, `==contains`, `greater`,
 *   `less`. A choice value of `-1` (the "- Select -" option) counts as empty.
 * - A hidden field posts nothing.
 *
 * @since WPUF_SINCE
 */

const on = ( value ) => 'yes' === value || 'on' === value || true === value || 'true' === value;

const toArray = ( value ) => {
    if ( Array.isArray( value ) ) {
        return value;
    }

    if ( value && 'object' === typeof value ) {
        return Object.values( value );
    }

    return undefined === value || null === value ? [] : [ value ];
};

const asList = ( value ) => {
    if ( Array.isArray( value ) ) {
        return value.map( String ).filter( ( v ) => '' !== v && '-1' !== v );
    }

    if ( value && 'object' === typeof value ) {
        return Object.values( value ).map( String ).filter( Boolean );
    }

    if ( null === value || undefined === value || '' === value || '-1' === String( value ) ) {
        return [];
    }

    return [ String( value ) ];
};

/**
 * The rules of a condition setting, in one shape.
 *
 * @param {Object} cond `wpuf_cond` or `submit_button_cond`.
 * @return {Array<{name: string, operator: string, option: string}>} Rules
 */
export function rulesOf( cond ) {
    if ( Array.isArray( cond.conditions ) ) {
        return cond.conditions.map( ( c ) => ( { name: c && c.name ? String( c.name ) : '', operator: ( c && c.operator ) || '=', option: c && undefined !== c.option ? String( c.option ) : '' } ) );
    }

    const fields = toArray( cond.cond_field );
    const operators = toArray( cond.cond_operator );
    const options = toArray( cond.cond_option );

    return fields.map( ( name, index ) => ( {
        name: name ? String( name ) : '',
        operator: undefined !== operators[ index ] && null !== operators[ index ] ? String( operators[ index ] ) : '',
        option: undefined !== options[ index ] && null !== options[ index ] ? String( options[ index ] ) : '',
    } ) );
}

/**
 * One rule against the current values.
 *
 * @param {Object} rule   Rule
 * @param {Object} values name => value
 * @return {boolean} Matches
 */
export function ruleMatches( rule, values ) {
    if ( ! rule.name ) {
        return false;
    }

    const list = asList( values[ rule.name ] );
    const option = rule.option;

    switch ( rule.operator ) {
        case '==empty':
            return 0 === list.length;
        case '!=empty':
            return list.length > 0;
        case '==contains':
            return list.some( ( value ) => value.indexOf( option ) > -1 );
        case 'greater':
            return list.length > 0 && Number( list[ 0 ] ) > Number( option );
        case 'less':
            return list.length > 0 && Number( list[ 0 ] ) < Number( option );
        case '!=':
            return ! list.includes( option );
        case '=':
            return list.includes( option );
        default:
            return false;
    }
}

const evaluate = ( cond, values ) => {
    if ( ! cond || ! on( cond.condition_status ) ) {
        return true;
    }

    const results = rulesOf( cond ).map( ( rule ) => ruleMatches( rule, values ) );

    if ( ! results.length ) {
        return true;
    }

    return 'any' === cond.cond_logic ? results.includes( true ) : ! results.includes( false );
};

/**
 * Whether a field is visible for the current values.
 *
 * @param {Object} field  Field settings (with `wpuf_cond`).
 * @param {Object} values name => value
 * @return {boolean} Visible
 */
export function isVisible( field, values ) {
    return evaluate( field.wpuf_cond, values );
}

/** The key a field is known by in the visible set (fields without a name use their id or template). */
export const fieldKey = ( field ) => field.name || ( field.id ? `#${ field.id }` : `@${ field.template }` );

/**
 * The visible fields, in order, column fields recursed (an invisible column hides its children).
 *
 * @param {Array}  fields Fields.
 * @param {Object} values name => value
 * @return {Set<string>} Keys of the visible fields
 */
export function visibleNames( fields, values ) {
    const names = new Set();

    const walk = ( list ) => {
        ( list || [] ).forEach( ( field ) => {
            if ( ! field || ! isVisible( field, values ) ) {
                return;
            }

            names.add( fieldKey( field ) );

            if ( 'column_field' === field.template && field.inner_fields ) {
                Object.values( field.inner_fields ).forEach( ( column ) => walk( column || [] ) );
            }
        } );
    };

    walk( fields );

    return names;
}

/**
 * Whether the submit button's own condition (`submit_button_cond`) allows submitting.
 *
 * @param {Object} settings Form settings.
 * @param {Object} values   name => value
 * @return {boolean} Enabled
 */
export function submitAllowed( settings, values ) {
    return evaluate( settings && settings.submit_button_cond, values );
}
