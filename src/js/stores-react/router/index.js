/**
 * DESCRIPTION: Custom router store for WordPress admin navigation
 * DESCRIPTION: Enables URL-based navigation for the subscriptions app
 */
import { registerStore } from '@wordpress/data';

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
	 * @return {Object} Action object
	 */
	navigate( newParams ) {
		return {
			type: 'NAVIGATE',
			params: newParams,
		};
	},
};

/**
 * Parse URL search string into params object
 *
 * @param {string} searchString - URL search string
 * @return {Object} Parsed parameters
 */
function parseUrlParams( searchString ) {
	const urlParams = new URLSearchParams( searchString );
	const params = {};

	for ( const [ key, value ] of urlParams ) {
		// Handle multiple values for the same key
		if ( params[ key ] ) {
			if ( Array.isArray( params[ key ] ) ) {
				params[ key ].push( value );
			} else {
				params[ key ] = [ params[ key ], value ];
			}
		} else {
			params[ key ] = value;
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
	getQueryParams( state ) {
		// Read from store state, with fallback to URL for initial load
		if ( state.params && Object.keys( state.params ).length > 0 ) {
			return state.params;
		}
		// Initial load - read from URL directly
		return parseUrlParams( window.location.search );
	},

	/**
	 * Get a specific query parameter
	 *
	 * @param {Object} state - Current state
	 * @param {string} key - Parameter key
	 * @return {string|Array|null} Parameter value or null if not found
	 */
	getParam( state, key ) {
		const params = selectors.getQueryParams( state );
		return params[ key ] || null;
	},
};

/**
 * Reducer for router store
 *
 * @param {Object} state - Current state
 * @param {Object} action - Action to process
 * @return {Object} New state
 */
function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'NAVIGATE':
			// Update URL without reloading page
			const url = new URL( window.location.href );

			// Update/add new params and remove ones set to null/undefined
			// Existing params (like 'page') are preserved automatically
			Object.entries( action.params ).forEach( ( [ key, value ] ) => {
				if ( value !== null && value !== undefined && value !== '' ) {
					url.searchParams.set( key, value );
				} else if ( value === null || value === undefined ) {
					url.searchParams.delete( key );
				}
			} );

			// Update browser URL
			window.history.pushState( {}, '', url.toString() );

			// Store the complete URL params (not just the action params)
			// This ensures all params are available in the state for the selector
			const allParams = parseUrlParams( url.search );

			return {
				...state,
				params: allParams,
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
	persist: [ 'params' ],
};

registerStore( 'core/router', storeConfig );

export default storeConfig;
