/**
 * One wp-scripts config for the React admin apps (builder, forms list,
 * subscriptions, settings, AI form builder).
 *
 * Sources live in src/admin/apps/<app> (task 3.5). Output: assets/js/screens/<name>.js + .asset.php
 * (minified, but not named .min.js: translate.wordpress.org and `wp i18n make-pot` skip
 * *.min.js, so the strings would never be extracted and the JSON translations, keyed
 * by the enqueued file's path, would not match) and assets/css/<name>.css + -rtl.css.
 * Plus the shared layer entries admin-runtime and admin-ui (design.md D24).
 * Build one app with `WPUF_ENTRY=<name>`, e.g. `WPUF_ENTRY=form-builder`.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const path = require( 'path' );
const wpufExternals = require( './webpack.wpuf-externals' );

const entries = {
    'form-builder': './src/admin/apps/form-builder/index.jsx',
    'forms-list-screen': './src/admin/apps/forms-list/index.jsx',
    subscriptions: './src/admin/apps/subscriptions/index.jsx',
    'settings-screen': './src/admin/apps/settings/index.jsx',
    'ai-form-builder': './src/admin/apps/ai-form-builder/index.jsx',
    // Shell of the single React admin app (task 5d).
    'admin-app': './src/admin/app/index.js',
    'registration-promo': './src/admin/apps/registration-promo/index.jsx',
    onboarding: './src/admin/apps/onboarding/index.jsx',
    welcome: './src/admin/apps/welcome/index.jsx',
    help: './src/admin/apps/help/index.jsx',
    tools: './src/admin/apps/tools/index.jsx',
    transactions: './src/admin/apps/transactions/index.jsx',
    'coupons-promo': './src/admin/apps/coupons-promo/index.jsx',
};

// The shared layer (design.md D24): bundles @wpuf/* sources and plugin-ui,
// published on window.wpuf. Built without the externals map below.
const sharedEntries = {
    'admin-runtime': './src/admin/shared/runtime/admin-runtime.js',
    'admin-ui': './src/admin/shared/runtime/admin-ui.js',
};

const only = process.env.WPUF_ENTRY;

if ( only && ! entries[ only ] && ! sharedEntries[ only ] ) {
    throw new Error( `WPUF_ENTRY "${ only }" is not one of: ${ [ ...Object.keys( entries ), ...Object.keys( sharedEntries ) ].join( ', ' ) }` );
}

// Keep wp-scripts' own mini-css-extract-plugin instance (its CSS loader only
// accepts that copy). Its RTL plugin writes [name]-rtl.css to the output root,
// so the standalone one keeps the css/ path. The block-only plugins (block.json
// copy, render PHP paths) and the cleaner do not belong to these apps.
const plugins = defaultConfig.plugins.filter(
    ( plugin ) =>
        ! [ 'CleanWebpackPlugin', 'RtlCssPlugin', 'CopyPlugin', 'PhpFilePathsPlugin' ].includes( plugin.constructor.name )
);
const cssExtract = plugins.find( ( plugin ) => 'MiniCssExtractPlugin' === plugin.constructor.name );

if ( cssExtract ) {
    cssExtract.options.filename = 'css/[name].css';
}

plugins.push( new RtlCssPlugin( { filename: 'css/[name]-rtl.css' } ) );

/**
 * One config per group; the screen group resolves `@wpuf/*` and
 * `@wedevs/plugin-ui` to window.wpuf (their handles join each `.asset.php`).
 *
 * @param {Object}  group           Entry name => path.
 * @param {boolean} withWpufExternals Map the shared layer to window.wpuf.
 *
 * @return {Object} webpack config
 */
const config = ( group, withWpufExternals ) => ( {
    ...defaultConfig,
    entry: group,
    output: {
        filename: 'js/screens/[name].js',
        // Lazy chunks (the rich text editor), next to the bundle that loads them.
        chunkFilename: 'js/screens/[name].[contenthash:8].chunk.js',
        publicPath: 'auto',
        // Each group has its own webpack runtime on the page: separate chunk registries.
        uniqueName: withWpufExternals ? 'wpuf-admin-screens' : 'wpuf-admin-shared',
        path: path.resolve( __dirname, 'assets' ),
        // assets/ holds every other build's output and tracked sources: never clean it.
        clean: false,
    },
    plugins: withWpufExternals
        ? plugins.map( ( plugin ) =>
            'DependencyExtractionWebpackPlugin' === plugin.constructor.name
                ? new DependencyExtractionWebpackPlugin( {
                    ...plugin.options,
                    requestToExternal: wpufExternals.requestToExternal,
                    requestToHandle: wpufExternals.requestToHandle,
                } )
                : plugin
        )
        : // The shared group has no CSS: only its own dependency extraction.
        plugins
            .filter( ( plugin ) => 'DependencyExtractionWebpackPlugin' === plugin.constructor.name )
            .map( ( plugin ) => new DependencyExtractionWebpackPlugin( plugin.options ) ),
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            'postcss-config$': path.resolve( __dirname, 'postcss.config.admin.js' ),
        },
    },
    watchOptions: {
        ignored: [ '**/assets/js/**', '**/assets/css/**', '**/node_modules/**' ],
    },
} );

const pick = ( group ) => ( only ? ( group[ only ] ? { [ only ]: group[ only ] } : null ) : group );

module.exports = [
    pick( entries ) && config( pick( entries ), true ),
    pick( sharedEntries ) && config( pick( sharedEntries ), false ),
].filter( Boolean );
