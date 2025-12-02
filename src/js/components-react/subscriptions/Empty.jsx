import { __ } from '@wordpress/i18n';

export default function Empty() {
    return (
        <div className="wpuf-empty">
            <p>{__('Empty Component Placeholder', 'wp-user-frontend')}</p>
        </div>
    );
}
