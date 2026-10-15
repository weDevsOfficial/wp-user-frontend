/**
 * `wpuf/forms-list`: the forms list's server data (rows, pagination, status
 * counts, loading / error) per post type (`wpuf_forms`, `wpuf_profile`), over
 * the shared request layer (`@wpuf/api`). Only the latest request of a post
 * type writes its list: a slower, older answer (search typing, tab switches)
 * is dropped. View state (tab, search box, selection) stays in the screen.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';
import { request, restPath } from '@wpuf/api';

export const STORE_NAME = 'wpuf/forms-list';
export const PER_PAGE = 10;

const EMPTY_PAGINATION = {
    total_items: 0,
    total_pages: 0,
    current_page: 1,
    per_page: PER_PAGE,
};

const EMPTY = {
    forms: [],
    loading: true,
    error: null,
    counts: null,
    pagination: EMPTY_PAGINATION,
    requestId: 0,
};

const slice = ( state, postType ) => state[ postType ] || EMPTY;

export function reducer( state = {}, action ) {
    const current = slice( state, action.postType );

    switch ( action.type ) {
        case 'FETCH_START':
            return { ...state, [ action.postType ]: { ...current, loading: true, error: null, requestId: action.requestId } };

        case 'FETCH_DONE':
            if ( action.requestId !== current.requestId ) {
                return state;
            }

            return {
                ...state,
                [ action.postType ]: {
                    ...current,
                    loading: false,
                    forms: action.forms,
                    pagination: action.pagination,
                    counts: action.counts || current.counts,
                },
            };

        case 'FETCH_FAIL':
            if ( action.requestId !== current.requestId ) {
                return state;
            }

            return {
                ...state,
                [ action.postType ]: {
                    ...current,
                    loading: false,
                    error: action.error,
                    forms: [],
                    pagination: { ...EMPTY_PAGINATION, per_page: action.perPage },
                },
            };

        default:
            return state;
    }
}

let lastRequest = 0;

export const actions = {
    /**
     * Load one page of forms.
     *
     * @param {string} postType Post type slug.
     * @param {number} page     Page.
     * @param {string} status   Status tab.
     * @param {string} search   Search term.
     * @param {number} perPage  Rows per page.
     * @param {Object} [sort]   { orderby, order } (server default: newest first).
     *
     * @return {Function} Thunk.
     */
    fetchForms( postType, page = 1, status = 'any', search = '', perPage = PER_PAGE, sort = null ) {
        return async ( { dispatch } ) => {
            const requestId = ++lastRequest;
            const query = { page, per_page: perPage, status, post_type: postType };

            if ( search ) {
                query.s = search;
            }

            if ( sort && sort.orderby ) {
                query.orderby = sort.orderby;
                query.order = sort.order;
            }

            dispatch( { type: 'FETCH_START', postType, requestId } );

            try {
                const data = await request( restPath( 'wpuf/v1', '/wpuf_form' ), { query } );
                const ok = data && data.success && data.result;

                dispatch( {
                    type: 'FETCH_DONE',
                    postType,
                    requestId,
                    forms: ok ? data.result : [],
                    counts: data && data.counts && 'object' === typeof data.counts ? data.counts : null,
                    pagination: ok
                        ? {
                            total_items: parseInt( data.pagination?.total_items, 10 ) || 0,
                            total_pages: data.pagination?.total_pages || 0,
                            current_page: page,
                            per_page: perPage,
                        }
                        : { ...EMPTY_PAGINATION, per_page: perPage },
                } );
            } catch ( error ) {
                dispatch( { type: 'FETCH_FAIL', postType, requestId, error, perPage } );
            }
        };
    },
};

export const selectors = {
    getForms: ( state, postType ) => slice( state, postType ).forms,
    isLoading: ( state, postType ) => slice( state, postType ).loading,
    getError: ( state, postType ) => slice( state, postType ).error,
    getCounts: ( state, postType ) => slice( state, postType ).counts,
    getPagination: ( state, postType ) => slice( state, postType ).pagination,
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
