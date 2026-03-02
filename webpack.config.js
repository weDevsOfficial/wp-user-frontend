const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RtlCssPlugin = require('rtlcss-webpack-plugin');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'subscriptions': path.resolve(process.cwd(), 'src/js/subscriptions.jsx'),
    },
    output: {
        filename: 'js/[name].min.js',
        path: path.resolve(process.cwd(), 'assets'),
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            (plugin) =>
                plugin.constructor.name !== 'MiniCssExtractPlugin' &&
                plugin.constructor.name !== 'RtlCssPlugin' &&
                plugin.constructor.name !== 'CleanWebpackPlugin'
        ),
        new MiniCssExtractPlugin({
            filename: 'css/[name].css',
        }),
        new RtlCssPlugin({
            filename: 'css/[name]-rtl.css',
        }),
    ],
    watchOptions: {
        ignored: ['**/assets/js/**', '**/assets/css/**', '**/node_modules/**'],
    },
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            'postcss-config$': path.resolve(__dirname, 'postcss.config.react.js'),
        },
    },
};
