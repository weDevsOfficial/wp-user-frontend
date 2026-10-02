/**
 * Subscriptions API
 * Centralizes all apiFetch calls for the subscriptions module
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const NAMESPACE = '/wpuf/v1';
const ENDPOINT = `${NAMESPACE}/wpuf_subscription`;

/**
 * Fetch subscriptions list
 *
 * @param {Object} params - Query parameters (status, offset, per_page)
 * @return {Promise<Object>} API response
 */
export const fetchSubscriptions = async (params = {}) => {
    return apiFetch({
        path: addQueryArgs(ENDPOINT, params),
        method: 'GET',
    });
};

/**
 * Fetch single subscription
 *
 * @param {number} id - Subscription ID
 * @return {Promise<Object>} API response
 */
export const fetchSubscription = async (id) => {
    return apiFetch({
        path: `${ENDPOINT}/${id}`,
        method: 'GET',
    });
};

/**
 * Fetch subscription counts
 *
 * @param {string} status - Optional status filter
 * @return {Promise<Object>} API response
 */
export const fetchSubscriptionCounts = async (status = 'all') => {
    let path = `${ENDPOINT}/count`;
    if (status && status !== 'all') {
        path += `/${status}`;
    }
    return apiFetch({
        path,
        method: 'GET',
    });
};

/**
 * Create or Update subscription
 *
 * @param {Object} data - Subscription data
 * @return {Promise<Object>} API response
 */
export const updateSubscription = async (data) => {
    let path = ENDPOINT;
    if (data.ID) {
        path += `/${data.ID}`;
    }
    return apiFetch({
        path,
        method: 'POST',
        data: { subscription: data },
    });
};

/**
 * Delete subscription
 *
 * @param {number} id - Subscription ID
 * @return {Promise<Object>} API response
 */
export const deleteSubscription = async (id) => {
    return apiFetch({
        path: `${ENDPOINT}/${id}`,
        method: 'DELETE',
    });
};

/**
 * Fetch subscribers for a subscription
 *
 * @param {number} subscriptionId - Subscription ID
 * @return {Promise<Object>} API response
 */
export const fetchSubscribers = async (subscriptionId) => {
    return apiFetch({
        path: addQueryArgs(`${ENDPOINT}/subscribers`, { subscription_id: subscriptionId }),
        method: 'GET',
    });
};

/**
 * Fetch subscription settings
 *
 * @return {Promise<Object>} API response
 */
export const fetchSubscriptionSettings = async () => {
    return apiFetch({
        path: `${NAMESPACE}/subscription-settings`,
        method: 'GET',
    });
};

/**
 * Save subscription settings
 *
 * @param {Object} settings - Settings data
 * @return {Promise<Object>} API response
 */
export const saveSubscriptionSettings = async (settings) => {
    return apiFetch({
        path: `${NAMESPACE}/subscription-settings`,
        method: 'POST',
        data: settings,
    });
};
