<script setup>
import {__} from '@wordpress/i18n';
import ProBadge from '../ProBadge.vue';
import {ref} from '../../../../assets/vendor/vue-3/vue.esm-browser';

const transactionSummary = wpufTransactions.transactionSummary;
const toolTipIndex = ref( [] );

Object.keys( transactionSummary ).forEach( ( transaction, index ) => {
    toolTipIndex.value[index] = false;
});

const getChangePercentage = (changeType, percentage) => {
    return changeType === 'positive' ? `+${percentage}%` : `-${percentage}%`;
};
</script>
<template>
    <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
        <h3 class="wpuf-text-[24px] wpuf-font-semibold wpuf-my-0">
            {{ __('Transaction Summary', 'wp-user-frontend') }}
        </h3>
        <div class="wpuf-mt-3 wpuf-flex wpuf-ml-4">
            <select
                class="wpuf-mr-4 wpuf-block wpuf-w-full !wpuf-border-none wpuf-text-gray-900 !wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 focus:wpuf-ring-2 focus:wpuf-ring-indigo-600">
                <option>{{ __( 'Select', 'wp-user-frontend' ) }}</option>
                <option>{{ __( 'This Month', 'wp-user-frontend' ) }}</option>
                <option>{{ __( 'Last Month', 'wp-user-frontend' ) }}</option>
                <option>{{ __( 'Last 6 Months', 'wp-user-frontend' ) }}</option>
                <option>{{ __( 'Custom Range', 'wp-user-frontend' ) }}</option>
            </select>
            <button type="button" class="wpuf-rounded wpuf-bg-white wpuf-px-8 wpuf-py-1 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">{{ __( 'Show', 'wp-user-frontend' ) }}</button>
        </div>
    </div>
    <div class="wpuf-bg-gray-100 wpuf-mt-8 wpuf-p-px wpuf-rounded-xl">
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 wpuf-gap-px bg-gray-900/5 wpuf-grid-cols-2 wpuf-grid-cols-5">
            <div
                v-for="(transaction, key, index) in transactionSummary"
                :key="key"
                :class="index === 0 ? 'wpuf-rounded-s-xl' : index === Object.keys( transactionSummary ).length - 1 ? 'wpuf-rounded-e-xl' : ''"
                class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 wpuf-relative">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">{{ transaction.label }}</dt>
                <div
                    :class="transaction.change_type === 'positive' ? 'wpuf-text-green-600' : 'wpuf-text-rose-600'"
                    class="wpuf-text-xs wpuf-font-medium wpuf-text-gray-700 wpuf-flex wpuf-relative">
                    {{ getChangePercentage(transaction.change_type, transaction.percentage) }}
                    <div
                        v-if="transaction.is_pro_preview"
                        class="wpuf-ml-2 wpuf-z-40 hover:wpuf-cursor-pointer">
                        <ProBadge />
                    </div>
                </div>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">${{ transaction.amount }}</dd>
                <div v-if="transaction.is_pro_preview" class="wpuf-absolute wpuf-bg-slate-50/50 wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full hover:wpuf-bg-slate-60/50"></div>
            </div>
        </dl>
    </div>
</template>
