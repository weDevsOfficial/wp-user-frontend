/**
 * DESCRIPTION: Container component for SubscriptionForm
 * DESCRIPTION: Uses compose to connect data and actions from the subscription store
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import SubscriptionForm from '../SubscriptionForm';

/**
 * Map state to props for SubscriptionForm
 *
 * @param {Function} select - The select function from @wordpress/data
 * @param {Object} props - Component props
 * @return {Object} Mapped props from store state
 */
const mapStateToProps = ( select, { subscriptionId } ) => {
	const store = select( 'wpuf/subscriptions' );
	return {
		subscription: store.getItem(),
		isUpdating: store.isUpdating(),
		isDirty: store.isDirty(),
		isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
		currentSubscriptionStatus: store.getCurrentStatus(),
		allCount: store.getCounts(),
	};
};

/**
 * Map dispatch to props for SubscriptionForm
 *
 * @param {Function} dispatch - The dispatch function from @wordpress/data
 * @return {Object} Mapped props from action creators
 */
const mapDispatchToProps = ( dispatch ) => {
	const storeDispatch = dispatch( 'wpuf/subscriptions' );
	return {
		setItem: storeDispatch.setItem,
		setItemCopy: storeDispatch.setItemCopy,
		modifyItem: storeDispatch.modifyItem,
		setIsDirty: storeDispatch.setIsDirty,
		setIsUnsavedPopupOpen: storeDispatch.setIsUnsavedPopupOpen,
		setBlankItem: storeDispatch.setBlankItem,
		updateItem: storeDispatch.updateItem,
		fetchCounts: storeDispatch.fetchCounts,
	};
};

// Create the container component using compose
export default compose(
	withSelect( mapStateToProps ),
	withDispatch( mapDispatchToProps )
)( SubscriptionForm );
