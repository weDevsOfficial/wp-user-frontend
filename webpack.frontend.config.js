/**
 * One wp-scripts config for the frontend React apps (frontend-react-architecture.md 7).
 *
 * Entries: `runtime` (the kit, published on window.wpuf.frontend together with
 * lucide-react), `forms` (post form, edit form) and `account-page` (the account
 * shortcode), `login` (the `[wpuf-login]` form). Output: assets/js/frontend/<name>.js + .asset.php (not .min.js:
 * `wp i18n make-pot` skips *.min.js), assets/css/frontend/<name>.css + -rtl.css
 * from the CSS each entry imports. Build one with `WPUF_ENTRY=<name>`.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const path = require( 'path' );
const wpufExternals = require( './webpack.wpuf-externals' );

const apps = {
    forms: './src/frontend/forms/index.jsx',
    'account-page': './src/frontend/account/index.jsx',
    login: './src/frontend/login/index.jsx',
    plans: './src/frontend/plans/index.jsx',
};

// The kit: bundles @wpuf/frontend-kit sources and lucide-react, no frontend externals.
const sharedEntries = {
    runtime: './src/frontend/kit/runtime.js',
};

const only = process.env.WPUF_ENTRY;

if ( only && ! apps[ only ] && ! sharedEntries[ only ] ) {
    throw new Error( `WPUF_ENTRY "${ only }" is not one of: ${ [ ...Object.keys( apps ), ...Object.keys( sharedEntries ) ].join( ', ' ) }` );
}

const plugins = defaultConfig.plugins.filter(
    ( plugin ) =>
        ! [ 'CleanWebpackPlugin', 'RtlCssPlugin', 'CopyPlugin', 'PhpFilePathsPlugin' ].includes( plugin.constructor.name )
);
const cssExtract = plugins.find( ( plugin ) => 'MiniCssExtractPlugin' === plugin.constructor.name );

if ( cssExtract ) {
    cssExtract.options.filename = 'css/frontend/[name].css';
}

plugins.push( new RtlCssPlugin( { filename: 'css/frontend/[name]-rtl.css' } ) );

const config = ( group, withFrontendExternals ) => ( {
    ...defaultConfig,
    entry: group,
    output: {
        filename: 'js/frontend/[name].js',
        chunkFilename: 'js/frontend/[name].[contenthash:8].chunk.js',
        path: path.resolve( __dirname, 'assets' ),
        publicPath: 'auto',
        uniqueName: 'wpuf-frontend',
        clean: false,
    },
    plugins: plugins.map( ( plugin ) =>
        'DependencyExtractionWebpackPlugin' === plugin.constructor.name
            ? new DependencyExtractionWebpackPlugin( {
                ...plugin.options,
                requestToExternal: withFrontendExternals ? wpufExternals.frontendRequestToExternal : undefined,
                requestToHandle: withFrontendExternals ? wpufExternals.frontendRequestToHandle : undefined,
            } )
            : plugin
    ),
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            '@wpuf/frontend-kit': path.resolve( __dirname, 'src/frontend/kit/index.js' ),
            'postcss-config$': path.resolve( __dirname, 'postcss.config.frontend.js' ),
        },
    },
    watchOptions: {
        ignored: [ '**/assets/js/**', '**/assets/css/**', '**/node_modules/**' ],
    },
} );

const pick = ( group ) => ( only ? ( group[ only ] ? { [ only ]: group[ only ] } : null ) : group );

module.exports = [
    pick( apps ) && config( pick( apps ), true ),
    pick( sharedEntries ) && config( pick( sharedEntries ), false ),
].filter( Boolean );
