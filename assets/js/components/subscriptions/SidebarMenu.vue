<script setup>
import { __ } from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';
import {useComponentStore} from '../../stores/component';

const subscriptionStore = useSubscriptionStore();
const componentStore = useComponentStore();

</script>
<template>
    <div
    :class="subscriptionStore.isUnsavedPopupOpen ? 'wpuf-blur' : ''">
        <div class="wpuf-flex wpuf-flex-col wpuf-px-4">
            <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-4">
                <h3>{{ __('Subscriptions', 'wp-user-frontend') }}</h3>
            </div>
            <ul class="wpuf-space-y-2 wpuf-text-lg">
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('all'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'all' ? 'wpuf-bg-gray-50 wpuf-text-indigo-600' : ''"
                    class="wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-p-2 wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer">
                    {{ __('All Subscriptions', 'wp-user-frontend') }}
                    <span
                        v-if="subscriptionStore.allCount.all > 0"
                        class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-rounded-full wpuf-ring-1">
                        {{ subscriptionStore.allCount.all }}
                    </span>
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('publish'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'publish' ? 'wpuf-bg-gray-50' : ''"
                    class="wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-p-2 wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer">
                    {{ __('Published', 'wp-user-frontend') }}
                    <span
                        v-if="subscriptionStore.allCount.publish > 0"
                        class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-rounded-full wpuf-ring-1">
                        {{ subscriptionStore.allCount.publish }}
                    </span>
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('draft'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'draft' ? 'wpuf-bg-gray-50 wpuf-text-indigo-600' : ''"
                    class="wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-p-2 wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer">
                    {{ __('Drafts', 'wp-user-frontend') }}
                    <span
                        v-if="subscriptionStore.allCount.draft > 0"
                        class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-rounded-full wpuf-ring-1">
                        {{ subscriptionStore.allCount.draft }}
                    </span>
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('trash'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'trash' ? 'wpuf-bg-gray-50 wpuf-text-indigo-600' : ''"
                    class="wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-indigo-600 hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-p-2 wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer">
                    {{ __('Trash', 'wp-user-frontend') }}
                    <span
                        v-if="subscriptionStore.allCount.trash > 0"
                        class="wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-shadow-sm wpuf-rounded-full wpuf-ring-1">
                        {{ subscriptionStore.allCount.trash }}
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
