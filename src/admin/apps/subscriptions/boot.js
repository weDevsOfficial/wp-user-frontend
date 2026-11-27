/**
 * The subscriptions screen's page global (`wpufSubscriptions`), read in one
 * place; a live getter.
 *
 * @since WPUF_SINCE
 */

/** @return {Object} `wpufSubscriptions`. */
export const subscriptionsData = () => window.wpufSubscriptions || {};
