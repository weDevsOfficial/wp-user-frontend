/**
 * The frontend apps' @wordpress/data stores (`wpuf/frontend-form`,
 * `wpuf/account`): reducers, selectors and thunks, with the kit's REST
 * calls mocked per test.
 */
module.exports = {
    rootDir: __dirname,
    testMatch: [ '**/*.test.js' ],
    testEnvironment: 'node',
    moduleNameMapper: {
        '^@wpuf/frontend-kit$': '<rootDir>/__mocks__/frontend-kit.js',
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
