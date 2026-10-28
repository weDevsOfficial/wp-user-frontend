/**
 * `wpuf/v1/admin/tools/*` calls.
 *
 * @since WPUF_SINCE
 */
import { request, restPath } from '@wpuf/api';

const path = ( route, query ) => restPath( 'wpuf/v1', 'admin/tools/' + route, query );

export const runTool = ( route, data ) => request( path( route ), { method: 'POST', data: data || {} } );

export const getForms = ( type ) => request( path( 'forms', { type } ) );

export const exportForms = ( type, ids ) => request( path( 'export', { type, ids } ) );

export const getShortcodes = () => request( path( 'shortcodes' ) );

export const getOnboarding = () => request( path( 'onboarding' ) );

export const importFile = ( file ) => {
    const body = new window.FormData();

    body.append( 'file', file );

    return request( path( 'import' ), { method: 'POST', body } );
};

/**
 * Copy text, with the textarea fallback the classic page used.
 *
 * @param {string} text Text.
 *
 * @return {Promise<void>} Settles when copied.
 */
export const copyText = ( text ) => {
    if ( window.navigator.clipboard?.writeText ) {
        return window.navigator.clipboard.writeText( text );
    }

    const area = document.createElement( 'textarea' );

    area.value = text;
    document.body.appendChild( area );
    area.select();
    document.execCommand( 'copy' );
    area.remove();

    return Promise.resolve();
};
