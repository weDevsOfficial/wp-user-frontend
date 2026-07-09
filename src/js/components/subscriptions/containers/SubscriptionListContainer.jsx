/**
 * DESCRIPTION: Container component for SubscriptionList
 * DESCRIPTION: Uses compose to connect list data and actions from the subscription store
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import SubscriptionList from '../SubscriptionList';

/**
 * Map state to props for SubscriptionList
 *
 * @param {Function} select - The select function from @wordpress/data
 * @param {Object} props - Component props
 * @return {Object} Mapped props from store state
 */
const mapStateToProps = ( select, { initialStatus } ) => {
	const store = select( 'wpuf/subscriptions' );
	return {
		subscriptions: store.getItems(),
		counts: store.getCounts(),
		isLoading: store.isLoading(),
		currentSubscriptionStatus: initialStatus || 'all',
	};
};

/**
 * Map dispatch to props for SubscriptionList
 *
 * @param {Function} dispatch - The dispatch function from @wordpress/data
 * @return {Object} Mapped props from action creators
 */
const mapDispatchToProps = ( dispatch ) => {
	const storeDispatch = dispatch( 'wpuf/subscriptions' );
	return {
		fetchItems: storeDispatch.fetchItems,
		fetchCounts: storeDispatch.fetchCounts,
		setCurrentStatus: storeDispatch.setCurrentStatus,
		setItem: storeDispatch.setItem,
	};
};

// Create the container component using compose
export default compose(
	withSelect( mapStateToProps ),
	withDispatch( mapDispatchToProps )
)( SubscriptionList );
