/**
 * The forms list's data for a post type, from the `wpuf/forms-list` store.
 * Kept as the screen's hook (same return shape as before the store).
 *
 * @since WPUF_SINCE
 */
import { useCallback } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

import { PER_PAGE, store } from '../store';

export { PER_PAGE };

/**
 * @param {Object} options
 * @param {string} options.postType Post type slug. Default 'wpuf_forms'.
 * @param {Object} [options.sort]   { orderby, order }: every fetch sorts so.
 *
 * @return {Object} { forms, loading, error, pagination, counts, fetchForms }
 */
const useFormsFetch = ( { postType = 'wpuf_forms', sort = null } = {} ) => {
    const data = useSelect(
        ( select ) => {
            const list = select( store );

            return {
                forms: list.getForms( postType ),
                loading: list.isLoading( postType ),
                error: list.getError( postType ),
                pagination: list.getPagination( postType ),
                counts: list.getCounts( postType ),
            };
        },
        [ postType ]
    );
    const { fetchForms: load } = useDispatch( store );

    const fetchForms = useCallback(
        ( page = 1, status = 'any', search = '', perPage = PER_PAGE ) => load( postType, page, status, search, perPage, sort ),
        [ load, postType, sort ]
    );

    return { ...data, fetchForms };
};

export default useFormsFetch;
