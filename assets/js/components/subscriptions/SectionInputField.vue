<script setup>
import {computed, inject, onMounted, ref, toRaw, toRefs, watch} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import Multiselect from '@vueform/multiselect';
import {useSubscriptionStore} from '../../stores/subscription';
import {useFieldDependencyStore} from '../../stores/fieldDependency';
import {__} from '@wordpress/i18n';
import ProBadge from '../ProBadge.vue';
import ProTooltip from '../ProTooltip.vue';
import {storeToRefs} from 'pinia';

const emit = defineEmits(['toggleDependentFields']);

const subscriptionStore = useSubscriptionStore();

const subSection = inject( 'subSection' );

const props = defineProps( {
    field: Object,
    fieldId: String,
    isChildField: {
        type: Boolean,
        default: false,
    },
} );

const dependencyStore = useFieldDependencyStore();
const subscription = subscriptionStore.currentSubscription;
const errors = storeToRefs( subscriptionStore.errors );

const { field, fieldId, isChildField } = toRefs( props );

const publishedDate = ref( new Date() );

const showProBadge = computed( () => {
    return field.value.is_pro && !wpufSubscriptions.isProActive;
} );

const getFieldValue = () => {
    switch (field.value.db_type) {
        case 'meta':
            return subscriptionStore.getMetaValue( field.value.db_key );

        case 'meta_serialized':
            return subscriptionStore.getSerializedMetaValue( field.value.db_key, field.value.serialize_key );

        default:
            return subscription.hasOwnProperty(field.value.db_key) ? subscription[field.value.db_key] : '';
    }
};

const value = computed(() => {
    const fieldValue = getFieldValue( field.value.db_type, field.value.db_key );

    return getModifiedValue( field.value.type, fieldValue );
});

const getModifiedValue = (fieldType, fieldValue) => {
    switch (fieldType) {
        case 'switcher':
            return fieldValue === 'on' || fieldValue === 'yes' || fieldValue === 'private'

        case 'time-date':
            return new Date( fieldValue );

        case 'inline':
            return '';

        case 'multi-select':
            return Array.isArray(fieldValue) ? fieldValue : [];

        default:
            return fieldValue;

    }
};

const handleDate = (modelData) => {
    publishedDate.value = modelData;

    if (field.value.db_type === 'post') {
        subscriptionStore.modifyCurrentSubscription( field.value.db_key, modelData );
    } else {
        subscriptionStore.setMetaValue( field.value.db_key, modelData );
    }
};

const switchStatus = ref( value );

const toggleOnOff = () => {
    if (field.value.db_key === 'post_status') {
        subscriptionStore.modifyCurrentSubscription( field.value.db_key, switchStatus.value ? 'publish' : 'private' );
    } else {
        subscriptionStore.setMetaValue( field.value.db_key, switchStatus.value ? 'off' : 'on' );
    }
};

const showField = computed(() => {
    return !dependencyStore.hiddenFields.includes( fieldId.value );
});

const modifySubscription = (event) => {
    switch (field.value.db_type) {
        case 'meta_serialized':
            subscriptionStore.modifyCurrentSubscription( field.value.db_key, event.target.value, field.value.serialize_key );
            break;

        case 'post':
            subscriptionStore.modifyCurrentSubscription( field.value.db_key, event.target.value );
            break;

        default:
            subscriptionStore.setMetaValue( field.value.db_key, event.target.value );

    }
};

const processInput = (event) => {
    if (field.value.db_key === 'post_title') {
        subscriptionStore.modifyCurrentSubscription( 'post_name', event.target.value.replace(/\s+/g, '-').toLowerCase() );
    }
};

const processNumber = (event) => {
    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', '.'];
    if (!allowedKeys.includes(event.key) && isNaN(Number(event.key))) {
        event.preventDefault();
    }
}

const options = computed( () => {
    if ( ! wpufSubscriptions.fields.advanced_configuration.hasOwnProperty( 'taxonomy_restriction' ) ) {
        return [];
    }

    return wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[field.value.id].term_fields;
} );

const onMultiSelectChange = ( currentValue ) => {
    const tempObj = toRaw( subscriptionStore.taxonomyRestriction );

    tempObj[fieldId.value] = currentValue;

    subscriptionStore.$patch({
        taxonomyRestriction: tempObj,
    });
};

const fieldLabelClasses = computed(() => {
    const classes = ['wpuf-gap-4'];
    if (field.value.label) {
        classes.push('wpuf-grid wpuf-grid-cols-3 wpuf-p-4');
    } else {
        classes.push('wpuf-py-4 wpuf-pl-3 wpuf-pr-4');
    }

    if (isChildField.value) classes.push('wpuf-col-span-2 wpuf-w-1/2');

    return classes;
});

onMounted(() => {
    if ( field.value.type === 'switcher' ) {
        emit('toggleDependentFields', fieldId.value, switchStatus.value);
    }
});

onMounted(() => {
    if ( field.value.type !== 'multi-select' ) {
        return;
    }

    // first get all the term fields as an array
    const termFields = wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[field.value.id].term_fields.map( ( item ) => {
        return item.value;
    } );

    let selectedValues = [];

    // then check if the selected values are in the term fields array
    value.value.map( ( item ) => {
        if (termFields.includes( item )) {
            selectedValues.push( item );
        }
    } );

    const tempObj = toRaw( subscriptionStore.taxonomyRestriction );

    tempObj[fieldId.value] = selectedValues;

    // update the store
    subscriptionStore.$patch({
        taxonomyRestriction: tempObj,
    });
});

</script>

<style>
@import '@vueform/multiselect/themes/default.css';

.multiselect-caret {
    margin-top: 0.25rem;
}

.dp__input {
    font-size: .875rem !important;
    padding-top: .25rem !important;
    padding-bottom: .25rem !important;
}
</style>

<template>
    <div
        v-show="showField"
        :class="fieldLabelClasses">
        <div
            v-if="field.label"
           class="wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-flex wpuf-items-center"
        >
            <label :for="field.name" v-html="field.label"></label>
            <span
                v-if="field.tooltip"
                class="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
                 :data-tip="field.tooltip">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
                    <path d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
                          stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
            &nbsp;&nbsp;
            <span class="pro-icon-title wpuf-relative wpuf-pt-1 wpuf-group">
                <ProBadge v-if="showProBadge" />
                <ProTooltip />
            </span>
        </div>
        <div
            class="wpuf-w-full wpuf-col-span-2 wpuf-relative wpuf-group">
            <div
                v-if="showProBadge"
                class="wpuf-hidden wpuf-rounded-md group-hover:wpuf-flex group-hover:wpuf-cursor-pointer wpuf-absolute wpuf-items-center wpuf-justify-center wpuf-bg-black/25 wpuf-z-10 wpuf-p-4 wpuf-w-[104%] wpuf-h-[180%] wpuf-top-[-40%] wpuf-left-[-2%]">
                <a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&amp;utm_medium=popup"
                   target="_blank"
                   class="wpuf-inline-flex wpuf-align-center wpuf-p-2 wpuf-bg-amber-600 wpuf-text-white hover:wpuf-text-white wpuf-rounded-md">
                    {{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }}
                    <span class="pro-icon icon-white">
                        <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19.2131 4.11564C19.2161 4.16916 19.2121 4.22364 19.1983 4.27775L17.9646 10.5323C17.9024 10.7741 17.6796 10.9441 17.4235 10.9455L10.0216 10.9818H10.0188H2.61682C2.35933 10.9818 2.13495 10.8112 2.07275 10.5681L0.839103 4.29542C0.824897 4.23985 0.820785 4.18385 0.824374 4.12895C0.34714 3.98269 0 3.54829 0 3.03636C0 2.40473 0.528224 1.89091 1.17757 1.89091C1.82692 1.89091 2.35514 2.40473 2.35514 3.03636C2.35514 3.39207 2.18759 3.71033 1.92523 3.92058L3.46976 5.43433C3.86011 5.81695 4.40179 6.03629 4.95596 6.03629C5.61122 6.03629 6.23596 5.7336 6.62938 5.22647L9.1677 1.95491C8.95447 1.74764 8.82243 1.46124 8.82243 1.14545C8.82243 0.513818 9.35065 0 10 0C10.6493 0 11.1776 0.513818 11.1776 1.14545C11.1776 1.45178 11.0526 1.72982 10.8505 1.93556L10.8526 1.93811L13.3726 5.21869C13.7658 5.73069 14.3928 6.03636 15.0499 6.03636C15.6092 6.03636 16.1351 5.82451 16.5305 5.43978L18.0848 3.92793C17.8169 3.71775 17.6449 3.39644 17.6449 3.03636C17.6449 2.40473 18.1731 1.89091 18.8224 1.89091C19.4718 1.89091 20 2.40473 20 3.03636C20 3.53462 19.6707 3.9584 19.2131 4.11564ZM17.8443 12.6909C17.8443 12.3897 17.5932 12.1455 17.2835 12.1455H2.77884C2.46916 12.1455 2.21809 12.3897 2.21809 12.6909V14C2.21809 14.3012 2.46916 14.5455 2.77884 14.5455H17.2835C17.5932 14.5455 17.8443 14.3012 17.8443 14V12.6909Z" fill="#FB9A28" />
                        </svg>
                    </span>
                </a>
            </div>
            <input
                v-if="field.type === 'input-text'"
                type="text"
                :value="value"
                :name="field.name"
                :id="field.name"
                :placeholder="field.placeholder ? field.placeholder : ''"
                @input="[modifySubscription($event), processInput($event)]"
                :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">
            <input
                v-if="field.type === 'input-number'"
                type="number"
                :value="value"
                :name="field.name"
                :id="field.name"
                :placeholder="field.placeholder ? field.placeholder : ''"
                @input="[modifySubscription($event), processInput($event)]"
                @keydown="processNumber"
                min="-1"
                :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">
            <textarea
                v-if="field.type === 'textarea'"
                :name="field.name"
                :id="field.name"
                :placeholder="field.placeholder ? field.placeholder : ''"
                rows="3"
                @input="[modifySubscription($event), processInput($event)]"
                :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">{{ value }}</textarea>
            <button
                v-if="field.type === 'switcher'"
                @click="[toggleOnOff(), $emit('toggleDependentFields', fieldId, switchStatus)]"
                type="button"
                :value="value"
                :name="field.name"
                :id="field.name"
                :class="switchStatus ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                class="placeholder:wpuf-text-gray-400 wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out"
                role="switch">
                <span
                    aria-hidden="true"
                    :class="switchStatus ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                    class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out">
                </span>
            </button>
            <VueDatePicker
                v-if="field.type === 'time-date'"
                textInput
                v-model="publishedDate"
                :name="field.name"
                :uid="field.name"
                enable-seconds
                @update:model-value="handleDate" />
            <select v-if="field.type === 'select'"
                    :name="field.name"
                    :id="field.name"
                    :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                    class="wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"
                    @input="[modifySubscription($event), processInput($event)]">
                <option
                    v-for="(item, key) in field.options"
                    :value="key"
                    :selected="key === value"
                    :key="key">{{ item }}</option>
            </select>
            <Multiselect
                v-if="field.type === 'multi-select'"
                :id="field.id"
                :name="field.name"
                :placeholder="field.placeholder ? field.placeholder : __( 'Select options', 'wp-user-frontend' )"
                v-model="value"
                :options="options"
                mode="tags"
                @input="onMultiSelectChange"
                :close-on-select="false"
                :classes="{
                    container: 'wpuf-w-full wpuf-border wpuf-rounded-md !wpuf-border-gray-300 wpuf-bg-white wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm',
                    wrapper: 'wpuf-min-h-max wpuf-align-center wpuf-cursor-pointer wpuf-flex wpuf-justify-end wpuf-w-full wpuf-relative',
                    placeholder: 'wpuf-ml-2 wpuf-flex wpuf-items-center wpuf-h-full wpuf-absolute wpuf-left-0 wpuf-top-0 wpuf-pointer-events-none wpuf-bg-transparent wpuf-form-color-placeholder rtl:wpuf-left-auto rtl:wpuf-right-0 rtl:wpuf-pl-0 wpuf-form-pl-input rtl:wpuf-form-pr-input',
                    tags: 'wpuf-h-max wpuf-flex-grow wpuf-flex-shrink wpuf-flex wpuf-flex-wrap wpuf-items-center wpuf-pl-1 wpuf-pt-1 wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2',
                    tag: 'wpuf-bg-indigo-600 wpuf-text-white wpuf-text-sm wpuf-font-semibold wpuf-py-0.5 wpuf-pl-2 wpuf-rounded wpuf-mr-1 wpuf-mb-1 wpuf-flex wpuf-items-center wpuf-whitespace-nowrap wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2 rtl:wpuf-mr-0 rtl:wpuf-ml-1',
                    clear: 'wpuf-mt-1 wpuf-pr-2',
                }"
            />
            <div
                v-if="field.description"
                class="label">
                <span class="label-text-alt">{{ field.description }}</span>
            </div>
            <div
                v-if="subscriptionStore.errors[fieldId]"
                class="label">
                <span class="label-text-alt wpuf-text-red-500">{{ subscriptionStore.errors[fieldId].message }}</span>
            </div>
        </div>
    </div>
</template>
