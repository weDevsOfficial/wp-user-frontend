<template>
    <div class="wpuf-ai-form-wrapper wpuf-font-sans wpuf-w-full wpuf-h-screen wpuf-overflow-hidden wpuf-relative wpuf-flex wpuf-items-center wpuf-justify-center" style="background-color: #F5F5F5;">
        <div class="wpuf-ai-form-content wpuf-w-full wpuf-max-w-[768px] wpuf-h-auto wpuf-min-h-[416px] wpuf-mx-4 sm:wpuf-mx-auto wpuf-bg-white wpuf-border wpuf-border-slate-300 wpuf-rounded-lg wpuf-p-6 sm:wpuf-p-9">
            <!-- Animated Icon -->
            <div class="wpuf-flex wpuf-justify-center wpuf-mb-5">
                <div class="wpuf-relative">
                    <img :src="getAIStarUrl()" alt="Processing" class="wpuf-w-24 wpuf-h-24"/>
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
        },
        aiWaitTimeoutMs: {
            type: Number,
            default: 30000 // 30 seconds timeout
        }
    },
    data() {
        return {
            currentStep: this.initialStep,
            showConfetti: false,
            confettiUrl: '',
            isProcessing: false,
            aiResponseReceived: false,
            _intervals: [] // Track intervals for cleanup
        };
    },
    methods: {
        __: (text, domain = 'wp-user-frontend') => {
            if (typeof window.__ === 'function') {
                return window.__(text, domain);
            }
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
                this.currentStep = 4;
            }, this.stepDelay * 3);
        },

        completeGeneration() {
            // Add 500ms delay before showing confetti
            const confettiTimeoutId = setTimeout(() => {
                this.showConfetti = true;
                this.confettiUrl = this.getConfettiUrl();
            }, 500);
            this._intervals.push(confettiTimeoutId);

            const timeoutId = setTimeout(() => {
                this.$emit('generation-complete');
                this.isProcessing = false;
            }, this.completeDelay + 500); // Add 500ms to complete delay to account for confetti delay
            this._intervals.push(timeoutId);
        },

        onAIResponseReceived() {
            this.aiResponseReceived = true;
            // Complete generation with delay when AI response is received
            this.completeGeneration();
        },
        
        getAIStarUrl() {
            const config = window.wpufAIFormBuilder || {};
            const baseUrl = config.assetUrl ||
                           config.pluginUrl ||
                           (typeof wpuf_frontend !== 'undefined' ? wpuf_frontend.asset_url : null) ||
                           '';
            return `${baseUrl}/images/ai-star.gif`;
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
                return '';
            }

            return `${baseUrl}/images/confetti_transparent.gif`;
        },
        
        reset() {
            this.currentStep = 1;
            this.showConfetti = false;
            this.isProcessing = false;
            this.aiResponseReceived = false;
            this.clearIntervals();
        },
        
        clearIntervals() {
            // Clear all tracked intervals
            this._intervals.forEach(interval => {
                clearInterval(interval);
            });
            this._intervals = [];
        }
    },
    mounted() {
        // Set confetti URL
        this.confettiUrl = this.getConfettiUrl();
        
        // Auto-start generation if enabled
        if (this.autoStart) {
            this.startGeneration();
        }
    },
    
    beforeUnmount() {
        // Clean up intervals when component is destroyed
        this.clearIntervals();
    }
};
</script>
