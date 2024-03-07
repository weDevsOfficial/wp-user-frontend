import {defineStore} from 'pinia';
import {ref} from 'vue';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( { currentSubscription: ref(null) } ),
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },

        updateSubscription( subscription ) {
            console.log(subscription);
        }
    }
} );
