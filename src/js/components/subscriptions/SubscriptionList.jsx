/**
 * DESCRIPTION: Subscription list view component
 * DESCRIPTION: Displays grid of subscription cards with filtering and pagination
 * DESCRIPTION: Refactored to use URL-based navigation via router
 */
import { useState, useEffect, useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import LoadingSpinner from './LoadingSpinner';
import SubscriptionBox from './SubscriptionBox';
import ListHeader from './ListHeader';
import Empty from './Empty';
import Pagination from './Pagination';
import { SubscriptionListActions } from '../../slots';
import { useSubscriptionNavigation } from '../../hooks';

const SubscriptionList = () => {
	const [currentPage, setCurrentPage] = useState(1);

	// Get current status from URL params
	const { params } = useSelect((select) => {
		return {
			params: select('wpuf/subscriptions-router').getQueryParams(),
		};
	}, []);

	const currentSubscriptionStatus = params.post_status || 'all';
	const currentPageFromUrl = params.p ? parseInt(params.p, 10) : 1;

	// Sync local page state with URL
	useEffect(() => {
		if (params.p) {
			setCurrentPage(currentPageFromUrl);
		}
	}, [currentPageFromUrl, params.p]);

	const { currentSubscriptionStatus: storeStatus, allCount, isLoading, subscriptionList } = useSelect((select) => {
		const store = select('wpuf/subscriptions');
		return {
			currentSubscriptionStatus: store.getCurrentStatus(),
			allCount: store.getCounts(),
			isLoading: store.isLoading(),
			subscriptionList: store.getItems(),
		};
	}, []);

	const { fetchItems, fetchCounts, setCurrentStatus, setItem } = useDispatch('wpuf/subscriptions');

	// Navigation hook
	const { goToNew, goToEdit, goToPage } = useSubscriptionNavigation();

	// Sync store status with URL status
	useEffect(() => {
		if (currentSubscriptionStatus !== storeStatus) {
			setCurrentStatus(currentSubscriptionStatus);
		}
	}, [currentSubscriptionStatus, storeStatus]);

	// Fetch subscriptions and counts on mount and when status changes
	useEffect(() => {
		const fetchData = async () => {
			await fetchItems(currentSubscriptionStatus || 'all');
			await fetchCounts();
		};
		fetchData();
	}, [currentSubscriptionStatus]);

	const handleAddSubscription = () => {
		goToNew();
	};

	const handleChangePage = (page) => {
		// Navigate to page - this updates the URL
		goToPage(page, currentSubscriptionStatus);
		setCurrentPage(page);
	};

	const handleEditSubscription = useCallback((subscriptionId) => {
		goToEdit(subscriptionId);
	}, [goToEdit]);

	// Messages based on status
	const emptyMessages = useMemo(() => ({
		all: __('Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF\'s Subscription Features – From Charging Users for Posting to Exclusive Content Access.', 'wp-user-frontend'),
		publish: __('Ops! It looks like you haven\'t published any subscriptions yet. To create a new subscription and start monetizing your content, click the \'Add Subscription\' button above.', 'wp-user-frontend'),
		draft: __('Ops! It looks like you haven\'t saved any subscriptions as drafts yet.', 'wp-user-frontend'),
		trash: __('Your trash is empty! If you delete a subscription, it will be moved here.', 'wp-user-frontend'),
	}), []);

	const headerMessage = useMemo(() => ({
		all: __('Manage and monitor all your subscriptions. Edit details or create new ones as needed.', 'wp-user-frontend'),
		publish: __('Oversee all active subscriptions currently available for users.', 'wp-user-frontend'),
		draft: __('Handle subscriptions that are saved as drafts but not yet published.', 'wp-user-frontend'),
		trash: __('Review deleted subscriptions. Restore or permanently delete them as required.', 'wp-user-frontend'),
	}), []);

	// Get count for current status
	const count = (allCount && allCount[currentSubscriptionStatus]) ? allCount[currentSubscriptionStatus] : 0;
	// eslint-disable-next-line no-undef
	const wpufSubscriptions = window.wpufSubscriptions || {};
	const perPage = parseInt(wpufSubscriptions.perPage || 10);
	const showPagination = count > perPage;

	// Show loading spinner while fetching
	if (isLoading) {
		return <LoadingSpinner />;
	}

	// Show empty state if no subscriptions
	const isEmpty = !subscriptionList || subscriptionList.length === 0;

	return (
		<div className="wpuf-pl-[48px]">
			{isEmpty ? (
				<>
					<ListHeader message={{ status: currentSubscriptionStatus || 'all', text: headerMessage[currentSubscriptionStatus || 'all'] }} />
					<Empty
						message={emptyMessages[currentSubscriptionStatus || 'all']}
						currentSubscriptionStatus={currentSubscriptionStatus || 'all'}
						onAddSubscription={handleAddSubscription}
					/>
				</>
			) : (
				<>
					<ListHeader message={{ status: currentSubscriptionStatus || 'all', text: headerMessage[currentSubscriptionStatus || 'all'] }} />

					{/* Extension slot: Pro and third-party plugins can add actions above the grid */}
					<SubscriptionListActions.Slot
						fillProps={ { subscriptions: subscriptionList, currentStatus: currentSubscriptionStatus } }
					/>

					<div className="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-[40px]">
						{subscriptionList.map((subscription) => (
							<SubscriptionBox
								key={subscription.ID}
								subscription={subscription}
								onEdit={handleEditSubscription}
							/>
						))}
					</div>
					{showPagination && (
						<div className="wpuf-mt-8">
							<Pagination
								currentPage={currentPage}
								count={count}
								maxVisibleButtons={3}
								perPage={perPage}
								onChangePage={handleChangePage}
							/>
						</div>
					)}
				</>
			)}
		</div>
	);
};

export default SubscriptionList;
