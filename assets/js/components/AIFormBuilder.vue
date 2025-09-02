<template>
    <!-- Stage 1: Form Input -->
    <div v-if="currentStage === 'input'" class="wpuf-ai-form-wrapper">
        <div class="wpuf-ai-form-content wpuf-w-[720px] wpuf-h-[672px] wpuf-absolute wpuf-top-[93px] wpuf-left-[439px] wpuf-gap-12 wpuf-opacity-100 wpuf-bg-white wpuf-rounded-lg wpuf-p-4">
                <!-- Header -->
                <div class="wpuf-text-center wpuf-mb-6">
                    <h2 class="wpuf-text-3xl wpuf-font-semibold !wpuf-text-black wpuf-mb-2">
                        {{ __('Create Form with AI', 'wp-user-frontend') }}
                    </h2>
                    <p class="wpuf-text-lg wpuf-text-gray-500">
                        {{ __('Automatically generate smart, customizable forms using AI.', 'wp-user-frontend') }}
                    </p>
                </div>

                <!-- Form Description -->
                <div class="wpuf-mb-6">
                    <div class="wpuf-relative">
                        <textarea 
                            v-model="formDescription"
                            class="wpuf-w-full wpuf-px-4 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-text-gray-500 wpuf-resize-none focus:wpuf-outline-none focus:wpuf-border-emerald-500 focus:wpuf-ring-2 focus:wpuf-ring-emerald-200 wpuf-transition-all"
                            rows="6"
                            maxlength="500"
                            :placeholder="__('Describe your form', 'wp-user-frontend')"
                        ></textarea>
                    </div>
                    <div class="wpuf-text-right wpuf-mt-2 wpuf-text-sm wpuf-text-gray-600">
                        {{ formDescription.length }}/500 {{ __('Characters', 'wp-user-frontend') }}
                    </div>
                </div>

                <!-- Prompt Templates -->
                <div class="wpuf-mb-6">
                    <p class="wpuf-text-gray-900 wpuf-mb-4 wpuf-text-lg">
                        {{ __('Or create using our Prompts:', 'wp-user-frontend') }}
                    </p>
                    <div class="wpuf-flex wpuf-flex-wrap wpuf-gap-4">
                        <button 
                            v-for="template in promptTemplates" 
                            :key="template"
                            @click="selectPrompt(template)"
                            :class="{ 'wpuf-prompt-btn-active': selectedPrompt === template }"
                            class="wpuf-px-4 wpuf-py-2 wpuf-border wpuf-border-gray-200 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 hover:wpuf-border-emerald-600 hover:wpuf-text-emerald-700 wpuf-transition-all wpuf-text-sm wpuf-font-medium"
                        >
                            {{ template }}
                        </button>
                    </div>
                </div> 

                <!-- Action Buttons -->
                <div class="wpuf-flex wpuf-justify-center wpuf-gap-4">
                    <button 
                        @click="goBack"
                        class="wpuf-px-6 wpuf-py-3 wpuf-border wpuf-text-base wpuf-leading-6 wpuf-border-gray-300 wpuf-rounded-md wpuf-text-gray-700 wpuf-font-medium hover:wpuf-bg-gray-50 wpuf-transition-colors"
                    >
                        {{ __('Back', 'wp-user-frontend') }}
                    </button>
                    <button 
                        @click="startGeneration"
                        :disabled="!formDescription.trim() || isGenerating"
                        class="wpuf-px-8 wpuf-py-4 wpuf-bg-emerald-600 hover:wpuf-bg-emerald-700 wpuf-text-white wpuf-rounded-lg wpuf-transition-colors wpuf-flex wpuf-items-center wpuf-gap-2 disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed"
                    >
                        <span v-if="!isGenerating" class="wpuf-text-base wpuf-leading-6">{{ __('Generate Form', 'wp-user-frontend') }}</span>
                        <span v-else class="wpuf-font-medium wpuf-text-base wpuf-leading-6">{{ __('Generating...', 'wp-user-frontend') }}</span>
                        <svg v-if="!isGenerating" class="wpuf-w-5 wpuf-h-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div v-else class="wpuf-animate-spin wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-white wpuf-border-t-transparent wpuf-rounded-full"></div>
                    </button>
                </div>
        </div>
    </div>

    <!-- Stage 2: Generating -->
    <div v-else-if="currentStage === 'generating'" class="wpuf-ai-form-wrapper">
        <div class="wpuf-ai-form-content wpuf-w-[768px] wpuf-h-[416px] wpuf-absolute wpuf-top-[275px] wpuf-left-[415px] wpuf-gap-12 wpuf-opacity-100 wpuf-bg-white wpuf-rounded-lg wpuf-border wpuf-border-[#E2E8F0] wpuf-pt-9 wpuf-pr-[134px] wpuf-pb-9 wpuf-pl-[134px]">
                <!-- Animated Icon -->
                <div class="wpuf-flex wpuf-justify-center wpuf-mb-5">
                    <div class="wpuf-relative">
                        <svg width="92" height="92" viewBox="0 0 92 92" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <rect width="92" height="92" fill="url(#pattern0_5555_45606)"/>
                            <defs>
                            <pattern id="pattern0_5555_45606" patternContentUnits="objectBoundingBox" width="1" height="1">
                            <use xlink:href="#image0_5555_45606" transform="scale(0.00362319)"/>
                            </pattern>
                            <image id="image0_5555_45606" width="276" height="276" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
                            </defs>
                        </svg>
                    </div>
                </div>
                
                <!-- Title -->
                <h3 class="wpuf-text-center wpuf-text-xl wpuf-font-base wpuf-text-gray-900 wpuf-mb-12">
                    {{ __('Generating your form...', 'wp-user-frontend') }}
                </h3>
                
                <!-- Progress Steps -->
                <div class="wpuf-grid wpuf-grid-cols-1 wpuf-justify-items-center wpuf-gap-1">
                    <!-- Step 1 -->
                    <div class="wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-justify-center wpuf-w-full wpuf-max-w-md" :class="{ 'wpuf-opacity-100': currentStep >= 1, 'wpuf-opacity-40': currentStep < 1 }">
                        <div class="wpuf-flex-shrink-0">
                            <div v-if="currentStep > 1" class="wpuf-w-5 wpuf-h-5 wpuf-bg-emerald-600 wpuf-rounded-full wpuf-flex wpuf-items-center wpuf-justify-center">
                                <svg class="wpuf-w-3 wpuf-h-3 wpuf-text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div v-else-if="currentStep === 1" class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-emerald-600 wpuf-border-t-transparent wpuf-rounded-full wpuf-animate-spin"></div>
                            <div v-else class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-gray-300 wpuf-rounded-full"></div>
                        </div>
                        <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-leading-8 wpuf-flex-1">{{ __('Analyzing your request and detecting the form type...', 'wp-user-frontend') }}</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-justify-center wpuf-w-full wpuf-max-w-md" :class="{ 'wpuf-opacity-100': currentStep >= 2, 'wpuf-opacity-40': currentStep < 2 }">
                        <div class="wpuf-flex-shrink-0">
                            <div v-if="currentStep > 2" class="wpuf-w-5 wpuf-h-5 wpuf-bg-emerald-600 wpuf-rounded-full wpuf-flex wpuf-items-center wpuf-justify-center">
                                <svg class="wpuf-w-3 wpuf-h-3 wpuf-text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div v-else-if="currentStep === 2" class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-emerald-600 wpuf-border-t-transparent wpuf-rounded-full wpuf-animate-spin"></div>
                            <div v-else class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-gray-300 wpuf-rounded-full"></div>
                        </div>
                        <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-leading-8 wpuf-flex-1">{{ __('Finalizing the title, required fields, and labels...', 'wp-user-frontend') }}</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-justify-center wpuf-w-full wpuf-max-w-md" :class="{ 'wpuf-opacity-100': currentStep >= 3, 'wpuf-opacity-40': currentStep < 3 }">
                        <div class="wpuf-flex-shrink-0">
                            <div v-if="currentStep > 3" class="wpuf-w-5 wpuf-h-5 wpuf-bg-emerald-600 wpuf-rounded-full wpuf-flex wpuf-items-center wpuf-justify-center">
                                <svg class="wpuf-w-3 wpuf-h-3 wpuf-text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div v-else-if="currentStep === 3" class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-emerald-600 wpuf-border-t-transparent wpuf-rounded-full wpuf-animate-spin"></div>
                            <div v-else class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-gray-300 wpuf-rounded-full"></div>
                        </div>
                        <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-leading-8 wpuf-flex-1">{{ __('Almost done! Generating your form preview...', 'wp-user-frontend') }}</p>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-justify-center wpuf-w-full wpuf-max-w-md" :class="{ 'wpuf-opacity-100': currentStep >= 4, 'wpuf-opacity-40': currentStep < 4 }">
                        <div class="wpuf-flex-shrink-0">
                            <div v-if="currentStep > 4" class="wpuf-w-5 wpuf-h-5 wpuf-bg-emerald-600 wpuf-rounded-full wpuf-flex wpuf-items-center wpuf-justify-center">
                                <svg class="wpuf-w-3 wpuf-h-3 wpuf-text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div v-else-if="currentStep === 4" class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-emerald-600 wpuf-border-t-transparent wpuf-rounded-full wpuf-animate-spin"></div>
                            <div v-else class="wpuf-w-5 wpuf-h-5 wpuf-border-2 wpuf-border-gray-300 wpuf-rounded-full"></div>
                        </div>
                        <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-leading-8 wpuf-flex-1">{{ __('Here\'s your AI-generated form - ready to customize and use!', 'wp-user-frontend') }}</p>
                    </div>
                </div>
                
                <!-- Confetti Animation -->
                <div v-if="showConfetti" class="wpuf-confetti-container wpuf-absolute wpuf-inset-0 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-pointer-events-none wpuf-z-50">
                    <img :src="confettiUrl" alt="Confetti" class="wpuf-w-full wpuf-h-full wpuf-object-cover"/>
                </div>
        </div>
    </div>

    <!-- Stage 3: Success Chat Interface -->
    <div v-else class="wpuf-ai-form-wrapper">
        <div class="wpuf-ai-form-container wpuf-h-screen wpuf-overflow-hidden">
            <div class="wpuf-ai-form-content wpuf-bg-white wpuf-rounded-lg wpuf-h-full wpuf-flex wpuf-flex-col">
                
                <!-- Header Section -->
                <div class="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-px-6">
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
                        <button @click="regenerateForm" class="wpuf-btn-regenerate">
                            {{ __('Regenerate', 'wp-user-frontend') }}
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.3523 7.79032H17.5128V7.78884M2.48682 16.3703V12.2098M2.48682 12.2098L6.64735 12.2098M2.48682 12.2098L5.13756 14.8622C5.963 15.6892 7.01055 16.3166 8.22034 16.6408C11.8879 17.6235 15.6577 15.447 16.6405 11.7794M3.35898 8.22068C4.3417 4.5531 8.11152 2.37659 11.7791 3.35932C12.9889 3.68348 14.0365 4.31091 14.8619 5.1379L17.5128 7.78884M17.5128 3.62982V7.78884" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button @click="editInBuilder" class="wpuf-btn-edit-builder">
                            {{ __('Edit in Builder', 'wp-user-frontend') }}
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="wpuf-grid-container">
                    <!-- Left Side - Chat Box -->
                    <div class="wpuf-chat-box wpuf-flex wpuf-flex-col wpuf-h-full">
                        <div class="wpuf-chat-header wpuf-flex-shrink-0">
                            <div class="wpuf-flex wpuf-items-center wpuf-gap-3">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.625 9.75C8.625 9.95711 8.45711 10.125 8.25 10.125C8.04289 10.125 7.875 9.95711 7.875 9.75C7.875 9.54289 8.04289 9.375 8.25 9.375C8.45711 9.375 8.625 9.54289 8.625 9.75ZM8.625 9.75H8.25M12.375 9.75C12.375 9.95711 12.2071 10.125 12 10.125C11.7929 10.125 11.625 9.95711 11.625 9.75C11.625 9.54289 11.7929 9.375 12 9.375C12.2071 9.375 12.375 9.54289 12.375 9.75ZM12.375 9.75H12M16.125 9.75C16.125 9.95711 15.9571 10.125 15.75 10.125C15.5429 10.125 15.375 9.95711 15.375 9.75C15.375 9.54289 15.5429 9.375 15.75 9.375C15.9571 9.375 16.125 9.54289 16.125 9.75ZM16.125 9.75H15.75M2.25 12.7593C2.25 14.3604 3.37341 15.754 4.95746 15.987C6.04357 16.1467 7.14151 16.27 8.25 16.3556V21L12.4335 16.8165C12.6402 16.6098 12.9193 16.4923 13.2116 16.485C15.1872 16.4361 17.1331 16.2678 19.0425 15.9871C20.6266 15.7542 21.75 14.3606 21.75 12.7595V6.74056C21.75 5.13946 20.6266 3.74583 19.0425 3.51293C16.744 3.17501 14.3926 3 12.0003 3C9.60776 3 7.25612 3.17504 4.95747 3.51302C3.37342 3.74593 2.25 5.13956 2.25 6.74064V12.7593Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h2 class="wpuf-success-title">{{ __('WPUF Form Generation Chat', 'wp-user-frontend') }}</h2>
                            </div>
                        </div>
                        
                        <div class="wpuf-chat-scrollable wpuf-flex-1 wpuf-overflow-y-auto" ref="chatContainer">
                            <div class="wpuf-chat-messages">
                                <div v-for="(message, index) in chatMessages" :key="index" 
                                     :class="message.type === 'user' ? 'wpuf-message-user' : 'wpuf-message-ai'">
                                    
                                    <!-- User Message -->
                                    <div v-if="message.type === 'user'" class="wpuf-ai-user-message">
                                        <div class="wpuf-message-bubble wpuf-message-bubble-user">
                                            <p>{{ message.content }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- AI Message -->
                                    <div v-else class="wpuf-ai-message">
                                        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="wpuf-ai-icon">
                                            <circle cx="18" cy="18" r="18" fill="#10B981"/>
                                            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white"/>
                                            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white"/>
                                            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white"/>
                                            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white"/>
                                        </svg>
                                        <div class="wpuf-flex-1">
                                            <div class="wpuf-message-bubble wpuf-message-bubble-ai">
                                                <p v-html="message.content"></p>
                                                <div v-if="message.showButtons" class="wpuf-message-actions">
                                                    <button @click="applyForm" class="wpuf-btn-apply">{{ __('Apply', 'wp-user-frontend') }}</button>
                                                    <button @click="rejectForm" class="wpuf-btn-reject">{{ __('Reject', 'wp-user-frontend') }}</button>
                                                </div>
                                            </div>
                                            <div v-if="message.status" class="wpuf-message-status">
                                                <span>{{ message.status }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wpuf-chat-input-container wpuf-flex-shrink-0">
                            <div class="wpuf-chat-input-wrapper">
                                <textarea 
                                    v-model="userInput"
                                    @keyup.enter.prevent="sendMessage"
                                    class="wpuf-chat-input"
                                    :placeholder="__('Type your message here...', 'wp-user-frontend')"
                                ></textarea>
                                <button @click="sendMessage" class="wpuf-send-button">
                                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.99972 10L1.2688 1.12451C7.88393 3.04617 14.0276 6.07601 19.4855 9.99974C14.0276 13.9235 7.884 16.9535 1.26889 18.8752L3.99972 10ZM3.99972 10L11.5 10" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Form Preview -->
                    <div class="wpuf-form-preview wpuf-flex wpuf-flex-col wpuf-h-full">
                        <div class="wpuf-form-header wpuf-flex-shrink-0">
                            <h3 class="wpuf-form-title">{{ formTitle }}</h3>
                            <p class="wpuf-form-description">{{ __('Please complete all information below', 'wp-user-frontend') }}</p>
                        </div>
                        
                        <div class="wpuf-form-scrollable wpuf-flex-1 wpuf-overflow-y-auto">
                            <div class="wpuf-form-fields">
                                <div v-for="field in formFields" :key="field.id" class="wpuf-form-field">
                                    <label class="wpuf-form-label">{{ field.label }}</label>
                                    
                                    <div v-if="field.type === 'text'" class="wpuf-form-input">
                                        {{ field.placeholder }}
                                    </div>
                                    
                                    <div v-else-if="field.type === 'email'" class="wpuf-form-input">
                                        {{ field.placeholder }}
                                    </div>
                                    
                                    <div v-else-if="field.type === 'select'" class="wpuf-form-input wpuf-select">
                                        {{ field.placeholder }}
                                        <svg class="wpuf-select-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    
                                    <div v-else-if="field.type === 'file'" class="wpuf-form-file">
                                        <svg class="wpuf-file-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C18.6569 10 20 11.3431 20 13V19C20 20.6569 18.6569 22 17 22H7C5.34315 22 4 20.6569 4 19V13C4 11.3431 5.34315 10 7 10Z" stroke="currentColor" stroke-width="2"/>
                                            <path d="M12 14V18M10 16H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <span>{{ field.placeholder }}</span>
                                    </div>
                                    
                                    <div v-else-if="field.type === 'textarea'" class="wpuf-form-textarea">
                                        {{ field.placeholder }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wpuf-form-footer wpuf-flex-shrink-0">
                            <button @click="editWithBuilder" class="wpuf-btn-edit-full">
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

<script>
export default {
    name: 'AIFormBuilder',
    
    data() {
        return {
            // Stage management
            currentStage: 'input', // 'input', 'generating', 'success'
            
            // Input stage data
            formDescription: '',
            selectedPrompt: '',
            isGenerating: false,
            promptTemplates: [
                'Paid Guest Post',
                'Portfolio Submission', 
                'Classified Ads',
                'Coupon Submission',
                'Real Estate Property Listing',
                'News/Press Release Submission',
                'Product Listing'
            ],
            
            // Generating stage data
            currentStep: 1,
            showConfetti: false,
            confettiUrl: '',
            
            // Success stage data
            formTitle: 'Portfolio Submission',
            formId: null,
            userInput: '',
            chatMessages: [
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
            formFields: [
                { id: 1, type: 'text', label: 'First Name', placeholder: 'Enter your first name' },
                { id: 2, type: 'email', label: 'Email', placeholder: 'Enter email address' },
                { id: 3, type: 'select', label: 'Select File Types', placeholder: 'Select File Types' },
                { id: 4, type: 'file', label: 'File Upload', placeholder: 'Only JPEG, PNG and PDF files and max size of (025*300 or larger recommended, up to 5MB each)' },
                { id: 5, type: 'textarea', label: 'Comment', placeholder: 'Write here your Comment' },
                { id: 6, type: 'text', label: 'Last Name', placeholder: 'Enter your last name' },
                { id: 7, type: 'text', label: 'Phone Number', placeholder: '+1 (555) 000-0000' },
                { id: 8, type: 'text', label: 'Company Name', placeholder: 'Enter your company name' },
                { id: 9, type: 'text', label: 'Job Title', placeholder: 'Enter your job title' },
                { id: 10, type: 'text', label: 'Website URL', placeholder: 'https://example.com' },
                { id: 11, type: 'text', label: 'LinkedIn Profile', placeholder: 'https://linkedin.com/in/yourprofile' },
                { id: 12, type: 'select', label: 'Years of Experience', placeholder: 'Select years' },
                { id: 13, type: 'textarea', label: 'Skills', placeholder: 'List your key skills...' },
                { id: 14, type: 'textarea', label: 'Portfolio Description', placeholder: 'Describe your portfolio projects...' },
                { id: 15, type: 'file', label: 'Additional Documents', placeholder: 'Drop files here or click to upload' },
                { id: 16, type: 'select', label: 'Availability', placeholder: 'Select availability' },
                { id: 17, type: 'textarea', label: 'References', placeholder: 'Provide references if available...' }
            ]
        };
    },
    
    methods: {
        __( text ) {
            // Translation function placeholder
            return text;
        },
        
        // Input stage methods
        selectPrompt( template ) {
            this.selectedPrompt = template;
            this.formDescription = 'Create a form for ' + template;
        },
        
        goBack() {
            window.history.back();
        },
        
        startGeneration() {
            if (!this.formDescription.trim()) {
                alert(this.__('Please describe your form or select a prompt template.'));
                return;
            }
            
            this.isGenerating = true;
            this.currentStage = 'generating';
            this.simulateGeneration();
        },
        
        // Generating stage methods
        simulateGeneration() {
            this.currentStep = 1;
            const steps = [1, 2, 3, 4];
            
            steps.forEach((step, index) => {
                setTimeout(() => {
                    this.currentStep = step;
                    
                    if (step === 4) {
                        setTimeout(() => {
                            this.showConfetti = true;
                            this.confettiUrl = (window.wpufAIFormBuilder?.assetUrl || '/wp-content/plugins/wp-user-frontend/assets') + '/images/confetti_transparent.gif';
                            
                            setTimeout(() => {
                                this.currentStage = 'success';
                                this.isGenerating = false;
                            }, 2000);
                        }, 1000);
                    }
                }, (index + 1) * 1500);
            });
        },
        
        sendMessage() {
            if (!this.userInput.trim()) return;
            
            this.chatMessages.push({
                type: 'user',
                content: this.userInput
            });
            
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
        
        applyForm() {
            console.log('Form applied');
        },
        
        rejectForm() {
            console.log('Form rejected');
        },
        
        regenerateForm() {
            console.log('Regenerating form');
        },
        
        editInBuilder() {
            if (this.formId) {
                window.location.href = `admin.php?page=wpuf-post-forms&action=edit&id=${this.formId}`;
            } else {
                window.location.href = 'admin.php?page=wpuf-post-forms';
            }
        },
        
        editWithBuilder() {
            this.editInBuilder();
        }
    },
    
    mounted() {
        // Get data from localized script
        const localData = window.wpufAIFormBuilder || {};
        
        // Set stage from localized data
        this.currentStage = localData.stage || 'input';
        
        // Get form data from localized data
        this.formId = localData.formId || '';
        this.formTitle = localData.formTitle || 'Portfolio Submission';
        this.formDescription = localData.description || '';
        this.selectedPrompt = localData.prompt || '';
        this.confettiUrl = localData.confettiUrl || '';
        
        // If we're in generating stage, start the simulation
        if (this.currentStage === 'generating') {
            this.simulateGeneration();
        }
        
        // If we're in success stage, scroll to bottom of chat
        if (this.currentStage === 'success') {
            this.scrollToBottom();
        }
    }
};
</script>