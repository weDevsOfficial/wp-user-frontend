<script setup>
import SubscriptionBox from './SubscriptionBox.vue';
import Empty from './Empty.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import {storeToRefs} from 'pinia';
import Pagination from './Pagination.vue';
import {onMounted, ref, computed, watch} from 'vue';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import {useComponentStore} from '../../stores/component';
import ListHeader from './ListHeader.vue';
import EmptyTrash from './EmptyTrash.vue';
import {HollowDotsSpinner} from 'epic-spinners';

const subscriptionStore = useSubscriptionStore();
const componentStore = useComponentStore();
const subscriptions = storeToRefs( subscriptionStore ).subscriptionList;
const count = ref( subscriptionStore.allCount.all );
const perPage = parseInt( wpufSubscriptions.perPage );
const totalPages = ref( Math.ceil( count.value / wpufSubscriptions.perPage ) );
const changePageTo = ( page ) => {
    const offset = ( page - 1 ) * parseInt( wpufSubscriptions.perPage );

    subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus, offset );
};

watch( () => subscriptionStore.currentSubscriptionStatus, ( newValue ) => {
    count.value = subscriptionStore.allCount[newValue];
    totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );
} );

onMounted( () => {
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
            totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );
        }
    } )
    .catch( ( error ) => {
        console.log( error );
    } );
} );

const {currentComponent} = storeToRefs( componentStore );

</script>

<template>
    <div v-if="subscriptionStore.isSubscriptionLoading"
         class="wpuf-flex wpuf-h-svh wpuf-items-center wpuf-justify-center">
        <hollow-dots-spinner
            :animation-duration="1000"
            :dot-size="20"
            :dots-num="3"
            :color="'#7DC442'"
        />
    </div>
    <div v-if="!subscriptionStore.isSubscriptionLoading">
        <div v-if="!count" class="wpuf-px-8 wpuf-pb-8">
            <ListHeader/>
            <Empty v-if="subscriptionStore.currentSubscriptionStatus !== 'trash'"/>
            <EmptyTrash v-if="subscriptionStore.currentSubscriptionStatus === 'trash'"/>
        </div>
        <div v-else class="wpuf-px-8 wpuf-pb-8">
            <ListHeader/>
            <div class="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-4">
                <SubscriptionBox v-for="subscription in subscriptions" :subscription=subscription
                                 :key="subscription.ID"/>
            </div>
        </div>
        <Pagination v-if="count > perPage" @changePageTo="changePageTo"/>
    </div>
</template>
