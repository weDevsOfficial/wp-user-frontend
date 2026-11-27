module.exports = {
    rootDir: __dirname,
    testMatch: [ '<rootDir>/**/*.test.js' ],
    transform: {
        '^.+\\.jsx?$': [
            'babel-jest',
            {
                presets: [
                    [ '@babel/preset-env', { targets: { node: 'current' } } ],
                    [ '@babel/preset-react', { runtime: 'automatic' } ],
                ],
            },
        ],
    },
    transformIgnorePatterns: [
        'node_modules/(?!(@wordpress)/)',
    ],
    moduleNameMapper: {
        '^@wordpress/data$': '<rootDir>/__mocks__/@wordpress/data.js',
        '^@wordpress/hooks$': '<rootDir>/__mocks__/@wordpress/hooks.js',
    },
};
