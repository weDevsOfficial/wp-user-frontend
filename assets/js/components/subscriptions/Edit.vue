<script setup>
import {__} from '@wordpress/i18n';
import InfoCard from './InfoCard.vue';
import SubscriptionsDetails from './SubscriptionsDetails.vue';
import {useComponentStore} from '../../stores/component';
import {useSubscriptionStore} from '../../stores/subscription';
import {ref} from 'vue';
import UpdateButton from './UpdateButton.vue';

const isUpdating = ref( false );

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();

const resetErrors = () => {
    for (const item in errors) {
        errors[item] = false;
    }
};

const updateSubscription = () => {
    console.log('updateSubscription');
};

</script>
<template>
    <div class="wpuf-px-12">
        <button
            type="button"
            @click="[componentStore.setCurrentComponent('List'), subscriptionStore.setCurrentSubscription(null)]"
            class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3.5 wpuf-py-2.5 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <span class="dashicons dashicons-arrow-left-alt"></span>&nbsp;{{ __( 'Back', 'wp-user-frontend' ) }}</button>
        <h3 class="wpuf-text-lg wpuf-font-bold wpuf-mb-0">{{ __( 'Edit', 'wp-user-frontend' ) }}</h3>
        <InfoCard />
        <SubscriptionsDetails />
        <div class="wpuf-mt-8 wpuf-text-end">
            <UpdateButton
                @update-subscription="updateSubscription"
                :is-updating="isUpdating.value" />
        </div>
    </div>
</template>
