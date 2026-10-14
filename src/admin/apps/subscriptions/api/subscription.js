/**
 * Subscriptions API
 * Centralizes the REST calls of the subscriptions module; they go through
 * the shared request layer (`@wpuf/api`: nonce and REST root from core,
 * timeout, GET retry on a 5xx, JSON parsing that skips stray PHP output).
 */
import { request } from '@wpuf/api';
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
    return request(addQueryArgs(ENDPOINT, params), {
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
    return request(`${ENDPOINT}/${id}`, {
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
    return request(path, {
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
    return request(path, {
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
    return request(`${ENDPOINT}/${id}`, {
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
    return request(addQueryArgs(`${ENDPOINT}/subscribers`, { subscription_id: subscriptionId }), {
        method: 'GET',
    });
};

/**
 * Fetch subscription settings
 *
 * @return {Promise<Object>} API response
 */
export const fetchSubscriptionSettings = async () => {
    return request(`${NAMESPACE}/subscription-settings`, {
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
    return request(`${NAMESPACE}/subscription-settings`, {
        method: 'POST',
        data: settings,
    });
};

/**
 * Fetch the subscribers of one pack (the Subscribers view)
 *
 * @param {number} subscriptionId - Subscription pack ID
 * @param {Object} params - status, orderby, order, page, per_page
 * @return {Promise<Object>} { pack, items, total, pages, counts }
 */
export const fetchPackSubscribers = async (subscriptionId, params = {}) => {
    return request(addQueryArgs(`${NAMESPACE}/admin/subscriptions/${subscriptionId}/subscribers`, params), {
        method: 'GET',
    });
};
