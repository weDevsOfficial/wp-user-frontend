/**
 * `wpuf/tools`: the Tools screen's server data: the export form lists per
 * form type, the shortcode reference and the onboarding entry, loaded over
 * `wpuf/v1/admin/tools/*` (tools/api.js). Each load runs when a tab opens,
 * like before, so an import shows up in Export without a reload. Busy
 * flags and the picked forms stay in the tabs.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';

import { getForms, getOnboarding, getShortcodes } from '../api';

export const STORE_NAME = 'wpuf/tools';

const EMPTY = { items: null, error: null };

export const initialState = {
    forms: {},
    shortcodes: EMPTY,
    onboarding: EMPTY,
};

export function reducer( state = initialState, action ) {
    switch ( action.type ) {
        case 'RECEIVE_FORMS':
            return { ...state, forms: { ...state.forms, [ action.formType ]: { items: action.items, error: action.error || null } } };

        case 'RECEIVE':
            return { ...state, [ action.key ]: { items: action.items, error: action.error || null } };

        default:
            return state;
    }
}

export const actions = {
    /**
     * Load the forms of a type for Export. A failed load leaves an empty
     * list and the error.
     *
     * @param {string} formType wpuf_forms|wpuf_profile
     *
     * @return {Function} Thunk; resolves with the error, or null.
     */
    loadForms( formType ) {
        return async ( { dispatch } ) => {
            try {
                dispatch( { type: 'RECEIVE_FORMS', formType, items: await getForms( formType ) } );

                return null;
            } catch ( error ) {
                dispatch( { type: 'RECEIVE_FORMS', formType, items: [], error } );

                return error;
            }
        };
    },

    /**
     * Load the shortcode reference. A failed load keeps the list empty.
     *
     * @return {Function} Thunk.
     */
    loadShortcodes() {
        return async ( { dispatch } ) => {
            try {
                dispatch( { type: 'RECEIVE', key: 'shortcodes', items: await getShortcodes() } );
            } catch ( error ) {
                dispatch( { type: 'RECEIVE', key: 'shortcodes', items: null, error } );
            }
        };
    },

    /**
     * Load the current onboarding entry (the page-load copy can be a run
     * behind after Exit setup). Failures keep the page-load copy.
     *
     * @return {Function} Thunk.
     */
    loadOnboarding() {
        return async ( { dispatch } ) => {
            try {
                dispatch( { type: 'RECEIVE', key: 'onboarding', items: await getOnboarding() } );
            } catch ( error ) {
                dispatch( { type: 'RECEIVE', key: 'onboarding', items: null, error } );
            }
        };
    },
};

export const selectors = {
    getForms: ( state, formType ) => ( state.forms[ formType ] || EMPTY ).items,
    getFormsError: ( state, formType ) => ( state.forms[ formType ] || EMPTY ).error,
    getShortcodes: ( state ) => state.shortcodes.items,
    getShortcodesError: ( state ) => state.shortcodes.error,
    getOnboarding: ( state ) => state.onboarding.items,
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
