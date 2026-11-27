// `@wpuf/api` in the store tests: `request` is a jest mock each test answers.
module.exports = {
    request: jest.fn(),
    restPath: ( namespace, path, query ) => {
        const base = '/' + namespace + '/' + String( path ).replace( /^\/+/, '' );
        const pairs = Object.keys( query || {} )
            .filter( ( key ) => undefined !== query[ key ] )
            .map( ( key ) => key + '=' + query[ key ] );

        return pairs.length ? base + '?' + pairs.join( '&' ) : base;
    },
};
