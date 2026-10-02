import { registerStore } from '@wordpress/data';
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import { STORE_NAME } from './constants';

const store = registerStore(STORE_NAME, {
    reducer,
    actions,
    selectors,
    resolvers,
});

export default store;
