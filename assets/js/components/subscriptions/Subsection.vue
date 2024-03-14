<script setup>
import {inject, toRefs, h} from 'vue';
import SectionInputField from './SectionInputField.vue';
const props = defineProps( {
    subSection: Object,
    subscription: Object,
    fields: Object,
} );

const wpufSubscriptions = inject( 'wpufSubscriptions' );

const {subSection, subscription, fields} = toRefs( props );

console.log(Object.keys(fields).length);

</script>

<template>
    <div
        class="wpuf-border wpuf-border-gray-200 wpuf-rounded-t-xl wpuf-rounded-b-xl dark:wpuf-border-gray-70 wpuf-mt-4 wpuf mb-4">
        <h2 class="wpuf-m-0">
            <button type="button"
                    class="wpuf-rounded-t-xl wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 dark:wpuf-text-gray-400 wpuf-bg-gray-100 dark:wpuf-bg-gray-800 wpuf-gap-3">
                <span>{{ subSection.label }}</span>
                <svg data-accordion-icon class="wpuf-w-3 wpuf-h-3 wpuf-rotate-180 shrink-0" aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5 5 1 1 5"/>
                </svg>
            </button>
        </h2>
        <div id="accordion-overview">
            <div
                v-for="field in fields"
                class="wpuf-p-4 dark:wpuf-bg-gray-900">
                <SectionInputField :field="field" :subscription="subscription"/>
            </div>
        </div>
    </div>
</template>
