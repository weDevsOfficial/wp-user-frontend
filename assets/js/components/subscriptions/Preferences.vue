<script setup>
import { ref, onMounted } from 'vue';
import { __ } from '@wordpress/i18n';
import { useSubscriptionStore } from '../../stores/subscription';
import { useNoticeStore } from '../../stores/notice';

const subscriptionStore = useSubscriptionStore();
const noticeStore = useNoticeStore();

const isSaving = ref(false);
const settings = ref({
    button_color: '' // Empty means use Tailwind's wpuf-bg-primary
});

onMounted(() => {
    loadSettings();
});

const loadSettings = async () => {
    try {
        const response = await fetch(`${wpufSubscriptions.rest_url}wpuf/v1/subscription-settings`, {
            headers: {
                'X-WP-Nonce': wpufSubscriptions.nonce
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            settings.value = { ...settings.value, ...data };
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
};

const saveSettings = async () => {
    isSaving.value = true;
    
    try {
        const response = await fetch(`${wpufSubscriptions.rest_url}wpuf/v1/subscription-settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpufSubscriptions.nonce
            },
            body: JSON.stringify(settings.value)
        });
        
        if (response.ok) {
            noticeStore.addNotice({
                type: 'success',
                message: __('Preferences saved successfully', 'wp-user-frontend')
            });
        } else {
            throw new Error('Failed to save settings');
        }
    } catch (error) {
        noticeStore.addNotice({
            type: 'error',
            message: __('Failed to save settings', 'wp-user-frontend')
        });
    } finally {
        isSaving.value = false;
    }
};
</script>

<template>
    <div class="wpuf-p-10 wpuf-max-w-4xl">
        <div class="wpuf-mb-6">
            <h2 class="wpuf-text-2xl wpuf-font-semibold wpuf-text-gray-900 wpuf-mb-2">
                {{ __('Subscription Preferences', 'wp-user-frontend') }}
            </h2>
            <p class="wpuf-text-sm wpuf-text-gray-600">
                {{ __('Configure subscription appearance preferences', 'wp-user-frontend') }}
            </p>
        </div>
        
        <div>
            <div class="wpuf-space-y-6">
                <!-- Button Appearance Section -->
                <div>
                    <h3 class="wpuf-text-lg wpuf-font-medium wpuf-text-gray-900 wpuf-mb-4">
                        {{ __('Color Settings', 'wp-user-frontend') }}
                    </h3>
                    
                    <div>
                        <div class="wpuf-flex wpuf-items-center wpuf-mb-1">
                            <label class="wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">
                                {{ __('Button Color', 'wp-user-frontend') }}
                            </label>
                            <span
                                class="wpuf-tooltip before:wpuf-bg-gray-700 before:wpuf-text-zinc-50 after:wpuf-border-t-gray-700 after:wpuf-border-x-transparent wpuf-cursor-pointer wpuf-ml-2 wpuf-z-10"
                                :data-tip="__('Custom color for subscription buttons. Leave empty to use the default primary color from theme.', 'wp-user-frontend')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none">
                                    <path d="M9.833 12.333H9V9h-.833M9 5.667h.008M16.5 9a7.5 7.5 0 1 1-15 0 7.5 7.5 0 1 1 15 0z"
                                          stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="wpuf-flex wpuf-items-center wpuf-space-x-3">
                            <div class="wpuf-flex wpuf-items-center wpuf-space-x-3">
                                <input
                                    v-model="settings.button_color"
                                    type="color"
                                    class="wpuf-h-10 wpuf-w-20 wpuf-rounded wpuf-border wpuf-border-gray-300 wpuf-cursor-pointer"
                                />
                                <input
                                    v-model="settings.button_color"
                                    type="text"
                                    placeholder="#4f46e5 or empty for default"
                                    class="wpuf-rounded-md wpuf-border-gray-300 wpuf-shadow-sm focus:wpuf-border-primary focus:wpuf-ring-primary wpuf-text-sm wpuf-w-48"
                                />
                            </div>
                            <div class="wpuf-ml-4 wpuf-w-32">
                                <button
                                    type="button"
                                    :class="settings.button_color ? '' : 'wpuf-bg-primary hover:wpuf-bg-primaryHover'"
                                    :style="settings.button_color ? { backgroundColor: settings.button_color } : {}"
                                    @mouseover="settings.button_color && ($event.target.style.filter = 'brightness(0.9)')"
                                    @mouseout="settings.button_color && ($event.target.style.filter = 'brightness(1)')"
                                    class="wpuf-subscription-buy-btn wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm wpuf-ring-0 wpuf-transition-all wpuf-duration-200 wpuf-leading-6">
                                    {{ __('Buy Now', 'wp-user-frontend') }}
                                </button>
                            </div>
                        </div>
                        <p class="wpuf-text-xs wpuf-text-gray-500 wpuf-mt-2">
                            {{ __('Leave empty to use the default primary color from your Tailwind configuration.', 'wp-user-frontend') }}
                        </p>
                    </div>
                </div>
                
                <!-- Save Button -->
                <div class="wpuf-pt-6 wpuf-flex wpuf-justify-end">
                    <button
                        @click="saveSettings"
                        :disabled="isSaving"
                        type="button"
                        class="wpuf-rounded-md wpuf-bg-primary wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-primary disabled:wpuf-opacity-50">
                        {{ isSaving ? __('Saving...', 'wp-user-frontend') : __('Save Preferences', 'wp-user-frontend') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>