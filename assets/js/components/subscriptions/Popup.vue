<script setup>
import {__} from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';
import {computed} from 'vue';

const emit = defineEmits( ['deleteSubscription', 'trashSubscription', 'hidePopup'] );
const subscriptionStore = useSubscriptionStore();
const currentSubscriptionStatus = subscriptionStore.currentSubscriptionStatus;
const info = computed( () => {
    switch ( currentSubscriptionStatus ) {
        case 'trash':
            return {
                title: __( 'Delete Subscription', 'wp-user-frontend' ),
                message: __( 'Are you sure you want to delete this subscription? This action cannot be undone.', 'wp-user-frontend' ),
                actionText: __( 'Delete', 'wp-user-frontend' ),
            }
        default:
            return {
                title: __( 'Trash Subscription', 'wp-user-frontend' ),
                message: __( 'This subscription will be moved to the trash. Are you sure?', 'wp-user-frontend' ),
                actionText: __( 'Trash', 'wp-user-frontend' ),
            }
    }
} );

const emitAction = () => {
    if ( currentSubscriptionStatus === 'trash' ) {
        emit('deleteSubscription');
    } else {
        emit('trashSubscription');
    }
}

</script>
<template>
    <div class="wpuf-fixed wpuf-z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="wpuf-fixed wpuf-inset-0 wpuf-bg-gray-500 wpuf-bg-opacity-75 wpuf-transition-opacity"></div>
        <div class="wpuf-fixed wpuf-inset-0 wpuf-z-10 wpuf-w-screen wpuf-overflow-y-auto">
            <div class="wpuf-flex wpuf-min-h-full wpuf-justify-center wpuf-text-center wpuf-items-center wpuf-p-0">
                <div class="wpuf-relative wpuf-transform wpuf-overflow-hidden wpuf-rounded-lg wpuf-bg-white wpuf-px-4 wpuf-pb-4 wpuf-pt-5 wpuf-text-left wpuf-shadow-xl wpuf-transition-all wpuf-my-8 wpuf-w-full wpuf-max-w-lg wpuf-p-6">
                    <div class="wpuf-absolute wpuf-right-0 wpuf-top-0 wpuf-pr-4 wpuf-pt-4 wpuf-block">
                        <button @click="$emit('hidePopup')" type="button" class="wpuf-rounded-md wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-500 focus:wpuf-outline-none">
                            <span class="wpuf-sr-only">Close</span>
                            <svg class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="wpuf-flex wpuf-items-start">
                        <div class="wpuf-ml-4 wpuf-mt-0 wpuf-text-left">
                            <h3 class="wpuf-text-base wpuf-font-semibold wpuf-leading-6 wpuf-text-gray-900" id="modal-title">
                                {{ info.title }}</h3>
                            <div class="wpuf-mt-2">
                                <p class="wpuf-text-sm wpuf-text-gray-500">{{ info.message }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="wpuf-mt-4 wpuf-flex wpuf-flex-row-reverse">
                        <button
                            type="button"
                            @click="emitAction"
                            class="wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-red-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-red-500 wpuf-ml-3 wpuf-w-auto">
                            {{ info.actionText }}</button>
                        <button
                            type="button"
                            @click="$emit('hidePopup')"
                            class="wpuf-inline-flex wpuf-justify-center wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 wpuf-mt-0 wpuf-w-auto">
                            {{ __( 'Cancel', 'wp-user-frontend' ) }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
