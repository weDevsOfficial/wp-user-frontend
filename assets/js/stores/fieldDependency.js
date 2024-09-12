import {defineStore} from 'pinia';

export const useFieldDependencyStore = defineStore( 'fieldDependency', {
    state: () => ( {
        modifierFields: {},
        hiddenFields: [],
        modifierFieldStatus: {},
    } ),
} );
