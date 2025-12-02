import { __ } from '@wordpress/i18n';

export default function Unsaved({ onClose, onGoToList }) {
    return (
        <div className="wpuf-unsaved">
            <p>{__('Unsaved Changes Placeholder', 'wp-user-frontend')}</p>
            <button onClick={onClose}>{__('Cancel', 'wp-user-frontend')}</button>
            <button onClick={onGoToList}>{__('Leave', 'wp-user-frontend')}</button>
        </div>
    );
}
