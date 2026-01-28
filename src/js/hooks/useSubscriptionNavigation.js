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
	const { navigate } = useDispatch( 'core/router' );

	/**
	 * Navigate to edit subscription
	 *
	 * @param {number} id - Subscription ID
	 */
	const goToEdit = useCallback( ( id ) => {
		navigate( { action: 'edit', id: String( id ), post_status: null, p: null } );
	}, [ navigate ] );

	/**
	 * Navigate to new subscription form
	 */
	const goToNew = useCallback( () => {
		navigate( { action: 'new', id: null, post_status: null, p: null } );
	}, [ navigate ] );

	/**
	 * Navigate to list view
	 *
	 * @param {string|null} status - Optional status filter (all, publish, draft, trash)
	 */
	const goToList = useCallback( ( status = null ) => {
		const params = {
			action: null, // Clear action param to return to list view
			id: null, // Clear id param
			p: null, // Clear pagination
		};
		if ( status && status !== 'all' ) {
			params.post_status = status;
		} else {
			params.post_status = null; // Clear status filter if 'all'
		}
		navigate( params );
	}, [ navigate ] );

	/**
	 * Navigate to specific page
	 *
	 * @param {number} page - Page number
	 * @param {string|null} status - Optional status filter
	 */
	const goToPage = useCallback( ( page, status = null ) => {
		const params = { p: String( page ) };
		if ( status && status !== 'all' ) {
			params.post_status = status;
		}
		navigate( params );
	}, [ navigate ] );

	return { goToEdit, goToNew, goToList, goToPage };
};

export default useSubscriptionNavigation;
