/**
 * Jump to a settings section from anywhere on the settings screen (a note
 * under a field, a search result): the screen opens the tab that holds the
 * section, on that section, and leaves the search. Edits made so far stay
 * in the store (Save still saves them).
 *
 * @since WPUF_SINCE
 */
export const OPEN_SECTION_EVENT = 'wpuf-settings-open-section';

/**
 * @param {string} section Section id (e.g. `wpuf_general`).
 */
export const openSettingsSection = ( section ) => {
    window.dispatchEvent( new window.CustomEvent( OPEN_SECTION_EVENT, { detail: { section } } ) );
};
