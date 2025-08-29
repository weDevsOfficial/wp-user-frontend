import {defineStore} from 'pinia';

export const useNoticeStore = defineStore( 'notice', {
    state: () => ( {
        display: false,
        notices: [],
    } ),
    actions: {
        addNotice( notice ) {
            this.notices.push( notice );
            this.display = true;
            // Auto-hide after 3 seconds
            setTimeout(() => {
                this.removeNotice(this.notices.indexOf(notice));
            }, 3000);
        },
        removeNotice( index ) {
            this.notices.splice( index, 1 );
            if (this.notices.length === 0) {
                this.display = false;
            }
        },
    },
} );
