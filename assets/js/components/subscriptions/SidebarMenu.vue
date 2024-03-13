<script setup>
    import { __ } from '@wordpress/i18n';
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
                <li class=" wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer hover:wpuf-text-white">
                    <a href="#/published">{{ __('Published', 'wp-user-frontend') }}</a>
                </li>
                <li class=" wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    <a href="#/drafts">{{ __('Drafts', 'wp-user-frontend') }}</a>
                </li>
                <li class=" wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    <a href="#/pending">{{ __('Pending', 'wp-user-frontend') }}</a>
                </li>
                <li class=" wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    <a href="#/trash">{{ __('Trash', 'wp-user-frontend') }}</a>
                </li>
                <li class=" wpuf-p-2 hover:wpuf-bg-gray-200 hover:wpuf-cursor-pointer">
                    <a href="#/">{{ __('All Subscriptions', 'wp-user-frontend') }}</a>
                </li>
            </ul>
        </div>
    </div>
</template>
