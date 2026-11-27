/**
 * UnsavedChanges: confirm dialog shown when leaving a tab with unsaved edits,
 * on the shared ConfirmDialog with its warning icon, like the other unsaved
 * changes dialogs. "Continue Editing" (the safe choice) has focus.
 */
import { __ } from '@wordpress/i18n';
import { ConfirmDialog } from '@wpuf/components';

const UnsavedChanges = ( { onDiscard, onContinue } ) => (
    <ConfirmDialog
        open
        title={ __( 'Unsaved Changes', 'wp-user-frontend' ) }
        message={ __( 'You have unsaved changes in these settings. Leaving this tab will discard your changes.', 'wp-user-frontend' ) }
        cancelText={ __( 'Continue Editing', 'wp-user-frontend' ) }
        confirmText={ __( 'Discard Changes', 'wp-user-frontend' ) }
        tone="danger"
        focusCancel
        icon="warning"
        onConfirm={ onDiscard }
        onCancel={ onContinue }
    />
);

export default UnsavedChanges;
