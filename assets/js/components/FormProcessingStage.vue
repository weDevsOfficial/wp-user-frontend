<template>
    <div class="wpuf-ai-form-wrapper wpuf-font-sans wpuf-w-full wpuf-h-screen wpuf-overflow-hidden wpuf-relative">
        <div class="wpuf-ai-form-content wpuf-w-full wpuf-max-w-[768px] wpuf-h-auto wpuf-min-h-[416px] wpuf-absolute wpuf-top-[275px] wpuf-left-1/2 wpuf-transform wpuf--translate-x-1/2 wpuf-mx-4 sm:wpuf-mx-auto wpuf-bg-white wpuf-border wpuf-border-slate-300 wpuf-rounded-lg wpuf-p-6 sm:wpuf-p-9">
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
            <h3 class="wpuf-text-center wpuf-text-xl wpuf-font-normal wpuf-text-gray-900 wpuf-mb-12">
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
</template>

<script>
export default {
    name: 'FormProcessingStage',
    props: {
        initialStep: {
            type: Number,
            default: 1
        },
        autoStart: {
            type: Boolean,
            default: true
        },
        stepDelay: {
            type: Number,
            default: 1500
        },
        confettiDelay: {
            type: Number,
            default: 1000
        },
        completeDelay: {
            type: Number,
            default: 2000
        },
        waitForAI: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            currentStep: this.initialStep,
            showConfetti: false,
            confettiUrl: '',
            isProcessing: false,
            aiResponseReceived: false
        };
    },
    methods: {
        __: (text, domain = 'wp-user-frontend') => {
            if (typeof window.__ === 'function') {
                return window.__(text, domain);
            }
            console.warn('Translation function not available:', text);
            return text;
        },
        
        startGeneration() {
            if (this.isProcessing) return;

            this.isProcessing = true;
            this.currentStep = 1;
            this.showConfetti = false;
            this.aiResponseReceived = false;

            if (!this.waitForAI) {
                // Use old fixed timing behavior
                const steps = [1, 2, 3, 4];

                steps.forEach((step, index) => {
                    setTimeout(() => {
                        this.currentStep = step;

                        if (step === 4) {
                            setTimeout(() => {
                                this.showConfetti = true;
                                this.confettiUrl = this.getConfettiUrl();

                                setTimeout(() => {
                                    this.$emit('generation-complete');
                                    this.isProcessing = false;
                                }, this.completeDelay);
                            }, this.confettiDelay);
                        }
                    }, (index + 1) * this.stepDelay);
                });
            } else {
                // Use AI-aware timing
                this.startStepsWithAIAwareness();
            }
        },

        startStepsWithAIAwareness() {
            // Progress through steps 1-3 normally
            setTimeout(() => {
                this.currentStep = 2;
            }, this.stepDelay);

            setTimeout(() => {
                this.currentStep = 3;
            }, this.stepDelay * 2);

            // Step 4 waits for AI response
            setTimeout(() => {
                this.waitForAICompletion();
            }, this.stepDelay * 3);
        },

        waitForAICompletion() {
            // Check if AI response already received
            if (this.aiResponseReceived) {
                this.completeGeneration();
                return;
            }

            // Set to step 4 and wait for AI
            this.currentStep = 4;

            // Check every 500ms for AI completion
            const checkInterval = setInterval(() => {
                if (this.aiResponseReceived) {
                    clearInterval(checkInterval);
                    this.completeGeneration();
                }
            }, 500);
        },

        completeGeneration() {
            setTimeout(() => {
                this.showConfetti = true;
                this.confettiUrl = this.getConfettiUrl();

                setTimeout(() => {
                    this.$emit('generation-complete');
                    this.isProcessing = false;
                }, this.completeDelay);
            }, this.confettiDelay);
        },

        onAIResponseReceived() {
            this.aiResponseReceived = true;
        },
        
        getConfettiUrl() {
            // Use localized asset URL from PHP, with safer fallback
            const config = window.wpufAIFormBuilder || {};

            // Try multiple fallback options for better compatibility
            const baseUrl = config.assetUrl ||
                           config.pluginUrl ||
                           (typeof wpuf_frontend !== 'undefined' ? wpuf_frontend.asset_url : null) ||
                           (document.querySelector('script[src*="/wp-user-frontend/"]')?.src.replace(/\/[^\/]+$/, '').replace(/\/js$/, '')) ||
                           '';

            // If we still don't have a base URL, construct from current script
            if (!baseUrl) {
                console.warn('WPUF: Unable to determine asset URL for confetti image');
                return '';
            }

            return `${baseUrl}/images/confetti_transparent.gif`;
        },
        
        reset() {
            this.currentStep = 1;
            this.showConfetti = false;
            this.isProcessing = false;
            this.aiResponseReceived = false;
        }
    },
    mounted() {
        // Set confetti URL
        this.confettiUrl = this.getConfettiUrl();
        
        // Auto-start generation if enabled
        if (this.autoStart) {
            this.startGeneration();
        }
    }
};
</script>
