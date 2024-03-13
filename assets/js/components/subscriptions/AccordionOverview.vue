<script setup>
import {__} from '@wordpress/i18n';
import {ref, toRefs} from 'vue';

const props = defineProps( {
    subscription: Object,
    errors: Object
} );

const {subscription} = toRefs( props );
</script>
<template>
    <h2 id="accordion-overview-heading" class="wpuf-mb-0">
        <button type="button" class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-border wpuf-border-b-0 wpuf-border-gray-200 wpuf-rounded-t-xl dark:wpuf-border-gray-700 dark:wpuf-text-gray-400 hover:wpuf-bg-gray-100 dark:hover:wpuf-bg-gray-800 wpuf-gap-3" data-accordion-target="#accordion-overview" aria-expanded="true" aria-controls="accordion-overview">
            <span>{{ __( 'Overview', 'wp-user-frontend' ) }}</span>
            <svg data-accordion-icon class="wpuf-w-3 wpuf-h-3 wpuf-rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
            </svg>
        </button>
    </h2>
    <div id="accordion-overview" aria-labelledby="accordion-overview-heading">
        <div class="wpuf-p-4 wpuf-border wpuf-border-b-0 wpuf-border-gray-200 dark:wpuf-border-gray-700 dark:wpuf-bg-gray-900">
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="plan-name" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Plan Name', 'wp-user-frontend') }}
                </label>
                <div>
                    <input
                        type="text"
                        :value="subscription.post_title"
                        name="plan-name"
                        id="plan-name"
                        class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-max-w-xs sm:wpuf-text-sm sm:wpuf-leading-6">
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('The subscription plan name') }}</p>
                </div>
            </div>
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="plan-details" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __( 'Plan Details', 'wp-user-frontend' ) }}
                </label>
                <div class="wpuf-mt-2 sm:wpuf-col-span-2 sm:wpuf-mt-0">
                    <textarea id="plan-details" name="plan-details" rows="3" class="wpuf-block wpuf-w-full wpuf-max-w-2xl wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">{{ subscription.post_content }}</textarea>
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('Write a few sentences about your plan') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
