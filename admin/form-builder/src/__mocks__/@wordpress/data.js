module.exports = {
    createReduxStore: jest.fn( () => ( {} ) ),
    register: jest.fn(),
    dispatch: jest.fn( () => new Proxy( {}, { get: () => jest.fn() } ) ),
    useSelect: jest.fn(),
    useDispatch: jest.fn( () => new Proxy( {}, { get: () => jest.fn() } ) ),
    select: jest.fn(),
};
