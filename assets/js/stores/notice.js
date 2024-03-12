import {defineStore} from 'pinia';

export const useNoticeStore = defineStore( 'notice', {
    state: () => ( {
        display: false,
        type: '',
        message: '',
    } )
} );
