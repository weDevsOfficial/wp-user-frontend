// DESCRIPTION: Webpack config for Gutenberg blocks, extending @wordpress/scripts defaults.
// Uses @wordpress/scripts build pipeline for block.json, asset manifests, and editor assets.

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'post-form': './src/js/blocks/post-form/index.js',
    },
    output: {
        path: path.resolve( __dirname, 'assets/js/blocks' ),
        filename: '[name].js',
    },
};
