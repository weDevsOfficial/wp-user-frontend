<script setup>
import {__} from '@wordpress/i18n';
import {toRefs, ref, onBeforeMount, inject} from 'vue';

const props = defineProps( {
    subscription: Object
} );

const { quickMenuStatus, showQuickMenu } = inject( 'quickMenu' );

const {subscription} = toRefs( props );
const pillBackground = ref( '' );
const isRecurring = ref( false );
const billingAmount = ref( 0 );

const setPillBackground = () => {
    const postStatus = subscription.value.post_status;

    if (postStatus === 'publish') {
        pillBackground.value = 'wpuf-bg-green-100';
    } else if (postStatus === 'draft') {
        pillBackground.value = 'wpuf-bg-gray-100';
    } else if (postStatus === 'pending') {
        pillBackground.value = 'wpuf-bg-yellow-100';
    } else if (postStatus === 'trash') {
        pillBackground.value = 'wpuf-bg-red-100';
    } else {
        pillBackground.value = 'wpuf-bg-indigo-100';
    }
};

const setBillingAmount = () => {
    if (parseFloat( subscription.value.meta_value.billing_amount ) === 0) {
        billingAmount.value = __( 'Free', 'wp-user-frontend' );
    } else {
        billingAmount.value = wpufSubscriptions.currencySymbol + subscription.value.meta_value.billing_amount;
    }
};

onBeforeMount( () => {
    setPillBackground();
    setBillingAmount();

    if ( subscription.value.meta_value.recurring_pay === 'yes' ) {
        isRecurring.value = true;
        billingAmount.value += '/' + subscription.value.meta_value.cycle_period[0].toUpperCase();
    }
} );

</script>
<template>
    <div class="wpuf-flex wpuf-justify-between wpuf-max-w-sm wpuf-p-6 wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-shadow dark:wpuf-bg-gray-800 dark:wpuf-border-gray-700 dark:hover:wpuf-bg-gray-700">
        <div
            class="wpuf-block">
            <h5 class="wpuf-mb-1 wpuf-m-0 wpuf-text-2xl wpuf-font-bold wpuf-tracking-tight wpuf-text-gray-900 dark:wpuf-text-white">
                {{ subscription.post_title }}</h5>
            <p class="wpuf-mt-1 wpuf-mb-1 wpuf-truncate wpuf-text-lg wpuf-text-gray-500">{{ billingAmount }}</p>
            <div :class="pillBackground"
                 class="wpuf-rounded-full wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm">
                {{ subscription.post_status }}
            </div>
            <button type="button"
                    class="wpuf-mt-8 wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600">
                {{ __( 'Edit', 'wp-user-frontend' ) }}
            </button>
        </div>
        <div class="wpuf-flex wpuf-justify-between wpuf-flex-col wpuf-relative">
            <svg @click="showQuickMenu" class="wpuf-mt-2 hover:wpuf-cursor-pointer" width="27" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29.96 122.88">
                <title>menu</title>
                <path
                    d="M15,0A15,15,0,1,1,0,15,15,15,0,0,1,15,0Zm0,92.93a15,15,0,1,1-15,15,15,15,0,0,1,15-15Zm0-46.47a15,15,0,1,1-15,15,15,15,0,0,1,15-15Z"/>
            </svg>
            <div
                :class="quickMenuStatus ? 'wpuf-block' : 'wpuf-hidden'"
                class="quick-menu wpuf--left-20 wpuf-absolute wpuf-rounded-xl wpuf-bg-white wpuf-text-sm wpuf-shadow-lg wpuf-ring-1 wpuf-ring-gray-900/5">
                <ul>
                    <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Edit', 'wp-user-frontend' ) }}</li>
                    <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Quick Edit', 'wp-user-frontend' ) }}</li>
                    <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Draft/Publish', 'wp-user-frontend' ) }}</li>
                    <li class="wpuf-px-4 wpuf-py-2 wpuf-mb-0 hover:wpuf-bg-gray-100 hover:wpuf-cursor-pointer">{{ __( 'Delete', 'wp-user-frontend' ) }}</li>
                </ul>
            </div>
            <span v-if="isRecurring" class="dashicons dashicons-controls-repeat"></span>
        </div>
    </div>
</template>
