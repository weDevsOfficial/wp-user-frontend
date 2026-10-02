import { ACTION_TYPES } from './constants';

export function setCurrentComponent(component) {
    return {
        type: ACTION_TYPES.SET_CURRENT_COMPONENT,
        component,
    };
}
