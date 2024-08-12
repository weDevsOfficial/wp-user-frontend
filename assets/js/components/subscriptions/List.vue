<script setup>
import SubscriptionBox from './SubscriptionBox.vue';
import Empty from './Empty.vue';
import {useSubscriptionStore} from '../../stores/subscription';
import {storeToRefs} from 'pinia';
import Pagination from './Pagination.vue';
import {onBeforeMount, ref, watch} from 'vue';
import ListHeader from './ListHeader.vue';
import {HollowDotsSpinner} from 'epic-spinners';
import {__} from '@wordpress/i18n';

const subscriptionStore = useSubscriptionStore();
const subscriptions = storeToRefs( subscriptionStore ).subscriptionList;
const count = ref( subscriptionStore.allCount.all );
const currentPage = storeToRefs( subscriptionStore ).currentPageNumber;
const perPage = parseInt( wpufSubscriptions.perPage );
const totalPages = ref( Math.ceil( count.value / wpufSubscriptions.perPage ) );
const maxVisibleButtons = ref( 3 );
const paginationKey = ref( 0 );
const changePageTo = ( page ) => {
    const offset = ( page - 1 ) * parseInt( wpufSubscriptions.perPage );
    subscriptionStore.setSubscriptionsByStatus( subscriptionStore.currentSubscriptionStatus, offset );

    currentPage.value = page;

    // refresh the pagination component
    paginationKey.value += 1;
};

const emptyMessages = {
    all: __( 'Powerful Subscription Features for Monetizing Your Content. Unlock a World of Possibilities with WPUF\'s Subscription Features – From Charging Users for Posting to Exclusive Content Access.',
        'wp-user-frontend' ),
    publish: __( 'Ops! It looks like you haven\'t published any subscriptions yet. To create a new subscription and start monetizing your content, click the \'Add Subscription\' button above.', 'wp-user-frontend' ),
    draft: __( 'Ops! It looks like you haven\'t saved any subscriptions as drafts yet.', 'wp-user-frontend' ),
    trash: __( 'Your trash is empty! If you delete a subscription, it will be moved here.', 'wp-user-frontend' ),
};

const headerMessage = {
    all: __( 'Manage and monitor all your subscriptions. Edit details or create new ones as needed.', 'wp-user-frontend' ),
    publish: __( 'Oversee all active subscriptions currently available for users.', 'wp-user-frontend' ),
    draft: __( 'Handle subscriptions that are saved as drafts but not yet published.', 'wp-user-frontend' ),
    trash: __( 'Review deleted subscriptions. Restore or permanently delete them as required.', 'wp-user-frontend' ),
};

onBeforeMount(
    () => {
        count.value = subscriptionStore.allCount[subscriptionStore.currentSubscriptionStatus];
        totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );
    }
);

watch(
    () => subscriptionStore.currentSubscriptionStatus,
    ( newValue ) => {
        count.value = subscriptionStore.allCount[newValue];
        totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );
        currentPage.value = 1;
    }
);

watch(
    () => subscriptionStore.allCount,
    ( newValue ) => {
        count.value = subscriptionStore.allCount[subscriptionStore.currentSubscriptionStatus];
        totalPages.value = Math.ceil( count.value / wpufSubscriptions.perPage );

        // refresh the pagination component
        paginationKey.value += 1;
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
        <div v-if="!count" class="wpuf-pl-[48px]">
            <ListHeader :message="headerMessage[subscriptionStore.currentSubscriptionStatus]" />
            <Empty :message="emptyMessages[subscriptionStore.currentSubscriptionStatus]"/>
        </div>
        <div v-else class="wpuf-pl-[48px]">
            <ListHeader :message="headerMessage[subscriptionStore.currentSubscriptionStatus]" />
            <div class="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-[40px]">
                <SubscriptionBox
                    v-for="subscription in subscriptions"
                    :subscription=subscription
                    :key="subscription.ID"/>
            </div>
        </div>
        <Pagination
            v-if="count > perPage"
            :key="paginationKey"
            :currentPage="currentPage"
            :count="count"
            :maxVisibleButtons="maxVisibleButtons"
            :totalPages="totalPages"
            :perPage="perPage"
            @changePageTo="changePageTo"/>
    </div>
</template>
