/**
 * The REST client of the frontend apps (frontend-react-architecture.md 4.4,
 * 4.5): apiFetch for JSON, XMLHttpRequest for uploads (progress). Every
 * error becomes an Error with `code`, `status` and the server's `data`.
 *
 * @since WPUF_SINCE
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const BASE = '/wpuf/v1';

export const fail = ( error ) => {
    const message = error?.message || __( 'Something went wrong. Check your connection and try again.', 'wp-user-frontend' );

    throw Object.assign( new Error( message ), {
        code: error?.code || 'unknown',
        status: error?.data?.status,
        data: error?.data || {},
    } );
};

const query = ( params = {} ) => {
    const search = new URLSearchParams();

    Object.entries( params ).forEach( ( [ key, value ] ) => {
        if ( '' !== value && undefined !== value && null !== value ) {
            search.append( key, value );
        }
    } );

    const string = search.toString();

    return string ? '?' + string : '';
};

const get = ( path, params ) => apiFetch( { path: BASE + path + query( params ) } ).catch( fail );
const send = ( path, method, data ) => apiFetch( { path: BASE + path, method, data } ).catch( fail );

// Post form.
export const getSchema = ( formId, { postId = 0, pageId = 0 } = {} ) => get( `/forms/${ formId }/schema`, { post_id: postId || '', page_id: pageId || '' } );
export const submitForm = ( formId, data ) => send( `/forms/${ formId }/submissions`, 'POST', data );
export const updateForm = ( formId, postId, data ) => send( `/forms/${ formId }/submissions/${ postId }`, 'PUT', data );
export const saveDraft = ( formId, data ) => send( `/forms/${ formId }/drafts`, 'POST', data );
export const getTerms = ( formId, taxonomy, parent = 0 ) => get( `/forms/${ formId }/terms`, { taxonomy, parent } );
export const searchTerms = ( formId, taxonomy, search ) => get( `/forms/${ formId }/terms`, { taxonomy, search } );
export const getEmbed = ( formId, url ) => send( `/forms/${ formId }/embed`, 'POST', { url } );
export const getStates = ( formId, country ) => get( `/forms/${ formId }/states`, { country } );
// Pro registration / profile forms.
export const getRegistrationSchema = ( formId, pageId = 0 ) => get( `/registration/${ formId }/schema`, { page_id: pageId || '' } );
export const submitRegistration = ( formId, data ) => send( `/registration/${ formId }/submissions`, 'POST', data );
export const updateRegistration = ( formId, data ) => send( `/registration/${ formId }/profile`, 'PUT', data );
export const checkUsername = ( value ) => get( '/registration/username-check', { value } );

// The `[wpuf-login]` screens (LoginController): the form's fields as posted.
export const logIn = ( data ) => send( '/login', 'POST', data );
export const requestPasswordReset = ( data ) => send( '/login/lost-password', 'POST', data );
export const resetPassword = ( data ) => send( '/login/reset-password', 'POST', data );

// Checkout (the pay page): the billing address with the item's tax, the
// states of a country, and Pro's coupons (the page's `wpuf_nonce`).
export const saveBillingAddress = ( data ) => send( '/account/billing-address', 'PUT', data );
export const getBillingStates = ( country ) => get( '/account/billing-address/states', { country } );
export const applyCoupon = ( packId, coupon, nonce ) => send( '/coupons/apply', 'POST', { pack_id: packId, coupon, wpuf_nonce: nonce } );
export const cancelCoupon = ( packId, nonce ) => send( '/coupons/cancel', 'POST', { pack_id: packId, wpuf_nonce: nonce } );

export const deleteUpload = ( attachmentId, nonce = '' ) => send( `/uploads/${ attachmentId }` + query( { wpuf_nonce: nonce } ), 'DELETE' );

// Account.
export const getAccount = () => get( '/account' );
export const getAccountPosts = ( type, page = 1, perPage = 0 ) => apiFetch( { path: BASE + '/account/posts' + query( { type, page, per_page: perPage || '' } ), parse: false } )
    .then( async ( response ) => {
        const data = await response.json();

        if ( ! response.ok ) {
            fail( data );
        }

        return data;
    } )
    .catch( fail );
export const deleteAccountPost = ( postId ) => send( `/account/posts/${ postId }`, 'DELETE' );
export const getSubscription = () => get( '/account/subscription' );

// `[wpuf_dashboard]`: the shortcode attributes go back exactly as the page
// rendered them, with their signature (DashboardController).
export const getDashboardPosts = ( { atts, sig }, page = 1, { status = '', search = '', type = '', overview = '' } = {} ) => get( '/dashboard/posts', { atts, sig, page, status, search, type, overview } );
export const deleteDashboardPost = ( { atts, sig }, postId ) => send( `/dashboard/posts/${ postId }` + query( { atts, sig } ), 'DELETE' );
export const getSection = ( slug, pagenum = 1 ) => get( `/account/sections/${ encodeURIComponent( slug ) }`, { pagenum } );
export const updateProfile = ( data ) => send( '/account/profile', 'PUT', data );
export const updatePassword = ( data ) => send( '/account/password', 'PUT', data );
export const deleteAvatar = () => send( '/account/avatar', 'DELETE' );
export const deleteCover = () => send( '/account/cover', 'DELETE' );

/**
 * Upload one file with progress (apiFetch has no progress events).
 *
 * @param {string}   path       Route under wpuf/v1 ('/uploads' or '/account/avatar')
 * @param {File}     file       The file
 * @param {Object}   fields     Extra body fields (form_id, type, wpuf_nonce, image_only)
 * @param {Function} onProgress 0..1
 * @param {Object}   [options]  { signal }
 * @return {Promise<Object>} The route's answer
 */
export const uploadFile = ( path, file, fields = {}, onProgress = null, { signal } = {} ) => new Promise( ( resolve, reject ) => {
    const settings = window.wpApiSettings || {};
    const root = ( settings.root || '/wp-json/' ).replace( /\/$/, '' );
    // The REST nonce: wpApiSettings, else the one wp-api-fetch was set up with.
    const nonce = settings.nonce || apiFetch.nonceMiddleware?.nonce || '';
    const xhr = new window.XMLHttpRequest();
    const body = new window.FormData();

    body.append( 'wpuf_file', file, file.name );
    Object.entries( fields ).forEach( ( [ key, value ] ) => body.append( key, value ) );

    xhr.open( 'POST', root + BASE + path );
    xhr.withCredentials = true;

    if ( nonce ) {
        xhr.setRequestHeader( 'X-WP-Nonce', nonce );
    }

    if ( xhr.upload && onProgress ) {
        xhr.upload.addEventListener( 'progress', ( event ) => {
            if ( event.lengthComputable ) {
                onProgress( event.loaded / event.total );
            }
        } );
    }

    xhr.addEventListener( 'load', () => {
        let data = {};

        try {
            data = JSON.parse( xhr.responseText || '{}' );
        } catch ( e ) {
            data = {};
        }

        if ( xhr.status >= 200 && xhr.status < 300 ) {
            resolve( data );
        } else {
            try {
                fail( data );
            } catch ( error ) {
                reject( error );
            }
        }
    } );
    xhr.addEventListener( 'error', () => reject( Object.assign( new Error( __( 'The upload failed. Check your connection and try again.', 'wp-user-frontend' ) ), { code: 'network' } ) ) );
    xhr.addEventListener( 'abort', () => reject( Object.assign( new Error( __( 'Upload cancelled.', 'wp-user-frontend' ) ), { code: 'aborted' } ) ) );

    if ( signal ) {
        signal.addEventListener( 'abort', () => xhr.abort() );
    }

    xhr.send( body );
} );
