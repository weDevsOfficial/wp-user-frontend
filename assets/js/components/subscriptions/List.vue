<script setup>
import {__} from '@wordpress/i18n';
import SubscriptionBox from './SubscriptionBox.vue';
import Empty from './Empty.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import {storeToRefs} from 'pinia';
import Pagination from './Pagination.vue';
import {onMounted, ref, computed, watch} from 'vue';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

const subscriptionStore = useSubscriptionStore();
const subscriptions = storeToRefs(subscriptionStore).subscriptionList;
const count = ref( 0 );
const totalPages = ref( 0 );
const changePageTo = ( page ) => {
    const offset = ( page - 1 ) * parseInt( wpufSubscriptions.perPage );

    subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus, offset );
};

watch( () => subscriptionStore.currentSubscriptionStatus, ( newValue ) => {
    apiFetch( {
        path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription/count/' + newValue ),
        method: 'GET',
        headers: {
            'X-WP-Nonce': wpufSubscriptions.nonce,
        },
    } )
    .then( ( response ) => {
        if (response.success) {
            count.value = parseInt( response.count );
            totalPages.value = Math.ceil(count.value / 10);
        }
    } )
    .catch( ( error ) => {
        console.log( error );
    } );
});

onMounted(() => {
    apiFetch( {
        path: addQueryArgs( '/wp-json/wpuf/v1/wpuf_subscription/count/' + subscriptionStore.currentSubscriptionStatus ),
        method: 'GET',
        headers: {
            'X-WP-Nonce': wpufSubscriptions.nonce,
        },
    } )
    .then( ( response ) => {
        if (response.success) {
            count.value = parseInt( response.count );
            totalPages.value = Math.ceil(count.value / 10);
        }
    } )
    .catch( ( error ) => {
        console.log( error );
    } );
});

</script>

<template>
    <div v-if="!count">
        <Empty />
    </div>
    <div v-else class="wpuf-all-subscriptions-list wpuf-px-12 wpuf-pb-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Subscriptions', 'wp-user-frontend' ) }}</h3>
        <p class="wpuf-text-sm wpuf-text-gray-500">{{ __( 'Explore and manage all subscriptions in one place', 'wp-user-frontend' ) }}</p>
        <div class="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-12">
            <SubscriptionBox v-for="subscription in subscriptions" :subscription=subscription :key="subscription.ID" />
        </div>
    </div>
    <Pagination v-if="count > 10" @changePageTo="changePageTo" :count="count" :totalPages="totalPages" />
</template>
