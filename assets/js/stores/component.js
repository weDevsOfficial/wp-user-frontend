import {defineStore} from 'pinia';

export const useComponentStore = defineStore( 'component', {
    state: () => ( { currentComponent: null } ),
    actions: {
        setCurrentComponent( component ) {
            this.currentComponent = component;
        },
    },
} )
