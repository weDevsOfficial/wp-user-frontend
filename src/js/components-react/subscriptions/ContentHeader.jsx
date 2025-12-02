import { __ } from '@wordpress/i18n';

export default function ContentHeader() {
    return (
        <div className="wpuf-content-header">
            <p>{__('Content Header Placeholder', 'wp-user-frontend')}</p>
        </div>
    );
}
