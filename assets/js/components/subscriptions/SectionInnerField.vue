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
        class="wpuf-grid wpuf-grid-cols-3 wpuf-p-4 wpuf-gap-4">
        <div class="wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-flex wpuf-items-center">
            <label :for="parentField.name" v-html="parentField.label"></label>
            <div
                v-if="parentField.tooltip"
                class="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
                :data-tip="parentField.tooltip">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
                    <path d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
                          stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>
        <div class="wpuf--ml-3 wpuf-flex wpuf-justify-between wpuf-col-span-2 wpuf--mr-3">
            <SectionInputField
                v-for="field in parentField.fields"
                :field="field"
                :fieldId="field.id"
                :isChildField="true"
            />
        </div>
    </div>
</template>
