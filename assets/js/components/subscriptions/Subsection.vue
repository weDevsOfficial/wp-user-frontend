<script setup>
import {computed, inject, provide, ref, toRefs} from 'vue';
import SectionInputField from './SectionInputField.vue';
import SectionInnerField from './SectionInnerField.vue';
import {useFieldDependencyStore} from '../../stores/fieldDependency';
const props = defineProps( {
    subSection: Object,
    subscription: Object,
    fields: Object,
} );
const {subSection, subscription, fields} = toRefs( props );

const wpufSubscriptions = inject( 'wpufSubscriptions' );

const dependencyStore = useFieldDependencyStore();

provide( 'subSection', subSection.value.id );

const showField = ref( true );
const hiddenFields = ref( [] );
const closed = ref( false );

const openTabs = [ 'overview', 'content_limits' ];

closed.value = !openTabs.includes( subSection.value.id );

const toggleDependentFields = (fieldId, status) => {
    if (!dependencyStore.modifierFields.hasOwnProperty( fieldId )) {
        return;
    }

    for (const field in fields.value) {
        if (dependencyStore.modifierFields[fieldId].hasOwnProperty( field )) {
            if (!status) {
                hiddenFields.value.push( field );
            } else {
                hiddenFields.value = hiddenFields.value.filter( (item) => item !== field );
            }
        }
    }
};

</script>
<template>
    <div
        class="wpuf-border wpuf-border-gray-200 wpuf-rounded-t-xl wpuf-rounded-b-xl wpuf-mt-4 wpuf-mb-4">
        <h2 class="wpuf-m-0">
            <button type="button"
                    @click="closed = !closed"
                    class="wpuf-rounded-t-xl wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-bg-gray-100 wpuf-gap-3">
                <span class="wpuf-flex">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 9C13 9.55228 12.5523 10 12 10C11.4477 10 11 9.55228 11 9C11 8.44772 11.4477 8 12 8C12.5523 8 13 8.44772 13 9Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 15C13 15.5523 12.5523 16 12 16C11.4477 16 11 15.5523 11 15C11 14.4477 11.4477 14 12 14C12.5523 14 13 14.4477 13 15Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 9C19 9.55228 18.5523 10 18 10C17.4477 10 17 9.55228 17 9C17 8.44772 17.4477 8 18 8C18.5523 8 19 8.44772 19 9Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 15C19 15.5523 18.5523 16 18 16C17.4477 16 17 15.5523 17 15C17 14.4477 17.4477 14 18 14C18.5523 14 19 14.4477 19 15Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 9C7 9.55228 6.55228 10 6 10C5.44772 10 5 9.55228 5 9C5 8.44772 5.44772 8 6 8C6.55228 8 7 8.44772 7 9Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 15C7 15.5523 6.55228 16 6 16C5.44772 16 5 15.5523 5 15C5 14.4477 5.44772 14 6 14C6.55228 14 7 14.4477 7 15Z" stroke="#111827" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    &nbsp;{{ subSection.label }}
                </span>
                <svg
                    :class="closed ? 'wpuf-rotate-90' : 'wpuf-rotate-180'"
                    data-accordion-icon class="wpuf-w-3 wpuf-h-3 wpuf-rotate-180 shrink-0" aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5 5 1 1 5"/>
                </svg>
            </button>
        </h2>
        <div
            v-show="!closed"
            v-for="(field, fieldId) in fields">
            <SectionInputField
                v-if="field.type !== 'inline'"
                @toggle-dependent-fields="toggleDependentFields"
                :hiddenFields="hiddenFields"
                :field="field"
                :fieldId="fieldId"
                :serializeKey="field.serialize_key"
                :subscription="subscription"/>
            <SectionInnerField v-else
               :parentField="field"
               :hiddenFields="hiddenFields"
               :fieldId="fieldId"
               :subscription="subscription"/>
        </div>
    </div>
</template>
