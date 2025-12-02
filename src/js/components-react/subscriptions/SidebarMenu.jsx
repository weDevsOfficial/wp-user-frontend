import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

export default function SidebarMenu({ onCheckIsDirty }) {
    const { currentSubscriptionStatus, counts, isUnsavedPopupOpen } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            currentSubscriptionStatus: store.getCurrentStatus(),
            counts: store.getCounts(),
            isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
        };
    }, []);

    const status = useMemo(
        () => [
            { all: __('All Subscriptions', 'wp-user-frontend') },
            { publish: __('Published', 'wp-user-frontend') },
            { draft: __('Drafts', 'wp-user-frontend') },
            { trash: __('Trash', 'wp-user-frontend') },
            { preferences: __('Preferences', 'wp-user-frontend') },
        ],
        []
    );

    return (
        <div className={isUnsavedPopupOpen ? 'wpuf-blur' : ''}>
            <div className="wpuf-flex wpuf-flex-col wpuf-pr-[48px]">
                <ul className="wpuf-space-y-2 wpuf-text-lg">
                    {status.map((item) => {
                        const key = Object.keys(item)[0];
                        const label = item[key];
                        const count = counts && counts[key] ? counts[key] : 0;
                        const isActive = currentSubscriptionStatus === key;

                        return (
                            <li
                                key={key}
                                onClick={() => onCheckIsDirty(key)}
                                className={`wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-py-2 wpuf-px-[20px] wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer ${
                                    isActive ? 'wpuf-bg-gray-50 wpuf-text-primary' : ''
                                }`}
                            >
                                {label}
                                {count > 0 && (
                                    <span
                                        className={`wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-rounded-full wpuf-w-max wpuf-h-max wpuf-border ${
                                            isActive ? 'wpuf-border-primary' : ''
                                        }`}
                                    >
                                        {count}
                                    </span>
                                )}
                            </li>
                        );
                    })}
                </ul>
            </div>
        </div>
    );
}

