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
                                            </div>
                                            <div v-if="message.status" class="wpuf-message-status wpuf-mt-2 wpuf-font-normal wpuf-italic wpuf-text-sm wpuf-leading-6 wpuf-tracking-normal wpuf-text-right wpuf-text-emerald-600">
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
                                    :placeholder="__('Type your message here...', 'wp-user-frontend')"
                                ></textarea>
                                <button @click="handleSendMessage" class="wpuf-send-button wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-rounded-lg wpuf-w-11 wpuf-h-11 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-cursor-pointer wpuf-flex-shrink-0 hover:wpuf-bg-emerald-800 wpuf-transition-colors">
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
                            <svg class="wpuf-animate-spin wpuf-w-8 wpuf-h-8 wpuf-text-emerald-600 wpuf-mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="wpuf-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="wpuf-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="wpuf-text-emerald-600 wpuf-font-medium wpuf-text-lg">{{ __('Generating...', 'wp-user-frontend') }}</p>
                            <p class="wpuf-text-gray-500 wpuf-text-sm">{{ __('Updating your form fields', 'wp-user-frontend') }}</p>
                        </div>
                        <div class="wpuf-form-header wpuf-border-b wpuf-border-gray-200 wpuf-pb-4 wpuf-flex-shrink-0">
                            <h3 class="wpuf-form-title wpuf-font-bold wpuf-text-3xl wpuf-leading-9 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-900 wpuf-m-0 wpuf-mb-2">{{ formTitle }}</h3>
                            <p class="wpuf-form-description wpuf-font-normal wpuf-text-lg wpuf-leading-6 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-500 wpuf-m-0">{{ __('Please complete all information below', 'wp-user-frontend') }}</p>
                        </div>
                        
                        <div class="wpuf-form-scrollable wpuf-flex-1 wpuf-overflow-y-auto wpuf-mb-4" style="scrollbar-width: thin; scrollbar-color: #10B981 transparent;">
                            <div class="wpuf-form-fields wpuf-flex wpuf-flex-col wpuf-gap-5">
                                <div v-for="field in formFields" :key="field.id" class="wpuf-form-field wpuf-flex wpuf-flex-col wpuf-gap-2">
                                    <!-- Field Label with Required Indicator -->
                                    <label class="wpuf-form-label wpuf-font-normal wpuf-text-base wpuf-leading-6 wpuf-tracking-normal wpuf-text-gray-900 wpuf-flex wpuf-items-center wpuf-gap-1">
                                        {{ field.label }}
                                        <span v-if="field.required" class="wpuf-required wpuf-text-red-500 wpuf-font-bold">*</span>
                                    </label>
                                    
                                    <!-- Help Text -->
                                    <p v-if="field.help_text" class="wpuf-field-help wpuf-text-sm wpuf-text-gray-500 wpuf-m-0 wpuf-mb-1">{{ field.help_text }}</p>
                                    
                                    <!-- WPUF Text Fields -->
                                    <div v-if="['text_field', 'text', 'email_address', 'email', 'website_url', 'url', 'numeric_text_field', 'phone_field'].includes(field.type)" 
                                         class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ field.placeholder || getFieldPlaceholder(field.type) }}</span>
                                    </div>
                                    
                                    <!-- WPUF Dropdown/Select -->
                                    <div v-else-if="['dropdown_field', 'select'].includes(field.type)" class="wpuf-form-select-container">
                                        <div class="wpuf-form-input wpuf-select wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-cursor-pointer"
                                             :class="''"
                                        >
                                            <span class="wpuf-text-gray-400">{{ field.placeholder || __('Select an option', 'wp-user-frontend') }}</span>
                                            <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.25 0.875001L7 7.125L0.75 0.875001" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg> 
                                        </div>
                                        <div v-if="field.options && field.options.length" class="wpuf-select-options wpuf-mt-2 wpuf-text-sm wpuf-text-gray-600">
                                            <strong>{{ __('Options:', 'wp-user-frontend') }}</strong>
                                            <ul class="wpuf-options-list wpuf-ml-4 wpuf-mt-1">
                                                <li v-for="option in field.options" :key="option.value" class="wpuf-option-item">
                                                    • {{ option.label }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Radio Buttons -->
                                    <div v-else-if="['radio_field', 'radio'].includes(field.type)" class="wpuf-form-radio-container">
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
                                    
                                    <!-- WPUF File Upload -->
                                    <div v-else-if="['image_upload', 'file', 'featured_image', 'file_upload'].includes(field.type)" 
                                         class="wpuf-form-file wpuf-border-2 wpuf-border-dashed wpuf-border-gray-300 wpuf-rounded wpuf-p-5 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-gap-2 wpuf-bg-gray-50 wpuf-text-gray-500 wpuf-text-center wpuf-text-sm"
                                         :class="''"
                                    >
                                        <svg class="wpuf-file-icon wpuf-w-8 wpuf-h-8 wpuf-text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4V16M12 4L8 8M12 4L16 8M4 17V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="wpuf-file-text">
                                            <strong>{{ __('Click to upload', 'wp-user-frontend') }}</strong>
                                            <div class="wpuf-text-gray-400">{{ field.placeholder || 'Drop files here or click to upload' }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Textarea -->
                                    <div v-else-if="['textarea_field', 'textarea'].includes(field.type)" 
                                         class="wpuf-form-textarea wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-min-h-[100px] wpuf-relative"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ field.placeholder || __('Enter your text here...', 'wp-user-frontend') }}</span>
                                    </div>
                                    
                                    <!-- WPUF Multiple Select -->
                                    <div v-else-if="['multiple_select', 'country_list_field'].includes(field.type)" class="wpuf-form-multiselect-container">
                                        <div class="wpuf-form-input wpuf-multiselect wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50"
                                             :class="''"
                                        >
                                            <span class="wpuf-text-gray-400">{{ field.placeholder || getFieldPlaceholder(field.type) }}</span>
                                        </div>
                                        <div v-if="field.options && field.options.length" class="wpuf-multiselect-options wpuf-mt-2 wpuf-text-sm wpuf-text-gray-600">
                                            <strong>{{ __('Available options:', 'wp-user-frontend') }}</strong>
                                            <ul class="wpuf-options-list wpuf-ml-4 wpuf-mt-1">
                                                <li v-for="option in field.options" :key="option.value" class="wpuf-option-item">
                                                    • {{ option.label }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <!-- WPUF Pro Date/Time Fields -->
                                    <div v-else-if="['date_field', 'time_field', 'date', 'time', 'datetime'].includes(field.type)" 
                                         class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between"
                                         :class="''"
                                    >
                                        <span class="wpuf-text-gray-400">{{ getFieldPlaceholder(field.type) }}</span>
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
                                    
                                    <!-- WPUF Post Fields -->
                                    <div v-else-if="['post_title', 'post_content', 'post_excerpt', 'post_tags', 'taxonomy'].includes(field.type)" 
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
                                    <div v-else-if="['custom_html', 'shortcode', 'action_hook', 'toc'].includes(field.type)" 
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
</style>

<script>
export default {
    name: 'FormSuccessStage',
    props: {
        formTitle: {
            type: String,
            default: 'Portfolio Submission'
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
            chatMessages: this.initializeChatMessages(),
            formFields: this.initializeFormFields(),
            previousFormFields: this.initializeFormFields(), // Store previous state for reject
            pendingChanges: null, // Store pending changes from chat
            isApplying: false,
            isFormUpdating: false
        };
    },
    methods: {
        __: window.__ || ((text) => text),
        
        generateSessionId() {
            return 'wpuf_chat_session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
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
            // If we have initial form fields from props, use them
            if (this.initialFormFields && this.initialFormFields.length > 0) {
                return [...this.initialFormFields];
            }
            
            // Otherwise start with basic portfolio submission form
            return [
                { id: 1, type: 'text_field', label: 'First Name', placeholder: 'Enter your first name', required: true },
                { id: 2, type: 'email_address', label: 'Email', placeholder: 'Enter email address', required: true },
                { id: 3, type: 'dropdown_field', label: 'Subject', placeholder: 'Select subject', required: false, options: [
                    { label: 'General Inquiry', value: 'general' },
                    { label: 'Portfolio Review', value: 'portfolio' },
                    { label: 'Collaboration', value: 'collaboration' }
                ]},
                { id: 4, type: 'file_upload', label: 'Portfolio Files', placeholder: 'Upload your portfolio files (PDF, images)', required: false },
                { id: 5, type: 'textarea_field', label: 'Message', placeholder: 'Tell us about your work', required: false }
            ];
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
        
        convertFieldsToPreview(apiFields) {
            // Convert API response fields to preview format
            return apiFields.map((field, index) => ({
                id: field.id || index + 1,
                type: field.type || 'text_field',
                label: field.label || field.name || 'Untitled Field',
                placeholder: field.placeholder || field.help || '',
                required: field.required || false,
                options: field.options || [],
                default: field.default || '',
                help_text: field.help || field.description || ''
            }));
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
            if (!this.userInput.trim()) return;
            
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
                    const aiMessage = {
                        type: 'ai',
                        content: response.message || 'Form has been updated successfully.',
                        showButtons: this.chatMessages.length > 1, // Show buttons for subsequent messages
                        response_data: response,
                        timestamp: new Date().toISOString()
                    };
                    
                    this.chatMessages.push(aiMessage);
                    
                    // Update conversation state
                    this.updateConversationState(userMessage, aiMessage);
                    
                    // If form was modified, update the preview
                    if (response.form_data) {
                        if (response.form_data.wpuf_fields) {
                            this.formFields = this.convertFieldsToPreview(response.form_data.wpuf_fields);
                        } else if (response.form_data.fields) {
                            this.formFields = this.convertFieldsToPreview(response.form_data.fields);
                        }
                        
                        if (response.form_data.form_title) {
                            this.$emit('title-updated', response.form_data.form_title);
                        }
                        
                        // Emit form update event
                        this.$emit('form-updated', response.form_data);
                        
                        // Add a small delay before removing blur to show the update visually
                        setTimeout(() => {
                            this.isFormUpdating = false;
                        }, 300);
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
                        
                        // Add a small delay before removing blur to show the update visually
                        setTimeout(() => {
                            this.isFormUpdating = false;
                        }, 300);
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
            
            const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({
                    prompt: message,
                    conversation_context: conversationContext
                })
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
                this.chatMessages.push({
                    type: 'ai',
                    content: `Error applying form: ${error.message}. Please try again.`,
                    showButtons: false,
                    isError: true,
                    timestamp: new Date().toISOString()
                });
                
                this.scrollToBottom();
            } finally {
                this.isApplying = false;
                this.isFormUpdating = false; // Ensure blur is removed in any case
            }
        },
        
        handleReject() {
            // Revert to previous form state if there are pending changes
            if (this.pendingChanges && this.previousFormFields) {
                this.formFields = JSON.parse(JSON.stringify(this.previousFormFields));
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
                
                // Clear pending changes but keep the current form state
                this.pendingChanges = null;
                this.previousFormFields = null;
            }
            
            // Hide buttons from the current chat message
            this.hideLastMessageButtons();
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
            this.$emit('regenerate-form');
        },
        
        handleEditInBuilder() {
            // Check if form has pro fields and show notification
            if (this.checkForProFields()) {
                this.showProNotification();
            }
            // Always proceed to edit
            this.$emit('edit-in-builder', this.formId);
        },
        
        handleEditWithBuilder() {
            // Check if form has pro fields and show notification
            if (this.checkForProFields()) {
                this.showProNotification();
            }
            // Always proceed to edit
            this.$emit('edit-with-builder', this.formId);
        },
        
        checkForProFields() {
            // List of pro field types
            const proFields = [
                'numeric_text_field', 'phone_number', 'address_field', 'country_list_field',
                'repeat_field', 'date_field', 'time_field', 'datetime_field',
                'multiple_select', 'checkbox_grid', 'multiple_choice_grid',
                'file_upload', 'audio_upload', 'video_upload',
                'google_map', 'really_simple_captcha', 'recaptcha',
                'ratings', 'linear_scale', 'qr_code', 'embed',
                'shortcode', 'action_hook', 'toc', 'column_field', 'step_start'
            ];
            
            // Check if any field in the form is a pro field
            return this.formFields.some(field => proFields.includes(field.type));
        },
        
        showProNotification() {
            // Create a simple notification popup
            const notificationContainer = document.createElement('div');
            notificationContainer.className = 'wpuf-pro-notification';
            notificationContainer.innerHTML = `
                <div class="wpuf-pro-notification-content">
                    <span class="dashicons dashicons-info-outline"></span>
                    <div class="wpuf-pro-notification-text">
                        <strong>${this.__('Pro Fields Detected', 'wp-user-frontend')}</strong>
                        <p>${this.__('This form contains pro fields. Some features may be limited without WPUF Pro.', 'wp-user-frontend')}</p>
                    </div>
                    <button class="wpuf-pro-notification-close">×</button>
                </div>
            `;
            
            document.body.appendChild(notificationContainer);
            
            // Add styles if not already present
            if (!document.getElementById('wpuf-pro-notification-styles')) {
                const style = document.createElement('style');
                style.id = 'wpuf-pro-notification-styles';
                style.textContent = `
                    .wpuf-pro-notification {
                        position: fixed;
                        top: 100px;
                        right: 20px;
                        z-index: 999999;
                        animation: slideInRight 0.3s ease;
                    }
                    .wpuf-pro-notification-content {
                        background: white;
                        border-left: 4px solid #f0b849;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                        border-radius: 4px;
                        padding: 15px 40px 15px 15px;
                        max-width: 400px;
                        display: flex;
                        align-items: start;
                        gap: 12px;
                        position: relative;
                    }
                    .wpuf-pro-notification-content .dashicons {
                        color: #f0b849;
                        font-size: 24px;
                        width: 24px;
                        height: 24px;
                        flex-shrink: 0;
                        margin-top: 2px;
                    }
                    .wpuf-pro-notification-text strong {
                        display: block;
                        color: #23282d;
                        margin-bottom: 4px;
                        font-size: 14px;
                    }
                    .wpuf-pro-notification-text p {
                        margin: 0;
                        color: #666;
                        font-size: 13px;
                        line-height: 1.4;
                    }
                    .wpuf-pro-notification-close {
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        background: none;
                        border: none;
                        font-size: 24px;
                        color: #999;
                        cursor: pointer;
                        padding: 0;
                        width: 24px;
                        height: 24px;
                        line-height: 20px;
                    }
                    .wpuf-pro-notification-close:hover {
                        color: #666;
                    }
                    @keyframes slideInRight {
                        from { 
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to { 
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Auto-close after 5 seconds
            setTimeout(() => {
                if (notificationContainer && notificationContainer.parentNode) {
                    notificationContainer.remove();
                }
            }, 5000);
            
            // Close on button click
            notificationContainer.querySelector('.wpuf-pro-notification-close').addEventListener('click', () => {
                notificationContainer.remove();
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
        this.scrollToBottom();
        
        // Initialize conversation state if we have initial messages
        if (this.initialMessages && this.initialMessages.length > 0) {
            // Find the first user message to determine if it was from a predefined template
            const firstUserMessage = this.initialMessages.find(msg => msg.type === 'user');
            if (firstUserMessage) {
                this.initializeConversationState(firstUserMessage.content);
            }
        }
        
        // If formTitle suggests a predefined template, initialize accordingly  
        if (this.formTitle && !this.conversationState.form_created) {
            this.initializeConversationState(this.formTitle);
        }
    }
};
</script>
