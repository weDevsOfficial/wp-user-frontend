import { __ } from '@wordpress/i18n';
import { useState, useEffect, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import SubscriptionBox from './SubscriptionBox';
import Empty from './Empty';
import Pagination from './Pagination';
import ListHeader from './ListHeader';

export default function List() {
    const [paginationKey, setPaginationKey] = useState(0);

    const {
        subscriptionList,
        isSubscriptionLoading,
        currentSubscriptionStatus,
        counts,
        currentPage,
    } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            subscriptionList: store.getItems(),
            isSubscriptionLoading: store.isLoading(),
            currentSubscriptionStatus: store.getCurrentStatus(),
            counts: store.getCounts(),
            currentPage: store.getCurrentPage(),
        };
    }, []);

    const { setSubscriptionsByStatus, setCurrentPage } = useDispatch('wpuf/subscriptions');

    const wpufSubscriptions = window.wpufSubscriptions || {};
    const perPage = parseInt(wpufSubscriptions.perPage || 10);

    const count = useMemo(() => {
        if (!counts || !currentSubscriptionStatus) {
            return 0;
        }
        return counts[currentSubscriptionStatus] || counts.all || 0;
    }, [counts, currentSubscriptionStatus]);

    const totalPages = useMemo(() => {
        return Math.ceil(count / perPage);
    }, [count, perPage]);

    const maxVisibleButtons = 3;

    const emptyMessages = {
        all: __(
            "Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF's Subscription Features – From Charging Users for Posting to Exclusive Content Access.",
            'wp-user-frontend'
        ),
        publish: __(
            "Ops! It looks like you haven't published any subscriptions yet. To create a new subscription and start monetizing your content, click the 'Add Subscription' button above.",
            'wp-user-frontend'
        ),
        draft: __("Ops! It looks like you haven't saved any subscriptions as drafts yet.", 'wp-user-frontend'),
        trash: __('Your trash is empty! If you delete a subscription, it will be moved here.', 'wp-user-frontend'),
    };

    const headerMessage = {
        all: __('Manage and monitor all your subscriptions. Edit details or create new ones as needed.', 'wp-user-frontend'),
        publish: __('Oversee all active subscriptions currently available for users.', 'wp-user-frontend'),
        draft: __('Handle subscriptions that are saved as drafts but not yet published.', 'wp-user-frontend'),
        trash: __('Review deleted subscriptions. Restore or permanently delete them as required.', 'wp-user-frontend'),
    };

    // Update count when status changes
    useEffect(() => {
        setPaginationKey((prev) => prev + 1);
    }, [counts, currentSubscriptionStatus]);

    const changePageTo = (page) => {
        const offset = (page - 1) * perPage;
        setSubscriptionsByStatus(currentSubscriptionStatus, offset);
        setCurrentPage(page);
        setPaginationKey((prev) => prev + 1);
    };

    if (isSubscriptionLoading) {
        return (
            <div className="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
                <div className="wpuf-inline-block wpuf-h-8 wpuf-w-8 wpuf-animate-spin wpuf-rounded-full wpuf-border-4 wpuf-border-solid wpuf-border-primary wpuf-border-r-transparent" role="status">
                    <span className="wpuf-sr-only">{__('Loading...', 'wp-user-frontend')}</span>
                </div>
            </div>
        );
    }

    if (!count) {
        return (
            <div className="wpuf-pl-[48px]">
                <ListHeader message={headerMessage[currentSubscriptionStatus] || headerMessage.all} />
                <Empty message={emptyMessages[currentSubscriptionStatus] || emptyMessages.all} />
            </div>
        );
    }

    return (
        <div className="wpuf-pl-[48px]">
            <ListHeader message={headerMessage[currentSubscriptionStatus] || headerMessage.all} />
            <div className="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-[40px]">
                {subscriptionList.map((subscription) => (
                    <SubscriptionBox key={subscription.ID} subscription={subscription} />
                ))}
            </div>
            {count > perPage && (
                <Pagination
                    key={paginationKey}
                    currentPage={currentPage}
                    count={count}
                    maxVisibleButtons={maxVisibleButtons}
                    totalPages={totalPages}
                    perPage={perPage}
                    onChangePageTo={changePageTo}
                />
            )}
        </div>
    );
}

