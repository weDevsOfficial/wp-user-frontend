/**
 * `wpuf/dashboard`: the state of every `[wpuf_dashboard]` list on the page,
 * one slice per mount (key): the page data, loading, error, the row being
 * deleted, the notice. The server sends the first page in the boot data;
 * other pages and deletes go through DashboardController with the signed
 * shortcode attributes.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { api } from '@wpuf/frontend-kit';

export const STORE_NAME = 'wpuf/dashboard';

const EMPTY = { data: null, loading: false, error: '', notice: '', deleting: 0, requestId: 0 };

const listOf = ( state, key ) => state[ key ] || EMPTY;

const patch = ( state, key, change ) => ( { ...state, [ key ]: { ...listOf( state, key ), ...change } } );

export function reducer( state = {}, action ) {
    switch ( action.type ) {
        case 'INIT':
            return patch( state, action.key, { data: action.data, notice: action.notice || '' } );

        case 'LOAD_START':
            return patch( state, action.key, { loading: true, error: '', requestId: action.requestId } );

        case 'LOAD_DONE':
            return action.requestId === listOf( state, action.key ).requestId
                ? patch( state, action.key, { loading: false, data: action.data } )
                : state;

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
     * A list's first page from the boot data.
     *
     * @param {string} key    Mount key.
     * @param {Object} data   Page data.
     * @param {string} notice Notice to show.
     *
     * @return {Object} Action.
     */
    init( key, data, notice = '' ) {
        return { type: 'INIT', key, data, notice };
    },

    /**
     * Load a page of a list. Only the latest load writes it.
     *
     * @param {string} key    Mount key.
     * @param {Object} signed { atts, sig }.
     * @param {number} page   Page.
     *
     * @return {Function} Thunk.
     */
    loadPage( key, signed, page ) {
        return async ( { dispatch } ) => {
            const requestId = ++lastRequest;

            dispatch( { type: 'LOAD_START', key, requestId } );

            try {
                dispatch( { type: 'LOAD_DONE', key, requestId, data: await api.getDashboardPosts( signed, page ) } );
            } catch ( e ) {
                dispatch( { type: 'LOAD_FAIL', key, requestId, error: e.message } );
            }
        };
    },

    /**
     * Trash a post, then reload the page it was on (the previous page when
     * it was the last row of the last page). Resolves with true when deleted.
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

            await dispatch( actions.loadPage( key, signed, page ) );
            dispatch( { type: 'DELETING', key, postId: 0 } );
            dispatch( { type: 'SET_NOTICE', key, notice: __( 'Post Deleted', 'wp-user-frontend' ) } );

            return true;
        };
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
