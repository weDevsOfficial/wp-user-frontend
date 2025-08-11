import {defineStore} from 'pinia';
import {ref} from 'vue';

export const useQuickEditStore = defineStore( 'quickEdit', {
    state: () => ( {isQuickEdit: ref( false )} ),
    actions: {
        setQuickEditStatus( status ) {
            this.isQuickEdit = status;
        },
    },
} );
