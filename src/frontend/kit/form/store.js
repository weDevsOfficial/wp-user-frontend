/**
 * `wpuf/frontend-form`: the state of every React form on the page (values,
 * errors, touched, step), one slice per form instance. `useForm()` is the
 * way in for the apps and field components; the store lets other code
 * (Pro, add-ons, tests) read a form with `select( 'wpuf/frontend-form' )`
 * by the instance key `useForm()` returns as `formKey`.
 *
 * A slice is written on the first change and starts from the form's
 * initial values, so the first render needs no dispatch.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';

export const STORE_NAME = 'wpuf/frontend-form';

const EMPTY = {};

const fresh = ( initial ) => ( { values: initial || {}, errors: EMPTY, touched: EMPTY, step: 0 } );

/** `next` as a value or a React-style updater of the current value. */
const resolve = ( next, current ) => ( 'function' === typeof next ? next( current ) : next );

export function reducer( state = {}, action ) {
    if ( ! action.key ) {
        return state;
    }

    const current = state[ action.key ] || fresh( action.initial );

    switch ( action.type ) {
        case 'SET_VALUE': {
            const errors = current.errors[ action.name ]
                ? Object.fromEntries( Object.entries( current.errors ).filter( ( [ name ] ) => name !== action.name ) )
                : current.errors;

            return { ...state, [ action.key ]: { ...current, values: { ...current.values, [ action.name ]: action.value }, errors } };
        }

        case 'SET_VALUES':
            return { ...state, [ action.key ]: { ...current, values: resolve( action.values, current.values ) } };

        case 'SET_ERRORS':
            return { ...state, [ action.key ]: { ...current, errors: resolve( action.errors, current.errors ) } };

        case 'TOUCH':
            return current.touched[ action.name ] ? state : { ...state, [ action.key ]: { ...current, touched: { ...current.touched, [ action.name ]: true } } };

        case 'SET_STEP':
            return { ...state, [ action.key ]: { ...current, step: resolve( action.step, current.step ) } };

        case 'REMOVE': {
            if ( ! state[ action.key ] ) {
                return state;
            }

            const next = { ...state };

            delete next[ action.key ];

            return next;
        }

        default:
            return state;
    }
}

export const actions = {
    setValue: ( key, name, value, initial ) => ( { type: 'SET_VALUE', key, name, value, initial } ),
    setValues: ( key, values, initial ) => ( { type: 'SET_VALUES', key, values, initial } ),
    setErrors: ( key, errors, initial ) => ( { type: 'SET_ERRORS', key, errors, initial } ),
    touch: ( key, name, initial ) => ( { type: 'TOUCH', key, name, initial } ),
    setStep: ( key, step, initial ) => ( { type: 'SET_STEP', key, step, initial } ),
    remove: ( key ) => ( { type: 'REMOVE', key } ),
};

export const selectors = {
    /** The form's slice, or null before its first change. */
    getForm: ( state, key ) => state[ key ] || null,
    getKeys: ( state ) => Object.keys( state ),
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
