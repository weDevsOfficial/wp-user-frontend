import { loadSettings } from './actions';

/**
 * Auto-fetch the settings payload the first time sections are read.
 *
 * @param {Object} state Current store state.
 *
 * @return {Function|undefined} loadSettings thunk or undefined if already loaded.
 */
export function getSections( state ) {
    if ( state.sections && state.sections.length > 0 ) {
        return;
    }
    return loadSettings();
}
