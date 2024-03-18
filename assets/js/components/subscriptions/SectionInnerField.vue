<script setup>

import {toRefs} from 'vue';
import SectionInputField from './SectionInputField.vue';

const props = defineProps( {
    parentField: Object,
    subscription: Object,
} );

const { parentField, subscription } = toRefs( props );

</script>
<template>
    <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
        <label for="plan-name" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
            {{ parentField.label }}
        </label>
        <div class="wpuf-w-max wpuf-flex">
            <SectionInputField v-for="field in parentField.fields"
                :field="field"
                :subscription="subscription" />
        </div>
    </div>
</template>
