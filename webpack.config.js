const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'subscriptions-react': path.resolve(process.cwd(), 'src/js/subscriptions-react.jsx'),
    },
    output: {
        filename: 'js/[name].min.js',
        path: path.resolve(process.cwd(), 'assets/react-build'),
    },
    watchOptions: {
        ignored: ['**/assets/react-build/**', '**/node_modules/**'],
    },
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            'postcss-config$': path.resolve(__dirname, 'postcss.config.react.js'),
        },
    },
};
