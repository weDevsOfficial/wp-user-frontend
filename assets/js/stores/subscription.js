import {defineStore} from 'pinia';
import {ref} from 'vue';
import apiFetch from '@wordpress/api-fetch';

export const useSubscriptionStore = defineStore( 'subscription', {
    state: () => ( { currentSubscription: ref(null) } ),
    actions: {
        setCurrentSubscription( subscription ) {
            this.currentSubscription = subscription;
        },
        updateSubscription( subscription ) {
            const requestOptions = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpufSubscriptions.nonce,
                },
                body: JSON.stringify( {subscription} )
            };

            return fetch(
                '/wp-json/wpuf/v1/wpuf_subscription/' + subscription.id,
                requestOptions )
                .then( ( response ) => response.json() )
                .catch( ( error ) => {
                    console.log( error );
                } );
        },
        modifySubscription( key, value ) {
            this.currentSubscription[key] = value;
        },
        setMetaValue(key, value) {
            this.currentSubscription.meta_value[key] = value;
        }
    }
} );
