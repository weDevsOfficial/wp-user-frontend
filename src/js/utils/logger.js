/**
 * DESCRIPTION: Debug logging utility for subscriptions React app
 * DESCRIPTION: Only logs when debug mode is enabled via wp_localize_script
 */

// Check if debug mode is enabled via wp_localize_script
const DEBUG = window.wpufSubscriptions?.debug || false;

/**
 * Logger utility for controlled logging
 */
export const logger = {
	/**
	 * Log messages (only in debug mode)
	 *
	 * @param {...*} args - Arguments to log
	 */
	log: ( ...args ) => {
		if ( DEBUG ) {
			console.log( '[WPUF Subscriptions]', ...args );
		}
	},

	/**
	 * Log error messages (always logged)
	 *
	 * @param {...*} args - Arguments to log
	 */
	error: ( ...args ) => {
		console.error( '[WPUF Subscriptions]', ...args );
	},

	/**
	 * Log warning messages (only in debug mode)
	 *
	 * @param {...*} args - Arguments to log
	 */
	warn: ( ...args ) => {
		if ( DEBUG ) {
			console.warn( '[WPUF Subscriptions]', ...args );
		}
	},

	/**
	 * Log info messages (only in debug mode)
	 *
	 * @param {...*} args - Arguments to log
	 */
	info: ( ...args ) => {
		if ( DEBUG ) {
			console.info( '[WPUF Subscriptions]', ...args );
		}
	},
};

export default logger;
