/**
 * Show a toast notification: the shared plugin-ui sonner toast (`notify`),
 * which replaced the builder's toastr.
 *
 * @param {string} message Notification message
 * @param {string} type    'success' | 'error' | 'warning' | 'info'
 */
import { notify } from '@wpuf/components';

export function showToast( message, type = 'success' ) {
    return notify( message, type );
}
