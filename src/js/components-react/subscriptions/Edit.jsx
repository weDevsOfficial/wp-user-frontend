import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import InfoCard from './InfoCard';
import SubscriptionsDetails from './SubscriptionsDetails';
import UpdateButton from './UpdateButton';

export default function Edit({ onGoToList, onCheckIsDirty }) {
    const { isUnsavedPopupOpen, currentSubscriptionStatus } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
            currentSubscriptionStatus: store.getCurrentStatus(),
        };
    }, []);

    const {
        resetErrors,
        validateFields,
        updateItem,
        setIsDirty,
        setIsUnsavedPopupOpen,
        setSubscriptionsByStatus,
        getSubscriptionCount,
        setItem,
    } = useDispatch('wpuf/subscriptions');

    const { setCurrentComponent } = useDispatch('wpuf/subscriptions-component');
    const { addNotice } = useDispatch('wpuf/subscriptions-notice');

    const updateSubscription = useCallback(async () => {
        resetErrors();

        const isValid = validateFields();
        if (!isValid) {
            return;
        }

        try {
            const result = await updateItem();
            if (result && result.success) {
                addNotice({
                    type: 'success',
                    message: result.message,
                });

                await setSubscriptionsByStatus(currentSubscriptionStatus);
                getSubscriptionCount();

                if (onGoToList) {
                    onGoToList();
                }
            } else if (result && !result.success) {
                addNotice({
                    type: 'danger',
                    message: result.message,
                });
            }
        } catch (error) {
            console.error('Update error:', error);
            addNotice({
                type: 'danger',
                message: __('An error occurred while updating the subscription.', 'wp-user-frontend'),
            });
        }
    }, [resetErrors, validateFields, updateItem, currentSubscriptionStatus, setSubscriptionsByStatus, getSubscriptionCount, addNotice, onGoToList]);

    return (
        <div className={`wpuf-px-12 ${isUnsavedPopupOpen ? 'wpuf-blur' : ''}`}>
            <h3 className="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{__('Edit Subscription', 'wp-user-frontend')}</h3>
            <InfoCard />
            <SubscriptionsDetails />
            <div className="wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end">
                <UpdateButton onUpdateSubscription={updateSubscription} />
                <button
                    onClick={() => onCheckIsDirty && onCheckIsDirty(currentSubscriptionStatus)}
                    type="button"
                    className="wpuf-mr-[10px] wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50"
                >
                    {__('Cancel', 'wp-user-frontend')}
                </button>
            </div>
        </div>
    );
}

