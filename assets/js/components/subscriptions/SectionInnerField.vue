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
        class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 wpuf-p-4">
        <label
            :for="parentField.name"
            class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
            {{ parentField.label }}
        </label>
        <div class="wpuf-w-max wpuf-flex wpuf-inline-input">
            <SectionInputField v-for="field in parentField.fields"
                :field="field"
                :hiddenFields="hiddenFields" />
        </div>
    </div>
</template>
