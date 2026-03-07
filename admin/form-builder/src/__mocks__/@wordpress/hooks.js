module.exports = {
    applyFilters: jest.fn( ( hookName, value ) => value ),
    doAction: jest.fn(),
    addFilter: jest.fn(),
    addAction: jest.fn(),
};
