<script setup>
import {__} from '@wordpress/i18n';

const stats = [
    { name: 'Revenue', value: '$405,091.00', change: '+4.75%', changeType: 'positive' },
    { name: 'Overdue invoices', value: '$12,787.00', change: '+54.02%', changeType: 'negative' },
    { name: 'Outstanding invoices', value: '$245,988.00', change: '-1.39%', changeType: 'positive' },
    { name: 'Expenses', value: '$30,156.00', change: '+10.18%', changeType: 'negative' },
];
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
        <dl class="wpuf-mx-auto wpuf-grid wpuf-grid-cols-1 wpuf-gap-px bg-gray-900/5 sm:wpuf-grid-cols-2 lg:wpuf-grid-cols-4">
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 sm:wpuf-px-6 xl:wpuf-px-8 wpuf-rounded-s-xl">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">Revenue</dt>
                <dd class="wpuf-text-xs wpuf-font-medium wpuf-text-gray-700">+4.75%</dd>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">$405,091.00</dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 sm:wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">Overdue invoices</dt>
                <dd class="wpuf-text-xs wpuf-font-medium text-rose-600">+54.02%</dd>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">$12,787.00</dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 sm:wpuf-px-6 xl:wpuf-px-8">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">Outstanding invoices</dt>
                <dd class="wpuf-text-xs wpuf-font-medium wpuf-text-gray-700">-1.39%</dd>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">$245,988.00</dd>
            </div>
            <div class="wpuf-flex wpuf-flex-wrap wpuf-items-baseline wpuf-justify-between wpuf-gap-x-4 wpuf-gap-y-2 wpuf-bg-white wpuf-px-4 wpuf-py-10 sm:wpuf-px-6 xl:wpuf-px-8 wpuf-rounded-e-xl">
                <dt class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-500">Expenses</dt>
                <dd class="wpuf-text-xs wpuf-font-medium text-rose-600">+10.18%</dd>
                <dd class="wpuf-w-full wpuf-flex-none wpuf-text-3xl wpuf-font-medium wpuf-leading-10 wpuf-tracking-tight wpuf-text-gray-900">$30,156.00</dd>
            </div>
        </dl>
    </div>
</template>
