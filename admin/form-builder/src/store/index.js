import { createReduxStore, register } from '@wordpress/data';
import { STORE_NAME } from './constants';
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

const store = createReduxStore( STORE_NAME, {
    reducer,
    actions,
    selectors,
} );

register( store );

export { STORE_NAME };
export default store;
