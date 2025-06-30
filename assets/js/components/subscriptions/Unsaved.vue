<script setup>
import {__} from '@wordpress/i18n';

</script>
<template>
    <div class="wpuf-h-80">
        <div class="wpuf-fixed wpuf-inset-0 wpuf-z-10 bg-secondary-700/50"></div>
        <div class="wpuf-fixed wpuf-inset-0 wpuf-z-50 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-p-4 sm:wpuf-p-0">
            <div class="wpuf-mx-auto wpuf-w-2/5 wpuf-overflow-hidden wpuf-rounded-lg wpuf-bg-white wpuf-shadow-xl">
                <div class="wpuf-relative wpuf-p-5">
                    <div class="wpuf-text-center">
                        <div class="wpuf-mx-auto wpuf-mb-5 wpuf-flex wpuf-h-10 wpuf-w-10 wpuf-items-center wpuf-justify-center wpuf-rounded-full wpuf-bg-yellow-100 wpuf-text-yellow-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpuf-h-6 wpuf-w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="wpuf-text-lg wpuf-font-medium text-secondary-900">Unsaved changes</h3>
                            <div class="wpuf-mt-2 wpuf-text-sm text-secondary-500">
                                <p class="wpuf-text-base">{{ __( 'You have unsaved changes in your current subscription.', 'wp-user-frontend' ) }}<br>{{ __( 'Navigating away from this page will cause your work to be lost.', 'wp-user-frontend' ) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="wpuf-mt-5 wpuf-flex wpuf-justify-end wpuf-gap-3">
                        <button @click="$emit('goToList')" type="button" class="wpuf-rounded-lg wpuf-border wpuf-border-red-500 wpuf-bg-red-500 wpuf-px-4 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-shadow-sm wpuf-transition-all hover:wpuf-border-red-700 hover:wpuf-bg-red-700 focus:wpuf-ring focus:wpuf-ring-red-200">{{ __('Discard Changes', 'wp-user-frontend') }}</button>
                        <button @click="$emit('closePopup')" type="button" class="wpuf-rounded-lg wpuf-border wpuf-border-blue-500 wpuf-bg-blue-500 wpuf-px-4 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-shadow-sm wpuf-transition-all hover:wpuf-border-blue-700 hover:wpuf-bg-blue-700 focus:wpuf-ring focus:wpuf-ring-blue-200">{{ __('Continue', 'wp-user-frontend') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
