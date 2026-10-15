/**
 * The frontend apps' dialogs (confirm, alert, login question) on the shared
 * WPUF dialog (assets/js/wpuf-dialog.js, a native <dialog> in the plugin-ui
 * design, placed inside the app root). The runtime depends on `wpuf-dialog`;
 * the browser's confirm / alert only stand in if it is missing.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const shared = () => window.wpufDialog;

/**
 * A confirm question.
 *
 * @param {Object}  props
 * @param {string}  props.title         Question.
 * @param {string}  [props.text]        More text.
 * @param {string}  [props.confirmText] Confirm button label.
 * @param {boolean} [props.danger]      Destructive action.
 * @param {Element} [props.root]        Container.
 * @return {Promise<boolean>} True when confirmed
 */
export default function confirmDialog( { title, text = '', confirmText = __( 'Delete', 'wp-user-frontend' ), danger = true, root } ) {
    if ( shared() ) {
        return shared().confirm( { title, text, root, confirmText, cancelText: __( 'Cancel', 'wp-user-frontend' ), tone: danger ? 'danger' : 'info' } );
    }

    return Promise.resolve( window.confirm( text ? title + '\n' + text : title ) ); // eslint-disable-line no-alert
}

/**
 * The error message of a failed submit, one "OK" button.
 *
 * @param {Object}  props
 * @param {string}  props.html   Message markup (escaped by the routes).
 * @param {Element} [props.root] Container.
 * @return {Promise<void>} Resolves when closed
 */
export function alertDialog( { html, root } ) {
    if ( shared() ) {
        return shared().alert( { html, root, tone: 'warning', okText: __( 'OK', 'wp-user-frontend' ) } );
    }

    const text = document.createElement( 'div' );
    text.innerHTML = html; // eslint-disable-line no-unsanitized/property -- detached node, used only for its text.
    window.alert( text.textContent ); // eslint-disable-line no-alert

    return Promise.resolve();
}

/**
 * The "login required" question, OK / Cancel.
 *
 * @param {Object}  props
 * @param {string}  props.text   Message.
 * @param {Element} [props.root] Container.
 * @return {Promise<boolean>} True on OK
 */
export function loginDialog( { text, root } ) {
    if ( shared() ) {
        return shared().confirm( { text, root, tone: 'warning', confirmText: __( 'OK', 'wp-user-frontend' ), cancelText: __( 'Cancel', 'wp-user-frontend' ) } );
    }

    return Promise.resolve( window.confirm( text ) ); // eslint-disable-line no-alert
}
