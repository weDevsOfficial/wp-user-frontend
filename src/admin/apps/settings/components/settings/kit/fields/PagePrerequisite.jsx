/**
 * Under a page setting that shows a WPUF screen through a shortcode (account,
 * edit, login, registration, subscription packs): when the picked page does
 * not hold that shortcode, say so and offer to add it in one click. Pages
 * made with a page builder are never flagged (their content is elsewhere).
 * Data: `wpuf/v1/admin/setup/pages` (Platform\Setup\Prerequisites).
 */
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { Button, notify } from '@wpuf/components';
import { TriangleAlert } from 'lucide-react';

// One request for the whole screen; every page field shares the answer.
let cache = null;
let loading = null;
const listeners = new Set();

const publish = ( data ) => {
    cache = data;
    listeners.forEach( ( listener ) => listener( data ) );
};

const load = () => {
    if ( ! loading ) {
        loading = request( restPath( 'wpuf/v1', 'admin/setup/pages' ) )
            .then( publish )
            .catch( () => null );
    }

    return loading;
};

const usePages = () => {
    const [ data, setData ] = useState( cache );

    useEffect( () => {
        listeners.add( setData );
        load();

        return () => listeners.delete( setData );
    }, [] );

    return data;
};

/**
 * @param {Object} props
 * @param {string} props.name  Setting name.
 * @param {*}      props.value Picked page id.
 */
export default function PagePrerequisite( { name, value } ) {
    const data = usePages();
    const [ busy, setBusy ] = useState( false );
    const pageId = parseInt( value, 10 ) || 0;
    const need = data && data.requirements ? data.requirements[ name ] : null;

    if ( ! need || ! pageId ) {
        return null;
    }

    const builder = ( data.map.builder || [] ).includes( pageId );
    const tags = ( data.map.pages || {} )[ pageId ] || [];

    if ( builder || tags.includes( need.tag ) ) {
        return null;
    }

    const add = () => {
        setBusy( true );
        request( restPath( 'wpuf/v1', `admin/setup/pages/${ pageId }/shortcode` ), { method: 'POST', data: { setting: name } } )
            .then( ( response ) => {
                publish( { ...data, map: response.map } );
                /* translators: %s: shortcode */
                notify( sprintf( __( 'Added %s to the page.', 'wp-user-frontend' ), need.shortcode ), 'success' );
            } )
            .catch( ( error ) => notify( ( error && error.message ) || __( 'Could not change the page.', 'wp-user-frontend' ), 'error' ) )
            .finally( () => setBusy( false ) );
    };

    return (
        <div data-wpuf-ui="" role="status" className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-md border border-solid border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
            <TriangleAlert size={ 14 } className="shrink-0" aria-hidden="true" />
            <span className="min-w-0 flex-1">
                { /* translators: %s: shortcode, e.g. [wpuf_account] */ }
                { sprintf( __( 'This page does not contain %s, so visitors will not see this screen on it.', 'wp-user-frontend' ), need.shortcode ) }
            </span>
            <Button size="xs" variant="secondary" busy={ busy } onClick={ add }>
                { /* translators: %s: shortcode */ }
                { sprintf( __( 'Add %s to the page', 'wp-user-frontend' ), need.shortcode ) }
            </Button>
        </div>
    );
}
