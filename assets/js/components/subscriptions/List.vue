<script setup>
import SubscriptionBox from './SubscriptionBox.vue';
import Empty from './Empty.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import {storeToRefs} from 'pinia';
import Pagination from './Pagination.vue';
import {onBeforeMount, ref, watch} from 'vue';
import ListHeader from './ListHeader.vue';
import EmptyTrash from './EmptyTrash.vue';
import {HollowDotsSpinner} from 'epic-spinners';

const subscriptionStore = useSubscriptionStore();
const subscriptions = storeToRefs( subscriptionStore ).subscriptionList;
const count = ref( subscriptionStore.allCount.all );
const currentPage = ref( 1 );
const perPage = parseInt( wpufSubscriptions.perPage );
const totalPages = ref( Math.ceil( count.value / wpufSubscriptions.perPage ) );
const changePageTo = ( page ) => {
    const offset = ( page - 1 ) * parseInt( wpufSubscriptions.perPage );
    currentPage.value = page;

    subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus, offset );
};

onBeforeMount(
    () => {
        count.value = subscriptionStore.allCount[subscriptionStore.currentSubscriptionStatus];
        totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );
    }
);

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
                <SubscriptionBox
                    v-for="subscription in subscriptions"
                    :subscription=subscription
                    @toggle-subscription-status="toggleSubscriptionStatus"
                    :key="subscription.ID"/>
            </div>
        </div>
        <Pagination v-if="count > perPage" :currentPage="currentPage" @changePageTo="changePageTo"/>
    </div>
</template>
