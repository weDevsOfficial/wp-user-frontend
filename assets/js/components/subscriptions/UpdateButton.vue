<script setup>
import {__} from '@wordpress/i18n';
import {ref} from 'vue';
import {useSubscriptionStore} from '../../stores/subscription';

const props = defineProps( {
    buttonText: {
        type: String,
        default: __('Update', 'wp-user-frontend'),
    },
} );

const subscriptionStore = useSubscriptionStore();
const buttonText = ref( props.buttonText );

</script>
<template>
    <div class="wpuf-relative">
        <button
            :disabled="subscriptionStore.isUpdating"
            :class="subscriptionStore.isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''"
            class="wpuf-peer wpuf-inline-flex wpuf-justify-between wpuf-items-center wpuf-cursor-pointer wpuf-bg-indigo-600 hover:wpuf-bg-indigo-800 wpuf-text-white wpuf-font-medium wpuf-text-base wpuf-py-2 wpuf-px-5 wpuf-rounded-md min-w-[122px]">
            {{ buttonText }}
            <svg class="wpuf-rotate-180 wpuf-w-3 wpuf-h-3 shrink-0 wpuf-ml-4"
                 data-accordion-icon="" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5 5 1 1 5"></path>
            </svg>
        </button>
        <div
            class="wpuf-hidden hover:wpuf-block peer-hover:wpuf-block wpuf-cursor-pointer wpuf-w-44 wpuf-z-40 wpuf-bg-white wpuf-border border-[#DBDBDB] wpuf-absolute wpuf-z-10 wpuf-shadow wpuf-right-0 wpuf-rounded-md after:content-[''] before:content-[''] after:wpuf-absolute before:wpuf-absolute after:w-[13px] before:w-[70%] before:-right-[1px] after:h-[13px] before:wpuf-h-3 before:wpuf-mt-3 after:top-[-7px] before:wpuf--top-6 after:right-[1.4rem] after:z-[-1] after:wpuf-bg-white after:wpuf-border after:border-[#DBDBDB] after:!rotate-45 after:wpuf-border-r-0 after:wpuf-border-b-0"
        >
        <span
            @click="() => {subscriptionStore.currentSubscription.post_status = 'publish'; $emit('updateSubscription'); }"
            :class="subscriptionStore.isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''"
            class="wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-indigo-700 hover:wpuf-text-white wpuf-rounded-t-md">
            {{ __( 'Publish', 'wp-user-frontend' ) }}
        </span>
        <span
            @click="() => {subscriptionStore.currentSubscription.post_status = 'draft'; $emit('updateSubscription');}"
            :class="subscriptionStore.isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''"
            class="wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-indigo-700 hover:wpuf-text-white wpuf-rounded-b-md">
            {{ __( 'Save as Draft', 'wp-user-frontend' ) }}
        </span>
        </div>
    </div>
</template>
