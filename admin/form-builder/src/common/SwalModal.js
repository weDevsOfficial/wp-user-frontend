import { __ } from '@wordpress/i18n';

/**
 * Check if SweetAlert2 is available.
 *
 * @return {boolean}
 */
function hasSwal() {
    return typeof window.Swal !== 'undefined';
}

/**
 * Get the asset URL for images.
 *
 * @return {string}
 */
function getAssetUrl() {
    return ( window.wpuf_admin_script || window.wpuf_form_builder || {} ).asset_url || '';
}

/**
 * Show a warning/error modal with custom warning icon.
 *
 * @param {Object} options
 * @param {string} options.title   Modal title.
 * @param {string} options.message Modal body text.
 *
 * @return {Promise|undefined} Swal promise if available.
 */
export function showAlert( { title, message } ) {
    if ( ! hasSwal() ) {
        return;
    }

    const iconSrc = getAssetUrl() + '/images/warning-circle.svg';

    return window.Swal.fire( {
        title,
        html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' + message + '</span>',
        iconHtml: '<img src="' + iconSrc + '" alt="warning">',
        showCancelButton: false,
        confirmButtonText: __( 'OK', 'wp-user-frontend' ),
        confirmButtonColor: '#059669',
        customClass: {
            confirmButton: 'wpuf-btn-primary',
            icon: 'wpuf-warning-icon',
        },
    } );
}

/**
 * Show a confirmation modal with confirm/cancel buttons.
 *
 * @param {Object}  options
 * @param {string}  options.title         Modal title.
 * @param {string}  options.message       Modal body text.
 * @param {string}  options.confirmText   Confirm button label.
 * @param {string}  options.cancelText    Cancel button label.
 * @param {string}  options.confirmColor  Confirm button color (default: '#EF4444' red).
 *
 * @return {Promise|undefined} Swal promise resolving to { isConfirmed }.
 */
export function showConfirm( {
    title,
    message,
    confirmText,
    cancelText,
    confirmColor = '#EF4444',
} ) {
    if ( ! hasSwal() ) {
        return;
    }

    const iconSrc = getAssetUrl() + '/images/warning-circle.svg';

    return window.Swal.fire( {
        title,
        html: '<span class="wpuf-text-gray-500 wpuf-font-medium">' + message + '</span>',
        iconHtml: '<img src="' + iconSrc + '" alt="warning">',
        showCancelButton: true,
        confirmButtonText: confirmText || __( 'Yes', 'wp-user-frontend' ),
        cancelButtonText: cancelText || __( 'Cancel', 'wp-user-frontend' ),
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#fff',
        reverseButtons: true,
        customClass: {
            icon: 'wpuf-warning-icon',
        },
    } );
}
