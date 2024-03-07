<script setup>
    import { ref } from 'vue';
    import {__} from '@wordpress/i18n';
    import { ExclamationCircleIcon } from '@heroicons/vue/20/solid';
    import VueDatePicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';
    import {useQuickEditStore} from '../../stores/quickEdit';
    import {useSubscriptionStore} from '../../stores/subscription';

    const subscriptionStore = useSubscriptionStore();

    const currentSubscription = subscriptionStore.currentSubscription;
    const date = ref(new Date(currentSubscription.post_date));
    const format = (date) => {
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();
    }
    const quickEditStore = useQuickEditStore();
    const isPlanPrivate = ref( currentSubscription.post_status === 'private' );
    const errors = ref( {
        planName: false,
        date: false,
        isPrivate: false,
    } );



</script>
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
                    :value="currentSubscription.post_title"
                />
                <div v-if="errors.planName" class="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3">
                    <ExclamationCircleIcon class="wpuf-h-5 wpuf-w-5 wpuf-text-red-500" aria-hidden="true" />
                </div>
            </div>
            <p v-if="errors.planName" class="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="email-error">{{ __('Not a valid plan name', 'wp-user-frontend') }}</p>
        </div>
        <div class="wpuf-px-2 sm:wpuf-px-2 lg:wpuf-px-2">
            <label for="date" class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Date', 'wp-user-frontend') }}</label>
            <div class="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
                <VueDatePicker v-model="date" :preview-format="format" />
            </div>
        </div>
        <div class="wpuf-px-2 sm:wpuf-px-2 lg:wpuf-px-2 wpuf-flex wpuf-justify-between wpuf-mt-6">
            <label for="plan-private" class="wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">{{ __('Private', 'wp-user-frontend') }}</label>
            <button
                @click="isPlanPrivate = !isPlanPrivate"
                type="button"
                :class="isPlanPrivate ? 'wpuf-bg-indigo-600' : 'wpuf-bg-gray-200'"
                class="wpuf-bg-gray-200 wpuf-relative wpuf-inline-flex wpuf-h-6 wpuf-w-11 wpuf-flex-shrink-0 wpuf-cursor-pointer wpuf-rounded-full wpuf-border-2 wpuf-border-transparent wpuf-transition-colors wpuf-duration-200 wpuf-ease-in-out focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-indigo-600 focus:wpuf-ring-offset-2" role="switch" aria-checked="true">
                <span aria-hidden="true"
                      :class="isPlanPrivate ? 'wpuf-translate-x-5' : 'wpuf-translate-x-0'"
                      class="wpuf-translate-x-0 wpuf-pointer-events-none wpuf-inline-block wpuf-h-5 wpuf-w-5 wpuf-transform wpuf-rounded-full wpuf-bg-white wpuf-shadow wpuf-ring-0 wpuf-transition wpuf-duration-200 wpuf-ease-in-out"></span>
            </button>
        </div>
        <div class="wpuf-flex wpuf-mt-8 wpuf-flex-row-reverse">
            <button type="button" class="wpuf-ml-4 wpuf-rounded-md wpuf-bg-indigo-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ __('Update', 'wp-user-frontend') }}
            </button>
            <button @click="quickEditStore.setQuickEditStatus(false)" type="button" class="wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50">
                {{ __('Cancel', 'wp-user-frontend') }}
            </button>
        </div>
    </div>
</template>
