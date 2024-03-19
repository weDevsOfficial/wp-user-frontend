import {defineStore} from 'pinia';
import {ref} from 'vue';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( { currentSubscription: ref(null) } ),
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },
        updateSubscription( subscription ) {
            const requestOptions = {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify( {subscription} )
            };

            return fetch(
                wpufSubscriptions.siteUrl + '/wp-json/wpuf/v1/wpuf_subscription/' + subscription.id,
                requestOptions )
                .then( ( response ) => response.json() );
        },
        modifySubscription( key, value ) {
            this.currentSubscription[key] = value;
        },
        setMetaValue(key, value) {
            this.currentSubscription.meta_value[key] = value;
        }
    }
} );
