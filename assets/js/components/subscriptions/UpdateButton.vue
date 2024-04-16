<script setup>
import {__} from '@wordpress/i18n';
import {ref} from 'vue';

const props = defineProps( {
    isUpdating: {
        type: Boolean,
        required: true,
    },
    buttonText: {
        type: String,
        default: __('Update', 'wp-user-frontend'),
    },
} );

const isUpdating = ref( props.isUpdating );
const buttonText = ref( props.buttonText );

</script>
<template>
    <button
        @click="$emit('updateSubscription')"
        :disabled="isUpdating"
        type="button"
        :class="isUpdating ? 'wpuf-cursor-not-allowed' : ''"
        class="wpuf-ml-4 wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        {{ buttonText }}
    </button>
</template>
