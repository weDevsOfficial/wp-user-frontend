<template>
    <div class="wpuf-ai-form-wrapper wpuf-font-sans wpuf-bg-white wpuf-w-full wpuf-h-screen wpuf-overflow-hidden wpuf-relative">
        <div class="wpuf-ai-form-content wpuf-w-full wpuf-max-w-[720px] wpuf-h-auto wpuf-min-h-[672px] wpuf-absolute wpuf-top-[93px] wpuf-left-1/2 wpuf-transform wpuf--translate-x-1/2 wpuf-mx-4 sm:wpuf-mx-auto wpuf-bg-white wpuf-p-4 sm:wpuf-p-6">
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
                        :class="selectedPrompt === template ? 'wpuf-prompt-btn-active wpuf-bg-emerald-600 wpuf-text-white wpuf-border-emerald-600 hover:wpuf-text-emerald-200 wpuf-px-4 wpuf-py-2 wpuf-rounded-md wpuf-transition-all wpuf-text-sm wpuf-font-medium' : 'wpuf-px-4 wpuf-py-2 wpuf-border wpuf-border-gray-200 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 hover:wpuf-border-emerald-600 hover:wpuf-text-emerald-700 wpuf-transition-all wpuf-text-sm wpuf-font-medium'"
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
</template>

<script>
export default {
    name: 'FormInputStage',
    props: {
        initialDescription: {
            type: String,
            default: ''
        },
        initialSelectedPrompt: {
            type: String,
            default: ''
        },
        generating: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            formDescription: this.initialDescription,
            selectedPrompt: this.initialSelectedPrompt,
            isGenerating: this.generating,
            promptTemplates: [
                this.__('Event Registration', 'wp-user-frontend'),
                this.__('Customer Feedback', 'wp-user-frontend'),
                this.__('Support Ticket', 'wp-user-frontend'),
                this.__('Guest Post', 'wp-user-frontend'),
                this.__('Job Application', 'wp-user-frontend'),
                this.__('Contact Us', 'wp-user-frontend')
            ]
        };
    },
    watch: {
        generating(newVal) {
            this.isGenerating = newVal;
        }
    },
    methods: {
        __: window.__ || ((text) => text),
        
        selectPrompt(template) {
            this.selectedPrompt = template;
            this.formDescription = template;
            this.$emit('update:selectedPrompt', template);
            this.$emit('update:formDescription', template);
        },
        
        goBack() {
            this.$emit('go-back');
        },
        
        startGeneration() {
            if (!this.formDescription.trim() || this.isGenerating) return;
            
            this.$emit('start-generation', {
                description: this.formDescription,
                selectedPrompt: this.selectedPrompt
            });
        }
    }
};
</script>