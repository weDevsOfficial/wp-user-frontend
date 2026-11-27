import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    currentComponent: null,
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case ACTION_TYPES.SET_CURRENT_COMPONENT:
            return {
                ...state,
                currentComponent: action.component,
            };
        default:
            return state;
    }
}
