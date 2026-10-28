// `@wpuf/frontend-kit` in the store tests: only the REST calls, as jest mocks.
module.exports = {
    api: {
        getAccount: jest.fn(),
        getAccountPosts: jest.fn(),
        deleteAccountPost: jest.fn(),
        getDashboardPosts: jest.fn(),
        deleteDashboardPost: jest.fn(),
    },
};
