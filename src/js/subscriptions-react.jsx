/**
 * DESCRIPTION: Entry point for Subscriptions React app
 * DESCRIPTION: Renders the subscription management interface
 */
import { createRoot, useState, useEffect } from '@wordpress/element';
import Header from './components/Header';
import ContentHeader from './components/subscriptions/ContentHeader';
import SidebarMenu from './components/subscriptions/SidebarMenu';
import SubscriptionForm from './components/subscriptions/SubscriptionForm';

// Import stores to register them
import './stores-react/subscription';
import './stores-react/fieldDependency';
import './stores-react/notice';
import './stores-react/component';
import './stores-react/quickEdit';

// Import styles
import '../css/subscriptions-react.css';

// Placeholder component for subscription list/content area
const SubscriptionComponent = () => {
	return (
		<div className="wpuf-p-4 wpuf-text-center wpuf-text-gray-500">
			<p>Subscription list will be displayed here</p>
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
	const [ currentSubscriptionStatus, setCurrentSubscriptionStatus ] = useState( 'all' );
	const [ allCount, setAllCount ] = useState( { all: 0 } );

	const handleAddSubscription = () => {
		navigate( { action: 'add-new' } );
	};

	const checkIsDirty = ( status ) => {
		// TODO: Implement dirty check before switching status
		setCurrentSubscriptionStatus( status );
	};

	return (
		<>
			<Header utm="wpuf-subscriptions" />
			<ContentHeader
				currentSubscriptionStatus={ currentSubscriptionStatus }
				allCount={ allCount }
				onAddSubscription={ handleAddSubscription }
			/>
			<div className="wpuf-flex wpuf-pt-[40px] wpuf-px-[20px]">
				<div className="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
					<SidebarMenu
						currentSubscriptionStatus={ currentSubscriptionStatus }
						allCount={ allCount }
						onCheckIsDirty={ checkIsDirty }
					/>
				</div>
				<div className="wpuf-basis-4/5">
					<SubscriptionComponent />
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
