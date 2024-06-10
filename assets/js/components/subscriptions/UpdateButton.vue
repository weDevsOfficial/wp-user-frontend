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
    <button
        @click="$emit('updateSubscription')"
        :disabled="subscriptionStore.isUpdating"
        type="button"
        :class="subscriptionStore.isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-indigo-500' : 'wpuf-bg-indigo-600'"
        class="wpuf-ml-4 wpuf-rounded-lg wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        {{ buttonText }}
    </button>
</template>
