<script setup>
import {__} from '@wordpress/i18n';
import {toRefs} from 'vue';

const props = defineProps( {
    subscription: Object
} )

const {subscription} = toRefs( props )

</script>
<template>
    <div
        class="wpuf-subscription-box wpuf-border-2 wpuf-border-gray-200 wpuf-px-4 wpuf-py-4">
        <div
            class="wpuf-flex wpuf-flex-wrap wpuf-items-center wpuf-justify-between sm:wpuf-flex-nowrap">
            <div>
                <h3 class="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-m-0">{{ subscription.title.rendered }}</h3>
                <div class=" wpuf-rounded-full wpuf-px-2.5 wpuf-py-1 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{{ subscription.status }}</div>
            </div>
        </div>
    </div>

</template>
