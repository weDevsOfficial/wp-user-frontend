/**
 * `wpuf/transactions`: the Transactions list's server data (rows, counts,
 * totals, page size, loading / error) and its row actions, over
 * `wpuf/v1/admin/transactions` (transactions/api.js). Only the latest load
 * writes the list: a slower, older answer (search typing, tab switches) is
 * dropped. The query (tab, filters, sort, page) and the selection stay in
 * the screen, which keeps them in the route.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';

import { getTransactions, runAction } from '../api';

export const STORE_NAME = 'wpuf/transactions';

export const initialState = {
    result: null,
    loading: true,
    error: null,
    requestId: 0,
};

export function reducer( state = initialState, action ) {
    switch ( action.type ) {
        case 'FETCH_START':
            return { ...state, loading: true, error: null, requestId: action.requestId };

        case 'FETCH_DONE':
            return action.requestId === state.requestId ? { ...state, loading: false, result: action.result } : state;

        case 'FETCH_FAIL':
            return action.requestId === state.requestId ? { ...state, loading: false, error: action.error } : state;

        case 'FETCH_CANCEL':
            return action.requestId === state.requestId ? { ...state, loading: false } : state;

        default:
            return state;
    }
}

let lastRequest = 0;

export const actions = {
    /**
     * Load a page of transactions.
     *
     * @param {Object}      query    Tab, filters, sort, page, per_page.
     * @param {AbortSignal} [signal] Cancels the request.
     *
     * @return {Function} Thunk; resolves with the answer, or null.
     */
    fetchTransactions( query, signal ) {
        return async ( { dispatch } ) => {
            const requestId = ++lastRequest;

            dispatch( { type: 'FETCH_START', requestId } );

            try {
                const result = await getTransactions( query, signal );

                dispatch( { type: 'FETCH_DONE', requestId, result } );

                return result;
            } catch ( error ) {
                // A cancelled load (unmount, newer query) is not an error.
                dispatch( { type: signal?.aborted ? 'FETCH_CANCEL' : 'FETCH_FAIL', requestId, error } );

                return null;
            }
        };
    },

    /**
     * Accept, reject or delete rows.
     *
     * @param {string}   action accept|reject|delete
     * @param {Object[]} rows   Rows ({ kind, id }).
     *
     * @return {Function} Thunk; resolves with { done, skipped, message, counts }.
     */
    runAction( action, rows ) {
        return () => runAction( action, rows );
    },
};

export const selectors = {
    getResult: ( state ) => state.result,
    isLoading: ( state ) => state.loading,
    getError: ( state ) => state.error,
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
