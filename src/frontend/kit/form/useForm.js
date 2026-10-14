/**
 * The state of one form: values (from the schema's edit values or the
 * fields' defaults), errors, touched, the visible set under conditional
 * logic, and the steps (`{ pre, list }`, split on `step_start`).
 *
 * @since WPUF_SINCE
 */
import { useCallback, useMemo, useState } from '@wordpress/element';
import { visibleNames } from './conditions';
import { validateAll } from './validation';

const MULTI = [ 'multiple_select', 'checkbox_field', 'image_upload', 'featured_image', 'file_upload', 'profile_photo', 'cover_photo', 'avatar' ];

/** Every field with a name, column fields recursed, in order. */
export function flatten( fields ) {
    const out = [];

    const walk = ( list ) => {
        ( list || [] ).forEach( ( field ) => {
            out.push( field );

            if ( 'column_field' === field.template && field.inner_fields ) {
                Object.values( field.inner_fields ).forEach( ( column ) => walk( column || [] ) );
            }
        } );
    };

    walk( fields );

    return out;
}

const defaultOf = ( field ) => {
    if ( undefined !== field.value && null !== field.value ) {
        if ( 'taxonomy' === field.template && ! Array.isArray( field.value ) ) {
            return field.value ? [ field.value ] : [];
        }

        if ( MULTI.includes( field.template ) ) {
            return Array.isArray( field.value ) ? field.value.map( String ) : ( '' === field.value ? [] : [ String( field.value ) ] );
        }

        if ( 'featured_image' === field.template ) {
            return field.value ? [ String( field.value ) ] : [];
        }

        return field.value;
    }

    if ( 'checkbox_field' === field.template || 'multiple_select' === field.template ) {
        return Array.isArray( field.selected ) ? field.selected.map( String ) : ( field.selected ? [ String( field.selected ) ] : [] );
    }

    if ( 'dropdown_field' === field.template || 'radio_field' === field.template ) {
        return field.selected ? String( field.selected ) : '';
    }

    if ( 'taxonomy' === field.template ) {
        return [];
    }

    if ( MULTI.includes( field.template ) ) {
        return [];
    }

    return undefined === field.default || null === field.default ? '' : field.default;
};

/**
 * @param {Object} schema The form schema.
 * @return {Object} form state and helpers
 */
export default function useForm( schema ) {
    const all = useMemo( () => flatten( schema.fields || [] ), [ schema.fields ] );
    const [ values, setValues ] = useState( () => {
        const initial = {};

        all.forEach( ( field ) => {
            if ( field.name ) {
                initial[ field.name ] = defaultOf( field );
            }
        } );

        return initial;
    } );
    const [ errors, setErrors ] = useState( {} );
    const [ touched, setTouched ] = useState( {} );
    const [ step, setStep ] = useState( 0 );

    const visible = useMemo( () => visibleNames( schema.fields || [], values ), [ schema.fields, values ] );

    /**
     * The classic multistep split: fields before the first `step_start` stay
     * outside the steps (always shown), every `step_start` opens a fieldset
     * with its label as the legend.
     */
    const steps = useMemo( () => {
        const pre = [];
        const list = [];

        ( schema.fields || [] ).forEach( ( field ) => {
            if ( 'step_start' === field.template ) {
                list.push( { title: field.label || '', prev: field.step_start?.prev_button_text || '', next: field.step_start?.next_button_text || '', fields: [] } );

                return;
            }

            ( list.length ? list[ list.length - 1 ].fields : pre ).push( field );
        } );

        return { pre, list };
    }, [ schema.fields ] );

    const setValue = useCallback( ( name, value ) => {
        setValues( ( current ) => ( { ...current, [ name ]: value } ) );
        setErrors( ( current ) => {
            if ( ! current[ name ] ) {
                return current;
            }

            const next = { ...current };
            delete next[ name ];

            return next;
        } );
    }, [] );

    const touch = useCallback( ( name ) => setTouched( ( current ) => ( current[ name ] ? current : { ...current, [ name ]: true } ) ), [] );

    /** Validate the visible fields (of one step when given); returns the errors. */
    const validate = useCallback( ( fields = null ) => {
        const subject = flatten( fields || schema.fields || [] ).filter( ( field ) => field.name && visible.has( field.name ) );
        const found = validateAll( subject, values, { schema } );

        setErrors( ( current ) => ( fields ? { ...current, ...found } : found ) );

        return found;
    }, [ schema, values, visible ] );

    /** The fields on screen: all of them, or the fields outside the steps plus the current step (multistep). */
    const onScreen = useCallback( ( multistep ) => ( multistep && steps.list.length ? [ ...steps.pre, ...( steps.list[ step ] ? steps.list[ step ].fields : [] ) ] : schema.fields || [] ), [ steps, step, schema.fields ] );

    return { all, values, setValue, setValues, errors, setErrors, touched, touch, visible, steps, step, setStep, onScreen, validate };
}
