module.exports = {
    rootDir: __dirname,
    testMatch: [ '<rootDir>/**/*.test.js' ],
    testEnvironment: 'node',
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
