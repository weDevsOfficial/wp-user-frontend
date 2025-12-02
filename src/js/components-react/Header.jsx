import { __ } from '@wordpress/i18n';

export default function Header() {
    return (
        <div className="wpuf-header">
            <h1 className="wpuf-text-2xl wpuf-font-bold">{__('Subscriptions', 'wp-user-frontend')}</h1>
        </div>
    );
}
