import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';

export default function Empty({ message }) {
    const { currentSubscriptionStatus } = useSelect((select) => ({
        currentSubscriptionStatus: select('wpuf/subscriptions').getCurrentStatus(),
    }), []);

    const { setCurrentComponent } = useDispatch('wpuf/subscriptions-component');

    const defaultMessage = __('No Subscription created yet!', 'wp-user-frontend');
    const displayMessage = message || defaultMessage;

    return (
        <div className="wpuf-h-[50vh] wpuf-flex wpuf-items-center wpuf-justify-center">
            <div className="wpuf-w-3/4 wpuf-text-center">
                {currentSubscriptionStatus === 'all' && (
                    <svg
                        className="wpuf-mx-auto wpuf-h-12 wpuf-w-12 wpuf-text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            vectorEffect="non-scaling-stroke"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                        />
                    </svg>
                )}
                {currentSubscriptionStatus === 'all' && (
                    <h3 className="wpuf-text-3xl wpuf-text-gray-900">
                        {__('No Subscription created yet!', 'wp-user-frontend')}
                    </h3>
                )}
                <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-text-center wpuf-mt-8">
                    {displayMessage}
                </p>
                {currentSubscriptionStatus === 'all' && (
                    <div className="wpuf-mt-12">
                        <button
                            type="button"
                            onClick={() => setCurrentComponent('New')}
                            className="wpuf-rounded-md wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-primary"
                        >
                            <span className="dashicons dashicons-plus-alt"></span>&nbsp;&nbsp;&nbsp;
                            {__('Add Subscription', 'wp-user-frontend')}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
