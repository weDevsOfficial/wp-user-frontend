<script setup>
import {toRefs} from 'vue';
import {__} from '@wordpress/i18n';
import SubscriptionBox from './SubscriptionBox.vue';

const props = defineProps( {
    subscriptions: Array
} );

const {subscriptions} = toRefs( props );

</script>

<template>
    <div class="wpuf-all-subscriptions-list wpuf-px-12">
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Subscriptions', 'wp-user-frontend' ) }}</h3>
        <p class="wpuf-text-sm wpuf-text-gray-500">{{ __( 'Explore and manage all subscriptions in one place', 'wp-user-frontend' ) }}</p>
        <div class="wpuf-grid wpuf-grid-cols-3 wpuf-gap-4 wpuf-mt-12">
            <SubscriptionBox v-for="subscription in subscriptions" :subscription=subscription :key="subscription.ID" />
        </div>
    </div>
</template>
