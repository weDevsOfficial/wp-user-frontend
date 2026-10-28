jest.mock( '@wordpress/api-fetch', () => jest.fn() );

import apiFetch from '@wordpress/api-fetch';

import { MAX_RETRIES, REQUEST_TIMEOUT_MS, normalizeError, request, restPath } from './request';

beforeEach( () => {
    apiFetch.mockReset();
} );

/**
 * A fetch Response double.
 *
 * @param {number} status Status.
 * @param {string} text   Body.
 *
 * @return {Object} Response-like.
 */
const response = ( status, text ) => ( { status, ok: status >= 200 && status < 300, statusText: 'Status ' + status, text: async () => text } );

test( 'restPath joins namespace, route and query', () => {
    expect( restPath( 'wpuf/v1', '/forms' ) ).toBe( '/wpuf/v1/forms' );
    expect( restPath( '/wpuf/v1/', 'forms', { page: 2 } ) ).toBe( '/wpuf/v1/forms?page=2' );
} );

test( 'normalizeError gives one shape', () => {
    expect( normalizeError( { code: 'rest_forbidden', message: 'No', data: { status: 403 } } ) ).toEqual( { code: 'rest_forbidden', message: 'No', status: 403, data: { status: 403 } } );
    expect( normalizeError( new TypeError( 'Failed to fetch' ) ) ).toMatchObject( { code: 'network', status: 0, message: 'Failed to fetch' } );
    const abort = new Error( 'aborted' );
    abort.name = 'AbortError';
    expect( normalizeError( abort ) ).toMatchObject( { code: 'timeout', status: 0 } );
    expect( normalizeError( abort ).message ).toBe( 'The server took too long to answer. Reload the page to try again.' );
} );

test( 'GET is retried on a 5xx, then resolves', async () => {
    jest.useFakeTimers();
    apiFetch
        .mockRejectedValueOnce( response( 500, 'x' ) )
        .mockResolvedValueOnce( response( 200, '{"ok":true}' ) );

    const done = request( '/wpuf/v1/forms', { query: { page: 1 } } );
    await jest.advanceTimersByTimeAsync( 1000 );

    await expect( done ).resolves.toEqual( { ok: true } );
    expect( apiFetch ).toHaveBeenCalledTimes( 2 );
    expect( apiFetch.mock.calls[ 0 ][ 0 ] ).toMatchObject( { method: 'GET', path: '/wpuf/v1/forms?page=1', parse: false } );
    jest.useRealTimers();
} );

test( 'GET gives up after MAX_RETRIES and rejects normalized', async () => {
    jest.useFakeTimers();
    apiFetch.mockRejectedValue( { code: 'http_503', message: 'down', data: { status: 503 } } );

    const done = request( '/wpuf/v1/forms' );
    const assertion = expect( done ).rejects.toMatchObject( { code: 'http_503', status: 503 } );
    await jest.advanceTimersByTimeAsync( 5000 );
    await assertion;
    expect( apiFetch ).toHaveBeenCalledTimes( MAX_RETRIES + 1 );
    jest.useRealTimers();
} );

test( 'a POST is never retried', async () => {
    apiFetch.mockRejectedValue( { code: 'http_500', message: 'x', data: { status: 500 } } );

    await expect( request( '/wpuf/v1/forms', { method: 'POST', data: { a: 1 } } ) ).rejects.toMatchObject( { status: 500 } );
    expect( apiFetch ).toHaveBeenCalledTimes( 1 );
    expect( apiFetch.mock.calls[ 0 ][ 0 ] ).toMatchObject( { method: 'POST', data: { a: 1 } } );
} );

test( 'a request that hangs is aborted after the timeout', async () => {
    jest.useFakeTimers();
    apiFetch.mockImplementation( ( { signal } ) => new Promise( ( resolve, reject ) => {
        signal.addEventListener( 'abort', () => {
            const error = new Error( 'aborted' );
            error.name = 'AbortError';
            reject( error );
        } );
    } ) );

    const done = request( '/wpuf/v1/slow', { method: 'POST' } );
    const assertion = expect( done ).rejects.toMatchObject( { code: 'timeout' } );
    await jest.advanceTimersByTimeAsync( REQUEST_TIMEOUT_MS + 1 );
    await assertion;
    jest.useRealTimers();
} );

test( 'the body is parsed even with stray output in front of the JSON', async () => {
    apiFetch.mockResolvedValue( response( 200, '\nDeprecated: other plugin in /x.php on line 5\n{"success":true,"forms":[1]}' ) );

    await expect( request( '/wpuf/v1/forms' ) ).resolves.toEqual( { success: true, forms: [ 1 ] } );
} );

test( 'a 204 or empty body resolves to null, a body without JSON rejects as invalid_json', async () => {
    apiFetch.mockResolvedValueOnce( response( 204, '' ) );
    await expect( request( '/wpuf/v1/forms/1', { method: 'DELETE' } ) ).resolves.toBeNull();

    apiFetch.mockResolvedValueOnce( response( 200, '<html>not json</html>' ) );
    await expect( request( '/wpuf/v1/forms' ) ).rejects.toMatchObject( { code: 'invalid_json', status: 200 } );
} );

test( 'a WP REST error body is rejected as is, other failures as http_<status>', async () => {
    apiFetch.mockRejectedValueOnce( response( 403, 'Notice: x\n{"code":"rest_forbidden","message":"No","data":{"status":403}}' ) );
    await expect( request( '/wpuf/v1/forms', { method: 'POST' } ) ).rejects.toEqual( { code: 'rest_forbidden', message: 'No', status: 403, data: { status: 403 } } );

    apiFetch.mockRejectedValueOnce( response( 502, '<html>Bad gateway</html>' ) );
    await expect( request( '/wpuf/v1/forms', { method: 'POST' } ) ).rejects.toMatchObject( { code: 'http_502', status: 502, message: 'Status 502' } );
} );

test( 'parse: false hands back the raw response', async () => {
    const raw = response( 200, '{"a":1}' );
    apiFetch.mockResolvedValue( raw );

    await expect( request( '/wpuf/v1/forms', { parse: false } ) ).resolves.toBe( raw );
    expect( apiFetch.mock.calls[ 0 ][ 0 ] ).toMatchObject( { parse: false } );
} );
