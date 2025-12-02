import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

export default function ListHeader({ message }) {
    const { currentSubscriptionStatus } = useSelect((select) => ({
        currentSubscriptionStatus: select('wpuf/subscriptions').getCurrentStatus(),
    }), []);

    const title = useMemo(() => {
        switch (currentSubscriptionStatus) {
            case 'any':
                return __('All Subscriptions', 'wp-user-frontend');
            case 'publish':
                return __('Published', 'wp-user-frontend');
            case 'draft':
                return __('Drafts', 'wp-user-frontend');
            case 'trash':
                return __('Trash', 'wp-user-frontend');
            default:
                return __('Subscriptions', 'wp-user-frontend');
        }
    }, [currentSubscriptionStatus]);

    const defaultMessage = __('Explore and manage all subscriptions in one place', 'wp-user-frontend');
    const displayMessage = message || defaultMessage;

    return (
        <>
            <h3 className="wpuf-text-lg wpuf-font-bold wpuf-m-0">{title}</h3>
            <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-mb-0">{displayMessage}</p>
        </>
    );
}

