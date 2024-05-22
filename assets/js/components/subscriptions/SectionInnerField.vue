<script setup>
import {computed, toRefs} from 'vue';
import SectionInputField from './SectionInputField.vue';

const props = defineProps( {
    parentField: Object,
    fieldId: String,
    hiddenFields: Array,
} );

const { parentField, fieldId, hiddenFields } = toRefs( props );

const showField = computed(() => {
    return !hiddenFields.value.includes( fieldId.value );
});

</script>
<template>
    <div
        v-show="showField"
        class="wpuf-grid wpuf-grid-cols-3">
        <label
            :for="parentField.name"
            class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 wpuf-pl-4">
            {{ parentField.label }}
        </label>
        <div class="wpuf-mr-2 wpuf-contents">
            <SectionInputField v-for="field in parentField.fields"
               :field="field"
               :hiddenFields="hiddenFields" />
        </div>
    </div>
</template>
