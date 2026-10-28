/**
 * Route every WPUF admin toast through ToastCard (FlyHR's `shared/toast/
 * install.ts`): plugin-ui's sonner `toast` singleton gets its type methods
 * replaced once with ones that render the card via `toast.custom`, so every
 * call site (`notify()`, direct `toast.success()`, Pro's screens through
 * window.wpuf.ui) gets the design. Options a caller passes (description,
 * action, cancel, icon, id, duration...) go through; sonner keeps the ones it
 * acts on. Unlike FlyHR no generic second line is added: WPUF's messages are
 * develop's full sentences.
 *
 * @since WPUF_SINCE
 */
import { createElement } from '@wordpress/element';
import { toast } from '@wedevs/plugin-ui';
import ToastCard from './ToastCard';

/** How long each type stays (errors outlast successes: the reader acts on them). */
const DURATION = {
    success: 3000,
    error: 5000,
    warning: 4000,
    info: 3000,
    message: 4000,
    loading: Number.POSITIVE_INFINITY,
};

const TYPES = [ 'success', 'error', 'warning', 'info', 'message', 'loading' ];

/**
 * Sonner's options once the card draws description, icon, actions and close.
 *
 * @param {Object} data Caller's options.
 *
 * @return {Object} Options for sonner.
 */
const sonnerOptions = ( data ) => {
    const rest = { ...data };

    [ 'description', 'icon', 'action', 'cancel', 'closeButton' ].forEach( ( key ) => delete rest[ key ] );

    return rest;
};

const cardFor = ( type ) => ( message, data = {} ) => {
    const duration = undefined !== data.duration ? data.duration : ( DURATION[ type ] || 4000 );

    return toast.custom(
        ( id ) => createElement( ToastCard, {
            type,
            title: message,
            description: data.description,
            icon: data.icon,
            action: data.action,
            cancel: data.cancel,
            duration,
            closeButton: false !== data.closeButton,
            onDismiss: () => toast.dismiss( id ),
        } ),
        { ...sonnerOptions( data ), duration }
    );
};

/**
 * Patch the shared toast once (idempotent: free and Pro bundles may both
 * import this).
 *
 * @since WPUF_SINCE
 *
 * @return {void}
 */
export function installWpufToasts() {
    if ( ! toast || toast.__wpufPatched ) {
        return;
    }

    TYPES.forEach( ( type ) => {
        if ( 'function' === typeof toast[ type ] ) {
            toast[ type ] = cardFor( type );
        }
    } );

    toast.__wpufPatched = true;
}

installWpufToasts();
