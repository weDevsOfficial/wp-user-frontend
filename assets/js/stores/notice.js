import {defineStore} from 'pinia';

export const useNoticeStore = defineStore( 'notice', {
    state: () => ( {
        display: false,
        notices: [],
    } ),
    actions: {
        addNotice( notice ) {
            this.notices.push( notice );
        },
        removeNotice( index ) {
            this.notices.splice( index, 1 );
        },
    },
} );
