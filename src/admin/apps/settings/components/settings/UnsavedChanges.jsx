/**
 * UnsavedChanges: confirm dialog shown when leaving a tab with unsaved edits,
 * on the shared ConfirmDialog (its unsaved-changes picture on top). "Continue
 * Editing" (the safe choice) has focus.
 */
import { __ } from '@wordpress/i18n';
import { ConfirmDialog } from '@wpuf/components';
import { settingsData } from '../../boot';

const UnsavedChanges = ( { onDiscard, onContinue } ) => (
    <ConfirmDialog
        open
        title={ __( 'Unsaved Changes', 'wp-user-frontend' ) }
        message={ __( 'You have unsaved changes in these settings. Leaving this tab will discard your changes.', 'wp-user-frontend' ) }
        cancelText={ __( 'Continue Editing', 'wp-user-frontend' ) }
        confirmText={ __( 'Discard Changes', 'wp-user-frontend' ) }
        tone="danger"
        focusCancel
        media={ <img src={ `${ settingsData().asset_url || '' }/images/modal/unsaved-changes.svg` } alt="" className="size-[100px]" /> }
        onConfirm={ onDiscard }
        onCancel={ onContinue }
    />
);

export default UnsavedChanges;
