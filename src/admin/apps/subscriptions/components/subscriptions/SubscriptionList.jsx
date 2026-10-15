/**
 * DESCRIPTION: Subscription list view component
 * DESCRIPTION: Displays grid of subscription cards with filtering and pagination
 * DESCRIPTION: Refactored to use URL-based navigation via router
 */
import { useEffect, useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Button, EmptyState, ErrorState, Pagination, Skeleton } from '@wpuf/components';
import { Inbox, Package, Plus } from 'lucide-react';
import SubscriptionBox from './SubscriptionBox';
import ListHeader from './ListHeader';
import { SubscriptionListActions } from '../../slots';
import { useSubscriptionNavigation } from '../../hooks';
import { subscriptionsData } from '../../boot';

// Loading: the card grid's shape (ux-states.md "card grid skeleton"), no layout jump.
const CardSkeleton = () => (
	<div className="bg-white border border-gray-200 rounded-xl shadow-sm" aria-hidden="true">
		<div className="border-b border-gray-900/5 bg-gray-50 p-6 rounded-t-xl">
			<Skeleton lines={ 2 } />
		</div>
		<div className="p-6">
			<Skeleton lines={ 2 } />
		</div>
	</div>
);

const SubscriptionList = () => {
	const { params } = useSelect( ( select ) => ( {
		params: select( 'wpuf/subscriptions-router' ).getQueryParams(),
	} ), [] );

	const currentSubscriptionStatus = params.post_status || 'all';
	const currentPage = Math.max( 1, parseInt( params.p, 10 ) || 1 );

	const { currentSubscriptionStatus: storeStatus, allCount, isLoading, listError, subscriptionList } = useSelect( ( select ) => {
		const store = select( 'wpuf/subscriptions' );
		return {
			currentSubscriptionStatus: store.getCurrentStatus(),
			allCount: store.getCounts(),
			isLoading: store.isLoading(),
			listError: store.getListError(),
			subscriptionList: store.getItems(),
		};
	}, [] );

	const { fetchItems, fetchCounts, setCurrentStatus } = useDispatch( 'wpuf/subscriptions' );
	const { goToNew, goToEdit, goToPage } = useSubscriptionNavigation();

	// eslint-disable-next-line no-undef
	const wpufSubscriptions = subscriptionsData();
	const perPage = parseInt( wpufSubscriptions.perPage || 10, 10 );
	const count = ( allCount && allCount[ currentSubscriptionStatus ] ) ? allCount[ currentSubscriptionStatus ] : 0;

	// Sync store status with URL status
	useEffect( () => {
		if ( currentSubscriptionStatus !== storeStatus ) {
			setCurrentStatus( currentSubscriptionStatus );
		}
	}, [ currentSubscriptionStatus, storeStatus ] );

	// The page's items, like develop (offset = ( page - 1 ) * perPage), and the counts.
	const load = useCallback( () => {
		fetchItems( currentSubscriptionStatus, ( currentPage - 1 ) * perPage );
		fetchCounts();
	}, [ currentSubscriptionStatus, currentPage, perPage ] );

	useEffect( () => {
		load();
	}, [ load ] );

	// After a card action: reload this page; step back when the page emptied.
	const handleChanged = useCallback( () => {
		if ( currentPage > 1 && subscriptionList && 1 === subscriptionList.length ) {
			goToPage( currentPage - 1, currentSubscriptionStatus );
			return;
		}
		load();
	}, [ currentPage, subscriptionList, currentSubscriptionStatus, goToPage, load ] );

	const handleEditSubscription = useCallback( ( subscriptionId ) => {
		goToEdit( subscriptionId );
	}, [ goToEdit ] );

	// Messages based on status
	const emptyMessages = useMemo( () => ( {
		all: __( 'Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF\'s Subscription Features – From Charging Users for Posting to Exclusive Content Access.', 'wp-user-frontend' ),
		publish: __( 'Ops! It looks like you haven\'t published any subscriptions yet. To create a new subscription and start monetizing your content, click the \'Add Subscription\' button above.', 'wp-user-frontend' ),
		draft: __( 'Ops! It looks like you haven\'t saved any subscriptions as drafts yet.', 'wp-user-frontend' ),
		trash: __( 'Your trash is empty! If you delete a subscription, it will be moved here.', 'wp-user-frontend' ),
	} ), [] );

	const headerMessage = useMemo( () => ( {
		all: __( 'Manage and monitor all your subscriptions. Edit details or create new ones as needed.', 'wp-user-frontend' ),
		publish: __( 'Oversee all active subscriptions currently available for users.', 'wp-user-frontend' ),
		draft: __( 'Handle subscriptions that are saved as drafts but not yet published.', 'wp-user-frontend' ),
		trash: __( 'Review deleted subscriptions. Restore or permanently delete them as required.', 'wp-user-frontend' ),
	} ), [] );

	const header = <ListHeader message={ { status: currentSubscriptionStatus, text: headerMessage[ currentSubscriptionStatus ] } } />;

	if ( isLoading ) {
		return (
			<div className="pl-[48px]" aria-busy="true">
				{ header }
				<div className="grid grid-cols-3 gap-4 mt-[40px]">
					{ Array.from( { length: Math.min( perPage, Math.max( count, 3 ) ) } ).map( ( _, index ) => <CardSkeleton key={ index } /> ) }
				</div>
			</div>
		);
	}

	if ( listError ) {
		return (
			<div className="pl-[48px]">
				{ header }
				<ErrorState className="mt-[40px]" message={ listError } onRetry={ load } />
			</div>
		);
	}

	const isEmpty = ! subscriptionList || 0 === subscriptionList.length;

	if ( isEmpty ) {
		const isAll = 'all' === currentSubscriptionStatus;

		return (
			<div className="pl-[48px]">
				{ header }
				<EmptyState
					size={ isAll ? 'page' : 'card' }
					icon={ isAll ? Package : Inbox }
					title={ isAll ? __( 'No Subscription created yet!', 'wp-user-frontend' ) : emptyMessages[ currentSubscriptionStatus ] }
					description={ isAll ? emptyMessages[ currentSubscriptionStatus ] : null }
					actions={ isAll ? (
						<Button onClick={ goToNew }>
							<Plus size={ 16 } aria-hidden="true" />
							{ __( 'Add Subscription', 'wp-user-frontend' ) }
						</Button>
					) : null }
				/>
			</div>
		);
	}

	return (
		<>
			<div className="pl-[48px]">
				{ header }

				{/* Extension slot: Pro and third-party plugins can add actions above the grid */}
				<SubscriptionListActions.Slot
					fillProps={ { subscriptions: subscriptionList, currentStatus: currentSubscriptionStatus } }
				/>

				<div className="grid grid-cols-3 gap-4 mt-[40px]">
					{ subscriptionList.map( ( subscription ) => (
						<SubscriptionBox
							key={ subscription.ID }
							subscription={ subscription }
							onEdit={ handleEditSubscription }
							onChanged={ handleChanged }
						/>
					) ) }
				</div>
			</div>
			{ count > perPage && (
				<Pagination
					variant="footer"
					className="mt-6 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm"
					currentPage={ currentPage }
					total={ count }
					perPage={ perPage }
					onPageChange={ ( page ) => goToPage( page, currentSubscriptionStatus ) }
				/>
			) }
		</>
	);
};

export default SubscriptionList;
