export function getSections( state ) {
    return state.sections;
}

export function getFields( state ) {
    return state.fields;
}

export function getIa( state ) {
    return state.ia;
}

export function getValues( state ) {
    return state.values;
}

export function getSavedValues( state ) {
    return state.savedValues;
}

export function getSectionValues( state, sectionId ) {
    return state.values[ sectionId ] || {};
}

export function getValue( state, sectionId, name ) {
    const section = state.values[ sectionId ] || {};
    return section[ name ];
}

export function getCaps( state ) {
    return state.caps;
}

export function getModules( state ) {
    return state.modules;
}

export function getProSections( state ) {
    return state.proSections;
}

export function getExtra( state ) {
    return state.extra || {};
}

export function getExtraValue( state, key ) {
    return ( state.extra || {} )[ key ];
}

export function getActiveTab( state ) {
    return state.activeTab;
}

export function getSearch( state ) {
    return state.search;
}

export function isLoading( state ) {
    return state.isLoading;
}

export function isSaving( state ) {
    return state.isSaving;
}

export function isDirty( state ) {
    return state.isDirty;
}

export function getError( state ) {
    return state.error;
}
