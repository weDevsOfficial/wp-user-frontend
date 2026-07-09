/**
 * UnsavedChanges — confirm modal shown when leaving a tab with unsaved edits.
 * Follows the User Directory free module's DeleteConfirmModal design (custom
 * centered overlay card), not the @wordpress/components Modal.
 */
import { __ } from '@wordpress/i18n';
import ModalShell from './ModalShell';

const UnsavedChanges = ( { onDiscard, onContinue } ) => {
    return (
        <ModalShell onClose={ onContinue } labelledBy="wpuf-unsaved-modal-title">
                <img
                    src={ `${ ( window.wpuf_settings || {} ).asset_url || '' }/images/modal/unsaved-changes.svg` }
                    alt=""
                    aria-hidden="true"
                    className="wpuf-h-[100px] wpuf-w-[100px]"
                />

                <h1 id="wpuf-unsaved-modal-title" className="wpuf-m-0 wpuf-mt-7 wpuf-text-2xl wpuf-font-extrabold wpuf-text-gray-700">
                    { __( 'Unsaved Changes', 'wp-user-frontend' ) }
                </h1>
                <p className="wpuf-m-0 wpuf-mt-3 wpuf-text-base wpuf-font-medium wpuf-leading-7 wpuf-text-gray-500">
                    { __( 'You have unsaved changes in these settings.', 'wp-user-frontend' ) }
                    <br />
                    { __( 'Leaving this tab will discard your changes.', 'wp-user-frontend' ) }
                </p>

                <div className="wpuf-mt-9 wpuf-flex wpuf-justify-center wpuf-gap-5">
                    <button
                        data-primary
                        onClick={ onContinue }
                        className="wpuf-h-[50px] wpuf-rounded-md wpuf-border !wpuf-border-gray-300 wpuf-bg-white wpuf-px-6 wpuf-text-base wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50"
                    >
                        { __( 'Continue Editing', 'wp-user-frontend' ) }
                    </button>
                    <button
                        onClick={ onDiscard }
                        className="wpuf-h-[50px] wpuf-rounded-md wpuf-bg-[#EF4444] wpuf-px-6 wpuf-text-base wpuf-font-medium !wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-red-600"
                    >
                        { __( 'Discard Changes', 'wp-user-frontend' ) }
                    </button>
                </div>
        </ModalShell>
    );
};

export default UnsavedChanges;
