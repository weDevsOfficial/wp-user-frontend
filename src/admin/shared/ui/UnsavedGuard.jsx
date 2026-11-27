/**
 * Unsaved changes guard (subscriptions behaviour): while `dirty`, leaving the
 * page asks the browser to confirm, and an in-app navigation the screen
 * intercepts opens this dialog: "Continue Editing" keeps the user here,
 * "Discard Changes" (destructive) lets the navigation happen.
 */
import { __ } from '@wordpress/i18n';

import ConfirmDialog from './ConfirmDialog';
import { useUnsavedGuard } from '../hooks';

/**
 * @param {Object}   props
 * @param {boolean}  props.dirty       There are unsaved changes.
 * @param {boolean}  props.open        The screen intercepted a navigation.
 * @param {Function} props.onDiscard   Discard and continue the navigation.
 * @param {Function} props.onContinue  Stay and keep editing.
 * @param {*}        [props.message]   Override the text.
 */
export default function UnsavedGuard( { dirty, open, onDiscard, onContinue, message } ) {
    useUnsavedGuard( dirty );

    return (
        <ConfirmDialog
            open={ open }
            tone="danger"
            icon="warning"
            title={ __( 'Unsaved Changes', 'wp-user-frontend' ) }
            message={ message || (
                <>
                    { __( 'You have unsaved changes in your current subscription.', 'wp-user-frontend' ) }
                    <br />
                    { __( 'Navigating away from this page will cause your work to be lost.', 'wp-user-frontend' ) }
                </>
            ) }
            confirmText={ __( 'Discard Changes', 'wp-user-frontend' ) }
            cancelText={ __( 'Continue Editing', 'wp-user-frontend' ) }
            onConfirm={ onDiscard }
            onCancel={ onContinue }
        />
    );
}
