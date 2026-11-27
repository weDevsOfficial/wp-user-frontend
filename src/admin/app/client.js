/**
 * Screen side of the single React admin app (task 5d). A screen bundle uses
 * these helpers so it works both inside the app (one page, hash routes) and
 * on its own page (app off, older Pro, third parties enqueueing the bundle).
 *
 * @since WPUF_SINCE
 */

let context = null;

/**
 * Whether the screen runs inside the admin app.
 *
 * @return {boolean} In the app.
 */
export const inApp = () => null !== context;

/**
 * Register a screen: with the app shell when the page is the app, else mount
 * on the screen's own page right away (its container exists there).
 *
 * @param {string}   id          Screen id (a route's `app`).
 * @param {string[]} containers  Ids of the screen's own-page mount elements.
 * @param {Function} mount       ( element, context|null ) => cleanup.
 */
export const registerScreen = ( id, containers, mount ) => {
    const api = {
        mount: ( element, appContext ) => {
            context = appContext;

            const cleanup = mount( element, appContext );

            return () => {
                if ( 'function' === typeof cleanup ) {
                    cleanup();
                }

                context = null;
            };
        },
    };

    if ( window.wpufAdmin && window.wpufAdmin.app ) {
        if ( window.wpuf && window.wpuf.app ) {
            window.wpuf.app.registerScreen( id, api );
        } else {
            window.wpufAdminScreens = window.wpufAdminScreens || [];
            window.wpufAdminScreens.push( [ id, api ] );
        }

        return;
    }

    for ( const containerId of containers ) {
        const element = document.getElementById( containerId );

        if ( element ) {
            mount( element, null );

            return;
        }
    }
};

/**
 * Query of the screen's route: the hash route query in the app, the page's
 * query string otherwise.
 *
 * @return {Object} Query.
 */
export const getRouteQuery = () => ( context ? context.getQuery() : Object.fromEntries( new URLSearchParams( window.location.search ) ) );

/**
 * Change the screen's query (no reload, no new history entry by default).
 *
 * @param {Object}  patch           Keys to set; null/'' removes a key.
 * @param {Object}  options         Options.
 * @param {boolean} options.replace Replace the history entry (default true).
 */
export const setRouteQuery = ( patch, { replace = true } = {} ) => {
    const query = { ...getRouteQuery(), ...patch };

    Object.keys( query ).forEach( ( key ) => {
        if ( null === query[ key ] || undefined === query[ key ] || '' === query[ key ] ) {
            delete query[ key ];
        }
    } );

    if ( context ) {
        context.setQuery( query, { replace } );

        return;
    }

    const search = new URLSearchParams( query ).toString();
    const url = `${ window.location.pathname }${ search ? `?${ search }` : '' }${ window.location.hash }`;

    window.history[ replace ? 'replaceState' : 'pushState' ]( null, '', url );
};

/**
 * Ask before the app leaves this screen's route (e.g. unsaved changes). On the
 * screen's own page this does nothing (beforeunload covers page loads).
 *
 * @param {Function} guard ( next ) => boolean|Promise<boolean>.
 *
 * @return {Function} Remove the guard.
 */
export const addRouteGuard = ( guard ) => ( context ? context.addGuard( guard ) : () => {} );

/**
 * Follow query changes of the screen's route (back/forward inside a screen).
 *
 * @param {Function} listener ( query ) => void.
 *
 * @return {Function} Stop following.
 */
export const onRouteQuery = ( listener ) => ( context ? context.onQuery( listener ) : () => {} );

/**
 * Open another admin route: a hash change when the route runs in the app,
 * else the old URL (outside the app, or a route that is still its own page;
 * one navigation instead of a hash change and a redirect).
 *
 * @param {string} path    App route path (`/post-forms/12/edit`).
 * @param {string} pageUrl Old page URL.
 */
export const openRoute = ( path, pageUrl ) => {
    if ( context && context.isAppRoute( path ) ) {
        context.navigate( path );

        return;
    }

    window.location.href = pageUrl;
};
