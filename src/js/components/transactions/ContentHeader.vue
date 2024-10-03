<script setup>
import {__} from '@wordpress/i18n';
import ProBadge from '../ProBadge.vue';
import {ref} from 'vue';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

const transactionSummary = ref( wpufTransactions.transactionSummary );
const filterIndex = ref( 0 );

const getFilteredData = () => {
    const queryParams = {};

    switch (filterIndex.value) {
        case 1:
            queryParams['time'] = 'this_month';
            break;
        case 2:
            queryParams['time'] = 'last_month';
            break;
        case 3:
            queryParams['time'] = 'last_6_months';
            break;
        default:
            queryParams['time'] = 'all';
            break;
    }

    queryParams['per_page'] = wpufTransactions.perPage;

    summaryLoading.value = true;

    apiFetch(
        {
            path: addQueryArgs( '/wp-json/wpuf/v1/transactions', queryParams ),
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpufTransactions.nonce,
            },
        }
    ).then((response) => {
        if (response.success) {
            transactionSummary.value = response.result;
        }
    }).finally(() => {
        summaryLoading.value = false;
    });
};

const filteringOptions = [
    __( 'All', 'wp-user-frontend' ),
    __( 'This Month', 'wp-user-frontend' ),
    __( 'Last Month', 'wp-user-frontend' ),
    __( 'Last 6 Months', 'wp-user-frontend' ),
];

const selected = ref( '' );

const summaryLoading = ref( false );

const getReadablePercentage = ( percentage ) => {
    if (percentage === 0) {
        return '';
    }

    return percentage > 0 ? `+${percentage}%` : `${percentage}%`;
};

</script>
<template>
    <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
        <h3 class="wpuf-text-[24px] wpuf-font-semibold wpuf-my-0">
            {{ __('Transaction Summary', 'wp-user-frontend') }}
        </h3>
        <div class="wpuf-mt-3 wpuf-flex wpuf-ml-4">
            <select
                v-model="filterIndex"
                class="wpuf-mr-4 wpuf-block wpuf-w-full !wpuf-border-none wpuf-text-gray-900 !wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 focus:wpuf-ring-2 focus:wpuf-ring-indigo-600">
                <option
                    v-for="(option, index) in filteringOptions"
                    :key="index"
                    :value="index"
                >{{ option }}</option>
            </select>
            <button
                type="button"
                :class="summaryLoading ? 'wpuf-opacity-50 wpuf-pointer-events-none' : ''"
                class="wpuf-rounded wpuf-bg-white wpuf-px-8 wpuf-py-1 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 hover:wpuf-cursor-pointer"
                @click="getFilteredData">{{ __( 'Show', 'wp-user-frontend' ) }}</button>
        </div>
    </div>
    <div class="wpuf-bg-gray-100 wpuf-mt-8 wpuf-p-px wpuf-rounded-xl wpuf-relative">
        <div
            v-if="summaryLoading"
            class="wpuf-absolute wpuf-w-full wpuf-h-full wpuf-left-0 wpuf-top-0 wpuf-z-50 wpuf-bg-slate-50/50 wpuf-rounded-xl wpuf-flex wpuf-items-center wpuf-justify-evenly">
            <svg class="wpuf-animate-spin wpuf-h-5 wpuf-w-5 wpuf-mr-3" viewBox="0 0 24 24">
                <path class="wpuf-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 wpuf-gap-px bg-gray-900/5 wpuf-grid-cols-2 wpuf-grid-cols-5">
            <div
                v-for="(transaction, key, index) in transactionSummary"
                :class="index === 0 ? 'wpuf-rounded-s-xl' : index === Object.keys( transactionSummary ).length - 1 ? 'wpuf-rounded-e-xl' : ''"
                class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 wpuf-relative">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">{{ transaction.label }}</dt>
                <div
                    v-if="transaction.percentage"
                    :class="transaction.change_type === '+' ? 'wpuf-text-green-600' : 'wpuf-text-rose-600'"
                    class="wpuf-text-xs wpuf-font-medium wpuf-text-gray-700 wpuf-flex wpuf-relative">
                    {{ getReadablePercentage( transaction.percentage ) }}
                    <div
                        v-if="transaction.is_pro_preview"
                        class="wpuf-ml-2 wpuf-z-40 hover:wpuf-cursor-pointer">
                        <ProBadge />
                    </div>
                </div>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">${{ transaction.amount }}</dd>
                <div v-if="transaction.is_pro_preview" class="wpuf-absolute wpuf-bg-slate-50/50 wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full hover:wpuf-bg-slate-60/50"></div>
            </div>
            {{ transaction }}
        </dl>
    </div>
</template>
