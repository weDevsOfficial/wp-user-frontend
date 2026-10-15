/**
 * The admin apps' @wordpress/data stores (forms list, tools, transactions):
 * reducers, selectors and thunks, with `@wpuf/api` mocked per test.
 */
module.exports = {
    rootDir: __dirname,
    // Only these apps: form-builder/__mocks__ mocks @wordpress/data for its own suite.
    roots: [ '<rootDir>/forms-list', '<rootDir>/tools', '<rootDir>/transactions' ],
    testMatch: [ '**/store/**/*.test.js' ],
    testEnvironment: 'node',
    moduleNameMapper: {
        '^@wpuf/api$': '<rootDir>/__mocks__/wpuf-api.js',
    },
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
    transformIgnorePatterns: [ 'node_modules/(?!(@wordpress)/)' ],
};
