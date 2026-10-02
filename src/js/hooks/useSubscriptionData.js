/**
 * DESCRIPTION: Custom hook for accessing subscription data from store
 * DESCRIPTION: Consolidates common useSelect patterns for subscription state
 */
import { useSelect } from '@wordpress/data';

/**
 * Hook for accessing subscription data from the store
 *
 * @return {Object} Subscription data including item, status, and loading states
 */
export const useSubscriptionData = () => {
	return useSelect(
		( select ) => {
			const store = select( 'wpuf/subscriptions' );
			return {
				subscription: store.getItem(),
				isUpdating: store.isUpdating(),
				isDirty: store.isDirty(),
				isLoading: store.isLoading(),
				isUnsavedPopupOpen: store.isUnsavedPopupOpen(),
				currentStatus: store.getCurrentStatus(),
				counts: store.getCounts(),
				hasErrors: store.hasError(),
				errors: store.getErrors(),
			};
		},
		[]
	);
};

export default useSubscriptionData;
