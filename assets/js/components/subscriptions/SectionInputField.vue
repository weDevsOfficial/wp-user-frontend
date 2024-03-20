<script setup>
import {computed, inject, ref, toRefs} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import {useSubscriptionStore} from '../../stores/subscription';

const subscriptionStore = useSubscriptionStore();

const wpufSubscriptions = inject( 'wpufSubscriptions' );
const currentSection = inject( 'currentSection' );
const subSection = inject( 'subSection' );

const props = defineProps( {
    field: Object,
    fieldId: String,
    hiddenFields: Array,
} );

const subscription = subscriptionStore.currentSubscription;

const { field, fieldId, hiddenFields } = toRefs( props );

const publishedDate = ref(new Date());

const getMetaValue = (key) => {
    if (!subscription.meta_value.hasOwnProperty( key )) {
        return '';
    }

    return subscription.meta_value[key];
};

const getFieldValue = () => {
    switch (field.value.db_type) {
        case 'meta':
            return getMetaValue( field.value.db_key );

        default:
            return subscription.hasOwnProperty(field.value.db_key) ? subscription[field.value.db_key] : '';
    }
};

const value = computed(() => {
    let fieldValue = getFieldValue( field.value.db_type, field.value.db_key );

    return getModifiedValue(field.value.type, fieldValue);
});

const getModifiedValue = (fieldType, fieldValue) => {
    switch (fieldType) {
        case 'switcher':
            return fieldValue === 'on' || fieldValue === 'yes' || fieldValue === 'private'

        case 'time-date':
            return new Date( fieldValue );

        case 'inline':
            return '';

        default:
            return fieldValue;

    }
};

const handleDate = (modelData) => {
    publishedDate.value = modelData;
};

const switchStatus = ref( value );

const toggleOnOff = () => {
    if (field.value.db_key === 'post_status') {
        subscriptionStore.modifySubscription( field.value.db_key, switchStatus.value ? 'publish' : 'private' );
    } else {
        subscriptionStore.setMetaValue( field.value.db_key, switchStatus.value ? 'off' : 'on' );
    }
};

const showField = computed(() => {
    return !hiddenFields.value.includes( fieldId.value );
});

</script>
<style scoped>
.dp__theme_light {
    --dp-background-color: none;
    --dp-text-color: none;
    --dp-hover-color: none;
    --dp-hover-text-color: none;
    --dp-hover-icon-color: none;
    --dp-primary-color: none;
    --dp-primary-disabled-color: none;
    --dp-primary-text-color: none;
    --dp-secondary-color: none;
    --dp-border-color: none;
    --dp-menu-border-color: none;
    --dp-border-color-hover: none;
    --dp-disabled-color: none;
    --dp-scroll-bar-background: none;
    --dp-scroll-bar-color: none;
    --dp-success-color: none;
    --dp-success-color-disabled: none;
    --dp-icon-color: none;
    --dp-danger-color: none;
    --dp-marker-color: none;
    --dp-tooltip-color: none;
    --dp-disabled-color-text: none;
    --dp-highlight-color: none;
    --dp-range-between-dates-background-color: none;
    --dp-range-between-dates-text-color: none;
    --dp-range-between-border-color: none;
}
</style>
<template>
    <div
        v-if="showField"
        :class="field.label ? 'sm:wpuf-grid sm:wpuf-grid-cols-3' : 'wpuf-block'"
        class="wpuf-items-start sm:wpuf-gap-4 wpuf-p-4">
        <label v-if="field.label"
               :for="field.name"
               class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
            {{ field.label }}
        </label>
        <div class="wpuf-w-max">
            <input
                v-if="field.type === 'input-text'"
                type="text"
                :value="value"
                :name="field.name"
                :id="field.name"
                class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-max-w-xs sm:wpuf-text-sm sm:wpuf-leading-6">
            <input
                v-if="field.type === 'input-number'"
                type="number"
                :value="value"
                :name="field.name"
                :id="field.name"
                class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-max-w-xs sm:wpuf-text-sm sm:wpuf-leading-6">
            <textarea
                v-if="field.type === 'textarea'"
                :name="field.name"
                :id="field.name"
                rows="3"
                class="wpuf-block wpuf-w-full wpuf-max-w-2xl wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">{{ value }}</textarea>
            <button
                v-if="field.type === 'switcher'"
                @click="[toggleOnOff(), $emit('toggleDependentFields', fieldId, switchStatus)]"
                type="button"
                :value="value"
                :class="switchStatus ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out"
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
                enable-seconds
                @update:model-value="handleDate" />
            <select v-if="field.type === 'select'"
                    :name="field.name"
                    :id="field.name">
                <option
                    v-for="(item, key) in field.options"
                    :value="key"
                    :key="key">{{ item }}</option>
            </select>
            <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ field.description }}</p>
        </div>
    </div>
</template>
