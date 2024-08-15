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
        <div class="wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-flex wpuf-items-center wpuf-pl-4">
        <label :for="parentField.name" v-html="parentField.label"></label>
            <div
                v-if="parentField.tooltip"
                class="wpuf-tooltip wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
                :data-tip="parentField.tooltip">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
                    <path d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
                          stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        <div class="wpuf-mr-2 wpuf-contents">
            <SectionInputField v-for="field in parentField.fields"
               :field="field" :fieldId="field.id" />
        </div>
        </div>
    </div>
</template>
