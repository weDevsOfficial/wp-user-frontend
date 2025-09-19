<template>
    <div class="wpuf-ai-form-wrapper wpuf-font-sans wpuf-bg-white wpuf-w-full wpuf-h-screen wpuf-overflow-hidden wpuf-relative">
        <div class="wpuf-ai-form-container wpuf-h-screen wpuf-overflow-hidden">
            <div class="wpuf-ai-form-content wpuf-bg-white wpuf-rounded-lg wpuf-h-full wpuf-flex wpuf-flex-col">
                
                <!-- Header Section -->
                <div class="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-px-6 wpuf-pt-6 wpuf-pb-3">
                    <!-- Left Side - Logo and Text -->
                    <div class="wpuf-flex wpuf-items-center wpuf-gap-3">
                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                        </svg>
                        <div>
                            <h1 class="wpuf-text-2xl wpuf-font-semibold wpuf-text-gray-900 wpuf-m-0">{{ __('AI Form Builder', 'wp-user-frontend') }}</h1>
                            <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-m-0">{{ __('Generate forms instantly with AI assistance', 'wp-user-frontend') }}</p>
                        </div>
                    </div>
                    
                    <!-- Right Side - Buttons -->
                    <div class="wpuf-flex wpuf-gap-3">
                        <button @click="handleRegenerate" class="wpuf-btn-regenerate wpuf-bg-white wpuf-text-gray-500 wpuf-border wpuf-border-gray-300 wpuf-py-2 wpuf-px-4 wpuf-rounded-lg wpuf-text-sm wpuf-cursor-pointer wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-transition-all hover:wpuf-bg-gray-50 hover:wpuf-border-gray-400">
                            {{ __('Regenerate', 'wp-user-frontend') }}
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3523 7.79032H17.5128V7.78884M2.48682 16.3703V12.2098M2.48682 12.2098L6.64735 12.2098M2.48682 12.2098L5.13756 14.8622C5.963 15.6892 7.01055 16.3166 8.22034 16.6408C11.8879 17.6235 15.6577 15.447 16.6405 11.7794M3.35898 8.22068C4.3417 4.5531 8.11152 2.37659 11.7791 3.35932C12.9889 3.68348 14.0365 4.31091 14.8619 5.1379L17.5128 7.78884M17.5128 3.62982V7.78884" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button @click="handleEditInBuilder" class="wpuf-btn-edit-builder wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-py-2 wpuf-px-4 wpuf-rounded-lg wpuf-text-sm wpuf-cursor-pointer wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-transition-colors hover:wpuf-bg-emerald-800">
                            {{ __('Edit in Builder', 'wp-user-frontend') }}
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="wpuf-grid-container wpuf-flex wpuf-flex-col lg:wpuf-flex-row wpuf-gap-5 wpuf-p-2 sm:wpuf-p-5">
                    <!-- Left Side - Chat Box -->
                    <div class="wpuf-chat-box wpuf-w-full wpuf-h-[60vh] sm:wpuf-h-[70vh] md:wpuf-h-[75vh] lg:wpuf-h-[80vh] xl:wpuf-h-[85vh] wpuf-max-h-[1072px] wpuf-bg-slate-50 wpuf-border wpuf-border-slate-200 wpuf-rounded-lg wpuf-p-4 sm:wpuf-p-6 lg:wpuf-p-9 wpuf-flex wpuf-flex-col wpuf-shadow-md wpuf-overflow-hidden">
                        <div class="wpuf-chat-header wpuf-mb-6 wpuf-pb-4 wpuf-border-b wpuf-border-slate-200 wpuf-flex-shrink-0">
                            <div class="wpuf-flex wpuf-items-center wpuf-gap-3">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.625 9.75C8.625 9.95711 8.45711 10.125 8.25 10.125C8.04289 10.125 7.875 9.95711 7.875 9.75C7.875 9.54289 8.04289 9.375 8.25 9.375C8.45711 9.375 8.625 9.54289 8.625 9.75ZM8.625 9.75H8.25M12.375 9.75C12.375 9.95711 12.2071 10.125 12 10.125C11.7929 10.125 11.625 9.95711 11.625 9.75C11.625 9.54289 11.7929 9.375 12 9.375C12.2071 9.375 12.375 9.54289 12.375 9.75ZM12.375 9.75H12M16.125 9.75C16.125 9.95711 15.9571 10.125 15.75 10.125C15.5429 10.125 15.375 9.95711 15.375 9.75C15.375 9.54289 15.5429 9.375 15.75 9.375C15.9571 9.375 16.125 9.54289 16.125 9.75ZM16.125 9.75H15.75M2.25 12.7593C2.25 14.3604 3.37341 15.754 4.95746 15.987C6.04357 16.1467 7.14151 16.27 8.25 16.3556V21L12.4335 16.8165C12.6402 16.6098 12.9193 16.4923 13.2116 16.485C15.1872 16.4361 17.1331 16.2678 19.0425 15.9871C20.6266 15.7542 21.75 14.3606 21.75 12.7595V6.74056C21.75 5.13946 20.6266 3.74583 19.0425 3.51293C16.744 3.17501 14.3926 3 12.0003 3C9.60776 3 7.25612 3.17504 4.95747 3.51302C3.37342 3.74593 2.25 5.13956 2.25 6.74064V12.7593Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h2 class="wpuf-success-title wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800 wpuf-m-0">{{ __('WPUF Form Generation Chat', 'wp-user-frontend') }}</h2>
                            </div>
                        </div>
                        
                        <div class="wpuf-chat-scrollable wpuf-flex-1 wpuf-overflow-y-auto wpuf-mb-4 wpuf-max-h-[calc(100vh-300px)]" ref="chatContainer" style="scrollbar-width: thin; scrollbar-color: #10B981 transparent;">
                            <div class="wpuf-chat-messages wpuf-flex wpuf-flex-col wpuf-gap-4">
                                <div v-for="(message, index) in chatMessages" :key="index" 
                                     :class="message.type === 'user' ? 'wpuf-message-user wpuf-flex wpuf-justify-end' : 'wpuf-message-ai wpuf-flex wpuf-gap-3 wpuf-items-start'">
                                    
                                    <!-- User Message -->
                                    <div v-if="message.type === 'user'">
                                        <div class="wpuf-py-3 wpuf-px-4 wpuf-rounded-2xl wpuf-w-full wpuf-bg-[#ECFDF5] wpuf-text-emerald-800 wpuf-rounded-br wpuf-font-normal wpuf-leading-6" style="font-size: 1.25rem !important;">
                                            <p class="wpuf-text-base wpuf-m-0">{{ message.content }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- AI Message -->
                                    <div v-else class="wpuf-ai-message wpuf-flex wpuf-gap-3 wpuf-items-start">
                                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="wpuf-ai-icon wpuf-flex-shrink-0 wpuf-w-9 wpuf-h-9">
                                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                                        </svg>
                                        <div class="wpuf-flex-1 wpuf-flex-1">
                                            <div class="wpuf-message-bubble wpuf-message-bubble-ai wpuf-py-3 wpuf-px-4 wpuf-rounded-2xl wpuf-w-full wpuf-bg-white wpuf-text-gray-600 wpuf-rounded-bl wpuf-text-base" style="color: #4B5563 !important; font-size: 1rem !important;">
                                                <p class="wpuf-text-gray-600 wpuf-text-base wpuf-m-0" v-html="message.content"></p>
                                                <div v-if="message.showButtons" class="wpuf-message-actions wpuf-mt-3 wpuf-flex wpuf-gap-2">
                                                    <button @click="handleAccept" :disabled="isApplying" class="wpuf-btn-accept wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-py-1.5 wpuf-px-3 wpuf-rounded wpuf-text-sm wpuf-cursor-pointer wpuf-transition-colors hover:wpuf-bg-emerald-800 disabled:wpuf-bg-gray-400 disabled:wpuf-cursor-not-allowed">
                                                        {{ __('Accept', 'wp-user-frontend') }}
                                                    </button>
                                                    <button @click="handleReject" :disabled="isApplying" class="wpuf-btn-reject wpuf-bg-red-600 wpuf-text-white wpuf-border-none wpuf-py-1.5 wpuf-px-3 wpuf-rounded wpuf-text-sm wpuf-cursor-pointer wpuf-transition-colors hover:wpuf-bg-red-800 disabled:wpuf-bg-gray-400 disabled:wpuf-cursor-not-allowed">{{ __('Reject', 'wp-user-frontend') }}</button>
                                                </div>
                                                <!-- Accepted status indicator -->
                                                <div v-if="message.acceptedStatus" class="wpuf-accepted-status wpuf-mt-2 wpuf-text-xs wpuf-text-green-600">
                                                    {{ message.acceptedStatus }}
                                                </div>
                                                <!-- Checkpoint restore button (checkpoints are auto-saved) -->
                                                <div v-if="message.hasCheckpoint && message.checkpointSaved" class="wpuf-checkpoint-actions wpuf-mt-2 wpuf-flex wpuf-items-center wpuf-gap-2">
                                                    <button 
                                                        @click="restoreCheckpoint(index)" 
                                                        class="wpuf-btn-restore wpuf-bg-blue-500 wpuf-text-white wpuf-border-none wpuf-py-1 wpuf-px-2.5 wpuf-rounded wpuf-text-xs wpuf-cursor-pointer wpuf-transition-all hover:wpuf-bg-blue-600 wpuf-flex wpuf-items-center wpuf-gap-1.5"
                                                        title="Restore form to this checkpoint"
                                                    >
                                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M3 10C3 13.866 6.13401 17 10 17C13.866 17 17 13.866 17 10C17 6.13401 13.866 3 10 3C7.07421 3 4.56316 4.77516 3.52779 7.28M3 3V7.5H7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        {{ __('Restore to this checkpoint', 'wp-user-frontend') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div v-if="message.status && isStatusVisible(index)" class="wpuf-message-status wpuf-mt-2 wpuf-font-normal wpuf-italic wpuf-text-sm wpuf-leading-6 wpuf-tracking-normal wpuf-text-right wpuf-text-emerald-600 wpuf-transition-all wpuf-duration-500" :class="{'wpuf-opacity-100': isStatusVisible(index), 'wpuf-opacity-0': !isStatusVisible(index)}">
                                                <span>{{ message.status }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wpuf-chat-input-container wpuf-border-t wpuf-border-slate-200 wpuf-pt-4 wpuf-flex-shrink-0">
                            <div class="wpuf-chat-input-wrapper wpuf-flex wpuf-gap-3 wpuf-items-end">
                                <textarea 
                                    v-model="userInput"
                                    @keyup.enter.prevent="handleSendMessage"
                                    class="wpuf-chat-input wpuf-flex-1 wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-p-3 wpuf-text-sm wpuf-resize-none wpuf-min-h-[44px] wpuf-max-h-[120px] wpuf-font-inherit wpuf-outline-none focus:wpuf-border-emerald-500 focus:wpuf-shadow-lg focus:wpuf-shadow-emerald-100"
                                    :placeholder="hasPendingButtons ? __('Please accept or reject the changes above', 'wp-user-frontend') : (isFormUpdating ? __('Please wait while form is being generated...', 'wp-user-frontend') : __('Type your message here...', 'wp-user-frontend'))"
                                    :disabled="isFormUpdating || hasPendingButtons"
                                    :class="{ 'wpuf-opacity-50 wpuf-cursor-not-allowed': isFormUpdating || hasPendingButtons }"
                                ></textarea>
                                <button 
                                    @click="handleSendMessage" 
                                    class="wpuf-send-button wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-rounded-lg wpuf-w-11 wpuf-h-11 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-cursor-pointer wpuf-flex-shrink-0 hover:wpuf-bg-emerald-800 wpuf-transition-colors"
                                    :disabled="isFormUpdating || hasPendingButtons"
                                    :class="{ 'wpuf-opacity-50 wpuf-cursor-not-allowed': isFormUpdating || hasPendingButtons }"
                                >
                                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.99972 10L1.2688 1.12451C7.88393 3.04617 14.0276 6.07601 19.4855 9.99974C14.0276 13.9235 7.884 16.9535 1.26889 18.8752L3.99972 10ZM3.99972 10L11.5 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Form Preview -->
                    <div class="wpuf-form-preview wpuf-w-full wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-4 sm:wpuf-p-6 lg:wpuf-p-8 wpuf-flex wpuf-flex-col wpuf-gap-6 wpuf-shadow-md wpuf-h-[60vh] sm:wpuf-h-[70vh] md:wpuf-h-[75vh] lg:wpuf-h-[80vh] xl:wpuf-h-[85vh] wpuf-max-h-[1072px] wpuf-relative" 
                         :class="{ 'wpuf-form-updating': isFormUpdating }">
                        
                        <!-- Form Updating Overlay -->
                        <div v-if="isFormUpdating" class="wpuf-form-updating-overlay wpuf-absolute wpuf-inset-0 wpuf-bg-white wpuf-bg-opacity-80 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-z-10 wpuf-rounded-lg">
                            <svg width="93" height="92" viewBox="0 0 93 92" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect x="0.5" width="92" height="92" fill="url(#pattern0_5555_45993)"/>
                                <defs>
                                <pattern id="pattern0_5555_45993" patternContentUnits="objectBoundingBox" width="1" height="1">
                                <use xlink:href="#image0_5555_45993" transform="scale(0.00362319)"/>
                                </pattern>
                                <image id="image0_5555_45993" width="276" height="276" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
                                </defs>
                            </svg>
                            <p class="wpuf-text-emerald-600 wpuf-font-medium wpuf-text-lg">{{ __('Generating...', 'wp-user-frontend') }}</p>
                        </div>
                        <div v-if="formTitle || formFields.length > 0" class="wpuf-form-header wpuf-border-b wpuf-border-gray-200 wpuf-pb-4 wpuf-flex-shrink-0">
                            <h3 v-if="formTitle" class="wpuf-form-title wpuf-font-bold wpuf-text-3xl wpuf-leading-9 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-900 wpuf-m-0 wpuf-mb-2">{{ formTitle }}</h3>
                            <p v-if="formFields.length > 0" class="wpuf-form-description wpuf-font-normal wpuf-text-lg wpuf-leading-6 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-500 wpuf-m-0">{{ formDescription || __('Please complete all information below', 'wp-user-frontend') }}</p>
                        </div>
                        
                        <div class="wpuf-form-scrollable wpuf-flex-1 wpuf-overflow-y-auto wpuf-mb-4" style="scrollbar-width: thin; scrollbar-color: #10B981 transparent;">
                            <!-- Empty State -->
                            <div v-if="formFields.length === 0 && !isFormUpdating" class="wpuf-empty-state wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-full wpuf-min-h-[300px]">
                                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="wpuf-mb-4">
                                    <circle cx="40" cy="40" r="40" fill="#F3F4F6"/>
                                    <path d="M28 32H52M28 40H52M28 48H44" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <p class="wpuf-text-gray-500 wpuf-text-lg wpuf-text-center wpuf-mb-2">{{ __('Your form is being generated', 'wp-user-frontend') }}</p>
                                <p class="wpuf-text-gray-400 wpuf-text-sm wpuf-text-center">{{ __('Form fields will appear here once generated', 'wp-user-frontend') }}</p>
                            </div>
                            
                            <!-- Form Fields -->
                            <div v-if="formFields.length > 0" class="wpuf-form-fields wpuf-flex wpuf-flex-col wpuf-gap-5">
                                <div v-for="field in formFields" :key="field.id" class="wpuf-form-field wpuf-flex wpuf-flex-col wpuf-gap-2">
                                    <!-- Field Label with Required Indicator -->
                                    <label class="wpuf-form-label wpuf-font-normal wpuf-text-base wpuf-leading-6 wpuf-tracking-normal wpuf-text-gray-900 wpuf-flex wpuf-items-center wpuf-gap-1">
                                        {{ field.label }}
                                        <span v-if="field.required" class="wpuf-required wpuf-text-red-500 wpuf-font-bold">*</span>
                                    </label>
                                    
                                    <!-- Help Text -->
                                    <p v-if="field.help_text" class="wpuf-field-help wpuf-text-sm wpuf-text-gray-500 wpuf-m-0 wpuf-mb-1">{{ field.help_text }}</p>
                                    
                                    <!-- WPUF Text Fields -->
                                    <div v-if="['text', 'email', 'url', 'number', 'tel', 'post_title'].includes(getWPUFFieldType(field))" 
                                         class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ field.placeholder || field.help || 'Enter text' }}</span>
                                    </div>
                                    
                                    <!-- WPUF Dropdown/Select -->
                                    <div v-else-if="['select', 'dropdown', 'dropdown_field'].includes(getWPUFFieldType(field))" class="wpuf-form-select-container">
                                        <div class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-cursor-pointer"
                                             :class="''"
                                             style="background-image: none;"
                                        >
                                            <span class="wpuf-text-gray-400">{{ field.placeholder || __('Select an option', 'wp-user-frontend') }}</span>
                                            <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.25 0.875001L7 7.125L0.75 0.875001" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Radio Buttons -->
                                    <div v-else-if="getWPUFFieldType(field) === 'radio'" class="wpuf-form-radio-container">
                                        <div class="wpuf-radio-group wpuf-flex wpuf-flex-col wpuf-gap-2" :class="field.required ? 'wpuf-border wpuf-rounded wpuf-p-3' : ''">
                                            <template v-if="field.options && field.options.length">
                                                <div v-for="option in field.options" :key="option.value" class="wpuf-radio-option wpuf-flex wpuf-items-center wpuf-gap-2">
                                                    <input type="radio" :name="`field_${field.id}`" :value="option.value" disabled class="wpuf-radio-input wpuf-text-emerald-600">
                                                    <label class="wpuf-radio-label wpuf-text-sm wpuf-text-gray-700">{{ option.label }}</label>
                                                </div>
                                            </template>
                                            <div v-else class="wpuf-text-gray-400 wpuf-text-sm">{{ __('No options configured', 'wp-user-frontend') }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Checkboxes -->
                                    <div v-else-if="['checkbox_field', 'checkbox'].includes(field.type)" class="wpuf-form-checkbox-container">
                                        <div class="wpuf-checkbox-group wpuf-flex wpuf-flex-col wpuf-gap-2" :class="field.required ? 'wpuf-border wpuf-rounded wpuf-p-3' : ''">
                                            <template v-if="field.options && field.options.length">
                                                <div v-for="option in field.options" :key="option.value" class="wpuf-checkbox-option wpuf-flex wpuf-items-center wpuf-gap-2">
                                                    <input type="checkbox" :value="option.value" disabled class="wpuf-checkbox-input wpuf-text-emerald-600">
                                                    <label class="wpuf-checkbox-label wpuf-text-sm wpuf-text-gray-700">{{ option.label }}</label>
                                                </div>
                                            </template>
                                            <div v-else class="wpuf-text-gray-400 wpuf-text-sm">{{ __('No options configured', 'wp-user-frontend') }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Terms of Conditions (ToC) -->
                                    <div v-else-if="field.type === 'toc'" class="wpuf-form-toc-container">
                                        <div class="wpuf-toc-wrapper wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-4 wpuf-bg-gray-50">
                                            <div v-if="field.toc_text" class="wpuf-toc-text wpuf-text-sm wpuf-text-gray-600 wpuf-mb-3">
                                                {{ field.toc_text }}
                                            </div>
                                            <div class="wpuf-toc-checkbox wpuf-flex wpuf-items-start wpuf-gap-2">
                                                <input type="checkbox" disabled class="wpuf-checkbox-input wpuf-text-emerald-600 wpuf-mt-1">
                                                <label class="wpuf-checkbox-label wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">
                                                    {{ field.description || 'I agree to the terms and conditions' }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF File Upload -->
                                    <div v-else-if="['image_upload', 'file', 'featured_image', 'file_upload'].includes(getWPUFFieldType(field))" 
                                         class="wpuf-form-file wpuf-border-2 wpuf-border-dashed wpuf-border-gray-300 wpuf-rounded wpuf-p-5 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-gap-2 wpuf-bg-gray-50 wpuf-text-gray-500 wpuf-text-center wpuf-text-sm"
                                         :class="''"
                                    >
                                        <svg class="wpuf-file-icon wpuf-w-8 wpuf-h-8 wpuf-text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4V16M12 4L8 8M12 4L16 8M4 17V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="wpuf-file-text">
                                            <strong>{{ field.button_label || __('Select Image', 'wp-user-frontend') }}</strong>
                                            <div class="wpuf-text-gray-400">{{ getWPUFFieldType(field) === 'image_upload' ? 'Upload image files' : 'Drop files here or click to upload' }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Textarea -->
                                    <div v-else-if="['textarea', 'post_content', 'post_excerpt'].includes(getWPUFFieldType(field))" 
                                         class="wpuf-form-textarea wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-min-h-[100px] wpuf-relative"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ field.placeholder || field.help || __('Enter your text here...', 'wp-user-frontend') }}</span>
                                    </div>
                                    
                                    <!-- WPUF Multiple Select -->
                                    <div v-else-if="['multiple_select', 'country_list_field'].includes(field.type)" class="wpuf-form-multiselect-container">
                                        <div class="wpuf-form-input wpuf-multiselect wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50"
                                             :class="''"
                                        >
                                            <span class="wpuf-text-gray-400">{{ field.placeholder || getFieldPlaceholder(field.type) }}</span>
                                        </div>

                                    </div>
                                    
                                    <!-- WPUF Date/Time Fields -->
                                    <div v-else-if="field.input_type === 'date' || field.template === 'date_field' || ['date_field', 'time_field', 'date', 'time', 'datetime'].includes(field.type)" 
                                         class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ field.placeholder || 'Select date' }}</span>
                                        <svg class="wpuf-date-icon wpuf-w-4 wpuf-h-4 wpuf-text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- WPUF Pro Rating Fields -->
                                    <div v-else-if="['ratings', 'linear_scale'].includes(field.type)" class="wpuf-form-rating-container">
                                        <div class="wpuf-rating-display wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-gap-2"
                                             :class="''"
                                        >
                                            <template v-if="field.type === 'ratings'">
                                                <span v-for="n in 5" :key="n" class="wpuf-star wpuf-text-gray-300 wpuf-text-lg">★</span>
                                            </template>
                                            <template v-else>
                                                <span class="wpuf-text-gray-400">{{ __('1', 'wp-user-frontend') }}</span>
                                                <div class="wpuf-flex-1 wpuf-bg-gray-200 wpuf-h-2 wpuf-rounded"></div>
                                                <span class="wpuf-text-gray-400">{{ __('10', 'wp-user-frontend') }}</span>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Pro Grid Fields -->
                                    <div v-else-if="['checkbox_grid', 'multiple_choice_grid'].includes(field.type)" class="wpuf-form-grid-container">
                                        <div class="wpuf-grid-preview wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-4 wpuf-text-sm wpuf-bg-gray-50"
                                             :class="''"
                                        >
                                            <div class="wpuf-grid-header wpuf-flex wpuf-items-center wpuf-gap-4 wpuf-mb-3">
                                                <div class="wpuf-flex-1"></div>
                                                <div class="wpuf-text-xs wpuf-text-gray-500">{{ __('Option 1', 'wp-user-frontend') }}</div>
                                                <div class="wpuf-text-xs wpuf-text-gray-500">{{ __('Option 2', 'wp-user-frontend') }}</div>
                                                <div class="wpuf-text-xs wpuf-text-gray-500">{{ __('Option 3', 'wp-user-frontend') }}</div>
                                            </div>
                                            <div class="wpuf-grid-row wpuf-flex wpuf-items-center wpuf-gap-4">
                                                <div class="wpuf-flex-1 wpuf-text-gray-600">{{ __('Row 1', 'wp-user-frontend') }}</div>
                                                <input :type="field.type === 'checkbox_grid' ? 'checkbox' : 'radio'" disabled class="wpuf-text-emerald-600">
                                                <input :type="field.type === 'checkbox_grid' ? 'checkbox' : 'radio'" disabled class="wpuf-text-emerald-600">
                                                <input :type="field.type === 'checkbox_grid' ? 'checkbox' : 'radio'" disabled class="wpuf-text-emerald-600">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Pro Special Fields -->
                                    <div v-else-if="['google_map', 'address_field', 'embed', 'qr_code'].includes(field.type)" 
                                         class="wpuf-form-special wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-5 wpuf-text-center wpuf-bg-gray-50"
                                         :class="''"
                                    >
                                        <div class="wpuf-special-icon wpuf-text-gray-400 wpuf-mb-2">
                                            <svg v-if="field.type === 'google_map'" class="wpuf-w-8 wpuf-h-8 wpuf-mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <svg v-else-if="field.type === 'address_field'" class="wpuf-w-8 wpuf-h-8 wpuf-mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <svg v-else class="wpuf-w-8 wpuf-h-8 wpuf-mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                            </svg>
                                        </div>
                                        <div class="wpuf-text-xs wpuf-text-gray-500">{{ field.placeholder || getFieldPlaceholder(field.type) }}</div>
                                    </div>
                                    
                                    <!-- WPUF Pro Captcha Fields -->
                                    <div v-else-if="['really_simple_captcha', 'math_captcha'].includes(field.type)" 
                                         class="wpuf-form-captcha wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-4 wpuf-text-center wpuf-bg-gray-50"
                                         :class="''"
                                    >
                                        <div class="wpuf-captcha-display wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded wpuf-p-3 wpuf-mb-3 wpuf-text-lg wpuf-font-mono">
                                            {{ field.type === 'math_captcha' ? '3 + 5 = ?' : 'CAPTCHA' }}
                                        </div>
                                        <input type="text" :placeholder="__('Enter code', 'wp-user-frontend')" disabled class="wpuf-w-full wpuf-p-2 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-text-center">
                                    </div>
                                    
                                    <!-- WPUF Taxonomy (Category) Field -->
                                    <div v-else-if="field.type === 'taxonomy'" 
                                         class="wpuf-form-select-container"
                                    >
                                        <div class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-cursor-pointer"
                                             :class="field.required ? 'wpuf-border-blue-300' : ''"
                                             style="background-image: none;"
                                        >
                                            <span class="wpuf-text-gray-400">{{ field.placeholder || '- Select -' }}</span>
                                            <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.25 0.875001L7 7.125L0.75 0.875001" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Post Fields -->
                                    <div v-else-if="['post_title', 'post_content', 'post_excerpt', 'post_tags'].includes(field.type)" 
                                         class="wpuf-form-post-field wpuf-border wpuf-border-blue-200 wpuf-bg-blue-50 wpuf-rounded wpuf-p-3"
                                         :class="field.required ? 'wpuf-border-blue-300' : ''"
                                    >
                                        <div class="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-mb-2">
                                            <svg class="wpuf-w-4 wpuf-h-4 wpuf-text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="wpuf-text-blue-800 wpuf-font-medium">{{ field.label }}</span>
                                        </div>
                                        <div class="wpuf-text-blue-600 wpuf-text-sm">{{ field.placeholder || getFieldPlaceholder(field.type) }}</div>
                                    </div>
                                    
                                    <!-- WPUF Layout Fields -->
                                    <div v-else-if="['section_break', 'column_field', 'step_start'].includes(field.type)" 
                                         class="wpuf-form-layout wpuf-border-2 wpuf-border-dashed wpuf-border-purple-300 wpuf-bg-purple-50 wpuf-rounded wpuf-p-4 wpuf-text-center"
                                    >
                                        <div class="wpuf-text-purple-600 wpuf-font-medium">{{ field.label }}</div>
                                    </div>
                                    
                                    <!-- WPUF Custom Fields -->
                                    <div v-else-if="['custom_html', 'shortcode', 'action_hook'].includes(field.type)" 
                                         class="wpuf-form-custom wpuf-border wpuf-border-yellow-300 wpuf-bg-yellow-50 wpuf-rounded wpuf-p-4"
                                    >
                                        <div class="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-mb-2">
                                            <svg class="wpuf-w-4 wpuf-h-4 wpuf-text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            </svg>
                                            <span class="wpuf-text-yellow-800 wpuf-font-medium">{{ field.label }}</span>
                                        </div>
                                        <div class="wpuf-text-yellow-600 wpuf-text-sm">{{ field.placeholder || getFieldPlaceholder(field.type) }}</div>
                                    </div>
                                    
                                    <!-- Fallback for unknown field types -->
                                    <div v-else class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-100 wpuf-flex wpuf-items-center wpuf-gap-2">
                                        <span class="wpuf-text-gray-500">{{ field.placeholder || __('Custom field', 'wp-user-frontend') }}</span>
                                    </div>
                                    
                                    <!-- Default Value Display -->
                                    <div v-if="field.default" class="wpuf-field-default wpuf-text-sm wpuf-text-blue-600 wpuf-flex wpuf-items-center wpuf-gap-1">
                                        <svg class="wpuf-w-4 wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('Default:', 'wp-user-frontend') }} {{ field.default }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wpuf-form-footer wpuf-border-t wpuf-border-gray-200 wpuf-pt-4 wpuf-flex-shrink-0">
                            <button @click="handleEditWithBuilder" class="wpuf-btn-edit-full wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-py-3 wpuf-px-5 wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-cursor-pointer wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-w-full wpuf-justify-center hover:wpuf-bg-emerald-800 wpuf-transition-colors">
                                {{ __('Edit with Builder', 'wp-user-frontend') }}
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Regenerate Confirmation Modal -->
        <div v-if="showRegenerateModal" class="wpuf-modal-overlay wpuf-fixed wpuf-inset-0 wpuf-bg-black wpuf-bg-opacity-75 wpuf-z-50 wpuf-flex wpuf-items-center wpuf-justify-center">
            <div class="wpuf-modal-content wpuf-bg-white wpuf-rounded-lg wpuf-shadow-lg" style="width: 660px; height: 480px; position: relative;">
                <div class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-h-full">
                    <!-- Icon -->
                    <div class="wpuf-flex wpuf-items-center wpuf-justify-center wpuf-mb-8">
                        <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="110" height="110" rx="55" fill="#D1FAE5"/>
                            <path d="M60 51V46C60 44.3431 58.6569 43 57 43L49 43C47.3431 43 46 44.3431 46 46L46 64C46 65.6569 47.3431 67 49 67H57C58.6569 67 60 65.6569 60 64V59M55 51L51 55M51 55L55 59M51 55L68 55" stroke="#0F172A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="wpuf-modal-title">
                        {{ __('Are you sure you want to leave and regenerate the form?', 'wp-user-frontend') }}
                    </h3>
                    
                    <!-- Description -->
                    <p class="wpuf-modal-description">
                        {{ __('If you decide to leave and regenerate the form, please be aware that you will lost the information you\'ve currently generated.', 'wp-user-frontend') }}
                    </p>
                    
                    <!-- Buttons -->
                    <div class="wpuf-flex wpuf-gap-4">
                        <button @click="confirmRegenerate" class="wpuf-modal-cancel-btn">
                            {{ __('Leave & Regenerate', 'wp-user-frontend') }}
                        </button>
                        <button @click="showRegenerateModal = false" class="wpuf-modal-regenerate-btn">
                            {{ __('Cancel', 'wp-user-frontend') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar styles for WebKit browsers */
.wpuf-chat-scrollable::-webkit-scrollbar,
.wpuf-form-scrollable::-webkit-scrollbar {
    width: 1px !important;
}

.wpuf-chat-scrollable::-webkit-scrollbar-track,
.wpuf-form-scrollable::-webkit-scrollbar-track {
    background: transparent;
}

.wpuf-chat-scrollable::-webkit-scrollbar-thumb,
.wpuf-form-scrollable::-webkit-scrollbar-thumb {
    background: #ecfffb;
    border-radius: 0.5px !important;
}

.wpuf-chat-scrollable::-webkit-scrollbar-thumb:hover,
.wpuf-form-scrollable::-webkit-scrollbar-thumb:hover {
    background: #e4e4e4;
}

/* Loading spinner animation */
@keyframes wpuf-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.wpuf-animate-spin {
    animation: wpuf-spin 1s linear infinite;
}

/* Form updating blur effect */
.wpuf-form-updating {
    transition: filter 0.3s ease-in-out;
}

.wpuf-form-updating .wpuf-form-header,
.wpuf-form-updating .wpuf-form-scrollable {
    filter: blur(2px);
    transition: filter 0.3s ease-in-out;
}

.wpuf-form-updating-overlay {
    backdrop-filter: blur(1px);
    transition: opacity 0.3s ease-in-out;
}

/* Ensure overlay text is crisp */
.wpuf-form-updating-overlay p,
.wpuf-form-updating-overlay svg {
    filter: none;
}

/* Status message animation */
.wpuf-message-status {
    transition: opacity 0.5s ease-in-out;
}

.wpuf-opacity-100 {
    opacity: 1 !important;
}

.wpuf-opacity-0 {
    opacity: 0 !important;
}

/* Checkpoint button styles */
.wpuf-checkpoint-actions {
    animation: wpuf-fade-in 0.3s ease-in-out;
}

@keyframes wpuf-fade-in {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.wpuf-btn-checkpoint,
.wpuf-btn-restore {
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.wpuf-btn-checkpoint:hover,
.wpuf-btn-restore:hover {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.wpuf-checkpoint-saved {
    animation: wpuf-pulse 1.5s ease-in-out;
}

@keyframes wpuf-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.wpuf-transition-all {
    transition: all 0.5s ease-in-out;
}

.wpuf-duration-500 {
    transition-duration: 500ms;
}

/* Regenerate Modal Styles */
.wpuf-modal-title {
    font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-weight: 500;
    font-size: 28px;
    line-height: 150%;
    letter-spacing: 0%;
    text-align: center;
    color: #111827;
    margin: 0 0 24px 0;
    max-width: 480px;
}

.wpuf-modal-description {
    font-weight: 400;
    font-size: 14px;
    letter-spacing: 0%;
    text-align: center;
    color: #6B7280;
    margin: 0 0 32px 0;
    max-width: 420px;
}

.wpuf-modal-cancel-btn {
    width: 201px;
    height: 50px;
    border-radius: 6px;
    padding: 13px 25px 13px 23px;
    gap: 12px;
    border: 1px solid #D1D5DB;
    background: #FFFFFF;
    color: #374151;
    font-weight: 500;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wpuf-modal-cancel-btn:hover {
    background: #F9FAFB;
    border-color: #9CA3AF;
}

.wpuf-modal-regenerate-btn {
    width: 201px;
    height: 50px;
    border-radius: 6px;
    padding: 13px 25px 13px 23px;
    gap: 12px;
    border: 1px solid #059669;
    background: #059669;
    color: #FFFFFF;
    font-weight: 500;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wpuf-modal-regenerate-btn:hover {
    background: #059669;
    border-color: #059669;
}
</style>

<script>
export default {
    name: 'FormSuccessStage',
    props: {
        formTitle: {
            type: String,
            default: ''
        },
        formId: {
            type: [String, Number],
            default: null
        },
        initialMessages: {
            type: Array,
            default: () => []
        },
        initialFormFields: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            userInput: '',
            formDescription: '',
            formSettings: {},
            sessionId: this.generateSessionId(),
            conversationState: {
                original_prompt: '',
                form_created: false,
                modifications_count: 0,
                context_history: [],
                is_predefined_template: false,
                template_modified: false,
                original_form_hash: null
            },
            chatMessages: [],
            formFields: [],
            previousFormFields: [], // Store previous state for reject
            pendingChanges: null, // Store pending changes from chat
            isApplying: false,
            isFormUpdating: false,
            visibleStatuses: new Set(), // Track which status messages are visible
            statusTimeouts: new Map(), // Track timeout IDs for auto-hide
            showRegenerateModal: false, // Track regenerate confirmation modal
            checkpoints: new Map() // Store checkpoints: messageIndex => formState
        };
    },
    computed: {
        // Check if there are any pending accept/reject buttons
        hasPendingButtons() {
            return this.chatMessages.some(message => message.showButtons === true);
        }
    },
    watch: {
        initialFormFields: {
            handler(newFields, oldFields) {
                console.log('FormSuccessStage: initialFormFields prop changed:', {
                    oldFields: oldFields ? oldFields.length : 0,
                    newFields: newFields ? newFields.length : 0,
                    currentFields: this.formFields.length
                });
                
                // Skip if no new fields
                if (!newFields || newFields.length === 0) {
                    return;
                }
                
                // Check if fields are actually different
                const fieldsAreDifferent = JSON.stringify(this.formFields) !== JSON.stringify(newFields);
                
                // Only show loader if we have current fields and they're changing
                if (this.formFields.length > 0 && fieldsAreDifferent) {
                    // Update fields immediately without delay
                    console.log('Updating form fields immediately...');
                    this.isFormUpdating = true;
                    this.formFields = [...newFields];
                    console.log('FormSuccessStage: Updated formFields:', this.formFields);
                    
                    // Remove loader quickly
                    this.$nextTick(() => {
                        this.isFormUpdating = false;
                        console.log('Form update complete');
                    });
                } else if (!fieldsAreDifferent) {
                    // Fields are the same, no need to update
                    console.log('Fields are the same, no update needed');
                } else {
                    // Initial load, update without loader
                    this.formFields = [...newFields];
                    console.log('Initial fields set:', this.formFields);
                }
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        __: window.__ || ((text) => text),
        
        generateSessionId() {
            return 'wpuf_chat_session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
        },
        
        // Checkpoint management methods
        saveCheckpoint(messageIndex) {
            // Save current form state as checkpoint
            const checkpoint = {
                formFields: JSON.parse(JSON.stringify(this.formFields)), // Deep clone
                formTitle: this.formTitle,
                formDescription: this.formDescription,
                formSettings: JSON.parse(JSON.stringify(this.formSettings)),
                timestamp: Date.now(),
                messageContent: this.chatMessages[messageIndex]?.content || ''
            };
            
            // Store checkpoint
            this.checkpoints.set(messageIndex, checkpoint);
            
            // Mark message as having saved checkpoint
            if (this.chatMessages[messageIndex]) {
                this.chatMessages[messageIndex].checkpointSaved = true;
            }
            
            // Show success feedback
            this.showStatusMessage('Checkpoint saved successfully', 'success');
        },
        
        restoreCheckpoint(messageIndex) {
            const checkpoint = this.checkpoints.get(messageIndex);
            if (!checkpoint) {
                this.showStatusMessage('No checkpoint found', 'error');
                return;
            }
            
            // Show loading state
            this.isFormUpdating = true;
            
            // Restore form state from checkpoint
            setTimeout(() => {
                this.formFields = JSON.parse(JSON.stringify(checkpoint.formFields));
                this.formDescription = checkpoint.formDescription;
                this.formSettings = JSON.parse(JSON.stringify(checkpoint.formSettings));
                
                // Update the form title if available
                if (checkpoint.formTitle) {
                    this.$emit('update-form-title', checkpoint.formTitle);
                }
                
                // Hide loading state
                this.isFormUpdating = false;
                
                // Show success message
                this.showStatusMessage('Form restored to checkpoint', 'success');
                
                // Scroll to form preview
                this.$nextTick(() => {
                    const formPreview = document.querySelector('.wpuf-form-preview');
                    if (formPreview) {
                        formPreview.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            }, 300); // Small delay for visual feedback
        },
        
        showStatusMessage(message, type = 'info') {
            // Add a temporary status message (you can expand this to show a toast notification)
            console.log(`[${type.toUpperCase()}] ${message}`);
        },
        
        // Get the actual field type from WPUF field structure
        getWPUFFieldType(field) {
            // Priority: input_type > template > type
            if (field.input_type) return field.input_type;
            if (field.template) return field.template;
            return field.type || 'text';
        },
        
        // Status visibility methods
        isStatusVisible(messageIndex) {
            return this.visibleStatuses.has(messageIndex);
        },
        
        showStatus(messageIndex) {
            this.visibleStatuses.add(messageIndex);
            
            // Clear any existing timeout for this status
            if (this.statusTimeouts.has(messageIndex)) {
                clearTimeout(this.statusTimeouts.get(messageIndex));
            }
            
            // Set timeout to hide status after 3 seconds
            const timeoutId = setTimeout(() => {
                this.hideStatus(messageIndex);
            }, 3000);
            
            this.statusTimeouts.set(messageIndex, timeoutId);
        },
        
        hideStatus(messageIndex) {
            this.visibleStatuses.delete(messageIndex);
            
            // Clear timeout if it exists
            if (this.statusTimeouts.has(messageIndex)) {
                clearTimeout(this.statusTimeouts.get(messageIndex));
                this.statusTimeouts.delete(messageIndex);
            }
        },
        
        initializeChatMessages() {
            // If we have initial messages from props, use them
            if (this.initialMessages && this.initialMessages.length > 0) {
                return [...this.initialMessages];
            }
            
            // Otherwise start with empty chat
            return [];
        },
        
        initializeFormFields() {
            console.log('FormSuccessStage initializeFormFields called');
            console.log('initialFormFields prop:', this.initialFormFields);
            console.log('initialFormFields length:', this.initialFormFields ? this.initialFormFields.length : 0);
            
            // If we have initial form fields from props, use them
            if (this.initialFormFields && this.initialFormFields.length > 0) {
                console.log('Using initialFormFields from props');
                return [...this.initialFormFields];
            }
            
            console.log('Starting with empty form - waiting for AI generation');
            // Start with empty form - no default fields
            return [];
        },
        
        updateConversationState(userMessage, aiResponse) {
            this.conversationState.context_history.push({
                timestamp: new Date().toISOString(),
                user_message: userMessage,
                ai_response: aiResponse,
                form_state: {
                    title: this.formTitle,
                    fields_count: this.formFields.length,
                    field_types: this.formFields.map(f => f.type)
                }
            });
            
            // Keep only last 10 interactions to avoid memory issues
            if (this.conversationState.context_history.length > 10) {
                this.conversationState.context_history = this.conversationState.context_history.slice(-10);
            }
            
            this.conversationState.modifications_count++;
        },

        /**
         * Check if a prompt is a modification request
         */
        isModificationPrompt(prompt) {
            const modificationKeywords = [
                // Add/Create operations
                'add', 'create', 'insert', 'include', 'append',
                
                // Remove/Delete operations  
                'remove', 'delete', 'take out', 'eliminate', 'drop',
                
                // Update/Change operations
                'modify', 'change', 'update', 'edit', 'alter', 'replace', 'convert',
                'make', 'set', 'turn', 'switch', 'transform',
                
                // Field-specific operations
                'field', 'button', 'text', 'label', 'title', 'description',
                'required', 'optional', 'dropdown', 'radio', 'checkbox',
                
                // Common modification phrases
                'from', 'to', 'into', 'instead of', 'rather than'
            ];
            
            const lowerPrompt = prompt.toLowerCase();
            return modificationKeywords.some(keyword => lowerPrompt.includes(keyword));
        },
        
        convertFieldsToPreview(apiFields) {
            // Convert API response fields to preview format
            // Handle both WPUF format (input_type, template) and simplified format (type)
            return apiFields.map((field, index) => {
                // Create base field object
                const convertedField = {
                    id: field.id || `field_${index + 1}`,
                    type: field.type || field.template || field.input_type || 'text_field',
                    input_type: field.input_type || field.type || 'text',
                    template: field.template || field.type || 'text_field',
                    label: field.label || field.name || 'Untitled Field',
                    name: field.name || field.label?.toLowerCase().replace(/\s+/g, '_') || `field_${index + 1}`,
                    placeholder: field.placeholder || field.help || '',
                    required: field.required === 'yes' || field.required === true || field.required === 'true',
                    default: field.default || '',
                    help_text: field.help || field.description || '',
                    is_meta: field.is_meta || 'yes',
                    size: field.size || '40',
                    width: field.width || 'large',
                    css: field.css || '',
                    wpuf_cond: field.wpuf_cond || {
                        condition_status: 'no',
                        cond_field: [],
                        cond_operator: ['='],
                        cond_option: ['- Select -'],
                        cond_logic: 'all'
                    },
                    wpuf_visibility: field.wpuf_visibility || {
                        selected: 'everyone',
                        choices: []
                    }
                };

                // Add textarea-specific attributes
                if (field.template === 'textarea' || field.template === 'post_content' || field.template === 'post_excerpt' || field.type === 'textarea') {
                    convertedField.rows = field.rows || '5';
                    convertedField.cols = field.cols || '25';
                    convertedField.rich = field.rich || 'no';
                    if (field.template === 'post_content') {
                        convertedField.rich = field.rich || 'yes';
                        convertedField.insert_image = field.insert_image || 'yes';
                    }
                }

                // Add file field-specific attributes
                if (field.template === 'file_upload' || field.template === 'file' || field.type === 'file_upload') {
                    convertedField.extension = field.extension || [];
                    convertedField.max_size = field.max_size || '2048';
                    convertedField.count = field.count || '1';
                }

                // Handle options carefully for fields that need them
                // WPUF expects options as an object/associative array for radio, checkbox, dropdown
                if (field.options !== undefined && field.options !== null) {
                    // Check if options is already an object (WPUF format)
                    if (typeof field.options === 'object' && !Array.isArray(field.options)) {
                        convertedField.options = field.options;
                    } else if (Array.isArray(field.options)) {
                        // Convert array format to WPUF object format
                        // WPUF expects: { 'value1': 'Label 1', 'value2': 'Label 2' }
                        const optionsObj = {};
                        field.options.forEach(opt => {
                            if (typeof opt === 'object' && opt !== null && opt.value && opt.label) {
                                optionsObj[opt.value] = opt.label;
                            } else if (typeof opt === 'string') {
                                // If just a string, use it as both value and label
                                optionsObj[opt] = opt;
                            }
                        });
                        convertedField.options = optionsObj;
                    } else if (typeof field.options === 'string') {
                        // If it's a string, parse it into object format
                        const optionsObj = {};
                        const lines = field.options.split('\n');
                        lines.forEach(line => {
                            if (line && line.includes('|')) {
                                const [value, label] = line.split('|');
                                if (value && label) {
                                    optionsObj[value.trim()] = label.trim();
                                }
                            } else if (line && line.trim()) {
                                optionsObj[line.trim()] = line.trim();
                            }
                        });
                        convertedField.options = optionsObj;
                    } else {
                        convertedField.options = {};
                    }
                } else if (['radio_field', 'checkbox_field', 'dropdown_field'].includes(convertedField.type) ||
                          ['radio', 'checkbox', 'select', 'dropdown'].includes(convertedField.input_type)) {
                    // Ensure options exist for fields that need them
                    convertedField.options = {};
                }
                
                return convertedField;
            });
        },

        /**
         * Normalize options to consistent format for preview
         */
        normalizeOptions(options) {
            if (!options) return [];
            
            // If options is already an array, return as-is
            if (Array.isArray(options)) {
                return options.map(option => {
                    if (typeof option === 'string') {
                        return { value: option, label: option };
                    }
                    return {
                        value: option.value || option.key || option.label,
                        label: option.label || option.value || option
                    };
                });
            }
            
            // If options is an object (WPUF format), convert to array
            if (typeof options === 'object') {
                return Object.entries(options).map(([key, value]) => ({
                    value: key,
                    label: value
                }));
            }
            
            return [];
        },
        
        /**
         * Check if a prompt matches predefined template patterns
         */
        isPredefinedPrompt(prompt) {
            const prompt_lower = prompt.toLowerCase();
            const predefined_patterns = [
                'paid guest post',
                'guest post', 
                'portfolio',
                'classified ad',
                'classified',
                'coupon',
                'real estate',
                'property listing',
                'property',
                'news',
                'press release',
                'product listing',
                'product'
            ];
            
            return predefined_patterns.some(pattern => prompt_lower.includes(pattern));
        },
        
        /**
         * Generate a hash of the current form state for change detection
         */
        generateFormHash() {
            const formState = {
                title: this.formTitle,
                fields: this.formFields.map(field => ({
                    type: field.type,
                    label: field.label,
                    required: field.required,
                    options: field.options
                }))
            };
            
            // Simple hash generation
            return JSON.stringify(formState).split('').reduce((hash, char) => {
                return ((hash << 5) - hash + char.charCodeAt(0)) & 0xffffffff;
            }, 0);
        },
        
        /**
         * Initialize conversation state based on the original prompt
         */
        initializeConversationState(originalPrompt) {
            if (originalPrompt) {
                this.conversationState.original_prompt = originalPrompt;
                this.conversationState.is_predefined_template = this.isPredefinedPrompt(originalPrompt);
                this.conversationState.original_form_hash = this.generateFormHash();
                this.conversationState.form_created = true;
                
                console.log('🔍 Form initialized:', {
                    prompt: originalPrompt,
                    is_predefined: this.conversationState.is_predefined_template,
                    form_hash: this.conversationState.original_form_hash
                });
            }
        },
        
        /**
         * Check if the current form has been modified from the original predefined template
         */
        hasTemplateBeenModified() {
            if (!this.conversationState.is_predefined_template) {
                return false; // Not a predefined template, always allow API calls
            }
            
            const currentHash = this.generateFormHash();
            const isModified = currentHash !== this.conversationState.original_form_hash;
            
            if (isModified && !this.conversationState.template_modified) {
                this.conversationState.template_modified = true;
                console.log('📝 Predefined template has been modified, enabling API calls');
            }
            
            return isModified;
        },
        
        /**
         * Determine if an API call is necessary based on template state and user message
         */
        shouldMakeAPICall(userMessage) {
            // Always make API call if not a predefined template
            if (!this.conversationState.is_predefined_template) {
                return true;
            }
            
            // If template has been modified, make API calls
            if (this.conversationState.template_modified || this.hasTemplateBeenModified()) {
                return true;
            }
            
            // Check if user message requests modifications
            if (this.isModificationRequest(userMessage)) {
                this.conversationState.template_modified = true;
                console.log('🔧 User requested modifications, enabling API calls');
                return true;
            }
            
            // For predefined templates that haven't been modified, 
            // provide helpful responses without API calls
            return false;
        },
        
        /**
         * Check if user message contains modification requests
         */
        isModificationRequest(message) {
            const modificationKeywords = [
                'add', 'remove', 'delete', 'change', 'modify', 'update', 'edit', 
                'replace', 'include', 'exclude', 'insert', 'new field', 
                'another field', 'more fields', 'different', 'custom',
                'need to add', 'can you add', 'please add', 'also add',
                'make it', 'instead of', 'rather than', 'without the'
            ];
            
            const messageLower = message.toLowerCase();
            return modificationKeywords.some(keyword => messageLower.includes(keyword));
        },
        
        /**
         * Generate a helpful response for predefined templates without making API calls
         */
        generatePredefinedResponse() {
            const responses = [
                "This form is based on our predefined template. The current fields are optimized for this type of submission. If you'd like to modify the form, please let me know what changes you need and I'll help customize it for you.",
                "I can see you're working with our predefined template. The form looks good as it is! If you need to add, remove, or modify any fields, just tell me what changes you'd like to make.",
                "This form uses our predefined structure which works well for most cases. Would you like me to add any additional fields or modify the existing ones?",
                "The current form is optimized for this type of submission. If you need any customizations, please let me know what specific changes you'd like me to make."
            ];
            
            // Return a random helpful response
            const randomIndex = Math.floor(Math.random() * responses.length);
            return responses[randomIndex];
        },
        
        async handleSendMessage() {
            if (!this.userInput.trim() || this.isFormUpdating) return;
            
            const userMessage = this.userInput.trim();
            this.userInput = '';
            
            // Store original prompt if this is the first message
            if (!this.conversationState.form_created && this.chatMessages.length === 0) {
                this.initializeConversationState(userMessage);
            }
            
            // Check if we need to make an API call or can provide a predefined response
            const shouldCallAPI = this.shouldMakeAPICall(userMessage);
            
            console.log('💭 Chat message analysis:', {
                message: userMessage,
                is_predefined_template: this.conversationState.is_predefined_template,
                template_modified: this.conversationState.template_modified,
                should_call_api: shouldCallAPI
            });
            
            // Add user message to chat
            this.chatMessages.push({
                type: 'user',
                content: userMessage,
                timestamp: new Date().toISOString()
            });
            
            // Add processing indicator
            const processingMessage = {
                type: 'ai',
                content: 'Processing your request...',
                showButtons: false,
                isProcessing: true,
                timestamp: new Date().toISOString()
            };
            this.chatMessages.push(processingMessage);
            this.scrollToBottom();
            
            try {
                let response;
                
                if (shouldCallAPI) {
                    // Enable form updating blur effect for API calls
                    this.isFormUpdating = true;
                    
                    // Make real API call for modified templates or non-predefined forms
                    response = await this.callChatAPI(userMessage);
                } else {
                    // Provide predefined response without API call
                    response = {
                        success: true,
                        message: this.generatePredefinedResponse(),
                        action: 'info',
                        form_data: null // No form changes
                    };
                    
                    console.log('🚀 Using predefined response (no API call needed)');
                }
                
                // Remove processing message
                const processingIndex = this.chatMessages.findIndex(msg => msg.isProcessing);
                if (processingIndex !== -1) {
                    this.chatMessages.splice(processingIndex, 1);
                }
                
                // Add AI response
                if (response.success) {
                    const messageContent = response.message || 'Form has been updated successfully.';
                    const aiMessage = {
                        type: 'ai',
                        content: messageContent,
                        showButtons: this.shouldShowButtons(response, messageContent),
                        hasCheckpoint: false, // Will be set after auto-save
                        checkpointSaved: false,
                        response_data: response,
                        timestamp: new Date().toISOString()
                    };
                    
                    this.chatMessages.push(aiMessage);
                    
                    // Auto-save checkpoint for successful responses (not error responses)
                    if (response.form_data && !response.error) {
                        const messageIndex = this.chatMessages.length - 1;
                        const checkpoint = {
                            formFields: JSON.parse(JSON.stringify(this.formFields)),
                            formTitle: this.formTitle,
                            formDescription: this.formDescription,
                            formSettings: JSON.parse(JSON.stringify(this.formSettings)),
                            timestamp: Date.now(),
                            messageContent: messageContent
                        };
                        
                        // Store checkpoint
                        this.checkpoints.set(messageIndex, checkpoint);
                        
                        // Mark message as having checkpoint
                        this.chatMessages[messageIndex].hasCheckpoint = true;
                        this.chatMessages[messageIndex].checkpointSaved = true;
                    }
                    
                    // Show status if message has one
                    if (aiMessage.status) {
                        this.$nextTick(() => {
                            this.showStatus(this.chatMessages.length - 1);
                        });
                    }
                    
                    // Update conversation state
                    this.updateConversationState(userMessage, aiMessage);
                    
                    // If form was modified, update the preview
                    // Check both response.form_data (for initial generation) and response.data (for chat modifications)
                    const formData = response.form_data || response.data;
                    
                    if (formData && formData.wpuf_fields) {
                        // Store previous form state before making changes
                        this.previousFormFields = JSON.parse(JSON.stringify(this.formFields));
                        const previousDescription = this.formDescription;
                        
                        // Convert and store new fields
                        const newFields = this.convertFieldsToPreview(formData.wpuf_fields);
                        
                        // Store as pending changes for accept/reject
                        this.pendingChanges = {
                            type: 'form_update',
                            fields: newFields,
                            formTitle: formData.form_title || this.formTitle,
                            formDescription: formData.form_description || this.formDescription,
                            previousDescription: previousDescription,
                            originalResponse: formData
                        };
                        
                        // Apply changes to preview temporarily (will be reverted on reject)
                        this.formFields = newFields;
                        
                        if (formData.form_description) {
                            this.formDescription = formData.form_description;
                        }
                        
                        // Remove blur immediately
                        this.isFormUpdating = false;
                    } else if (response.data && response.data.modification_type === 'add_field' && response.data.changes && response.data.changes.field) {
                        // Store previous form state before making changes
                        this.previousFormFields = JSON.parse(JSON.stringify(this.formFields));
                        
                        // Handle chat API field additions - store as pending changes
                        const newField = response.data.changes.field;
                        const formattedField = {
                            id: newField.id || `field_${this.formFields.length + 1}`,
                            type: newField.type || 'text_field',
                            label: newField.label || 'New Field',
                            name: newField.name || newField.label?.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '') || 'new_field',
                            required: newField.required || false,
                            placeholder: newField.placeholder || '',
                            help_text: newField.help || '',
                            options: newField.options || [],
                            default: newField.default || ''
                        };
                        
                        // Store pending changes instead of applying immediately
                        this.pendingChanges = {
                            type: 'add_field',
                            field: formattedField,
                            originalResponse: response.data
                        };
                        
                        // Apply changes to preview temporarily (will be reverted on reject)
                        this.formFields.push(formattedField);
                        console.log('📝 Stored pending field addition:', formattedField);
                        
                        // Remove blur immediately
                        this.isFormUpdating = false;
                    } else {
                        // No form changes, remove blur immediately
                        this.isFormUpdating = false;
                    }
                } else {
                    const errorMessage = {
                        type: 'ai',
                        content: response.message || 'Sorry, I could not process your request. Please try again.',
                        showButtons: false,
                        isError: true,
                        timestamp: new Date().toISOString()
                    };

                    this.chatMessages.push(errorMessage);

                    // Show status if error message has one
                    if (errorMessage.status) {
                        this.$nextTick(() => {
                            this.showStatus(this.chatMessages.length - 1);
                        });
                    }

                    // Remove form updating blur on error
                    this.isFormUpdating = false;

                    this.updateConversationState(userMessage, errorMessage);
                }
                
            } catch (error) {
                console.error('Chat API error:', error);
                
                // Remove processing message
                const processingIndex = this.chatMessages.findIndex(msg => msg.isProcessing);
                if (processingIndex !== -1) {
                    this.chatMessages.splice(processingIndex, 1);
                }
                
                // Remove form updating blur
                this.isFormUpdating = false;
                
                // Add error message
                const errorMessage = {
                    type: 'ai',
                    content: 'Sorry, there was an error processing your request. Please check your connection and try again.',
                    showButtons: false,
                    isError: true,
                    timestamp: new Date().toISOString()
                };
                
                this.chatMessages.push(errorMessage);
                
                // Show status if error message has one
                if (errorMessage.status) {
                    this.$nextTick(() => {
                        this.showStatus(this.chatMessages.length - 1);
                    });
                }
                
                this.updateConversationState(userMessage, errorMessage);
            }
            
            this.scrollToBottom();
        },
        
        async callChatAPI(message) {
            const config = window.wpufAIFormBuilder || {};
            const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
            const nonce = config.nonce || '';
            
            // Build comprehensive conversation context
            const conversationContext = {
                session_id: this.sessionId,
                conversation_state: this.conversationState,
                current_form: {
                    form_title: this.formTitle,
                    form_description: this.formDescription,
                    wpuf_fields: this.formFields.map(field => ({
                        name: field.label,
                        type: field.type,
                        label: field.label,
                        placeholder: field.placeholder,
                        help: field.help_text,
                        required: field.required ? 'yes' : 'no',
                        options: field.options || [],
                        default: field.default || ''
                    })),
                    settings: this.formSettings || {}
                },
                // Send cleaned chat history (without processing/error messages)
                chat_history: this.chatMessages
                    .filter(msg => !msg.isProcessing && !msg.isError && msg.type)
                    .slice(-8) // Last 8 messages for context
                    .map(msg => ({
                        type: msg.type,
                        content: msg.content,
                        timestamp: msg.timestamp
                    }))
            };
            
            console.log('🚀 Sending chat API request:', {
                prompt: message,
                context_summary: {
                    session_id: this.sessionId,
                    form_title: this.formTitle,
                    fields_count: this.formFields.length,
                    messages_count: conversationContext.chat_history.length,
                    modifications_count: this.conversationState.modifications_count
                }
            });
            
            // Always use generate endpoint for chat modifications since form isn't saved yet
            // Only use modify-form endpoint if we have a saved form ID from database
            const endpoint = 'wpuf/v1/ai-form-builder/generate';

            // Prepare request body for generate endpoint
            const requestBody = {
                prompt: message,
                session_id: this.sessionId,
                conversation_context: conversationContext,
                provider: config.provider || 'google',
                temperature: config.temperature || 0.7,
                max_tokens: config.maxTokens || 2000
            };

            const response = await fetch(restUrl + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(requestBody)
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                console.error('❌ Chat API Error:', {
                    status: response.status,
                    statusText: response.statusText,
                    error: errorData
                });
                throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
            }
            
            const result = await response.json();
            console.log('✅ Chat API Response:', result);
            
            return result;
        },
        
        
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.chatContainer) {
                    this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                }
            });
        },
        
        async handleApply() {
            // Apply the current form state and create the actual WPUF form
            this.isApplying = true;
            
            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';
                
                // Prepare form data in WPUF format
                const formData = {
                    form_title: this.formTitle || 'AI Generated Form',
                    form_description: this.formDescription || 'Form created with AI assistance',
                    wpuf_fields: this.formFields.map((field, index) => {
                        // If field already has full WPUF structure (from predefined), use it as-is
                        if (field.input_type && field.template && field.wpuf_cond) {
                            return field;
                        }
                        
                        // Otherwise, convert to full WPUF structure (for chat-added fields)
                        const fieldType = field.type || 'text_field';
                        return {
                            id: field.id || `field_${index + 1}`,
                            type: fieldType,
                            input_type: this.mapToInputType(fieldType),
                            template: fieldType,
                            required: field.required === true || field.required === 'yes' ? 'yes' : 'no',
                            label: field.label || 'New Field',
                            name: field.name || (field.label ? field.label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '') : `field_${index + 1}`),
                            is_meta: this.shouldBeMeta(field.name || field.label) ? 'yes' : 'no',
                            help: field.help_text || field.help || '',
                            css: field.css || '',
                            placeholder: field.placeholder || '',
                            default: field.default || '',
                            size: field.size || '40',
                            width: field.width || 'large',
                            options: field.options || [],
                            wpuf_cond: field.wpuf_cond || {
                                condition_status: 'no',
                                cond_field: [],
                                cond_operator: ['='],
                                cond_option: ['- Select -'],
                                cond_logic: 'all'
                            },
                            wpuf_visibility: field.wpuf_visibility || {
                                selected: 'everyone',
                                choices: []
                            }
                        };
                    }),
                    form_settings: this.formSettings || {
                        submit_text: 'Submit',
                        success_message: 'Form submitted successfully!',
                        form_template: 'default'
                    }
                };
                
                console.log('🚀 Applying form with data:', formData);
                
                // Call the create form API
                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({ form_data: formData })
                });
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('❌ Apply Form Error:', errorData);
                    throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                }
                
                const result = await response.json();
                console.log('✅ Form Applied Successfully:', result);
                
                // Emit success event with form ID
                this.$emit('form-applied', {
                    form_id: result.form_id,
                    edit_url: result.edit_url
                });
                
            } catch (error) {
                console.error('❌ Apply Form Error:', error);
                
                // Show error to user
                const errorMessage = {
                    type: 'ai',
                    content: `Error applying form: ${error.message}. Please try again.`,
                    showButtons: false,
                    isError: true,
                    timestamp: new Date().toISOString()
                };
                
                this.chatMessages.push(errorMessage);
                
                // Show status if error message has one
                if (errorMessage.status) {
                    this.$nextTick(() => {
                        this.showStatus(this.chatMessages.length - 1);
                    });
                }
                
                this.scrollToBottom();
            } finally {
                this.isApplying = false;
                this.isFormUpdating = false; // Ensure blur is removed in any case
            }
        },
        
        handleReject() {
            // Revert to previous form state if there are pending changes
            if (this.pendingChanges && this.previousFormFields) {
                // Restore previous form fields
                this.formFields = JSON.parse(JSON.stringify(this.previousFormFields));
                
                // If this was a full form update, we might need to restore description too
                if (this.pendingChanges.type === 'form_update' && this.pendingChanges.previousDescription !== undefined) {
                    this.formDescription = this.pendingChanges.previousDescription;
                }
                
                console.log('🔄 Reverted form to previous state');
                
                // Clear pending changes
                this.pendingChanges = null;
                this.previousFormFields = null;
            }
            
            // Hide buttons from the current chat message
            this.hideLastMessageButtons();
        },
        
        handleAccept() {
            // Accept the pending changes (they're already applied to formFields)
            if (this.pendingChanges) {
                console.log('✅ Accepted pending changes:', this.pendingChanges);
                
                // Handle different types of pending changes
                if (this.pendingChanges.type === 'form_update') {
                    // Full form update - emit title and form update
                    if (this.pendingChanges.formTitle && this.pendingChanges.formTitle !== this.formTitle) {
                        this.$emit('title-updated', this.pendingChanges.formTitle);
                    }
                    
                    // Emit the complete form update with original response fields
                    // Use the original fields from pendingChanges to preserve proper format
                    this.$emit('form-updated', {
                        wpuf_fields: this.pendingChanges.originalResponse?.wpuf_fields || this.formFields,
                        form_title: this.pendingChanges.formTitle || this.formTitle,
                        form_description: this.pendingChanges.formDescription || this.formDescription
                    });
                } else {
                    // Field addition - just emit form update
                    this.$emit('form-updated', {
                        wpuf_fields: this.formFields,
                        form_title: this.formTitle,
                        form_description: this.formDescription
                    });
                }
                
                // Clear pending changes but keep the current form state
                this.pendingChanges = null;
                this.previousFormFields = null;
            }
            
            // Find the message with buttons and auto-save checkpoint
            for (let i = this.chatMessages.length - 1; i >= 0; i--) {
                if (this.chatMessages[i].showButtons) {
                    // Hide buttons and add checkpoint properties
                    this.chatMessages[i].showButtons = false;
                    
                    // Auto-save checkpoint for this accepted state
                    // Use original response fields if available to preserve proper format
                    const checkpoint = {
                        formFields: this.pendingChanges?.originalResponse?.wpuf_fields 
                            ? JSON.parse(JSON.stringify(this.pendingChanges.originalResponse.wpuf_fields))
                            : JSON.parse(JSON.stringify(this.formFields)),
                        formTitle: this.formTitle,
                        formDescription: this.formDescription,
                        formSettings: JSON.parse(JSON.stringify(this.formSettings)),
                        timestamp: Date.now(),
                        messageContent: this.chatMessages[i]?.content || ''
                    };
                    
                    // Store checkpoint
                    this.checkpoints.set(i, checkpoint);
                    
                    // Mark as having checkpoint and already saved
                    this.chatMessages[i].hasCheckpoint = true;
                    this.chatMessages[i].checkpointSaved = true;
                    
                    // Add a status message for accepted changes
                    this.chatMessages[i].acceptedStatus = '✓ Changes accepted & checkpoint saved';
                    break;
                }
            }
        },
        
        /**
         * Determine if accept/reject buttons should be shown for a response
         */
        shouldShowButtons(response, messageContent) {
            // Never show buttons for error responses
            if (!response || !response.success || response.error) {
                return false;
            }

            // Never show buttons for processing messages
            if (messageContent.includes('Processing') || messageContent.includes('Loading')) {
                return false;
            }

            // Never show buttons for initial form creation
            if (messageContent.includes('Perfect! I\'ve created') || 
                messageContent.includes('Successfully created the form') ||
                this.chatMessages.length === 0) {
                return false;
            }

            // Never show buttons for informational/help messages
            const informationalPhrases = [
                'here are some suggestions',
                'you can try',
                'for example',
                'available options include',
                'wpuf supports',
                'you might want to',
                'consider using',
                'alternatively',
                'note that',
                'please note',
                'keep in mind',
                'remember that',
                'tip:',
                'helpful tip',
                'pro tip',
                'suggestion:',
                'recommendations',
                'best practices',
                'you can also',
                'if you need',
                'to learn more',
                'for more information',
                'documentation',
                'help',
                'guide',
                'tutorial',
                'how to',
                'instructions'
            ];

            const lowerContent = messageContent.toLowerCase();
            if (informationalPhrases.some(phrase => lowerContent.includes(phrase))) {
                return false;
            }

            // Never show buttons for simple confirmations without changes
            const simpleConfirmations = [
                'understood',
                'got it',
                'okay',
                'sure',
                'no problem',
                'will do',
                'of course',
                'absolutely',
                'certainly'
            ];

            if (simpleConfirmations.some(phrase => lowerContent.includes(phrase)) && 
                lowerContent.length < 100) {
                return false;
            }

            // Show buttons for form modifications that require user confirmation
            if (response.form_data || response.data) {
                // Check if it's a form modification
                const hasFormChanges = response.form_data?.wpuf_fields || 
                                     response.form_data?.fields ||
                                     response.data?.modification_type ||
                                     response.data?.changes;

                if (hasFormChanges) {
                    return true;
                }
            }

            // Show buttons for subsequent messages with actionable content
            const isSubsequentMessage = this.chatMessages.length > 1;
            const hasActionableContent = lowerContent.includes('added') || 
                                       lowerContent.includes('removed') || 
                                       lowerContent.includes('changed') || 
                                       lowerContent.includes('updated') || 
                                       lowerContent.includes('modified') || 
                                       lowerContent.includes('converted') ||
                                       lowerContent.includes('replaced') ||
                                       lowerContent.includes('field') ||
                                       lowerContent.includes('form') ||
                                       lowerContent.includes('button') ||
                                       lowerContent.includes('title') ||
                                       lowerContent.includes('description');

            return isSubsequentMessage && hasActionableContent;
        },

        hideLastMessageButtons() {
            // Find the last message with buttons and hide them
            for (let i = this.chatMessages.length - 1; i >= 0; i--) {
                if (this.chatMessages[i].showButtons) {
                    this.chatMessages[i].showButtons = false;
                    break;
                }
            }
        },
        
        mapToInputType(fieldType) {
            // Map WPUF field types to input types
            const typeMap = {
                'text_field': 'text',
                'email_address': 'email', 
                'website_url': 'url',
                'numeric_text_field': 'number',
                'phone_field': 'tel',
                'textarea_field': 'textarea',
                'dropdown_field': 'select',
                'radio_field': 'radio',
                'checkbox_field': 'checkbox',
                'multiple_select': 'multiselect',
                'file_upload': 'file_upload',
                'date_field': 'date',
                'time_field': 'time',
                'address_field': 'address_field',
                'country_list_field': 'select',
                'toc': 'checkbox',
                'google_map': 'google_map',
                'ratings': 'ratings'
            };
            return typeMap[fieldType] || 'text';
        },
        
        shouldBeMeta(fieldName) {
            // Standard WordPress meta fields
            const metaFields = ['title', 'content', 'excerpt', 'author', 'category', 'tags'];
            return !metaFields.includes(fieldName?.toLowerCase());
        },
        
        handleRegenerate() {
            // Show confirmation modal before regenerating
            this.showRegenerateModal = true;
        },
        
        confirmRegenerate() {
            // Close modal
            this.showRegenerateModal = false;
            
            // Clear all chat messages and form state
            this.chatMessages = [];
            this.formFields = [];
            this.previousFormFields = [];
            this.pendingChanges = null;
            this.formDescription = '';
            this.userInput = '';
            this.checkpoints.clear(); // Clear all checkpoints
            this.conversationState = {
                original_prompt: '',
                form_created: false,
                modifications_count: 0,
                context_history: [],
                is_predefined_template: false,
                template_modified: false,
                original_form_hash: null
            };
            
            // Generate new session ID to close current session
            this.sessionId = this.generateSessionId();
            
            // Emit regenerate event to parent
            this.$emit('regenerate-form');
        },
        
        handleEditInBuilder() {
            // Clear all checkpoints when editing in builder
            this.checkpoints.clear();
            
            // Check if form has pro fields and show notification only if Pro is not active
            const config = window.wpufAIFormBuilder || {};
            const isProActive = config.isProActive || false;
            
            if (!isProActive && this.checkForProFields()) {
                this.showProModal();
                return; // Don't proceed to edit if Pro is not active and form has pro fields
            }
            
            // Pass both formId and current form fields to parent
            this.$emit('edit-in-builder', {
                formId: this.formId,
                formFields: this.formFields
            });
        },
        
        handleEditWithBuilder() {
            // Check if form has pro fields and show notification only if Pro is not active
            const config = window.wpufAIFormBuilder || {};
            const isProActive = config.isProActive || false;
            
            if (!isProActive && this.checkForProFields()) {
                this.showProModal();
                return; // Don't proceed to edit if Pro is not active and form has pro fields
            }
            
            // Pass both formId and current form fields to parent
            this.$emit('edit-with-builder', {
                formId: this.formId,
                formFields: this.formFields
            });
        },
        
        checkForProFields() {
            // Comprehensive list of WPUF Pro field types
            // These can appear in type, input_type, or template properties
            const proFieldIdentifiers = [
                // Date/Time fields (Pro)
                'date_field',
                'time_field',
                'datetime_field',
                
                // Number/Phone fields (Pro)
                'numeric_text_field', 'numeric_field',
                'phone_field', 'phone_number',
                
                // Location fields (Pro)
                'address_field',
                'country_list_field', 'country_list',
                'google_map',
                
                // Advanced selection fields (Pro)
                'multiple_select', 'multi_select',
                'checkbox_grid',
                'multiple_choice_grid',
                
                // File upload fields (Pro)
                'file_upload',
                'audio_upload',
                'video_upload',
                
                // Special fields (Pro)
                'ratings', 'rating',
                'linear_scale',
                'qr_code',
                'embed',
                'shortcode',
                'action_hook',
                'toc', 'terms_conditions',
                'column_field',
                'step_start', 'multistep',
                'repeat_field', 'repeater',
                'really_simple_captcha',
                'math_captcha'
            ];
            
            // List of free fields that should NOT be considered as pro
            const freeFieldIdentifiers = [
                'text', 'text_field',
                'email', 'email_address',
                'url', 'website_url',
                'textarea', 'textarea_field',
                'dropdown', 'dropdown_field', 'select',
                'checkbox', 'checkbox_field',
                'radio', 'radio_field',
                'hidden', 'hidden_field',
                'html', 'section_break',
                'post_title', 'post_content', 'post_excerpt',
                'post_tags', 'taxonomy', 'category',
                'featured_image', 'image_upload',
                'recaptcha', 'recaptcha_v2', 'recaptcha_v3',
                'cloudflare_turnstile'
            ];
            
            // Check if any field in the form is a pro field
            // Check type, input_type, and template properties
            const foundProFields = this.formFields.filter(field => {
                // Check all three properties for field identifiers
                const fieldType = (field.type || '').toLowerCase();
                const inputType = (field.input_type || '').toLowerCase();
                const template = (field.template || '').toLowerCase();
                
                // First check if it's explicitly a free field
                const isFreeField = freeFieldIdentifiers.some(freeType => {
                    const freeTypeLower = freeType.toLowerCase();
                    return fieldType === freeTypeLower || 
                           inputType === freeTypeLower || 
                           template === freeTypeLower;
                });
                
                // If it's a free field, don't mark it as pro
                if (isFreeField) {
                    return false;
                }
                
                // Check if it's a pro field
                return proFieldIdentifiers.some(proType => {
                    const proTypeLower = proType.toLowerCase();
                    return fieldType === proTypeLower || 
                           inputType === proTypeLower || 
                           template === proTypeLower;
                });
            });
            
            console.log('Pro field detection:', {
                totalFields: this.formFields.length,
                proFieldsFound: foundProFields.length,
                proFields: foundProFields.map(f => ({
                    name: f.name,
                    type: f.type,
                    input_type: f.input_type,
                    template: f.template
                }))
            });
            
            return foundProFields.length > 0 ? foundProFields : null;
        },
        
        showProModal() {
            // Get the pro fields in the form
            const proFields = this.checkForProFields();
            if (!proFields) return;
            
            // Get unique field types with proper labels
            const fieldTypeLabels = {
                // Date/Time (Pro)
                'date_field': 'Date Picker',
                'time_field': 'Time Picker',
                'datetime_field': 'Date & Time',
                
                // Number/Phone
                'numeric_text_field': 'Numeric Text',
                'numeric_field': 'Numeric Text',
                'phone_field': 'Phone Number',
                'phone_number': 'Phone Number',
                'phone': 'Phone Number',
                
                // Location
                'address_field': 'Address',
                'address': 'Address',
                'country_list_field': 'Country List',
                'country_list': 'Country List',
                'google_map': 'Google Map',
                'map': 'Google Map',
                
                // Selection
                'multiple_select': 'Multi Select',
                'multi_select': 'Multi Select',
                'checkbox_grid': 'Checkbox Grid',
                'multiple_choice_grid': 'Multiple Choice Grid',
                
                // Files
                'file_upload': 'File Upload',
                'file': 'File Upload',
                'audio_upload': 'Audio Upload',
                'audio': 'Audio Upload',
                'video_upload': 'Video Upload',
                'video': 'Video Upload',
                
                // Special
                'ratings': 'Ratings',
                'rating': 'Ratings',
                'linear_scale': 'Linear Scale',
                'qr_code': 'QR Code',
                'embed': 'Embed',
                'shortcode': 'Shortcode',
                'action_hook': 'Action Hook',
                'toc': 'Terms & Conditions',
                'terms_conditions': 'Terms & Conditions',
                'column_field': 'Column Field',
                'column': 'Column Field',
                'step_start': 'Multi-Step Start',
                'multistep': 'Multi-Step Start',
                'repeat_field': 'Repeat Field',
                'repeater': 'Repeat Field',
                'really_simple_captcha': 'Really Simple Captcha',
                'captcha': 'Captcha',
                'recaptcha': 'reCAPTCHA',
                'recaptcha_v2': 'reCAPTCHA',
                'recaptcha_v3': 'reCAPTCHA v3'
            };
            
            // Get unique pro field types - check type, input_type, and template
            const getFieldDisplayType = (field) => {
                // Try to get the most specific identifier for the field
                return field.type || field.input_type || field.template || 'Unknown Field';
            };
            
            const uniqueProFields = [...new Set(proFields.map(field => getFieldDisplayType(field)))];
            const displayFields = uniqueProFields.slice(0, 4).map(type => {
                // Try to find a label for any of the possible type values
                const typeLower = type.toLowerCase();
                return {
                    fieldKey: typeLower,
                    label: fieldTypeLabels[typeLower] || fieldTypeLabels[type] || type
                };
            });
            
            // Create modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'wpuf-pro-modal-overlay';
            modalOverlay.innerHTML = `
                <div class="wpuf-pro-modal">
                    <div class="wpuf-pro-modal-header">
                        <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="110" height="110" rx="55" fill="#D1FAE5"/>
                            <path d="M66.0557 51.0931C66.0594 51.1593 66.0545 51.2268 66.0379 51.2937L64.5576 59.0337C64.4829 59.333 64.2155 59.5434 63.9082 59.545L55.0259 59.59H55.0225H46.1402C45.8312 59.59 45.5619 59.3789 45.4873 59.0781L44.0069 51.3156C43.9899 51.2468 43.9849 51.1775 43.9892 51.1096C43.4166 50.9286 43 50.391 43 49.7575C43 48.9759 43.6339 48.34 44.4131 48.34C45.1923 48.34 45.8262 48.9759 45.8262 49.7575C45.8262 50.1977 45.6251 50.5915 45.3103 50.8517L47.1637 52.725C47.6321 53.1985 48.2822 53.4699 48.9472 53.4699C49.7335 53.4699 50.4832 53.0953 50.9553 52.4678L54.0012 48.4192C53.7454 48.1627 53.5869 47.8083 53.5869 47.4175C53.5869 46.6358 54.2208 46 55 46C55.7792 46 56.4131 46.6358 56.4131 47.4175C56.4131 47.7966 56.2631 48.1406 56.0206 48.3953L56.0232 48.3984L59.0471 52.4581C59.519 53.0917 60.2714 53.47 61.0599 53.47C61.731 53.47 62.3621 53.2078 62.8367 52.7317L64.7017 50.8608C64.3803 50.6007 64.1738 50.2031 64.1738 49.7575C64.1738 48.9759 64.8077 48.34 65.5869 48.34C66.3661 48.34 67 48.9759 67 49.7575C67 50.3741 66.6048 50.8985 66.0557 51.0931ZM64.4131 61.705C64.4131 61.3322 64.1118 61.03 63.7402 61.03H46.3346C45.963 61.03 45.6617 61.3322 45.6617 61.705V63.325C45.6617 63.6978 45.963 64 46.3346 64H63.7402C64.1118 64 64.4131 63.6978 64.4131 63.325V61.705Z" fill="#0F172A"/>
                        </svg>
                    </div>
                    <h2 class="wpuf-pro-modal-title">${this.__('Pro feature detected', 'wp-user-frontend')}</h2>
                    <p class="wpuf-pro-modal-description">${this.__('Your form includes fields that require WPUF Pro version:', 'wp-user-frontend')}</p>
                    <div class="wpuf-pro-fields-list">
                        ${displayFields.map(field => {
                            const fieldKey = field.fieldKey || field;
                            const fieldLabel = field.label || field;
                            const iconUrl = this.getProFieldIcon(fieldKey);
                            const iconHtml = iconUrl 
                                ? `<img src="${iconUrl}" alt="${fieldLabel}" class="wpuf-pro-field-icon" onerror="this.style.display='none'" />` 
                                : '';
                            return `
                            <div class="wpuf-pro-field-item">
                                ${iconHtml}
                                <span>${fieldLabel}</span>
                            </div>
                        `;
                        }).join('')}
                    </div>
                    <div class="wpuf-pro-modal-buttons">
                        <button class="wpuf-pro-btn-continue">${this.__('Continue without Pro', 'wp-user-frontend')}</button>
                        <button class="wpuf-pro-btn-upgrade">${this.__('Upgrade to Pro', 'wp-user-frontend')}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modalOverlay);
            
            // Add styles if not already present
            if (!document.getElementById('wpuf-pro-modal-styles')) {
                const style = document.createElement('style');
                style.id = 'wpuf-pro-modal-styles';
                style.textContent = `
                    .wpuf-pro-modal-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 999999;
                        animation: fadeIn 0.3s ease;
                    }
                    .wpuf-pro-modal {
                        background: white;
                        border-radius: 8px;
                        width: 660px;
                        max-height: 702px;
                        padding: 40px;
                        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                        animation: slideUp 0.3s ease;
                        position: relative;
                        text-align: center;
                    }
                    .wpuf-pro-modal-header {
                        position: relative;
                        display: inline-block;
                        margin-bottom: 24px;
                    }
                    .wpuf-pro-modal-header svg {
                        width: 110px;
                        height: 110px;
                    }
                    
                    .wpuf-pro-modal-title {
                        font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        font-size: 28px;
                        font-weight: 600;
                        color: #1F2937;
                        margin: 0 0 12px 0;
                        line-height: 1.2;
                    }
                    .wpuf-pro-modal-description {
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        font-size: 16px;
                        color: #6B7280;
                        margin: 0 0 32px 0;
                        line-height: 1.5;
                    }
                    .wpuf-pro-fields-list {
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                        margin: 20px 0 24px;
                        padding: 0;
                    }
                    .wpuf-pro-field-item {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        width: 494px;
                        height: 56px;
                        padding: 16px 12px;
                        background: #FFFFFF;
                        border: 1px solid #E2E8F0;
                        border-radius: 8px;
                        font-size: 14px;
                        color: #374151;
                        box-sizing: border-box;
                        margin: 0 auto;
                    }
                    .wpuf-pro-field-icon {
                        width: 24px;
                        height: 24px;
                        flex-shrink: 0;
                    }
                    .wpuf-pro-modal-buttons {
                        display: flex;
                        gap: 16px;
                        justify-content: center;
                    }
                    .wpuf-pro-btn-continue,
                    .wpuf-pro-btn-upgrade {
                        padding: 12px 24px;
                        border-radius: 6px;
                        font-size: 16px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        border: none;
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }
                    .wpuf-pro-btn-continue {
                        background: white;
                        color: #334155;
                        border: 1px solid #E5E7EB;
                    }
                    .wpuf-pro-btn-upgrade {
                        background: #059669;
                        color: white;
                    }
                    .wpuf-pro-btn-upgrade:hover {
                        background: #059669;
                    }
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Handle button clicks
            const continueBtn = modalOverlay.querySelector('.wpuf-pro-btn-continue');
            const upgradeBtn = modalOverlay.querySelector('.wpuf-pro-btn-upgrade');
            
            continueBtn.addEventListener('click', () => {
                modalOverlay.remove();
                // Proceed with editing anyway
                this.$emit('edit-in-builder', {
                    formId: this.formId,
                    formFields: this.formFields
                });
            });
            
            upgradeBtn.addEventListener('click', () => {
                window.open('https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpdashboard&utm_medium=popup', '_blank');
                modalOverlay.remove();
            });
            
            // Close on overlay click
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    modalOverlay.remove();
                }
            });
        },
        
        
        getProFieldsList() {
            const proFieldsMap = {
                'numeric_text_field': 'Numeric Text Field',
                'phone_number': 'Phone Number',
                'address_field': 'Address Field',
                'country_list_field': 'Country List',
                'repeat_field': 'Repeatable Field',
                'date_field': 'Date Picker',
                'time_field': 'Time Picker',
                'datetime_field': 'Date & Time',
                'multiple_select': 'Multiple Select',
                'checkbox_grid': 'Checkbox Grid',
                'multiple_choice_grid': 'Radio Grid',
                'file_upload': 'File Upload',
                'audio_upload': 'Audio Upload',
                'video_upload': 'Video Upload',
                'google_map': 'Google Maps',
                'really_simple_captcha': 'Simple Captcha',
                'recaptcha': 'reCAPTCHA',
                'ratings': 'Star Rating',
                'linear_scale': 'Linear Scale',
                'qr_code': 'QR Code',
                'embed': 'Embed',
                'shortcode': 'Shortcode',
                'action_hook': 'Action Hook',
                'toc': 'Terms & Conditions',
                'column_field': 'Column Layout',
                'step_start': 'Multi-step Form'
            };
            
            // Get unique pro fields in this form
            const proFieldsInForm = this.formFields
                .filter(field => proFieldsMap[field.type])
                .map(field => `<li>${field.label} <span class="field-type">${proFieldsMap[field.type]}</span></li>`)
                .slice(0, 5); // Show max 5 fields
            
            if (proFieldsInForm.length === 0) {
                return '<li>No pro fields detected</li>';
            }
            
            return proFieldsInForm.join('');
        },
        
        getProFieldIcon(fieldKey) {
            // Map field keys to their corresponding WPUF Pro icons
            const iconMap = {
                // Date/Time fields (Pro only)
                'date': 'clock',
                'date_field': 'clock',
                'time': 'clock',
                'time_field': 'clock',
                'datetime': 'clock',
                'datetime_field': 'clock',
                
                // Location fields (Pro)
                'address_field': 'map',
                'country_list': 'globe-alt',
                'country_list_field': 'globe-alt',
                'google_map': 'location-marker',
                
                // Input fields (Pro)
                'numeric_text_field': 'adjustments-horizontal',
                'numeric_field': 'adjustments-horizontal',
                'phone_field': 'phone',
                'phone_number': 'phone',
                
                // File fields (Pro)
                'file_upload': 'arrow-up-tray',
                'audio_upload': 'arrow-up-tray',
                'video_upload': 'arrow-up-tray',
                
                // Special fields
                'ratings': 'star',
                'rating': 'star',
                'linear_scale': 'ellipsis-h',
                'qr_code': 'qrcode',
                'embed': 'code-bracket-square',
                'shortcode': 'code-bracket-square',
                'action_hook': 'command-line',
                'toc': 'exclamation-circle',
                'terms_conditions': 'exclamation-circle',
                'step_start': 'play',
                'multistep': 'play',
                'repeat_field': 'rectangle-stack',
                'repeater': 'rectangle-stack',
                'really_simple_captcha': 'document-check',
                'captcha': 'document-check',
                'math_captcha': 'check-circle',
                'checkbox_grid': 'th',
                'multiple_choice_grid': 'braille',
                'column_field': 'th',
                'multiple_select': 'squares-2x2',
                'multi_select': 'squares-2x2'
            };
            
            const iconName = iconMap[fieldKey];
            
            // If no icon found, return empty string (no icon)
            if (!iconName) {
                console.warn(`No icon mapping found for field: ${fieldKey}`);
                return '';
            }
            
            // Build the icon URL based on WPUF's asset structure
            const config = window.wpufAIFormBuilder || {};
            const assetUrl = config.assetUrl || '/wp-content/plugins/wp-user-frontend/assets';
            
            // Always use the regular asset URL for icons (they're in the free version)
            return `${assetUrl}/images/${iconName}.svg`;
        },
        
        // WPUF field type helper methods
        getFieldPlaceholder(fieldType) {
            const placeholders = {
                // Free fields
                'text_field': 'Enter text...',
                'text': 'Enter text...',
                'email_address': 'Enter email address...',
                'email': 'Enter email address...',
                'website_url': 'Enter website URL...',
                'url': 'Enter website URL...',
                'textarea_field': 'Enter your message...',
                'textarea': 'Enter your message...',
                'dropdown_field': 'Select an option',
                'select': 'Select an option',
                'multiple_select': 'Select multiple options',
                'radio_field': 'Select one option',
                'radio': 'Select one option',
                'checkbox_field': 'Select options',
                'checkbox': 'Select options',
                'image_upload': 'Upload image files',
                'file': 'Upload files',
                'featured_image': 'Upload featured image',
                'custom_hidden_field': 'Hidden field value',
                
                // Pro fields
                'address_field': 'Enter full address...',
                'country_list_field': 'Select country',
                'date_field': 'Select date',
                'time_field': 'Select time',
                'phone_field': 'Enter phone number',
                'numeric_text_field': 'Enter number',
                'file_upload': 'Upload files (Pro)',
                'google_map': 'Click to set location',
                'embed': 'Embed content will appear here',
                'qr_code': 'QR code will be generated',
                'ratings': 'Rate from 1 to 5 stars',
                'linear_scale': 'Select from 1 to 10',
                'checkbox_grid': 'Select checkboxes in grid',
                'multiple_choice_grid': 'Select radio options in grid',
                'repeat_field': 'Repeatable field group',
                'really_simple_captcha': 'Enter captcha code',
                'math_captcha': 'Solve math problem',
                'shortcode': 'Shortcode output',
                'action_hook': 'Custom hook execution',
                'toc': 'Accept terms and conditions',
                
                // Post fields
                'post_title': 'Enter post title',
                'post_content': 'Enter post content',
                'post_excerpt': 'Enter post excerpt',
                'post_tags': 'Enter tags (comma separated)',
                'taxonomy': 'Select categories',
                
                // Layout fields
                'section_break': 'Section break',
                'column_field': 'Column layout',
                'step_start': 'Multi-step form section',
                'custom_html': 'Custom HTML content',
                
                // Date/time
                'date': 'Select date',
                'time': 'Select time',
                'datetime': 'Select date and time'
            };
            return placeholders[fieldType] || 'Enter value...';
        },
        
        getWPUFFieldTypeLabel(fieldType) {
            const labels = {
                // Free fields
                'text_field': 'Text Field',
                'text': 'Text',
                'email_address': 'Email Address',
                'email': 'Email',
                'website_url': 'Website URL',
                'url': 'URL',
                'textarea_field': 'Text Area',
                'textarea': 'Text Area',
                'dropdown_field': 'Dropdown',
                'select': 'Select',
                'multiple_select': 'Multi Select',
                'radio_field': 'Radio Button',
                'radio': 'Radio',
                'checkbox_field': 'Checkbox',
                'checkbox': 'Checkbox',
                'image_upload': 'Image Upload',
                'file': 'File Upload',
                'featured_image': 'Featured Image',
                'custom_html': 'HTML Content',
                'custom_hidden_field': 'Hidden Field',
                'section_break': 'Section Break',
                'column_field': 'Column Layout',
                'recaptcha': 'reCAPTCHA',
                'cloudflare_turnstile': 'Cloudflare Turnstile',
                
                // Pro fields
                'address_field': 'Address Field',
                'country_list_field': 'Country List',
                'date_field': 'Date/Time Field',
                'time_field': 'Time Field',
                'phone_field': 'Phone Field',
                'numeric_text_field': 'Numeric Field',
                'file_upload': 'File Upload (Pro)',
                'google_map': 'Google Map',
                'embed': 'Embed Field',
                'qr_code': 'QR Code',
                'ratings': 'Star Rating',
                'linear_scale': 'Linear Scale',
                'checkbox_grid': 'Checkbox Grid',
                'multiple_choice_grid': 'Multiple Choice Grid',
                'repeat_field': 'Repeat Field',
                'really_simple_captcha': 'Really Simple CAPTCHA',
                'math_captcha': 'Math CAPTCHA',
                'shortcode': 'Shortcode',
                'action_hook': 'Action Hook',
                'toc': 'Terms & Conditions',
                'step_start': 'Step Start',
                
                // Post fields
                'post_title': 'Post Title',
                'post_content': 'Post Content',
                'post_excerpt': 'Post Excerpt',
                'post_tags': 'Post Tags',
                'taxonomy': 'Taxonomy/Categories',
                
                // Date/time fallbacks
                'date': 'Date',
                'time': 'Time',
                'datetime': 'Date & Time'
            };
            return labels[fieldType] || fieldType;
        }
    },
    mounted() {
        // Initialize form fields from props or defaults
        this.formFields = this.initializeFormFields();
        this.previousFormFields = [...this.formFields];
        
        // Don't show loader initially - only show it during actual transitions
        // The loader should only appear when fields are actively changing
        this.isFormUpdating = false;
        
        // Initialize chat messages from props
        this.chatMessages = this.initializeChatMessages();
        
        this.scrollToBottom();
        
        // Initialize conversation state if we have initial messages
        if (this.initialMessages && this.initialMessages.length > 0) {
            // Find the first user message to determine if it was from a predefined template
            const firstUserMessage = this.initialMessages.find(msg => msg.type === 'user');
            if (firstUserMessage) {
                this.initializeConversationState(firstUserMessage.content);
            }
            
            // Show status messages for initial messages that have them
            this.$nextTick(() => {
                this.chatMessages.forEach((message, index) => {
                    if (message.status) {
                        this.showStatus(index);
                    }
                });
            });
        }
        
        // If formTitle suggests a predefined template, initialize accordingly  
        if (this.formTitle && !this.conversationState.form_created) {
            this.initializeConversationState(this.formTitle);
        }
    },
    
    beforeDestroy() {
        // Clear all status timeouts to prevent memory leaks
        this.statusTimeouts.forEach(timeoutId => {
            clearTimeout(timeoutId);
        });
        this.statusTimeouts.clear();
    }
};
</script>
