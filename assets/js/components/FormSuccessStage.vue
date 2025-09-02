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
                                                    <button @click="handleApply" class="wpuf-btn-apply wpuf-bg-emerald-600 wpuf-text-white wpuf-border-none wpuf-py-1.5 wpuf-px-3 wpuf-rounded wpuf-text-sm wpuf-cursor-pointer wpuf-transition-colors hover:wpuf-bg-emerald-800">{{ __('Apply', 'wp-user-frontend') }}</button>
                                                    <button @click="handleReject" class="wpuf-btn-reject wpuf-bg-red-600 wpuf-text-white wpuf-border-none wpuf-py-1.5 wpuf-px-3 wpuf-rounded wpuf-text-sm wpuf-cursor-pointer wpuf-transition-colors hover:wpuf-bg-red-800">{{ __('Reject', 'wp-user-frontend') }}</button>
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
                    <div class="wpuf-form-preview wpuf-w-full wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-4 sm:wpuf-p-6 lg:wpuf-p-8 wpuf-flex wpuf-flex-col wpuf-gap-6 wpuf-shadow-md wpuf-h-[60vh] sm:wpuf-h-[70vh] md:wpuf-h-[75vh] lg:wpuf-h-[80vh] xl:wpuf-h-[85vh] wpuf-max-h-[1072px] wpuf-relative">
                        <div class="wpuf-form-header wpuf-border-b wpuf-border-gray-200 wpuf-pb-4 wpuf-flex-shrink-0">
                            <h3 class="wpuf-form-title wpuf-font-bold wpuf-text-3xl wpuf-leading-9 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-900 wpuf-m-0 wpuf-mb-2">{{ formTitle }}</h3>
                            <p class="wpuf-form-description wpuf-font-normal wpuf-text-lg wpuf-leading-6 wpuf-tracking-normal wpuf-text-center wpuf-text-gray-500 wpuf-m-0">{{ __('Please complete all information below', 'wp-user-frontend') }}</p>
                        </div>
                        
                        <div class="wpuf-form-scrollable wpuf-flex-1 wpuf-overflow-y-auto wpuf-mb-4" style="scrollbar-width: thin; scrollbar-color: #10B981 transparent;">
                            <div class="wpuf-form-fields wpuf-flex wpuf-flex-col wpuf-gap-5">
                                <div v-for="field in formFields" :key="field.id" class="wpuf-form-field wpuf-flex wpuf-flex-col wpuf-gap-2">
                                    <label class="wpuf-form-label wpuf-font-normal wpuf-text-base wpuf-leading-6 wpuf-tracking-normal wpuf-text-gray-900">{{ field.label }}</label>
                                    
                                    <div v-if="field.type === 'text'" class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-text-gray-400 wpuf-bg-gray-50">
                                        {{ field.placeholder }}
                                    </div>
                                    
                                    <div v-else-if="field.type === 'email'" class="wpuf-form-input wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-text-gray-400 wpuf-bg-gray-50">
                                        {{ field.placeholder }}
                                    </div>
                                    
                                    <div v-else-if="field.type === 'select'" class="wpuf-form-input wpuf-select wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-text-gray-400 wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-cursor-pointer">
                                        {{ field.placeholder }}
                                        <svg class="wpuf-select-icon wpuf-w-4 wpuf-h-4 wpuf-text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    
                                    <div v-else-if="field.type === 'file'" class="wpuf-form-file wpuf-border-2 wpuf-border-dashed wpuf-border-gray-300 wpuf-rounded wpuf-p-5 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-gap-2 wpuf-bg-gray-50 wpuf-text-gray-500 wpuf-text-center wpuf-text-sm">
                                        <svg class="wpuf-file-icon wpuf-w-6 wpuf-h-6 wpuf-text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C18.6569 10 20 11.3431 20 13V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V13C4 11.3431 5.34315 10 7 10Z" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 14V18M10 16H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <span>{{ field.placeholder }}</span>
                                    </div>
                                    
                                    <div v-else-if="field.type === 'textarea'" class="wpuf-form-textarea wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-p-3 wpuf-text-sm wpuf-text-gray-400 wpuf-bg-gray-50 wpuf-min-h-[80px]">
                                        {{ field.placeholder }}
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
            chatMessages: this.initialMessages.length > 0 ? this.initialMessages : [
                {
                    type: 'user',
                    content: 'Create a contact form'
                },
                {
                    type: 'ai',
                    content: "I'll create a contact form for you. What fields would you like to include?"
                },
                {
                    type: 'user',
                    content: 'Add name, email, phone, and message fields'
                },
                {
                    type: 'ai',
                    content: "Great! I'm adding those fields. Should the phone field be required?"
                },
                {
                    type: 'user',
                    content: 'No, make it optional. Also add a subject dropdown'
                },
                {
                    type: 'ai',
                    content: "Perfect! I've added a subject dropdown with options: General Inquiry, Support, Sales, and Feedback. Phone is now optional."
                },
                {
                    type: 'user',
                    content: 'Portfolio Submission'
                },
                {
                    type: 'ai',
                    content: `Perfect! I've created a Portfolio Submission form for you with the following fields:
                    <ul>
                        <li>First Name - Text input for personal identification</li>
                        <li>Email - Required field for communication</li>
                        <li>File Upload - For portfolio files (PDF, images)</li>
                        <li>Comment - Optional field for additional information</li>
                    </ul>
                    The form is ready and you can customize it further in the form builder!`,
                    showButtons: true,
                    status: 'Successfully applied the instruction.'
                }
            ],
            formFields: this.initialFormFields.length > 0 ? this.initialFormFields : [
                { id: 1, type: 'text', label: 'First Name', placeholder: 'Enter your first name' },
                { id: 2, type: 'email', label: 'Email', placeholder: 'Enter email address' },
                { id: 3, type: 'select', label: 'Select File Types', placeholder: 'Select File Types' },
                { id: 4, type: 'file', label: 'File Upload', placeholder: 'Only JPEG, PNG and PDF files and max size of (025*300 or larger recommended, up to 5MB each)' },
                { id: 5, type: 'textarea', label: 'Comment', placeholder: 'Write here your Comment' }
            ]
        };
    },
    methods: {
        __: window.__ || ((text) => text),
        
        handleSendMessage() {
            if (!this.userInput.trim()) return;
            
            this.chatMessages.push({
                type: 'user',
                content: this.userInput
            });
            
            this.$emit('send-message', this.userInput);
            
            // Simulate AI response
            setTimeout(() => {
                this.chatMessages.push({
                    type: 'ai',
                    content: 'Processing your request...'
                });
                this.scrollToBottom();
            }, 500);
            
            this.userInput = '';
            this.scrollToBottom();
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.chatContainer) {
                    this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                }
            });
        },
        
        handleApply() {
            this.$emit('apply-form');
        },
        
        handleReject() {
            this.$emit('reject-form');
        },
        
        handleRegenerate() {
            this.$emit('regenerate-form');
        },
        
        handleEditInBuilder() {
            this.$emit('edit-in-builder', this.formId);
        },
        
        handleEditWithBuilder() {
            this.$emit('edit-with-builder', this.formId);
        }
    },
    mounted() {
        this.scrollToBottom();
    }
};
</script>