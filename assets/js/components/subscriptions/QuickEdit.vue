<script setup>
    import {reactive, ref} from 'vue';
    import {__} from '@wordpress/i18n';
    import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';
    import VueDatePicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';
    import {useQuickEditStore} from '../../stores/quickEdit';
    import {useSubscriptionStore} from '../../stores/subscription';

    const subscriptionStore = useSubscriptionStore();

    const currentSubscription = subscriptionStore.currentSubscription;

    const planName = ref( currentSubscription.post_title );
    const date = ref(new Date(currentSubscription.post_date));
    const isPrivate = ref( currentSubscription.post_status === 'private' );
    const update = ref( {
        success: false,
        failed: false,
    } );

    const quickEditStore = useQuickEditStore();

    const errors = reactive( {
        planName: false,
        date: false,
        isPrivate: false,
    } );

    const handleDate = (modelData) => {
        date.value = modelData;
    }

    const resetErrors = () => {
        for (const item in errors) {
            errors[item] = false;
        }
    };

    const hasError = () => {
        for (const item in errors) {
            if (errors[item]) {
                return true;
            }
        }

        return false;
    };

    const updateSubscription = () => {
        resetErrors();

        if ( planName.value === '' ) {
            errors.planName = true;
        }

        // error if plan name contains #. PayPal doesn't allow # in package name
        if ( planName.value.includes('#') ) {
            errors.planName = true;
        }

        if ( typeof isPrivate.value !== 'boolean' ) {
            errors.isPrivate = true;
        }

        if ( date.value === null) {
            errors.date = true;

            return;
        }

        const year = date.value.getFullYear();
        const month = date.value.getMonth() + 1; // adding 1 because getMonth() returns 0-based month
        const day = date.value.getDate();
        const hours = date.value.getHours();
        const minutes = date.value.getMinutes();
        const seconds = date.value.getSeconds();

        if ( isNaN( year ) || isNaN( month ) || isNaN( day ) || isNaN( hours ) || isNaN( minutes ) || isNaN( seconds ) ) {
            errors.date = true;
        }

        if (hasError()) {
            return;
        }

        const promiseResult = subscriptionStore.updateSubscription( {
            id: currentSubscription.ID,
            planName: planName.value,
            mm: month,
            jj: day,
            aa: year,
            hh: hours,
            mn: minutes,
            ss: seconds,
            isPrivate: isPrivate.value,
        } );

        promiseResult.then((result) => {
            if (result.success) {
                update.value.success = true;
            } else {
                update.value.failed = true;
            }
        });
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
    <div class="wpuf-fixed wpuf-z-20 wpuf-top-1/3 wpuf-left-[calc(50%-5rem)] wpuf-w-1/4 wpuf-bg-white wpuf-p-6 wpuf-border wpuf-border-gray-200 wpuf-shadow dark:wpuf-bg-gray-800 dark:wpuf-border-gray-700 dark:hover:wpuf-bg-gray-700">
        <div class="wpuf-px-2 sm:wpuf-px-2 lg:wpuf-px-2">
            <label for="plan-name" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Plan name', 'wp-user-frontend') }}</label>
            <div class="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                <input
                    type="text"
                    name="plan-name"
                    id="plan-name"
                    :class="errors.planName ? 'wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500' : ''"
                    class="wpuf-block wpuf-w-full wpuf-rounded-md !wpuf-border-hidden wpuf-py-1.5 wpuf-pr-10 wpuf-ring-1 wpuf-ring-inset focus:wpuf-ring-2 focus:wpuf-ring-inset sm:wpuf-text-sm sm:wpuf-leading-6 !wpuf-shadow-none"
                    aria-invalid="true"
                    aria-describedby="plan-name-error"
                    v-model="planName"
                />
                <div v-if="errors.planName" class="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3">
                    <ExclamationCircleIcon class="wpuf-h-5 wpuf-w-5 wpuf-text-red-500" aria-hidden="true" />
                </div>
            </div>
            <p v-if="errors.planName" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ __('Not a valid plan name', 'wp-user-frontend') }}</p>
        </div>
        <div class="wpuf-px-2 sm:wpuf-px-2 lg:wpuf-px-2 wpuf-mt-4">
            <label for="date" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Date', 'wp-user-frontend') }}</label>
            <div
                :class="errors.date ? 'wpuf-border wpuf-border-red-500 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500' : 'wpuf-ring-indigo-600'"
                class="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                <VueDatePicker
                    textInput
                    v-model="date"
                    :state="!errors.date"
                    enable-seconds
                    @update:model-value="handleDate" />
            </div>
            <p v-if="errors.date" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ __('Not a valid date', 'wp-user-frontend') }}</p>
        </div>
        <div class="wpuf-px-2 sm:wpuf-px-2 lg:wpuf-px-2 wpuf-flex wpuf-justify-between wpuf-mt-6">
            <label for="plan-private" class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Private', 'wp-user-frontend') }}</label>
            <button
                @click="isPrivate = !isPrivate"
                type="button"
                :class="isPrivate ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2" role="switch" aria-checked="true">
                <span aria-hidden="true"
                      :class="isPrivate ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                      class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out"></span>
            </button>
            <p v-if="errors.isPrivate" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ __('Invalid Date', 'wp-user-frontend') }}</p>
        </div>
        <div class="wpuf-flex wpuf-mt-8 wpuf-flex-row-reverse">
            <button @click="updateSubscription" type="button" class="wpuf-ml-4 wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ __('Update', 'wp-user-frontend') }}
            </button>
            <button @click="quickEditStore.setQuickEditStatus(false)" type="button" class="wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                {{ __('Cancel', 'wp-user-frontend') }}
            </button>
        </div>
    </div>
    <div v-if="update.success" id="toast-success" class="wpuf-absolute wpuf-z-10 wpuf-flex wpuf-justify-between wpuf-items-center wpuf-w-full wpuf-max-w-xs wpuf-p-4 wpuf-mb-4 wpuf-text-gray-500 wpuf-bg-white wpuf-rounded-lg wpuf-shadow dark:wpuf-text-gray-400 dark:wpuf-bg-gray-800" role="alert">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
            <div class="wpuf-mr-2 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-w-8 wpuf-h-8 wpuf-text-green-500 wpuf-bg-green-100 wpuf-rounded-lg dark:wpuf-bg-green-800 dark:wpuf-text-green-200">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                </svg>
            </div>
            <div class="ms-3 wpuf-text-sm wpuf-font-normal">Updated successfully</div>
        </div>
        <button type="button" class="ms-auto wpuf--mx-1.5 wpuf--my-1.5 wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-900 wpuf-rounded-lg focus:wpuf-ring-2 focus:wpuf-ring-gray-300 wpuf-p-1.5 hover:wpuf-bg-gray-100 wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-h-8 wpuf-w-8 dark:wpuf-text-gray-500 dark:hover:wpuf-text-white dark:wpuf-bg-gray-800 dark:hover:wpuf-bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
            <span class="wpuf-sr-only">Close</span>
            <svg class="wpuf-w-3 wpuf-h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
    <div v-if="update.failed" id="toast-danger" class="wpuf-absolute wpuf-z-10 wpuf-flex wpuf-justify-between wpuf-items-center wpuf-w-full wpuf-max-w-xs wpuf-p-4 wpuf-mb-4 wpuf-text-gray-500 wpuf-bg-white wpuf-rounded-lg wpuf-shadow dark:wpuf-text-gray-400 dark:wpuf-bg-gray-800" role="alert">
        <div class="wpuf-flex wpuf-items-center wpuf-justify-between">
            <div class="wpuf-mr-2 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-w-8 wpuf-h-8 wpuf-text-red-500 wpuf-bg-red-100 wpuf-rounded-lg dark:wpuf-bg-red-800 dark:wpuf-text-red-200">
                <svg class="wpuf-w-5 wpuf-h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                </svg>
            </div>
            <div class="ms-3 wpuf-text-sm wpuf-font-normal">Error updating subscription</div>
        </div>
        <button type="button" class="ms-auto wpuf--mx-1.5 wpuf--my-1.5 wpuf-bg-white wpuf-text-gray-400 hover:wpuf-text-gray-900 wpuf-rounded-lg focus:wpuf-ring-2 focus:wpuf-ring-gray-300 wpuf-p-1.5 hover:wpuf-bg-gray-100 wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-h-8 wpuf-w-8 dark:wpuf-text-gray-500 dark:hover:wpuf-text-white dark:wpuf-bg-gray-800 dark:hover:wpuf-bg-gray-700" data-dismiss-target="#toast-danger" aria-label="Close">
            <span class="wpuf-sr-only">Close</span>
            <svg class="wpuf-w-3 wpuf-h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</template>
