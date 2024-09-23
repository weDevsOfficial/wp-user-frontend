<script setup>
import {__} from '@wordpress/i18n';
import {storeToRefs} from 'pinia';
import {useComponentStore} from '../../stores/component';
import {computed} from 'vue';
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

</script>
<template>
    <h3 class="wpuf-text-lg wpuf-font-bold wpuf-m-0">{{ title }}</h3>
    <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-mb-0">{{ props.message }}</p>
</template>
