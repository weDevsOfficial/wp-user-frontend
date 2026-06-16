// DESCRIPTION: Webpack config for Gutenberg blocks, extending @wordpress/scripts defaults.
// Coexists with Vite (used for Vue admin apps) — different entry points, different output.

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'subscription-packs': './src/js/blocks/subscription-packs/index.js',
    },
    output: {
        path: path.resolve( __dirname, 'assets/js' ),
        filename: '[name].js',
    },
};
