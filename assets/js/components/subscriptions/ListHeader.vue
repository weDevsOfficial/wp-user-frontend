<script setup>
import {__} from '@wordpress/i18n';
import {storeToRefs} from 'pinia';
import {useComponentStore} from '../../stores/component';
import {computed, ref} from 'vue';
import {useSubscriptionStore} from '../../stores/subscription';

const componentStore = useComponentStore();
const subscriptionStore = useSubscriptionStore();

const { currentComponent } = storeToRefs(componentStore);
const props = defineProps({
    message: {
        type: String,
        default: __( 'Explore and manage all subscriptions in one place', 'wp-user-frontend' ),
    },
});

const title = computed(() => {
    switch (subscriptionStore.currentSubscriptionStatus) {
        case 'all':
            return __( 'All Subscriptions', 'wp-user-frontend' );
        case 'publish':
            return __( 'Published', 'wp-user-frontend' );
        case 'draft':
            return __( 'Drafts', 'wp-user-frontend' );
        case 'trash':
            return __( 'Trash', 'wp-user-frontend' );
        default:
            return __( 'Subscriptions', 'wp-user-frontend' );

    }
});

const shouldShowButton = computed( () => {
    return !( subscriptionStore.currentSubscriptionStatus === 'trash' || ( subscriptionStore.currentSubscriptionStatus === 'all' && subscriptionStore.allCount.all === 0 ) );
} );

</script>
<template>
    <div class="wpuf-flex wpuf-justify-between">
        <div class="wpuf-w-3/5">
            <h3 class="wpuf-text-lg wpuf-font-bold wpuf-m-0">{{ title }}</h3>
            <p class="wpuf-text-sm wpuf-text-gray-500">{{ props.message }}</p>
        </div>
        <div class="wpuf-w-1/5">
            <button
                v-if="shouldShowButton"
                @click="componentStore.setCurrentComponent( 'New' )"
                type="button"
                class="wpuf-flex wpuf-items-center wpuf-rounded-md wpuf-bg-indigo-600 hover:wpuf-bg-indigo-500 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 21.6C17.3019 21.6 21.6 17.3019 21.6 12C21.6 6.69807 17.3019 2.4 12 2.4C6.69806 2.4 2.39999 6.69807 2.39999 12C2.39999 17.3019 6.69806 21.6 12 21.6ZM13.2 8.4C13.2 7.73726 12.6627 7.2 12 7.2C11.3372 7.2 10.8 7.73726 10.8 8.4V10.8H8.39999C7.73725 10.8 7.19999 11.3373 7.19999 12C7.19999 12.6627 7.73725 13.2 8.39999 13.2H10.8V15.6C10.8 16.2627 11.3372 16.8 12 16.8C12.6627 16.8 13.2 16.2627 13.2 15.6V13.2H15.6C16.2627 13.2 16.8 12.6627 16.8 12C16.8 11.3373 16.2627 10.8 15.6 10.8H13.2V8.4Z" fill="#FFF"/>
                        </svg>
                    </span>
                &nbsp;&nbsp;{{ __('Add Subscription', 'wp-user-frontend') }}
            </button>
        </div>
    </div>
</template>
