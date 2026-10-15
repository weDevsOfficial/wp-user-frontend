/**
 * The settings screen's page global (`wpuf_settings`: sections, fields,
 * values, urls), read in one place; a live getter, as Pro and the page
 * can update it.
 *
 * @since WPUF_SINCE
 */

/** @return {Object} `wpuf_settings`. */
export const settingsData = () => window.wpuf_settings || {};
