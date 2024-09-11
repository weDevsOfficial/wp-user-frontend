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

const openTabs = [ 'overview', 'content_limit', 'payment_details' ];

closed.value = !openTabs.includes( subSection.value.id );

const toggleDependentFields = (fieldId, status) => {
    if (!wpufSubscriptions.dependentFields.hasOwnProperty( fieldId )) {
        return;
    }

    dependencyStore.modifierFieldStatus[fieldId] = status;
    let hiddenFields = [];

    for ( const modifierFieldName in dependencyStore.modifierFieldStatus ) {
        for (const field in wpufSubscriptions.dependentFields[modifierFieldName]) {
            if (!dependencyStore.modifierFieldStatus[modifierFieldName]) {
                hiddenFields.push( field );
            } else {
                hiddenFields = hiddenFields.filter( (item) => item !== field );
            }
        }
    }

    dependencyStore.hiddenFields = hiddenFields;
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
                    <span v-if="subSection.sub_label" class="wpuf-relative wpuf-m-0 wpuf-p-0 wpuf-ml-2 wpuf-mt-[1px] wpuf-italic wpuf-text-[11px] wpuf-text-gray-400">
                        {{ subSection.sub_label }}
                    </span>
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
        <div
            v-if="!closed && subSection.notice"
            class="wpuf-rounded-b-xl wpuf-bg-yellow-50 wpuf-p-4">
                <div class="wpuf-flex wpuf-items-center">
                    <div class="wpuf-flex-shrink-0">
                        <svg class="wpuf-h-5 wpuf-w-5 wpuf-text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="wpuf-ml-3">
                        <div class="wpuf-mt-2 wpuf-text-sm wpuf-text-yellow-700">
                            <p v-html="subSection.notice.message"></p>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</template>
