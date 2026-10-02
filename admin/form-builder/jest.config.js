module.exports = {
    rootDir: __dirname,
    testMatch: [ '<rootDir>/src/**/*.test.js' ],
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
        '^@wordpress/data$': '<rootDir>/src/__mocks__/@wordpress/data.js',
        '^@wordpress/hooks$': '<rootDir>/src/__mocks__/@wordpress/hooks.js',
    },
};
