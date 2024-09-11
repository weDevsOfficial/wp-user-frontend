<script setup>
import {ref, toRaw} from 'vue';
import {__} from '@wordpress/i18n';
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import {useQuickEditStore} from '../../stores/quickEdit';
import {useSubscriptionStore} from '../../stores/subscription';
import {useNoticeStore} from '../../stores/notice';
import UpdateButton from './UpdateButton.vue';
import {storeToRefs} from 'pinia';

const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

const currentSubscription = subscriptionStore.currentSubscription;
const title = ref(currentSubscription.post_title);

const date = ref(new Date(currentSubscription.post_date));

const { errors } = storeToRefs( toRaw(subscriptionStore) );

const fields = subscriptionStore.fieldNames;

const quickEditStore = useQuickEditStore();

const getFormattedDate = (date) => {
    const year = date.getFullYear();
    // adding 1 because getMonth() returns 0-based month
    const month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
    const day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    const hours = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
    const minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
    const seconds = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();

    return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
};

const handleDate = (modelData) => {
    currentSubscription.post_date = getFormattedDate(modelData);
};

const updateSubscription = () => {
    subscriptionStore.isUpdating = true;
    subscriptionStore.resetErrors();
    currentSubscription.post_title = title.value;

    if(!subscriptionStore.validateFields( 'quickEdit' )) {
        subscriptionStore.isUpdating = false;

        return;
    }

    const promiseResult = subscriptionStore.updateSubscription();

    promiseResult.then((result) => {
        if (result.success) {
            noticeStore.display = true;
            noticeStore.type = 'success';
            noticeStore.message = result.message;

            setTimeout(() => {
                noticeStore.display = false;
                noticeStore.type = '';
                noticeStore.message = '';
            }, 3000);

            quickEditStore.isQuickEdit = false;
        } else {
            subscriptionStore.updateError.status = true;
            subscriptionStore.updateError.message = result.message;
        }
    });

    subscriptionStore.isUpdating = false;
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
    --dp-border-color: #8c8f94;
    --dp-menu-border-color: #8c8f94;
    --dp-border-color-hover: #8c8f94;
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
    --dp-range-between-border-color: #8c8f94;
}
.dp__input_valid {
    box-shadow: none;
}
</style>
<template>
    <div class="wpuf-rounded-lg wpuf-fixed wpuf-z-20 wpuf-top-1/3 wpuf-left-[calc(50%-5rem)] wpuf-w-1/3 wpuf-bg-white wpuf-p-6 wpuf-border wpuf-border-gray-200 wpuf-shadow">
        <div class="wpuf-px-2">
            <label for="plan-name" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Plan name', 'wp-user-frontend') }}</label>
            <div class="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                <input
                    type="text"
                    name="plan-name"
                    id="plan-name"
                    :class="errors.planName ? '!wpuf-border-red-500 wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500' : ''"
                    class="wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1 wpuf-pl-3 wpuf-pr-10 wpuf-text-left wpuf-shadow-sm focus:!wpuf-border-indigo-500 focus:wpuf-outline-none focus:wpuf-ring-1 focus:wpuf-ring-indigo-500 sm:wpuf-text-sm"
                    aria-invalid="true"
                    aria-describedby="plan-name-error"
                    @change="title = $event.target.value"
                    :value="title"
                />
                <div v-if="errors.planName" class="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3">
                    <ExclamationCircleIcon class="wpuf-h-5 wpuf-w-5 wpuf-text-red-500" aria-hidden="true" />
                </div>
            </div>
            <p v-if="errors.planName" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ errors.planName.message }}</p>
        </div>
        <div class="wpuf-px-2 wpuf-mt-4">
            <label for="date" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Date', 'wp-user-frontend') }}</label>
            <div
                :class="errors.date ? 'wpuf-border wpuf-border-red-500 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500' : 'wpuf-ring-indigo-600'"
                class="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                <VueDatePicker
                    textInput
                    v-model="date"
                    :state="!errors.date"
                    :is-24="false"
                    enable-seconds
                    @update:model-value="handleDate" />
            </div>
            <p v-if="errors.date" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ __('Not a valid date', 'wp-user-frontend') }}</p>
        </div>
        <div class="wpuf-px-2 wpuf-mt-4">
            <p v-if="subscriptionStore.updateError.status" id="filled_error_help" class="wpuf-mt-2 wpuf-text-xs wpuf-text-red-600">
                {{ subscriptionStore.updateError.message }}</p>
        </div>
        <div class="wpuf-flex wpuf-mt-8 wpuf-flex-row-reverse">
            <UpdateButton
                @update-subscription="updateSubscription" />
            <button
                @click="[quickEditStore.setQuickEditStatus(false), subscriptionStore.errors = {}]"
                :disabled="subscriptionStore.isUpdating"
                type="button"
                :class="subscriptionStore.isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''"
                class="wpuf-rounded-lg wpuf-mr-4 wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                {{ __('Cancel', 'wp-user-frontend') }}
            </button>
        </div>
    </div>
</template>
