/**
 * Under a setting that picks a form (Default Post Form, Submission Form,
 * Profile Form): when the picked form is a draft, in the trash or deleted,
 * say what visitors get and offer Publish / Restore in place (the forms
 * list's REST actions).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { Button, notify } from '@wpuf/components';
import { TriangleAlert } from 'lucide-react';

/**
 * @param {Object} props
 * @param {*}      props.value Picked form id.
 */
export default function FormPrerequisite( { value } ) {
    const id = parseInt( value, 10 ) || 0;
    // null: unknown / fine; 'draft' | 'trash' | 'missing'.
    const [ state, setState ] = useState( null );
    const [ busy, setBusy ] = useState( false );

    useEffect( () => {
        let live = true;

        setState( null );

        if ( ! id ) {
            return undefined;
        }

        request( restPath( 'wpuf/v1', `/admin/forms/${ id }` ) )
            .then( ( response ) => {
                const status = response && response.data ? response.data.post_status : '';

                if ( live ) {
                    setState( 'publish' === status ? null : ( 'trash' === status ? 'trash' : 'draft' ) );
                }
            } )
            .catch( ( error ) => {
                if ( live ) {
                    setState( error && 404 === error.status ? 'missing' : null );
                }
            } );

        return () => {
            live = false;
        };
    }, [ id ] );

    if ( ! state ) {
        return null;
    }

    const act = ( action ) => {
        setBusy( true );
        request( restPath( 'wpuf/v1', `/admin/forms/${ id }/${ action }` ), { method: 'POST' } )
            .then( ( response ) => {
                const status = response && response.data ? response.data.status : '';

                setState( 'publish' === status ? null : 'draft' );
                notify( 'publish' === action ? __( 'Form published.', 'wp-user-frontend' ) : __( 'Form restored as a draft.', 'wp-user-frontend' ), 'success' );
            } )
            .catch( ( error ) => notify( ( error && error.message ) || __( 'Something went wrong. Please try again.', 'wp-user-frontend' ), 'error' ) )
            .finally( () => setBusy( false ) );
    };

    const messages = {
        draft: __( 'This form is a draft, so visitors see "Please make sure you\'ve published your form." instead of it.', 'wp-user-frontend' ),
        trash: __( 'This form is in the trash, so visitors cannot use it.', 'wp-user-frontend' ),
        missing: __( 'The selected form no longer exists. Pick another one.', 'wp-user-frontend' ),
    };

    return (
        <div data-wpuf-ui="" role="status" className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-md border border-solid border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
            <TriangleAlert size={ 14 } className="shrink-0" aria-hidden="true" />
            <span className="min-w-0 flex-1">{ messages[ state ] }</span>
            { 'draft' === state && (
                <Button size="xs" variant="secondary" busy={ busy } onClick={ () => act( 'publish' ) }>
                    { __( 'Publish form', 'wp-user-frontend' ) }
                </Button>
            ) }
            { 'trash' === state && (
                <Button size="xs" variant="secondary" busy={ busy } onClick={ () => act( 'restore' ) }>
                    { __( 'Restore form', 'wp-user-frontend' ) }
                </Button>
            ) }
        </div>
    );
}
