/**
 * Webpack config for WPUF React admin apps.
 *
 * Extends @wordpress/scripts default config, which transforms JSX, externalizes
 * every `@wordpress/*` import to the `window.wp.*` globals WordPress already
 * ships, and emits a `[name].min.asset.php` with the exact script dependencies
 * + version for each entry.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'settings-react': path.resolve( process.cwd(), 'src/js/settings.jsx' ),
    },
    output: {
        ...defaultConfig.output,
        filename: 'js/[name].min.js',
        path: path.resolve( process.cwd(), 'assets' ),
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            ( plugin ) => plugin.constructor.name !== 'CleanWebpackPlugin'
        ),
    ],
    watchOptions: {
        ignored: [ '**/assets/js/**', '**/assets/css/**', '**/node_modules/**' ],
    },
};
