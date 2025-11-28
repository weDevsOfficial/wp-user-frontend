/**
 * Webpack Configuration for User Directory Free
 *
 * Based on Pro's user-directory module build setup
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

module.exports = {
    entry: {
        'wpuf-user-directory-free': './src/js/user-directory/index.js',
    },
    output: {
        path: path.resolve(__dirname, 'assets/js'),
        filename: '[name].js',
        publicPath: '',
        clean: false, // Don't clean other files
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            '@babel/preset-env',
                            ['@babel/preset-react', { runtime: 'automatic' }],
                        ],
                    },
                },
            },
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: { importLoaders: 1 }
                    },
                    'postcss-loader'
                ]
            }
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    externals: {
        '@wordpress/element': ['wp', 'element'],
        '@wordpress/i18n': ['wp', 'i18n'],
        '@wordpress/components': ['wp', 'components'],
        '@wordpress/api-fetch': ['wp', 'apiFetch'],
        'react': 'React',
        'react-dom': 'ReactDOM',
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/[name].css',
        }),
        new DependencyExtractionWebpackPlugin(),
    ],
    devtool: 'source-map',
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
};
