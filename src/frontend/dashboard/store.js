/**
 * `wpuf/dashboard`: the state of every `[wpuf_dashboard]` on the page, one
 * slice per mount (key): the list page, the overview blocks, the member's
 * filters (status, search, type), loading, error, the row being deleted,
 * the notice. The server sends the first page and the overview in the boot
 * data; other pages, filters and deletes go through DashboardController
 * with the signed shortcode attributes.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { api } from '@wpuf/frontend-kit';

export const STORE_NAME = 'wpuf/dashboard';

const NO_FILTERS = { status: '', search: '', type: '' };

const EMPTY = { data: null, overview: null, filters: NO_FILTERS, loading: false, error: '', notice: '', deleting: 0, requestId: 0 };

const listOf = ( state, key ) => state[ key ] || EMPTY;

const patch = ( state, key, change ) => ( { ...state, [ key ]: { ...listOf( state, key ), ...change } } );

export function reducer( state = {}, action ) {
    switch ( action.type ) {
        case 'INIT':
            return patch( state, action.key, {
                data: action.data,
                overview: action.overview || null,
                filters: { ...NO_FILTERS, ...( action.data?.filters || {} ) },
                notice: action.notice || '',
            } );

        case 'SET_FILTERS':
            return patch( state, action.key, { filters: { ...listOf( state, action.key ).filters, ...action.filters } } );

        case 'LOAD_START':
            return patch( state, action.key, { loading: true, error: '', requestId: action.requestId } );

        case 'LOAD_DONE': {
            if ( action.requestId !== listOf( state, action.key ).requestId ) {
                return state;
            }

            const { overview, ...data } = action.data;

            return patch( state, action.key, { loading: false, data, ...( overview ? { overview } : {} ) } );
        }

        case 'LOAD_FAIL':
            return action.requestId === listOf( state, action.key ).requestId
                ? patch( state, action.key, { loading: false, error: action.error } )
                : state;

        case 'DELETING':
            return patch( state, action.key, { deleting: action.postId } );

        case 'SET_ERROR':
            return patch( state, action.key, { error: action.error } );

        case 'SET_NOTICE':
            return patch( state, action.key, { notice: action.notice } );

        default:
            return state;
    }
}

let lastRequest = 0;

export const actions = {
    /**
     * A dashboard's first page and overview from the boot data.
     *
     * @param {string} key      Mount key.
     * @param {Object} data     Page data.
     * @param {string} notice   Notice to show.
     * @param {Object} overview Overview blocks.
     *
     * @return {Object} Action.
     */
    init( key, data, notice = '', overview = null ) {
        return { type: 'INIT', key, data, notice, overview };
    },

    /**
     * Load a page of the list with the current filters. Only the latest load writes it.
     *
     * @param {string}  key      Mount key.
     * @param {Object}  signed   { atts, sig }.
     * @param {number}  page     Page.
     * @param {boolean} overview Also reload the overview blocks.
     *
     * @return {Function} Thunk.
     */
    loadPage( key, signed, page, overview = false ) {
        return async ( { dispatch, select } ) => {
            const requestId = ++lastRequest;
            const filters = select.getList( key ).filters;

            dispatch( { type: 'LOAD_START', key, requestId } );

            try {
                dispatch( { type: 'LOAD_DONE', key, requestId, data: await api.getDashboardPosts( signed, page, { ...filters, overview: overview ? 1 : '' } ) } );
            } catch ( e ) {
                dispatch( { type: 'LOAD_FAIL', key, requestId, error: e.message } );
            }
        };
    },

    /**
     * Change the member's filters and load their first page.
     *
     * @param {string} key     Mount key.
     * @param {Object} signed  { atts, sig }.
     * @param {Object} filters Changed filters (status, search, type).
     *
     * @return {Function} Thunk.
     */
    filter( key, signed, filters ) {
        return async ( { dispatch } ) => {
            dispatch( { type: 'SET_FILTERS', key, filters } );
            await dispatch( actions.loadPage( key, signed, 1 ) );
        };
    },

    /**
     * Trash a post, then reload the page it was on (the previous page when
     * it was the last row of the last page) and the overview (the counts
     * change). Resolves with true when deleted.
     *
     * @param {string} key    Mount key.
     * @param {Object} signed { atts, sig }.
     * @param {number} postId Post ID.
     *
     * @return {Function} Thunk.
     */
    deletePost( key, signed, postId ) {
        return async ( { dispatch, select } ) => {
            const data = select.getList( key ).data || {};
            const page = 1 === ( data.items || [] ).length && data.page > 1 ? data.page - 1 : data.page || 1;

            dispatch( { type: 'DELETING', key, postId } );

            try {
                await api.deleteDashboardPost( signed, postId );
            } catch ( e ) {
                dispatch( { type: 'DELETING', key, postId: 0 } );
                dispatch( { type: 'SET_ERROR', key, error: e.message } );

                return false;
            }

            await dispatch( actions.loadPage( key, signed, page, true ) );
            dispatch( { type: 'DELETING', key, postId: 0 } );
            dispatch( { type: 'SET_NOTICE', key, notice: __( 'Post Deleted', 'wp-user-frontend' ) } );

            return true;
        };
    },

    setFilters( key, filters ) {
        return { type: 'SET_FILTERS', key, filters };
    },

    setError( key, error ) {
        return { type: 'SET_ERROR', key, error };
    },

    setNotice( key, notice ) {
        return { type: 'SET_NOTICE', key, notice };
    },
};

export const selectors = {
    getList: ( state, key ) => listOf( state, key ),
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
