/**
 * DESCRIPTION: Custom hook for accessing router query parameters
 * DESCRIPTION: Provides current URL params and navigation helpers
 */
import { useSelect } from '@wordpress/data';

/**
 * Hook for accessing router query parameters
 *
 * @return {Object} Router parameters including action, id, status, and page
 */
export const useRouterParams = () => {
	return useSelect(
		(select) => {
			const router = select('wpuf/subscriptions-router');
			const params = router.getQueryParams();
			return {
				action: params.action || 'list',
				subscriptionId: params.id ? parseInt(params.id, 10) : null,
				status: params.post_status || 'all',
				page: params.p ? parseInt(params.p, 10) : 1,
				params, // Return raw params for additional access
			};
		},
		[]
	);
};

export default useRouterParams;
