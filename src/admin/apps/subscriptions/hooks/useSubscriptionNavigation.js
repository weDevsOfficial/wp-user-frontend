/**
 * DESCRIPTION: Navigation hook for subscriptions
 * DESCRIPTION: Provides URL-based navigation methods for the subscriptions app
 */
import { useCallback } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

/**
 * Hook for navigating the subscriptions app using URL parameters
 *
 * @return {Object} Navigation methods
 */
export const useSubscriptionNavigation = () => {
	const { navigate } = useDispatch('wpuf/subscriptions-router');

	/**
	 * Navigate to edit subscription
	 *
	 * @param {number} id - Subscription ID
	 */
	const goToEdit = useCallback((id) => {
		// The status filter stays: the sidebar keeps it and Cancel / save return to it.
		navigate({ action: 'edit', id: String(id), p: null });
	}, [navigate]);

	/**
	 * Navigate to the subscribers of a pack
	 *
	 * @param {number} id - Subscription ID
	 */
	const goToSubscribers = useCallback((id) => {
		navigate({ action: 'subscribers', id: String(id), post_status: null, p: null });
	}, [navigate]);

	/**
	 * Navigate to new subscription form
	 */
	const goToNew = useCallback(() => {
		navigate({ action: 'new', id: null, p: null });
	}, [navigate]);

	/**
	 * Navigate to list view
	 *
	 * @param {string|null} [status] Status filter (all, publish, draft, trash); left out, the current one stays
	 */
	const goToList = useCallback((status) => {
		const params = {
			action: null, // Clear action param to return to list view
			id: null, // Clear id param
			p: null, // Clear pagination
		};
		// Left out, the current filter stays (the editor's Cancel / save); 'all' clears it.
		if (undefined !== status) {
			params.post_status = status && status !== 'all' ? status : null;
		}
		navigate(params);
	}, [navigate]);

	/**
	 * Navigate to specific page
	 *
	 * @param {number} page - Page number
	 * @param {string|null} status - Optional status filter
	 */
	const goToPage = useCallback((page, status = null) => {
		const params = { p: String(page) };
		if (status && status !== 'all') {
			params.post_status = status;
		}
		navigate(params);
	}, [navigate]);

	return { goToEdit, goToSubscribers, goToNew, goToList, goToPage };
};

export default useSubscriptionNavigation;
