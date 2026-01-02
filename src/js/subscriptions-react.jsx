/**
 * DESCRIPTION: Entry point for Subscriptions React app
 * DESCRIPTION: Renders the subscription management interface
 */
import { createRoot, useState, useEffect, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import Header from './components/Header';
import ContentHeader from './components/subscriptions/ContentHeader';
import SidebarMenu from './components/subscriptions/SidebarMenu';
import SubscriptionForm from './components/subscriptions/SubscriptionForm';
import SubscriptionBox from './components/subscriptions/SubscriptionBox';
import ListHeader from './components/subscriptions/ListHeader';
import Empty from './components/subscriptions/Empty';
import Pagination from './components/subscriptions/Pagination';

// Import stores to register them
import './stores-react/subscription';
import './stores-react/fieldDependency';
import './stores-react/notice';
import './stores-react/component';
import './stores-react/quickEdit';

// Import styles
import '../css/subscriptions-react.css';

// Loading spinner component
const LoadingSpinner = () => {
	return (
		<div className="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
			<div className="wpuf-animate-spin wpuf-h-12 wpuf-w-12 wpuf-border-4 wpuf-border-green-500 wpuf-border-t-transparent wpuf-rounded-full"></div>
		</div>
	);
};

// Helper to get URL params
const getUrlParams = () => {
	const params = new URLSearchParams( window.location.search );
	return {
		action: params.get( 'action' ),
		id: params.get( 'id' ),
	};
};

// Helper to navigate with URL params
const navigate = ( params ) => {
	const url = new URL( window.location.href );
	Object.keys( params ).forEach( ( key ) => {
		if ( params[ key ] === null ) {
			url.searchParams.delete( key );
		} else {
			url.searchParams.set( key, params[ key ] );
		}
	} );
	window.location.href = url.toString();
};

// List view component
const SubscriptionList = () => {
	const [ currentPage, setCurrentPage ] = useState( 1 );

	const { currentSubscriptionStatus, allCount, isLoading, subscriptionList } = useSelect( ( select ) => {
		const store = select( 'wpuf/subscriptions' );
		return {
			currentSubscriptionStatus: store.getCurrentStatus(),
			allCount: store.getCounts(),
			isLoading: store.isLoading(),
			subscriptionList: store.getItems(),
		};
	}, [] );

	const { fetchItems, fetchCounts, setCurrentStatus, setItem } = useDispatch( 'wpuf/subscriptions' );

	// Fetch subscriptions and counts on mount
	useEffect( () => {
		const fetchData = async () => {
			await fetchItems( currentSubscriptionStatus || 'all' );
			await fetchCounts();
		};
		fetchData();
	}, [] );

	const handleAddSubscription = () => {
		navigate( { action: 'add-new' } );
	};

	const checkIsDirty = ( status ) => {
		// TODO: Implement dirty check before switching status
		setCurrentStatus( status );
		fetchItems( status );
		setItem( null );
		setCurrentPage( 1 );
	};

	const handleChangePage = ( page ) => {
		// eslint-disable-next-line no-undef
		const wpufSubscriptions = window.wpufSubscriptions;
		const offset = ( page - 1 ) * parseInt( wpufSubscriptions.perPage || 10 );
		fetchItems( currentSubscriptionStatus || 'all', offset );
		setCurrentPage( page );
	};

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

	// Get count for current status
	const count = ( allCount && allCount[ currentSubscriptionStatus ] ) ? allCount[ currentSubscriptionStatus ] : 0;
	// eslint-disable-next-line no-undef
	const wpufSubscriptions = window.wpufSubscriptions || {};
	const perPage = parseInt( wpufSubscriptions.perPage || 10 );
	const showPagination = count > perPage;

	// Show loading spinner while fetching
	if ( isLoading ) {
		return (
			<>
				<Header utm="wpuf-subscriptions" />
				<LoadingSpinner />
			</>
		);
	}

	// Show empty state if no subscriptions
	const isEmpty = !subscriptionList || subscriptionList.length === 0;

	return (
		<>
			<Header utm="wpuf-subscriptions" />
			<ContentHeader
				currentSubscriptionStatus={ currentSubscriptionStatus || 'all' }
				allCount={ allCount }
				onAddSubscription={ handleAddSubscription }
			/>
			<div className="wpuf-flex wpuf-pt-[40px] wpuf-px-[20px]">
				<div className="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
					<SidebarMenu
						currentSubscriptionStatus={ currentSubscriptionStatus || 'all' }
						allCount={ allCount }
						onCheckIsDirty={ checkIsDirty }
					/>
				</div>
				<div className="wpuf-basis-4/5">
					<div className="wpuf-pl-[48px]">
						{ isEmpty ? (
							<>
								<ListHeader message={ { status: currentSubscriptionStatus || 'all', text: headerMessage[ currentSubscriptionStatus || 'all' ] } } />
								<Empty
									message={ emptyMessages[ currentSubscriptionStatus || 'all' ] }
									currentSubscriptionStatus={ currentSubscriptionStatus || 'all' }
									onAddSubscription={ handleAddSubscription }
								/>
							</>
						) : (
							<>
								<ListHeader message={ { status: currentSubscriptionStatus || 'all', text: headerMessage[ currentSubscriptionStatus || 'all' ] } } />
								<div className="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-[40px]">
									{ subscriptionList.map( ( subscription ) => (
										<SubscriptionBox
											key={ subscription.ID }
											subscription={ subscription }
										/>
									) ) }
								</div>
							</>
						) }
					</div>
					{ showPagination && (
						<Pagination
							currentPage={ currentPage }
							count={ count }
							maxVisibleButtons={ 3 }
							perPage={ perPage }
							onChangePage={ handleChangePage }
						/>
					) }
				</div>
			</div>
		</>
	);
};

const SubscriptionsApp = () => {
	const [ urlParams, setUrlParams ] = useState( getUrlParams() );

	useEffect( () => {
		const handlePopState = () => {
			setUrlParams( getUrlParams() );
		};

		window.addEventListener( 'popstate', handlePopState );
		return () => window.removeEventListener( 'popstate', handlePopState );
	}, [] );

	const action = urlParams.action;
	const id = urlParams.id;

	const navigateToList = () => {
		navigate( { action: null, id: null } );
	};

	return (
		<>
			{ ( action === 'add-new' || action === 'edit' ) ? (
				<SubscriptionForm
					mode={ action }
					subscriptionId={ id }
					onNavigateToList={ navigateToList }
				/>
			) : (
				<SubscriptionList />
			) }
		</>
	);
};

const container = document.getElementById('wpuf-subscription-page');

if (container) {
    const root = createRoot(container);
    root.render(<SubscriptionsApp />);
}
