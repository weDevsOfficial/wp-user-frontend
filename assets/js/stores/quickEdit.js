import {defineStore} from 'pinia';

export const useQuickEditStore = defineStore( 'quickEdit', {
    state: () => ( { isQuickEdit: false } ),
    actions: {
        setQuickEditStatus( status ) {
            this.isQuickEdit = status;
        },
    },
} );
