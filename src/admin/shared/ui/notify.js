/**
 * Toasts (replaces the builder's toastr `showToast( message, type )` and the
 * subscriptions notice store): plugin-ui's sonner, rendered by the Toaster in
 * WpufProviders with plugin-ui's defaults.
 * Types: success, error / danger, warning, info.
 */
import { toast } from '@wedevs/plugin-ui';

/**
 * @param {*}      message   Message.
 * @param {string} [type]    success|error|danger|warning|info
 * @param {Object} [options] sonner options, e.g. `{ action: { label, onClick } }`.
 *
 * @return {*} Toast id.
 */
export default function notify( message, type = 'success', options = undefined ) {
    const kind = 'danger' === type ? 'error' : type;
    const show = toast[ kind ] || toast;

    return options ? show( message, options ) : show( message );
}
