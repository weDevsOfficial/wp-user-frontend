/**
 * DESCRIPTION: Subscription list view component
 * DESCRIPTION: One list card (right of the status sidebar): the status title and text, Preferences and the
 * DESCRIPTION: card / list view switch, the packs as cards (default) or table rows, and the footer pagination;
 * DESCRIPTION: Preferences live in Settings > Payments > Subscriptions (quick link in the toolbar)
 */
import { useEffect, useMemo, useCallback, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Button, EmptyState, ErrorState, Pagination, Skeleton, TableSkeleton } from '@wpuf/components';
import { Inbox, LayoutGrid, List, Package, Plus, Settings } from 'lucide-react';
import SubscriptionBox from './SubscriptionBox';
import SubscriptionTable from './SubscriptionTable';
import { SubscriptionListActions } from '../../slots';
import { useSubscriptionNavigation } from '../../hooks';
import { subscriptionsData } from '../../boot';
import { openRoute } from '../../../../app/client';

// The former Preferences tab: its section in Settings (Payments > Subscriptions).
const PREFERENCES_ROUTE = '/settings?tab=wpuf_subscription_settings';
const openPreferences = () => openRoute( PREFERENCES_ROUTE, 'admin.php?page=wp-user-frontend#' + PREFERENCES_ROUTE );

// The viewer's card / list choice (a per-browser convenience; cards when unset).
const VIEW_KEY = 'wpuf_subscriptions_view';
const VIEWS = [ 'card', 'list' ];

const readView = () => {
	try {
		const stored = window.localStorage.getItem( VIEW_KEY );
		return VIEWS.includes( stored ) ? stored : 'card';
	} catch ( error ) {
		return 'card';
	}
};

const saveView = ( view ) => {
	try {
		window.localStorage.setItem( VIEW_KEY, view );
	} catch ( error ) {
		// Storage blocked: the choice lasts until the page reloads.
	}
};

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

const GRID = 'grid grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-3';

const ViewSwitch = ( { view, onChange } ) => (
	<div className="flex items-center gap-1" role="group" aria-label={ __( 'View', 'wp-user-frontend' ) }>
		<Button
			variant="icon"
			size="sm"
			aria-pressed={ 'card' === view }
			aria-label={ __( 'Card view', 'wp-user-frontend' ) }
			title={ __( 'Card view', 'wp-user-frontend' ) }
			className={ 'card' === view ? 'bg-gray-100 text-primary' : '' }
			onClick={ () => onChange( 'card' ) }
		>
			<LayoutGrid size={ 16 } aria-hidden="true" />
		</Button>
		<Button
			variant="icon"
			size="sm"
			aria-pressed={ 'list' === view }
			aria-label={ __( 'List view', 'wp-user-frontend' ) }
			title={ __( 'List view', 'wp-user-frontend' ) }
			className={ 'list' === view ? 'bg-gray-100 text-primary' : '' }
			onClick={ () => onChange( 'list' ) }
		>
			<List size={ 16 } aria-hidden="true" />
		</Button>
	</div>
);

const SubscriptionList = () => {
	const { params } = useSelect( ( select ) => ( {
		params: select( 'wpuf/subscriptions-router' ).getQueryParams(),
	} ), [] );

	const currentSubscriptionStatus = params.post_status || 'all';
	const isPreferences = 'preferences' === currentSubscriptionStatus;
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

	const [ view, setView ] = useState( readView );
	const changeView = useCallback( ( next ) => {
		setView( next );
		saveView( next );
	}, [] );

	// eslint-disable-next-line no-undef
	const wpufSubscriptions = subscriptionsData();
	const perPage = parseInt( wpufSubscriptions.perPage || 10, 10 );
	const count = ( allCount && allCount[ currentSubscriptionStatus ] ) ? allCount[ currentSubscriptionStatus ] : 0;

	// Sync store status with URL status
	useEffect( () => {
		if ( ! isPreferences && currentSubscriptionStatus !== storeStatus ) {
			setCurrentStatus( currentSubscriptionStatus );
		}
	}, [ isPreferences, currentSubscriptionStatus, storeStatus ] );

	// The page's items, like develop (offset = ( page - 1 ) * perPage), and the counts.
	const load = useCallback( () => {
		fetchItems( currentSubscriptionStatus, ( currentPage - 1 ) * perPage );
		fetchCounts();
	}, [ currentSubscriptionStatus, currentPage, perPage ] );

	useEffect( () => {
		if ( ! isPreferences ) {
			load();
		}
	}, [ isPreferences, load ] );

	// Old links to the Preferences tab (`post_status=preferences`) open its Settings section.
	useEffect( () => {
		if ( isPreferences ) {
			openPreferences();
		}
	}, [ isPreferences ] );

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

	// develop's ListHeader title per status (its "All" shows the plain "Subscriptions").
	const headerTitle = {
		all: __( 'Subscriptions', 'wp-user-frontend' ),
		publish: __( 'Published', 'wp-user-frontend' ),
		draft: __( 'Drafts', 'wp-user-frontend' ),
		trash: __( 'Trash', 'wp-user-frontend' ),
	};

	let content;

	if ( isPreferences || isLoading ) {
		const rows = Math.min( perPage, Math.max( count, 3 ) );

		content = 'list' === view ? (
			<TableSkeleton rows={ rows } checkbox={ false } />
		) : (
			<div className={ GRID } aria-busy="true">
				{ Array.from( { length: rows } ).map( ( _, index ) => <CardSkeleton key={ index } /> ) }
			</div>
		);
	} else if ( listError ) {
		content = <ErrorState className="m-4" message={ listError } onRetry={ load } />;
	} else if ( ! subscriptionList || 0 === subscriptionList.length ) {
		const isAll = 'all' === currentSubscriptionStatus;

		content = (
			<EmptyState
				size={ isAll ? 'page' : 'card' }
				icon={ isAll ? Package : Inbox }
				title={ isAll ? __( 'No Subscription created yet!', 'wp-user-frontend' ) : emptyMessages[ currentSubscriptionStatus ] }
				description={ isAll ? emptyMessages[ currentSubscriptionStatus ] : null }
				// The Published / Drafts texts point at "Add Subscription": offer it there too.
				actions={ 'trash' !== currentSubscriptionStatus ? (
					<Button onClick={ goToNew }>
						<Plus size={ 16 } aria-hidden="true" />
						{ __( 'Add Subscription', 'wp-user-frontend' ) }
					</Button>
				) : null }
			/>
		);
	} else {
		content = (
			<>
				{ 'list' === view ? (
					<SubscriptionTable
						subscriptions={ subscriptionList }
						onEdit={ handleEditSubscription }
						onChanged={ handleChanged }
					/>
				) : (
					<div className={ GRID }>
						{ subscriptionList.map( ( subscription ) => (
							<SubscriptionBox
								key={ subscription.ID }
								subscription={ subscription }
								onEdit={ handleEditSubscription }
								onChanged={ handleChanged }
							/>
						) ) }
					</div>
				) }
				{ count > perPage && (
					<Pagination
						variant="footer"
						currentPage={ currentPage }
						total={ count }
						perPage={ perPage }
						onPageChange={ ( page ) => goToPage( page, currentSubscriptionStatus ) }
					/>
				) }
			</>
		);
	}

	// List card (FlyHR, as the forms lists): toolbar with develop's status title and
	// text, Preferences + view switch, the packs or a state, footer pagination.
	return (
		<div className="rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
			<div className="flex flex-wrap items-center justify-between gap-3 border-0 border-b border-solid border-gray-200 px-4 py-3">
				<div className="min-w-0">
					<h3 className="m-0 text-base font-semibold text-gray-900">{ headerTitle[ currentSubscriptionStatus ] || __( 'Subscriptions', 'wp-user-frontend' ) }</h3>
					<p className="m-0 mt-1 text-sm text-gray-500">
						{ headerMessage[ currentSubscriptionStatus ] || __( 'Explore and manage all subscriptions in one place', 'wp-user-frontend' ) }
					</p>
				</div>
				<div className="flex items-center gap-2">
					<Button variant="secondary" size="sm" onClick={ openPreferences }>
						<Settings size={ 16 } aria-hidden="true" />
						{ __( 'Preferences', 'wp-user-frontend' ) }
					</Button>
					<ViewSwitch view={ view } onChange={ changeView } />
				</div>
			</div>

			{ /* Extension slot: Pro and third-party plugins can add actions above the packs */ }
			{ ! isPreferences && subscriptionList && subscriptionList.length > 0 && ! isLoading && ! listError && (
				<SubscriptionListActions.Slot
					fillProps={ { subscriptions: subscriptionList, currentStatus: currentSubscriptionStatus } }
				/>
			) }

			{ content }
		</div>
	);
};

export default SubscriptionList;
