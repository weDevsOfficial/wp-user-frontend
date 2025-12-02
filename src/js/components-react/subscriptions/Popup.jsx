import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

export default function Popup({ onDeleteSubscription, onTrashSubscription, onHidePopup }) {
    const { currentSubscriptionStatus } = useSelect((select) => ({
        currentSubscriptionStatus: select('wpuf/subscriptions').getCurrentStatus(),
    }), []);

    const info = useMemo(() => {
        switch (currentSubscriptionStatus) {
            case 'trash':
                return {
                    title: __('Delete Subscription', 'wp-user-frontend'),
                    message: __('Are you sure you want to delete this subscription? This action cannot be undone.', 'wp-user-frontend'),
                    actionText: __('Delete', 'wp-user-frontend'),
                };
            default:
                return {
                    title: __('Trash Subscription', 'wp-user-frontend'),
                    message: __('This subscription will be moved to the trash. Are you sure?', 'wp-user-frontend'),
                    actionText: __('Trash', 'wp-user-frontend'),
                };
        }
    }, [currentSubscriptionStatus]);

    const handleAction = () => {
        if (currentSubscriptionStatus === 'trash') {
            onDeleteSubscription();
        } else {
            onTrashSubscription();
        }
    };

    return (
        <div className="wpuf-fixed wpuf-z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div className="wpuf-fixed wpuf-inset-0 wpuf-bg-gray-500 wpuf-bg-opacity-75 wpuf-transition-opacity"></div>
            <div className="wpuf-fixed wpuf-inset-0 wpuf-z-10 wpuf-w-screen wpuf-overflow-y-auto">
                <div className="wpuf-flex wpuf-min-h-full wpuf-justify-center wpuf-text-center wpuf-items-center wpuf-p-0">
                    <div className="wpuf-relative wpuf-transform wpuf-overflow-hidden wpuf-rounded-lg wpuf-bg-white wpuf-px-4 wpuf-pb-4 wpuf-pt-5 wpuf-text-left wpuf-shadow-xl wpuf-transition-all wpuf-my-8 wpuf-w-full wpuf-max-w-lg wpuf-p-6">
                        <div className="wpuf-absolute wpuf-right-0 wpuf-top-0 wpuf-pr-4 wpuf-pt-4 wpuf-block">
                            <button
                                onClick={onHidePopup}
                                type="button"
                                className="wpuf-rounded-md wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-500 focus:wpuf-outline-none"
                            >
                                <span className="wpuf-sr-only">Close</span>
                                <svg
                                    className="wpuf-h-6 wpuf-w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div className="wpuf-flex wpuf-items-start">
                            <div className="wpuf-ml-4 wpuf-mt-0 wpuf-text-left">
                                <h3
                                    className="wpuf-text-base wpuf-font-semibold wpuf-leading-6 wpuf-text-gray-900"
                                    id="modal-title"
                                >
                                    {info.title}
                                </h3>
                                <div className="wpuf-mt-2">
                                    <p className="wpuf-text-sm wpuf-text-gray-500">{info.message}</p>
                                </div>
                            </div>
                        </div>
                        <div className="wpuf-mt-4 wpuf-flex wpuf-flex-row-reverse">
                            <button
                                type="button"
                                onClick={handleAction}
                                className="wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-red-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-red-500 wpuf-ml-3 wpuf-w-auto"
                            >
                                {info.actionText}
                            </button>
                            <button
                                type="button"
                                onClick={onHidePopup}
                                className="wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 wpuf-mt-0 wpuf-w-auto"
                            >
                                {__('Cancel', 'wp-user-frontend')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

