/**
 * A confirm dialog: SweetAlert2 when the classic bundle loaded it on the
 * page (the chat uses the same), the browser's confirm otherwise. Nothing
 * is appended to <body> by us (frontend-react-architecture.md 3.2, rule 4).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

export default function confirmDialog( { title, text = '', confirmText = __( 'Delete', 'wp-user-frontend' ), danger = true } ) {
    if ( 'function' === typeof window.Swal?.fire ) {
        return window.Swal.fire( {
            title,
            text,
            icon: danger ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: __( 'Cancel', 'wp-user-frontend' ),
            confirmButtonColor: danger ? '#dc2626' : '#111827',
            cancelButtonColor: '#e5e7eb',
            reverseButtons: true,
            customClass: { popup: 'wpuf-frontend-swal', cancelButton: 'wpuf-frontend-swal__cancel' },
        } ).then( ( result ) => !! result.isConfirmed );
    }

    return Promise.resolve( window.confirm( text ? title + '\n' + text : title ) ); // eslint-disable-line no-alert
}

/**
 * The classic error popup of a failed submit (SweetAlert2 warning, one "OK"
 * button, the classic colour), the browser alert when SweetAlert is not on
 * the page. `html` is the server's error message (escaped by the routes).
 *
 * @param {Object} props
 * @param {string} props.html Message markup.
 * @return {Promise<void>} Resolves when closed
 */
export function alertDialog( { html } ) {
    if ( 'function' === typeof window.Swal?.fire ) {
        return window.Swal.fire( {
            html,
            icon: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#d54e21',
            confirmButtonText: __( 'OK', 'wp-user-frontend' ),
            customClass: { cancelButton: 'btn btn-danger' },
        } ).then( () => undefined );
    }

    const text = document.createElement( 'div' );
    text.innerHTML = html; // eslint-disable-line no-unsanitized/property -- detached node, used only for its text.
    window.alert( text.textContent ); // eslint-disable-line no-alert

    return Promise.resolve();
}

/**
 * The classic "login required" question (SweetAlert2 warning, OK / Cancel).
 *
 * @param {Object} props
 * @param {string} props.text Message.
 * @return {Promise<boolean>} True on OK
 */
export function loginDialog( { text } ) {
    if ( 'function' === typeof window.Swal?.fire ) {
        return window.Swal.fire( {
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: __( 'OK', 'wp-user-frontend' ),
        } ).then( ( result ) => !! result.isConfirmed );
    }

    return Promise.resolve( window.confirm( text ) ); // eslint-disable-line no-alert
}
