<script setup>
import { __ } from '@wordpress/i18n';
import {useSubscriptionStore} from '../../stores/subscription';
import {useComponentStore} from '../../stores/component';

const subscriptionStore = useSubscriptionStore();
const componentStore = useComponentStore();
</script>
<template>
    <div>
        <div class="wpuf-flex wpuf-flex-col wpuf-px-4">
            <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-4">
                <h3>{{ __('Subscriptions', 'wp-user-frontend') }}</h3>
            <svg
                class="wpuf-w-6 wpuf-h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                ></path>
            </svg>
            </div>
            <ul class="wpuf-space-y-2 wpuf-text-lg">
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('publish'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'publish' ? 'wpuf-bg-gray-200' : ''"
                    class="wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    {{ __('Published', 'wp-user-frontend') }}
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('draft'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'draft' ? 'wpuf-bg-gray-200' : ''"
                    class="wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    {{ __('Drafts', 'wp-user-frontend') }}
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('pending'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'pending' ? 'wpuf-bg-gray-200' : ''"
                    class="wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    {{ __('Pending', 'wp-user-frontend') }}
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('trash'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'trash' ? 'wpuf-bg-gray-200' : ''"
                    class="wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    {{ __('Trash', 'wp-user-frontend') }}
                </li>
                <li
                    @click="[subscriptionStore.setSubscriptionsByStatus('all'), componentStore.setCurrentComponent('List')]"
                    :class="subscriptionStore.currentSubscriptionStatus === 'all' ? 'wpuf-bg-gray-200' : ''"
                    class="wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    {{ __('All Subscriptions', 'wp-user-frontend') }}
                </li>
            </ul>
        </div>
    </div>
</template>
