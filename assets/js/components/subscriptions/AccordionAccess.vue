<script setup>
import {__} from '@wordpress/i18n';
import {ref, toRefs} from 'vue';

import VueDatePicker from '@vuepic/vue-datepicker';

const props = defineProps( {
    subscription: Object,
    errors: Object
} );

const {subscription} = toRefs( props );

const isPrivate = ref( subscription.post_status === 'private' );

const publishedDate = ref(new Date(subscription.value.post_date));

const handleDate = (modelData) => {
    publishedDate.value = modelData;
}
</script>
<style scoped>
.dp__theme_light {
    --dp-background-color: none;
    --dp-text-color: none;
    --dp-hover-color: none;
    --dp-hover-text-color: none;
    --dp-hover-icon-color: none;
    --dp-primary-color: none;
    --dp-primary-disabled-color: none;
    --dp-primary-text-color: none;
    --dp-secondary-color: none;
    --dp-border-color: none;
    --dp-menu-border-color: none;
    --dp-border-color-hover: none;
    --dp-disabled-color: none;
    --dp-scroll-bar-background: none;
    --dp-scroll-bar-color: none;
    --dp-success-color: none;
    --dp-success-color-disabled: none;
    --dp-icon-color: none;
    --dp-danger-color: none;
    --dp-marker-color: none;
    --dp-tooltip-color: none;
    --dp-disabled-color-text: none;
    --dp-highlight-color: none;
    --dp-range-between-dates-background-color: none;
    --dp-range-between-dates-text-color: none;
    --dp-range-between-border-color: none;
}
</style>
<template>
    <h2 id="accordion-access-heading" class="wpuf-mb-0">
        <button type="button" class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-border wpuf-border-b-0 wpuf-border-gray-200 dark:wpuf-border-gray-700 dark:wpuf-text-gray-400 hover:wpuf-bg-gray-100 dark:hover:wpuf-bg-gray-800 wpuf-gap-3" data-accordion-target="#accordion-access" aria-expanded="false" aria-controls="accordion-access">
            <span>{{ __( 'Access and Visibility', 'wp-user-frontend' ) }}</span>
            <svg data-accordion-icon class="wpuf-w-3 wpuf-h-3 wpuf-rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
            </svg>
        </button>
    </h2>
    <div id="accordion-access" aria-labelledby="accordion-access-heading">
        <div class="wpuf-p-4 wpuf-border wpuf-border-b-0 wpuf-border-gray-200 dark:wpuf-border-gray-700 dark:wpuf-bg-gray-900">
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="is-plan-private" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Make Plan Private', 'wp-user-frontend') }}
                </label>
                <div>
                    <button
                        @click="isPrivate = !isPrivate"
                        type="button"
                        :class="isPrivate ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                        class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2"
                        role="switch"
                        aria-checked="true">
                            <span
                                aria-hidden="true"
                                :class="isPrivate ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                                class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out">
                            </span>
                    </button>
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('Make the subscription private or published', 'wp-user-frontend') }}</p>
                </div>
            </div>
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="slug" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Plan Slug', 'wp-user-frontend') }}
                </label>
                <div>
                    <input
                        type="text"
                        :value="subscription.post_name"
                        name="slug"
                        id="slug"
                        class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-max-w-xs sm:wpuf-text-sm sm:wpuf-leading-6">
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('The plan slug') }}</p>
                </div>
            </div>
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="slug" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Published on', 'wp-user-frontend') }}
                </label>
                <div>
                    <VueDatePicker
                        textInput
                        v-model="publishedDate"
                        enable-seconds
                        :state="!errors.date"
                        @update:model-value="handleDate" />
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('The subscription publishing date') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
