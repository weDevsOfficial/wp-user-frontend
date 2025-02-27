<script setup>
import {__} from '@wordpress/i18n';
import {useComponentStore} from '../../stores/component';
import {useSubscriptionStore} from '../../stores/subscription';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();

const props = defineProps( {
    message: {
        type: String,
        default: __( 'No Subscription created yet!', 'wp-user-frontend' ),
    },
} );
</script>

<template>
    <div class="wpuf-h-[50vh] wpuf-flex wpuf-items-center wpuf-justify-center">
        <div class="wpuf-w-3/4 wpuf-text-center">
            <svg
                v-if="subscriptionStore.currentSubscriptionStatus === 'all'"
                class="wpuf-mx-auto wpuf-h-12 wpuf-w-12 wpuf-text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3
                v-if="subscriptionStore.currentSubscriptionStatus === 'all'"
                class="wpuf-text-3xl wpuf-text-gray-900">
                {{ __( 'No Subscription created yet!', 'wp-user-frontend' ) }}
            </h3>
            <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-text-center wpuf-mt-8">
                {{ props.message }}
            </p>
            <div
                v-if="subscriptionStore.currentSubscriptionStatus === 'all'"
                class="wpuf-mt-12">
                <button
                    type="button"
                    @click="componentStore.setCurrentComponent( 'New' )"
                    class="wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600">
                    <span class="dashicons dashicons-plus-alt"></span>&nbsp;&nbsp;&nbsp;
                    {{ __( 'Add Subscription', 'wp-user-frontend' ) }}
                </button>
            </div>
        </div>
    </div>
</template>
