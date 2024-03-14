<script setup>

import {computed, ref, toRefs} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';

const props = defineProps( {
    field: Object,
    subscription: Object,
} );

const { field, subscription } = toRefs( props );

const switchStatus = ref( false );
const publishedDate = ref(new Date());

const value = computed(() => {
    switch (field.value.id) {
        case 'plan-name':
            return subscription.value.post_title;

        case 'plan-details':
            return subscription.value.post_content;

        case 'is-plan-private':
            switchStatus.value = subscription.value.post_status === 'private';

            return subscription.value.post_status === 'private';

        case 'plan-slug':
            return subscription.value.post_name;

        case 'published-on':
            publishedDate.value = new Date(subscription.value.post_date);

            return publishedDate;

        default:
            return '';
    }
});

const handleDate = (modelData) => {
    publishedDate.value = modelData;
};

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
    <div class="sm:wpuf-grid sm:wpuf-grid-cols-3 sm:wpuf-items-start sm:wpuf-gap-4 sm:wpuf-pb-4">
        <label for="plan-name" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 sm:wpuf-pt-1.5">
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
            <textarea
                v-if="field.type === 'textarea'"
                :name="field.name"
                :id="field.name"
                rows="3"
                class="wpuf-block wpuf-w-full wpuf-max-w-2xl wpuf-rounded-md wpuf-border-0 wpuf-py-1.5 wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 placeholder:wpuf-text-gray-400 focus:wpuf-ring-2 focus:wpuf-ring-inset focus:wpuf-ring-indigo-600 sm:wpuf-text-sm sm:wpuf-leading-6">{{ value }}</textarea>
            <button
                v-if="field.type === 'switcher'"
                @click="switchStatus = !switchStatus"
                type="button"
                :class="switchStatus ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2"
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
            <p class="wpuf-mt-3 wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600">{{ field.description }}</p>
        </div>
    </div>
</template>
