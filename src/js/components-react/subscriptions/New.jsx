import { __ } from '@wordpress/i18n';
import { useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import SubscriptionsDetails from './SubscriptionsDetails';
import UpdateButton from './UpdateButton';

export default function New({ onGoToList, onCheckIsDirty }) {
    const { currentSubscriptionStatus } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            currentSubscriptionStatus: store.getCurrentStatus(),
        };
    }, []);

    const {
        setBlankItem,
        resetErrors,
        validateFields,
        updateItem,
        setSubscriptionsByStatus,
        getSubscriptionCount,
    } = useDispatch('wpuf/subscriptions');

    const { setCurrentComponent } = useDispatch('wpuf/subscriptions-component');
    const { addNotice } = useDispatch('wpuf/subscriptions-notice');

    useEffect(() => {
        setBlankItem();
    }, [setBlankItem]);

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
                setCurrentComponent('List');
                getSubscriptionCount();
            } else if (result && !result.success) {
                addNotice({
                    type: 'danger',
                    message: result.message,
                });
            }
        } catch (error) {
            console.error('Create error:', error);
            addNotice({
                type: 'danger',
                message: __('An error occurred while creating the subscription.', 'wp-user-frontend'),
            });
        }
    }, [resetErrors, validateFields, updateItem, currentSubscriptionStatus, setSubscriptionsByStatus, getSubscriptionCount, setCurrentComponent, addNotice]);

    return (
        <div className="wpuf-px-12">
            <h3 className="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{__('New Subscription', 'wp-user-frontend')}</h3>
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

