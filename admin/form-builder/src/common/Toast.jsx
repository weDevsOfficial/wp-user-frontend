/**
 * Show a toast notification using toastr (already enqueued by WP).
 *
 * @param {string} message Notification message
 * @param {string} type    'success' | 'error' | 'warning' | 'info'
 */
export function showToast( message, type = 'success' ) {
    if ( typeof window.toastr !== 'undefined' ) {
        window.toastr[ type ]( message );
    }
}
