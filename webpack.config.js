// DESCRIPTION: Webpack config for Gutenberg blocks, extending @wordpress/scripts defaults.
// Uses @wordpress/scripts build pipeline for block.json, asset manifests, and editor assets.

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

// The entry key carries the js/blocks/ prefix and the output root is `assets`, so
// the bundle still lands at assets/js/blocks/post-form.js while block.json — which
// wp-scripts copies using its path relative to the source root — lands alongside it
// at assets/js/blocks/post-form/block.json. Setting output.path to assets/js/blocks
// instead duplicated that prefix (assets/js/blocks/js/blocks/post-form/block.json).
module.exports = {
    ...defaultConfig,
    entry: {
        'js/blocks/post-form': './src/js/blocks/post-form/index.js',
    },
    output: {
        path: path.resolve( __dirname, 'assets' ),
        filename: '[name].js',
    },
};
