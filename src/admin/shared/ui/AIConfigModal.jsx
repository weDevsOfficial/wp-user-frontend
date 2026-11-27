/**
 * AIConfigModal: the "AI Provider Not Configured" dialog, on the shared
 * ConfirmDialog (variant card, plugin-ui look). Used by the forms list and the builder's AI
 * options (window.wpuf.components.AIConfigModal).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { KeyRound } from 'lucide-react';
import ConfirmDialog from './ConfirmDialog';

const KeyIcon = () => (
    <span className="flex size-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600" aria-hidden="true">
        <KeyRound className="size-6" />
    </span>
);

const AIConfigModal = ( { isOpen, onClose, onGoToSettings } ) => (
    <ConfirmDialog
        open={ isOpen }
        variant="card"
        media={ <KeyIcon /> }
        title={ __( 'AI Provider Not Configured', 'wp-user-frontend' ) }
        message={ __( 'To use AI Form Generation, please connect an AI provider by adding your API key in the settings', 'wp-user-frontend' ) }
        cancelText={ __( 'Cancel', 'wp-user-frontend' ) }
        confirmText={ __( 'Go to Settings', 'wp-user-frontend' ) }
        tone="primary"
        onCancel={ onClose }
        onConfirm={ onGoToSettings }
    />
);

export default AIConfigModal;
