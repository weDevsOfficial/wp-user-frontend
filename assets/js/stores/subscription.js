import {defineStore} from 'pinia';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( { currentSubscription: null } ),
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },
    }
} );
