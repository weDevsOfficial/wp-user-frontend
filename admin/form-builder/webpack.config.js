const path = require( 'path' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

module.exports = ( env, argv ) => {
    const isProduction = argv.mode === 'production';

    return {
        context: __dirname,
        entry: {
            'form-builder': './src/index.jsx',
        },
        output: {
            path: path.resolve( __dirname, '../../assets/js' ),
            filename: '[name].min.js',
            clean: false,
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
                                [
                                    '@babel/preset-react',
                                    { runtime: 'automatic' },
                                ],
                            ],
                        },
                    },
                },
            ],
        },
        resolve: {
            extensions: [ '.js', '.jsx' ],
        },
        plugins: [
            new DependencyExtractionWebpackPlugin(),
        ],
        devtool: isProduction ? false : 'source-map',
    };
};
