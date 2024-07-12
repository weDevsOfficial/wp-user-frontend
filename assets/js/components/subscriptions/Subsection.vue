<script setup>
import {inject, onMounted, provide, ref, toRefs} from 'vue';
import SectionInputField from './SectionInputField.vue';
import SectionInnerField from './SectionInnerField.vue';
import {useFieldDependencyStore} from '../../stores/fieldDependency';
import ProBadge from '../ProBadge.vue';
import ProTooltip from '../ProTooltip.vue';
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
const closed = ref( false );

const openTabs = [ 'overview', 'content_limits', 'payment_details' ];

closed.value = !openTabs.includes( subSection.value.id );

const toggleDependentFields = (fieldId, status) => {
    if (!dependencyStore.modifierFields.hasOwnProperty( fieldId )) {
        return;
    }

    for (const field in fields.value) {
        if (dependencyStore.modifierFields[fieldId].hasOwnProperty( field )) {
            if (!status) {
                if ( dependencyStore.modifierFields[fieldId][field] === 'hide' ) {
                    dependencyStore.hiddenFields.push( field );
                } else {
                    dependencyStore.hiddenFields = dependencyStore.hiddenFields.filter( (item) => item !== field );
                }
            } else {
                if ( dependencyStore.modifierFields[fieldId][field] === 'hide' ) {
                    dependencyStore.hiddenFields = dependencyStore.hiddenFields.filter( (item) => item !== field );
                } else {
                    dependencyStore.hiddenFields.push( field );
                }
            }
        }
    }
};

</script>
<template>
    <div
        class="wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-rounded-b-xl wpuf-mt-4 wpuf-mb-4">
        <h2 class="wpuf-m-0">
            <button type="button"
                    @click="closed = !closed"
                    :class="closed ? 'wpuf-rounded-xl' : 'wpuf-rounded-t-xl'"
                    class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-bg-gray-100 wpuf-gap-3">
                <span class="wpuf-flex">
                    {{ subSection.label }}
                    <span class="pro-icon-title wpuf-relative wpuf-pt-1 wpuf-group wpuf-ml-2">
                        <ProBadge v-if="subSection.is_pro" />
                        <ProTooltip />
                    </span>
                </span>
                <svg
                    :class="closed ? 'wpuf-rotate-90' : 'wpuf-rotate-180'"
                    data-accordion-icon class="wpuf-w-3 wpuf-h-3 shrink-0" aria-hidden="true"
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
                :field="field"
                :fieldId="fieldId"
                :serializeKey="field.serialize_key"
                :subscription="subscription"/>
            <SectionInnerField v-else
               :parentField="field"
               :fieldId="fieldId"
               :subscription="subscription"/>
        </div>
    </div>
</template>
