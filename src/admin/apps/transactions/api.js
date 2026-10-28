/**
 * `wpuf/v1/admin/transactions` calls.
 *
 * @since WPUF_SINCE
 */
import { request, restPath } from '@wpuf/api';

export const getTransactions = ( query, signal ) => request( restPath( 'wpuf/v1', 'admin/transactions', query ), { signal } );

/**
 * @param {string}   action accept|reject|delete
 * @param {Object[]} rows   Rows ({ kind, id }).
 *
 * @return {Promise<Object>} { done, skipped, message, counts }
 */
export const runAction = ( action, rows ) => request( restPath( 'wpuf/v1', 'admin/transactions/' + action ), {
    method: 'POST',
    data: { items: rows.map( ( row ) => ( { kind: row.kind, id: row.id } ) ) },
} );
