/**
 * MessageModal: an alert for save / validation errors (instead of an
 * easy-to-miss inline banner), on the shared ConfirmDialog.
 */
import { __ } from '@wordpress/i18n';
import { ConfirmDialog } from '@wpuf/components';

export default function MessageModal( { title, message, onClose, tone = 'error', actionLabel } ) {
    return (
        <ConfirmDialog
            open
            title={ title }
            // Line breaks in a message (a list of problems) stay.
            message={ 'string' === typeof message ? <span className="whitespace-pre-line">{ message }</span> : message }
            confirmText={ actionLabel || __( 'OK', 'wp-user-frontend' ) }
            cancelText={ false }
            tone={ 'error' === tone ? 'danger' : 'primary' }
            icon="warning"
            onConfirm={ onClose }
            onCancel={ onClose }
        />
    );
}
