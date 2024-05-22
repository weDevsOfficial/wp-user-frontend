<script setup>
import {__} from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';
import {computed, ref} from 'vue';

const subscriptionStore = useSubscriptionStore();
const currentSubscription = subscriptionStore.currentSubscription;
const isRecurring = ref( false );

const billingAmount = computed(() => {
    if (parseFloat( currentSubscription.meta_value.billing_amount ) === 0) {
        return __( 'Free', 'wp-user-frontend' );
    } else {
        if ( currentSubscription.meta_value.recurring_pay === 'yes' ) {
            isRecurring.value = true;
            return wpufSubscriptions.currencySymbol + currentSubscription.meta_value.billing_amount + ' per ' + currentSubscription.meta_value.cycle_period;
        }

        return wpufSubscriptions.currencySymbol + currentSubscription.meta_value.billing_amount;
    }
});
</script>
<template>
    <div class="wpuf-mt-4 wpuf-border wpuf-border-gray-200">
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 bg-gray-900/5 wpuf-grid-cols-2 wpuf-grid-cols-4 wpuf-border-b-2 wpuf-border-dashed wpuf-bg-white">
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-px-4 wpuf-py-2 wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Plan', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">
                    {{ currentSubscription.post_title }}
                </dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-px-4 wpuf-py-2 wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Payment', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">
                    {{ billingAmount }}
                </dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-px-4 wpuf-py-2 wpuf-px-6 xl:wpuf-px-8"></div>
            <div v-if="isRecurring" class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-px-4 wpuf-py-5 wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500 wpuf-flex wpuf-items-center wpuf-justify-center">
                    <div class="dashicons dashicons-controls-repeat"></div>
                    &nbsp;&nbsp;{{ __( 'Recurring', 'wp-user-frontend' )}}
                </dt>
            </div>
        </dl>
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 bg-gray-900/5 wpuf-grid-cols-2 wpuf-grid-cols-4 wpuf-bg-white">
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-2 wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">
                    {{ __( 'Subscribers', 'wp-user-frontend' )}}
                </dt>
                <dd class="wpuf-flex wpuf-items-center wpuf-w-full wpuf-flex-none wpuf-text-xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">
                    <span class="dashicons dashicons-businessman"></span>&nbsp;&nbsp;{{ currentSubscription.subscribers }}
                </dd>
            </div>
        </dl>
    </div>
</template>
