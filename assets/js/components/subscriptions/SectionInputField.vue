<script setup>
import {computed, inject, onMounted, ref, toRaw, toRefs, watch} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import Multiselect from '@vueform/multiselect';
import {useSubscriptionStore} from '../../stores/subscription';
import {useFieldDependencyStore} from '../../stores/fieldDependency';
import {__} from '@wordpress/i18n';
import ProBadge from '../ProBadge.vue';
import ProTooltip from '../ProTooltip.vue';

const emit = defineEmits(['toggleDependentFields']);

const subscriptionStore = useSubscriptionStore();

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

const { field, fieldId, isChildField } = toRefs( props );

const publishedDate = ref( new Date() );

const showProBadge = computed( () => {
    return field.value.is_pro && !wpufSubscriptions.isProActive;
} );

const getFieldValue = () => {
    // Special handling for taxonomy restriction fields
    if ( field.value.type === 'multi-select' && field.value.id ) {
        if ( field.value.id.startsWith( 'view_' ) ) {
            // This is a view restriction field
            const value = subscriptionStore.taxonomyViewRestriction[field.value.id] || [];
            return value;
        } else {
            // This is a regular taxonomy restriction field
            const value = subscriptionStore.taxonomyRestriction[field.value.id] || [];
            return value;
        }
    }
    
    // Regular field handling
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
    const modifiedValue = getModifiedValue( field.value.type, fieldValue );
    
    return modifiedValue;
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
    // Handle both taxonomy_restriction and taxonomy_view_restriction sections
    if ( field.value.id && field.value.id.startsWith( 'view_' ) ) {
        // This is a view restriction field
        const taxonomyName = field.value.id.replace( 'view_', '' );
        if ( wpufSubscriptions.fields.advanced_configuration.hasOwnProperty( 'taxonomy_view_restriction' ) &&
             wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction.hasOwnProperty( taxonomyName ) ) {
            return wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction[taxonomyName].term_fields;
        }
    } else {
        // This is a regular taxonomy restriction field
        if ( wpufSubscriptions.fields.advanced_configuration.hasOwnProperty( 'taxonomy_restriction' ) &&
             wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction.hasOwnProperty( field.value.id ) ) {
            return wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[field.value.id].term_fields;
        }
    }

    return [];
} );

const onMultiSelectChange = ( currentValue ) => {
    // Handle both taxonomy restriction types
    if ( field.value.id && field.value.id.startsWith( 'view_' ) ) {
        // This is a view restriction field - use a separate store property
        const tempObj = toRaw( subscriptionStore.taxonomyViewRestriction || {} );
        tempObj[field.value.id] = currentValue;  // Use field.value.id instead of fieldId.value
        subscriptionStore.$patch({
            taxonomyViewRestriction: tempObj,
        });
    } else {
        // This is a regular taxonomy restriction field
        const tempObj = toRaw( subscriptionStore.taxonomyRestriction );
        tempObj[field.value.id] = currentValue;  // Use field.value.id instead of fieldId.value
        subscriptionStore.$patch({
            taxonomyRestriction: tempObj,
        });
    }
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

    // Only initialize if the store doesn't already have data for this field
    const hasExistingData = field.value.id.startsWith( 'view_' ) 
        ? subscriptionStore.taxonomyViewRestriction[field.value.id] 
        : subscriptionStore.taxonomyRestriction[field.value.id];
    
    if ( hasExistingData && hasExistingData.length > 0 ) {
        // Data already exists in store, don't override
        return;
    }

    let termFields = [];
    let selectedValues = [];

    // Handle both taxonomy restriction types
    if ( field.value.id && field.value.id.startsWith( 'view_' ) ) {
        // This is a view restriction field
        const taxonomyName = field.value.id.replace( 'view_', '' );
        if ( wpufSubscriptions.fields.advanced_configuration.hasOwnProperty( 'taxonomy_view_restriction' ) &&
             wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction.hasOwnProperty( taxonomyName ) ) {
            termFields = wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction[taxonomyName].term_fields.map( ( item ) => {
                return item.value;
            } );
        }
    } else {
        // This is a regular taxonomy restriction field
        if ( wpufSubscriptions.fields.advanced_configuration.hasOwnProperty( 'taxonomy_restriction' ) &&
             wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction.hasOwnProperty( field.value.id ) ) {
            termFields = wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[field.value.id].term_fields.map( ( item ) => {
                return item.value;
            } );
        }
    }

    // Check if the selected values are in the term fields array
    if ( value.value && Array.isArray( value.value ) ) {
        value.value.forEach( ( item ) => {
            if ( termFields.includes( item ) ) {
                selectedValues.push( item );
            }
        } );
    }

    // Update the appropriate store
    if ( field.value.id && field.value.id.startsWith( 'view_' ) ) {
        const tempObj = toRaw( subscriptionStore.taxonomyViewRestriction || {} );
        tempObj[field.value.id] = selectedValues;
        subscriptionStore.$patch({
            taxonomyViewRestriction: tempObj,
        });
    } else {
        const tempObj = toRaw( subscriptionStore.taxonomyRestriction );
        tempObj[field.value.id] = selectedValues;
        subscriptionStore.$patch({
            taxonomyRestriction: tempObj,
        });
    }
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
.dp__input_focus {
    --tw-ring-color: #3DB981;
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
                class="wpuf-hidden wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-emerald-200 group-hover:wpuf-flex group-hover:wpuf-cursor-pointer wpuf-absolute wpuf-items-center wpuf-justify-center wpuf-bg-emerald-50/50 wpuf-backdrop-blur-sm wpuf-z-10 wpuf-p-4 wpuf-w-[104%] wpuf-h-[180%] wpuf-top-[-40%] wpuf-left-[-2%]">
                <a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&amp;utm_medium=popup"
                   target="_blank"
                   class="wpuf-button button-upgrade-to-pro wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-bg-emerald-600 focus:wpuf-bg-emerald-700 hover:wpuf-bg-emerald-700 wpuf-text-white hover:wpuf-text-white wpuf-rounded-md wpuf-gap-2 wpuf-font-medium wpuf-text-sm">
                    {{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }}
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
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm !wpuf-shadow-none">
            <input
                v-if="field.type === 'input-number'"
                type="number"
                :value="value"
                :name="field.name"
                :id="field.name"
                :placeholder="field.placeholder ? field.placeholder : ''"
                @input="[modifySubscription($event), processInput($event)]"
                @keydown="processNumber"
                :min="field.min"
                :step="field.step"
                :default="field.default"
                :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm">
            <textarea
                v-if="field.type === 'textarea'"
                :name="field.name"
                :id="field.name"
                :placeholder="field.placeholder ? field.placeholder : ''"
                rows="3"
                @input="[modifySubscription($event), processInput($event)]"
                :class="subscriptionStore.errors[fieldId] ? '!wpuf-border-red-500' : '!wpuf-border-gray-300'"
                class="placeholder:wpuf-text-gray-400 wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm">{{ value }}</textarea>
            <button
                v-if="field.type === 'switcher'"
                @click="[toggleOnOff(), $emit('toggleDependentFields', fieldId, switchStatus)]"
                type="button"
                :value="value"
                :name="field.name"
                :id="field.name"
                :class="switchStatus ? 'wpuf-bg-primary' : 'wpuf-bg-gray-200'"
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
                    class="wpuf-w-full !wpuf-max-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm"
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
                    container: 'wpuf-w-full wpuf-border wpuf-rounded-md !wpuf-border-gray-300 wpuf-bg-white wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-primaryHover focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-primaryHover sm:wpuf-text-sm',
                    wrapper: 'wpuf-min-h-max wpuf-align-center wpuf-cursor-pointer wpuf-flex wpuf-justify-end wpuf-w-full wpuf-relative',
                    placeholder: 'wpuf-ml-2 wpuf-flex wpuf-items-center wpuf-h-full wpuf-absolute wpuf-left-0 wpuf-top-0 wpuf-pointer-events-none wpuf-bg-transparent wpuf-form-color-placeholder rtl:wpuf-left-auto rtl:wpuf-right-0 rtl:wpuf-pl-0 wpuf-form-pl-input rtl:wpuf-form-pr-input',
                    tags: 'wpuf-h-max wpuf-flex-grow wpuf-flex-shrink wpuf-flex wpuf-flex-wrap wpuf-items-center wpuf-pl-1 wpuf-pt-1 wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2',
                    tag: 'wpuf-bg-primary wpuf-text-white wpuf-text-sm wpuf-font-semibold wpuf-py-0.5 wpuf-pl-2 wpuf-rounded wpuf-mr-1 wpuf-mb-1 wpuf-flex wpuf-items-center wpuf-whitespace-nowrap wpuf-min-w-0 rtl:wpuf-pl-0 rtl:wpuf-pr-2 rtl:wpuf-mr-0 rtl:wpuf-ml-1',
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
