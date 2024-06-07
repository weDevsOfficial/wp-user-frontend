<script setup>
import {computed, toRefs} from 'vue';
import SectionInputField from './SectionInputField.vue';
import {useFieldDependencyStore} from '../../stores/fieldDependency';

const props = defineProps( {
    parentField: Object,
    fieldId: String,
} );

const { parentField, fieldId } = toRefs( props );

const dependencyStore = useFieldDependencyStore();

const showField = computed(() => {
    return !dependencyStore.hiddenFields.includes( fieldId.value );
});

</script>
<template>
    <div
        v-show="showField"
        class="wpuf-grid wpuf-grid-cols-3">
        <label
            :for="parentField.name"
            class="wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-pl-4">
            {{ parentField.label }}
        </label>
        <div class="wpuf-mr-2 wpuf-contents">
            <SectionInputField v-for="field in parentField.fields"
               :field="field" :fieldId="field.id" />
        </div>
    </div>
</template>
