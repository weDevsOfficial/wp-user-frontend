/**
 * DESCRIPTION: Resolvers for auto-fetching subscription data
 * DESCRIPTION: Resolvers automatically trigger when selectors are called
 */
import { fetchItems, fetchCounts } from './actions';

/**
 * Resolver for getItems selector
 * Auto-fetches subscriptions when the selector is called and data is empty
 *
 * @param {Object} state - Current store state
 * @param {string} status - Subscription status filter (all, publish, draft, trash)
 * @param {number} offset - Query offset for pagination
 * @return {Promise|undefined} Promise from fetchItems action or undefined if data exists
 */
export function getItems( state, status, offset ) {
	// Only fetch if items are empty (resolver won't trigger if data exists)
	if ( state.items && state.items.length > 0 ) {
		return;
	}
	return fetchItems( status || 'all', offset || 0 );
}

/**
 * Resolver for getCounts selector
 * Auto-fetches subscription counts when the selector is called and data is empty
 *
 * @param {Object} state - Current store state
 * @param {string} status - Subscription status filter (unused, fetches all counts)
 * @return {Promise|undefined} Promise from fetchCounts action or undefined if data exists
 */
export function getCounts( state ) {
	// Only fetch if counts are empty
	if ( state.counts && Object.keys( state.counts ).length > 0 ) {
		return;
	}
	return fetchCounts();
}
