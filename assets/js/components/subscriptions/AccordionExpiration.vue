<script setup>
import {__} from '@wordpress/i18n';
import {ref, toRefs} from 'vue';

const props = defineProps( {
    subscription: Object,
    errors: Object
} );

const {subscription} = toRefs( props );
const timeTypes = ['year', 'month', 'day'];
const postStatus = {
    publish: 'Publish',
    draft: 'Draft',
    private: 'Private',
    pending: 'Pending Review'
};

const isPostExpirationEnabled = ref( subscription.value.meta_value._enable_post_expiration === 'on' );
const isPostRollback = ref( subscription.value.meta_value.postnum_rollback_on_delete === 'yes' );
const isPostExpirationMailEnabled = ref( subscription.value.meta_value._enable_mail_after_expired === 'on' );
const postExpirationTime = ref( subscription.value.meta_value._post_expiration_time.split(' ') );
const postExpirationTimeValue = ref( postExpirationTime.value[0] );
const postExpirationTimeType = ref( postExpirationTime.value[1] ? postExpirationTime.value[1] : '' );

const publishedDate = ref(new Date(subscription.value.post_date));

const getMetaValue = (key) => {
    // check first if the key exists in the meta_value
    if (!subscription.value.meta_value.hasOwnProperty( key )) {
        return '';
    }

    return subscription.value.meta_value[key];
}

const handleDate = (modelData) => {
    publishedDate.value = modelData;
}

const capitalize = ( text ) => {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

const formatTimeType = (text) => {
    return capitalize(text) + '(s)';
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
    <h2 id="accordion-expiration-heading" class="wpuf-mb-0">
        <button type="button" class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-border wpuf-border-b-0 wpuf-border-gray-200 dark:wpuf-border-gray-700 dark:wpuf-text-gray-400 hover:wpuf-bg-gray-100 dark:hover:wpuf-bg-gray-800 wpuf-gap-3" data-accordion-target="#accordion-expiration" aria-expanded="false" aria-controls="accordion-expiration">
            <span>{{ __( 'Post Expiration', 'wp-user-frontend' ) }}</span>
            <svg data-accordion-icon class="wpuf-w-3 wpuf-h-3 wpuf-rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
            </svg>
        </button>
    </h2>

    <div id="accordion-expiration" aria-labelledby="accordion-expiration-heading">
        <div class="wpuf-p-4 wpuf-border wpuf-border-gray-200 dark:wpuf-border-gray-700 dark:wpuf-bg-gray-900">
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="post-expiration" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Enable Post Expiration', 'wp-user-frontend') }}
                </label>
                <div>
                    <button
                        @click="isPostExpirationEnabled = !isPostExpirationEnabled"
                        type="button"
                        :class="isPostExpirationEnabled ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                        class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2"
                        role="switch"
                        aria-checked="true">
                            <span
                                aria-hidden="true"
                                :class="isPostExpirationEnabled ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                                class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out">
                            </span>
                    </button>
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('') }}</p>
                </div>
            </div>
            <div v-if="isPostExpirationEnabled">
                <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                    <label for="post-expiration-value" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                        {{ __('Post Expiration Time	', 'wp-user-frontend') }}
                    </label>
                    <div class="wpuf-flex">
                        <input
                            type="text"
                            :value="postExpirationTimeValue"
                            name="post-expiration-value"
                            id="post-expiration-value"
                            class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-max-w-xs sm:wpuf-text-sm sm:wpuf-leading-6">
                        <select>
                            <option
                                v-for="type in timeTypes"
                                :value="type"
                                :selected="type === postExpirationTimeType"
                            >{{ formatTimeType(type) }}</option>
                        </select>
                    </div>
                </div>
                <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                    <label for="post-status" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                        {{ __('Post Status', 'wp-user-frontend') }}
                    </label>
                    <div class="wpuf-w-max">
                        <select>
                            <option
                                v-for="(item, key) in postStatus"
                                :value="key"
                                :selected="key === subscription.meta_value._expired_post_status"
                                :key="key">{{ item }}</option>
                        </select>
                        <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('Status of post after post expiration time is over', 'wp-user-frontend') }}</p>
                    </div>
                </div>
                <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                    <label for="expiration-mail" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                        {{ __('Send expiration mail', 'wp-user-frontend') }}
                    </label>
                    <div class="wpuf-w-max">
                        <button
                            @click="isPostExpirationMailEnabled = !isPostExpirationMailEnabled"
                            type="button"
                            :class="isPostExpirationMailEnabled ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                            class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2"
                            role="switch"
                            aria-checked="true">
                            <span
                                aria-hidden="true"
                                :class="isPostExpirationMailEnabled ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                                class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out">
                            </span>
                        </button>
                        <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ __('Send an e-mail to author after exceeding post expiration time', 'wp-user-frontend') }}</p>
                    </div>
                </div>
                <div v-if="isPostExpirationMailEnabled" class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                    <label for="expiration-message" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                        {{ __( 'Expiration Message', 'wp-user-frontend' ) }}
                    </label>
                    <div class="wpuf-mt-2 sm:wpuf-col-span-2 sm:wpuf-mt-0">
                        <textarea
                            id="expiration-message"
                            name="expiration-message"
                            rows="3"
                            class="wpuf-block wpuf-w-full wpuf-max-w-2xl wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">{{ getMetaValue('_post_expiration_message') }}</textarea>
                        <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">You may use: {post_author} {post_url} {blogname} {post_title} {post_status}</p>
                    </div>
                </div>
            </div>
            <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
                <label for="post-rollback" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
                    {{ __('Enable Post Number Rollback', 'wp-user-frontend') }}
                </label>
                <div class="wpuf-w-max">
                    <button
                        @click="isPostRollback = !isPostRollback"
                        type="button"
                        :class="isPostRollback ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                        class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2"
                        role="switch"
                        aria-checked="true">
                            <span
                                aria-hidden="true"
                                :class="isPostRollback ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                                class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out">
                            </span>
                    </button>
                    <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">
                        {{ __( 'If enabled, number of posts will be restored if the post is deleted.', 'wp-user-frontend' ) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
