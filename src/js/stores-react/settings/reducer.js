import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    sections: [],
    fields: {},
    ia: [],
    values: {},
    savedValues: {},
    extra: {},
    savedExtra: {},
    proSections: [],
    caps: {},
    modules: {},
    activeTab: '',
    search: '',
    isLoading: true,
    isSaving: false,
    isDirty: false,
    error: null,
};

export default function reducer( state = DEFAULT_STATE, action ) {
    switch ( action.type ) {
        case ACTION_TYPES.SET_DATA:
            return {
                ...state,
                sections: action.data.sections || [],
                fields: action.data.fields || {},
                ia: action.data.ia || [],
                values: action.data.values || {},
                savedValues: action.data.values || {},
                extra: action.data.extra || {},
                savedExtra: action.data.extra || {},
                proSections: action.data.pro_sections || [],
                caps: action.data.caps || {},
                modules: action.data.modules || {},
            };
        case ACTION_TYPES.SET_IS_LOADING:
            return { ...state, isLoading: action.isLoading };
        case ACTION_TYPES.SET_IS_SAVING:
            return { ...state, isSaving: action.isSaving };
        case ACTION_TYPES.SET_IS_DIRTY:
            return { ...state, isDirty: action.isDirty };
        case ACTION_TYPES.SET_ACTIVE_TAB:
            return { ...state, activeTab: action.activeTab };
        case ACTION_TYPES.SET_SEARCH:
            return { ...state, search: action.search };
        case ACTION_TYPES.SET_VALUE:
            return {
                ...state,
                isDirty: true,
                values: {
                    ...state.values,
                    [ action.sectionId ]: {
                        ...( state.values[ action.sectionId ] || {} ),
                        [ action.name ]: action.value,
                    },
                },
            };
        case ACTION_TYPES.SET_VALUES:
            return { ...state, values: action.values, savedValues: action.values };
        case ACTION_TYPES.SET_EXTRA_SAVED:
            // Snapshot the current extra channel as the saved baseline (after a
            // successful save), so a later Discard doesn't roll back persisted
            // own-option settings (tax, role templates, profile-form roles).
            return { ...state, savedExtra: state.extra };
        case ACTION_TYPES.SET_EXTRA_VALUE:
            return {
                ...state,
                isDirty: true,
                extra: {
                    ...state.extra,
                    [ action.key ]: action.value,
                },
            };
        case ACTION_TYPES.DISCARD:
            return { ...state, values: state.savedValues, extra: state.savedExtra, isDirty: false };
        case ACTION_TYPES.SET_ERROR:
            return { ...state, error: action.error };
        default:
            return state;
    }
}
