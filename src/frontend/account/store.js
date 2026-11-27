/**
 * `wpuf/account`: the account app's shared state: the location (query
 * string, written only through router.js), the profile and stats of the
 * header and overview, and each post type's list (page data, loading,
 * error). Sections read and change it through selectors / actions instead
 * of passing setters around. Form fields of Edit profile / Change password
 * stay local to their section.
 *
 * @since WPUF_SINCE
 */
import { createReduxStore, register } from '@wordpress/data';
import { api } from '@wpuf/frontend-kit';

import { currentSearch, pageOf, push, sectionOf } from './router';

export const STORE_NAME = 'wpuf/account';

const EMPTY_POSTS = { data: null, loading: true, error: '', requestId: 0 };

export const initialState = {
    search: '',
    profile: {},
    stats: [],
    posts: {},
};

const postsOf = ( state, type ) => state.posts[ type ] || EMPTY_POSTS;

const withPosts = ( state, type, patch ) => ( { ...state, posts: { ...state.posts, [ type ]: { ...postsOf( state, type ), ...patch } } } );

export function reducer( state = initialState, action ) {
    switch ( action.type ) {
        case 'INIT':
            return { ...state, search: action.search, profile: action.profile || {}, stats: action.stats || [] };

        case 'LOCATION':
            return state.search === action.search ? state : { ...state, search: action.search };

        case 'RECEIVE_ACCOUNT':
            return { ...state, profile: action.profile || state.profile, stats: action.stats || [] };

        case 'SET_PROFILE':
            return { ...state, profile: action.profile };

        case 'POSTS_START':
            return withPosts( state, action.postType, { loading: true, error: '', requestId: action.requestId } );

        case 'POSTS_DONE':
            return action.requestId === postsOf( state, action.postType ).requestId
                ? withPosts( state, action.postType, { loading: false, error: '', data: action.data } )
                : state;

        case 'POSTS_FAIL':
            return action.requestId === postsOf( state, action.postType ).requestId
                ? withPosts( state, action.postType, { loading: false, error: action.error, data: null } )
                : state;

        case 'POSTS_ERROR':
            return withPosts( state, action.postType, { error: action.error } );

        default:
            return state;
    }
}

let lastRequest = 0;

export const actions = {
    /**
     * The boot data and the first URL.
     *
     * @param {Object} boot { profile, stats }.
     *
     * @return {Object} Action.
     */
    init( boot ) {
        return { type: 'INIT', search: currentSearch(), profile: boot.profile, stats: boot.stats };
    },

    /**
     * Go to a URL of the account page (adds a history entry).
     *
     * @param {string} url URL.
     *
     * @return {Function} Thunk.
     */
    navigate( url ) {
        return ( { dispatch } ) => {
            push( url );
            dispatch( { type: 'LOCATION', search: currentSearch() } );
        };
    },

    /**
     * The address bar changed without us (back / forward).
     *
     * @param {string} search Query string.
     *
     * @return {Object} Action.
     */
    setLocation( search ) {
        return { type: 'LOCATION', search };
    },

    /**
     * Reload the header profile and the stats.
     *
     * @return {Function} Thunk.
     */
    refreshAccount() {
        return async ( { dispatch } ) => {
            try {
                const fresh = await api.getAccount();

                dispatch( { type: 'RECEIVE_ACCOUNT', profile: fresh.profile, stats: fresh.stats } );
            } catch ( e ) {
                // The header keeps what it shows.
            }
        };
    },

    setProfile( profile ) {
        return { type: 'SET_PROFILE', profile };
    },

    /**
     * Load a page of the user's posts of a type. Only the latest load of a
     * type writes the list.
     *
     * @param {string} postType Post type.
     * @param {number} page     Page.
     *
     * @return {Function} Thunk.
     */
    loadPosts( postType, page ) {
        return async ( { dispatch } ) => {
            const requestId = ++lastRequest;

            dispatch( { type: 'POSTS_START', postType, requestId } );

            try {
                dispatch( { type: 'POSTS_DONE', postType, requestId, data: await api.getAccountPosts( postType, page ) } );
            } catch ( e ) {
                dispatch( { type: 'POSTS_FAIL', postType, requestId, error: e.message } );
            }
        };
    },

    /**
     * Delete a post, then reload the list page and the stats. Resolves with
     * true when deleted; a failure shows on the list.
     *
     * @param {string} postType Post type.
     * @param {number} postId   Post ID.
     * @param {number} page     Page to reload.
     *
     * @return {Function} Thunk.
     */
    deletePost( postType, postId, page ) {
        return async ( { dispatch } ) => {
            try {
                await api.deleteAccountPost( postId );
            } catch ( e ) {
                dispatch( { type: 'POSTS_ERROR', postType, error: e.message } );

                return false;
            }

            dispatch( actions.loadPosts( postType, page ) );
            dispatch( actions.refreshAccount() );

            return true;
        };
    },

    setPostsError( postType, error ) {
        return { type: 'POSTS_ERROR', postType, error };
    },
};

export const selectors = {
    getSearch: ( state ) => state.search,
    getSection: ( state, fallback ) => sectionOf( state.search, fallback ),
    getPage: ( state ) => pageOf( state.search ),
    getProfile: ( state ) => state.profile,
    getStats: ( state ) => state.stats,
    getPosts: ( state, postType ) => postsOf( state, postType ),
};

export const store = createReduxStore( STORE_NAME, { reducer, actions, selectors } );

register( store );
