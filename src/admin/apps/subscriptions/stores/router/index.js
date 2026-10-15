/**
 * DESCRIPTION: Custom router store for WordPress admin navigation
 * DESCRIPTION: Enables URL-based navigation for the subscriptions app
 */
import { registerStore, dispatch } from '@wordpress/data';
import { getRouteQuery, inApp, setRouteQuery } from '../../../../app/client';

/**
 * Default state for router store
 */
const DEFAULT_STATE = {
	params: {},
};

/**
 * Actions for router store
 */
const actions = {
	/**
	 * Navigate to new URL params
	 *
	 * @param {Object} newParams - Query parameters to set
	 * @param {boolean} [replace=false] - Whether to replace history state instead of pushing
	 * @return {Object} Action object
	 */
	navigate(newParams, replace = false) {
		return {
			type: 'NAVIGATE',
			params: newParams,
			replace,
		};
	},

	/**
	 * Set params from URL (used for popstate)
	 *
	 * @param {Object} params - Query parameters from URL
	 * @return {Object} Action object
	 */
	setUrlParams(params) {
		return {
			type: 'SET_URL_PARAMS',
			params,
		};
	},
};

/**
 * Parse URL search string into params object
 *
 * @param {string} searchString - URL search string
 * @return {Object} Parsed parameters
 */
function parseUrlParams(searchString) {
	const urlParams = new URLSearchParams(searchString);
	const params = {};

	for (const [key, value] of urlParams) {
		// Handle multiple values for the same key
		if (params[key]) {
			if (Array.isArray(params[key])) {
				params[key].push(value);
			} else {
				params[key] = [params[key], value];
			}
		} else {
			params[key] = value;
		}
	}

	return params;
}

/**
 * Selectors for router store
 */
const selectors = {
	/**
	 * Get query parameters from URL
	 *
	 * @param {Object} state - Current state
	 * @return {Object} Query parameters
	 */
	getQueryParams(state) {
		// Read from store state, with fallback to URL for initial load
		if (state.params && Object.keys(state.params).length > 0) {
			return state.params;
		}
		// Initial load - read the route query (the admin app's hash route, or
		// the page's query string on its own page).
		return getRouteQuery();
	},

	/**
	 * Get a specific query parameter
	 *
	 * @param {Object} state - Current state
	 * @param {string} key - Parameter key
	 * @return {string|Array|null} Parameter value or null if not found
	 */
	getParam(state, key) {
		const params = selectors.getQueryParams(state);
		return params[key] || null;
	},
};

/**
 * Reducer for router store
 *
 * @param {Object} state - Current state
 * @param {Object} action - Action to process
 * @return {Object} New state
 */
function reducer(state = DEFAULT_STATE, action) {
	switch (action.type) {
		case 'NAVIGATE': {
			// Update the URL without reloading the page: the admin app's hash
			// route query, or the page's query string ('page' is kept).
			setRouteQuery(action.params, { replace: action.replace });

			// Store the complete params (not just the action params) so the
			// selector has all of them.
			return {
				...state,
				params: getRouteQuery(),
			};
		}

		case 'SET_URL_PARAMS':
			return {
				...state,
				params: action.params,
			};

		default:
			return state;
	}
}

/**
 * Register the router store
 */
const storeConfig = {
	reducer,
	selectors,
	actions,
	// No `persist`: the URL is the only source of truth. Persisted params
	// (localStorage) overrode the URL on the next load, so the menu link
	// reopened an old page, status or edit view (4.1a).
};

const STORE_NAME = 'wpuf/subscriptions-router';

registerStore(STORE_NAME, storeConfig);

// Browser back/forward on the screen's own page. In the admin app the shell
// reports query changes of the route instead (index.jsx, onRouteQuery).
window.addEventListener('popstate', () => {
	if (inApp()) {
		return;
	}

	const params = parseUrlParams(window.location.search);
	dispatch(STORE_NAME).setUrlParams(params);
});

export default storeConfig;
