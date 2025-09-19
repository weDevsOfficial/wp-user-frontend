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
                const index = this.notices.indexOf(notice);
                if (index !== -1) {
                    this.removeNotice(index);
                }
            }, 3000);
        },
        removeNotice( index ) {
            // Validate index is a valid integer within bounds
            if (!Number.isInteger(index) || index < 0 || index >= this.notices.length) {
                return;
            }

            this.notices.splice( index, 1 );
            if (this.notices.length === 0) {
                this.display = false;
            }
        },
    },
} );
