/**
 * DESCRIPTION: Custom hook for subscription list data
 * DESCRIPTION: Provides filtered list data for list view components
 */
import { useSelect } from '@wordpress/data';

/**
 * Hook for accessing subscription list data
 *
 * @param {string} status - The subscription status filter
 * @return {Object} List data including subscriptions, counts, and loading state
 */
export const useSubscriptionListData = ( status = 'all' ) => {
	return useSelect(
		( select ) => {
			const store = select( 'wpuf/subscriptions' );
			return {
				subscriptions: store.getItems(),
				counts: store.getCounts(),
				isLoading: store.isLoading(),
				currentStatus: store.getCurrentStatus(),
			};
		},
		[ status ]
	);
};

export default useSubscriptionListData;
