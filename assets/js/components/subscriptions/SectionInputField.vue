<script setup>
import {computed, inject, onMounted, ref, toRefs} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import {useSubscriptionStore} from '../../stores/subscription';
import {useFieldDependencyStore} from '../../stores/fieldDependency';

const emit = defineEmits(['toggleDependentFields']);

const subscriptionStore = useSubscriptionStore();

const subSection = inject( 'subSection' );

const props = defineProps( {
    field: Object,
    fieldId: String,
} );

const dependencyStore = useFieldDependencyStore();
const subscription = subscriptionStore.currentSubscription;

const { field, fieldId } = toRefs( props );

const publishedDate = ref(new Date());

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

</script>
<template>
    <div
        v-show="showField"
        :class="field.label ? 'wpuf-grid wpuf-grid-cols-3' : 'wpuf-block'"
        class="wpuf-gap-4 wpuf-p-4">
        <label v-if="field.label"
               :for="field.name"
               class="wpuf-block wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">
            {{ field.label }}
        </label>
        <div class="wpuf-w-full wpuf-col-span-2">
            <input
                v-if="field.type === 'input-text'"
                type="text"
                :value="value"
                :name="field.name"
                :id="field.name"
                @input="[modifySubscription($event), processInput($event)]"
                class="wpuf-w-full wpuf-rounded-md !wpuf-border-gray-300 wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">
            <input
                v-if="field.type === 'input-number'"
                type="number"
                :value="value"
                :name="field.name"
                :id="field.name"
                @input="[modifySubscription($event), processInput($event)]"
                class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">
            <textarea
                v-if="field.type === 'textarea'"
                :name="field.name"
                :id="field.name"
                rows="3"
                @input="[modifySubscription($event), processInput($event)]"
                class="wpuf-w-full wpuf-rounded-md wpuf-border-gray-300 wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm">{{ value }}</textarea>
            <button
                v-if="field.type === 'switcher'"
                @click="[toggleOnOff(), $emit('toggleDependentFields', fieldId, switchStatus)]"
                type="button"
                :value="value"
                :name="field.name"
                :id="field.name"
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
                :name="field.name"
                :uid="field.name"
                enable-seconds
                @update:model-value="handleDate" />
            <select v-if="field.type === 'select'"
                    :name="field.name"
                    :id="field.name"
                    class="wpuf-w-full !wpuf-max-w-full"
                    @input="[modifySubscription($event), processInput($event)]">
                <option
                    v-for="(item, key) in field.options"
                    :value="key"
                    :selected="key === value"
                    :key="key">{{ item }}</option>
            </select>
            <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ field.description }}</p>
        </div>
    </div>
</template>
