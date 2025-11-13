<template>
    <div>
        <!-- Stage 1: Form Input -->
        <FormInputStage
            v-if="currentStage === 'input'"
            :initialDescription="formDescription"
            :initialSelectedPrompt="selectedPrompt"
            :generating="isGenerating"
            @go-back="goBack"
            @start-generation="handleStartGeneration"
            @update:selectedPrompt="selectedPrompt = $event"
            @update:formDescription="formDescription = $event"
        />
        
        <!-- Stage 2: Processing/Generating -->
        <FormProcessingStage
            v-else-if="currentStage === 'generating'"
            :autoStart="true"
            :waitForAI="true"
            ref="processingStage"
            @generation-complete="handleGenerationComplete"
        />
        
        <!-- Stage 3: Success/Chat Interface -->
        <FormSuccessStage
            v-else-if="currentStage === 'success'"
            :formTitle="formTitle"
            :formId="formId"
            :initialMessages="chatMessages"
            :initialFormFields="formFields"
            @send-message="handleSendMessage"
            @apply-form="applyForm"
            @reject-form="rejectForm"
            @regenerate-form="regenerateForm"
            @edit-in-builder="editInBuilder"
            @edit-with-builder="editWithBuilder"
            @form-updated="handleFormUpdated"
            @title-updated="handleTitleUpdated"
        />
    </div>
</template>

<script>
import FormInputStage from './FormInputStage.vue';
import FormProcessingStage from './FormProcessingStage.vue';
import FormSuccessStage from './FormSuccessStage.vue';

export default {
    name: 'AIFormBuilder',
    
    components: {
        FormInputStage,
        FormProcessingStage,
        FormSuccessStage
    },
    
    data() {
        return {
            // Stage management
            currentStage: 'input', // 'input', 'generating', 'success'
            
            // Form data
            formDescription: '',
            selectedPrompt: '',
            isGenerating: false,
            formTitle: 'Generated Form',
            formId: null,
            
            // Chat data
            chatMessages: [],
            
            // Form fields
            formFields: [],
            
            // API data
            generatedFormData: null,
            sessionId: null
        };
    },
    
    methods: {
        __( text ) {
            // Translation function placeholder with error handling
            try {
                return window.__ ? window.__(text, 'wp-user-frontend') : text;
            } catch (error) {
                return text;
            }
        },
        
        goBack() {
            window.history.back();
        },
        
        handleStartGeneration(data) {
            this.formDescription = data.description;
            this.selectedPrompt = data.selectedPrompt;
            this.isGenerating = true;
            this.currentStage = 'generating';
            
            // Call AI form generation API
            this.callAIFormGenerationAPI(data.description);
        },
        
        handleGenerationComplete() {
            this.currentStage = 'success';
            this.isGenerating = false;
            this.initializeChatData();
        },
        
        async callAIFormGenerationAPI(prompt) {
            try {
                // Get configuration with fallbacks
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.rest_url || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';
                
                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        session_id: this.getSessionId(),
                        form_type: config.formType || 'post', // Pass form type to API
                        provider: config.provider || 'openai'
                        // Note: temperature and max_tokens are now handled by backend based on model configuration
                    })
                });
                
                if (!response.ok) {
                    // Try to get detailed error from response and extract clean message
                    let errorMessage = `HTTP ${response.status}: ${response.statusText}`;

                    try {
                        const errorData = await response.json();

                        // Extract the actual error message from the API response
                        if (errorData.message) {
                            // Check if the message contains JSON with error details
                            const jsonMatch = errorData.message.match(/\{[\s\S]*\}/);
                            if (jsonMatch) {
                                try {
                                    const parsedError = JSON.parse(jsonMatch[0]);
                                    if (parsedError.error && parsedError.error.message) {
                                        // Use the clean error message from the API
                                        errorMessage = parsedError.error.message;
                                    } else {
                                        errorMessage = errorData.message;
                                    }
                                } catch (e) {
                                    errorMessage = errorData.message;
                                }
                            } else {
                                errorMessage = errorData.message;
                            }
                        }

                        // Add additional details if available
                        if (errorData.data && errorData.data.details) {
                            const detailsText = typeof errorData.data.details === 'string'
                                ? errorData.data.details
                                : JSON.stringify(errorData.data.details);

                            // Only add details if they're different from the main message
                            if (!errorMessage.includes(detailsText)) {
                                errorMessage += ` (${detailsText})`;
                            }
                        }
                    } catch (e) {
                        // If can't parse JSON, use original error
                    }

                    throw new Error(errorMessage);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Store everything as-is from API
                    this.generatedFormData = result.data;
                    this.formTitle = result.data.form_title || 'Generated Form';
                    this.formFields = result.data.wpuf_fields || [];

                    // Notify processing stage that AI response is received
                    // Use nextTick to ensure ref is mounted, with retry fallback
                    const notifyProcessingStage = (retries = 3) => {
                        this.$nextTick(() => {
                            if (this.$refs.processingStage) {
                                this.$refs.processingStage.onAIResponseReceived();
                            } else if (retries > 0) {
                                setTimeout(() => notifyProcessingStage(retries - 1), 100);
                            } else {
                                this.handleGenerationComplete();
                            }
                        });
                    };
                    notifyProcessingStage();

                    // Processing stage will handle the transition timing
                } else {
                    // Check for specific error types
                    if (result.code === 'invalid_request' || result.code === 'generation_failed') {
                        // Non-form request error
                        this.handleGenerationError(
                            result.message || 'Form generation failed',
                            'invalid_request',
                            result
                        );
                    } else if (result.warning && result.warning_type === 'pro_field_requested') {
                        // Pro field warning - still generate the form
                        this.handleProFieldWarning(result);
                    } else {
                        this.handleGenerationError(result.message || 'Form generation failed', 'general', result);
                    }
                }
            } catch (error) {
                let errorMessage = 'Network error occurred';

                if (error.message.includes('HTTP')) {
                    errorMessage = error.message;
                } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Cannot connect to server. Please check if WordPress REST API is accessible.';
                }

                // Create error data object with full error information
                const errorData = {
                    name: error.name,
                    message: error.message,
                    stack: error.stack,
                    type: 'network_error'
                };

                this.handleGenerationError(errorMessage, 'general', errorData);
            }
        },
        
        handleGenerationError(message, errorType = 'general', errorData = null) {
            // Notify processing stage that AI response is received (even if error)
            if (this.$refs.processingStage) {
                this.$refs.processingStage.onAIResponseReceived();
            }

            this.isGenerating = false;
            this.currentStage = 'input';
            
            // Show styled error message instead of alert
            const config = window.wpufAIFormBuilder || {};
            const i18n = config.i18n || {};
            
            // Create a modal-like error display
            const errorContainer = document.createElement('div');
            errorContainer.className = 'wpuf-ai-error-modal';
            errorContainer.innerHTML = `
                <div class="wpuf-ai-error-overlay"></div>
                <div class="wpuf-ai-error-content">
                    <!-- Oops Icon -->
                    <div class="wpuf-ai-error-icon">
                        <svg width="116" height="110" viewBox="0 0 116 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M96.7102 53.1715C102.907 57.7232 104.056 65.9027 99.0762 72.1894C94.0964 78.4762 84.7677 80.6619 77.4444 77.5073C77.8725 84.7178 71.9689 91.5454 63.519 93.1678C55.069 94.8127 47.0698 90.6891 44.7714 83.8391C39.1607 89.4949 29.6742 90.9595 22.7115 86.9712C15.7488 83.0053 13.7659 74.9836 17.7993 68.449C9.46203 69.2151 1.93597 64.4606 0.538917 57.2049C-0.858135 49.9493 4.34701 42.7387 12.3688 40.3502C6.17219 35.7985 5.02301 27.619 10.0028 21.3322C14.9826 15.0455 24.3339 12.8598 31.6346 16.0144C31.2065 8.80381 37.1102 1.97627 45.5601 0.35389C54.01 -1.26849 62.0092 2.83253 64.3301 9.6826C69.9409 4.02679 79.4273 2.56213 86.3901 6.5505C93.3528 10.5389 95.3357 18.5381 91.3023 25.0727C99.6395 24.3066 107.166 29.0385 108.563 36.2942C109.96 43.5499 104.732 50.7605 96.7102 53.1715Z" fill="#FFEDD5"/>
                            <path d="M89.2528 107.521C95.1267 107.521 99.8884 102.76 99.8884 96.8856C99.8884 91.0117 95.1267 86.25 89.2528 86.25C83.3789 86.25 78.6172 91.0117 78.6172 96.8856C78.6172 102.76 83.3789 107.521 89.2528 107.521Z" fill="#FFEDD5"/>
                            <path d="M111.382 109.998C113.759 109.998 115.686 108.071 115.686 105.694C115.686 103.318 113.759 101.391 111.382 101.391C109.005 101.391 107.078 103.318 107.078 105.694C107.078 108.071 109.005 109.998 111.382 109.998Z" fill="#FFEDD5"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M82.9011 42.6006C82.6532 41.2936 82.3603 39.9867 82.0674 38.7249C81.9065 38.0701 81.7516 37.4563 81.6054 36.8775C81.4649 36.321 81.3326 35.7969 81.2111 35.2998C81.0985 34.9095 80.9884 34.5241 80.88 34.1445C80.6629 33.3849 80.4525 32.6486 80.2422 31.9424L86.8444 30.4102C86.9345 31.334 87.0472 32.2804 87.2049 33.2944C87.3626 34.3084 87.5204 35.2998 87.7232 36.2913C87.7829 36.5768 87.8408 36.8585 87.8978 37.1362C88.0342 37.8008 88.166 38.4431 88.309 39.0629C88.4324 39.6644 88.5664 40.2131 88.6892 40.7162C88.7458 40.9482 88.8001 41.1705 88.8498 41.3838C89.0301 42.1724 89.2104 42.8935 89.3906 43.5695L83.3518 45.0116C83.318 44.8088 83.2841 44.6116 83.2504 44.4145C83.2166 44.2173 83.1828 44.0202 83.149 43.8174L82.9011 42.6006ZM76.5036 41.4965C77.3373 41.4514 78.1485 41.5641 78.9372 41.7669C79.7258 41.9697 80.4244 42.3753 80.9877 42.871C81.5961 43.4343 82.0017 44.3131 82.227 45.4848C82.3397 46.0932 82.3397 46.7242 82.2495 47.3326C82.1594 48.0762 81.9115 48.7972 81.5285 49.4507C81.0778 50.2168 80.492 50.8703 79.7709 51.3885C78.847 52.042 77.8105 52.4476 76.7289 52.6278C75.6473 52.8532 74.5657 52.9658 73.4616 52.9433C72.6955 52.9433 71.9519 52.8306 71.2083 52.6729C70.5999 52.5377 70.0141 52.2898 69.4733 51.9744L69.6535 47.1974C70.4873 47.265 71.2984 47.2875 72.1322 47.2424C72.7406 47.2199 73.349 47.1523 73.9573 47.0396C74.3855 46.972 74.8136 46.8594 75.2192 46.7016C75.4896 46.6115 75.7375 46.4763 75.9853 46.3186C76.1656 46.2059 76.3459 46.0707 76.5036 45.913C74.5883 46.2735 73.011 46.3862 71.8167 46.2284C70.5999 46.0707 69.6535 45.7552 68.955 45.282C68.279 44.8765 67.7607 44.2681 67.4227 43.5695C67.1298 42.9161 66.9045 42.2626 66.7693 41.5641C66.5665 40.6177 66.5665 39.6262 66.7693 38.6799C66.9721 37.756 67.3101 36.8772 67.8283 36.066C68.3241 35.2774 68.9775 34.5788 69.7437 34.038C71.4111 32.8888 73.4842 32.4832 75.4671 32.9339C76.2783 33.1367 77.0219 33.4522 77.7429 33.8578C78.4414 34.3084 79.0724 34.8718 79.5906 35.5252L76.1881 40.798C75.8051 40.4825 75.3769 40.2346 74.9263 40.0093C74.5432 39.829 74.1376 39.6713 73.7095 39.5586C73.2814 39.446 72.8307 39.4234 72.38 39.5136C72.0646 39.5586 71.7716 39.7164 71.5914 39.9642C71.4336 40.167 71.3886 40.4374 71.4336 40.6853C71.5238 41.136 71.8167 41.4064 72.3124 41.4965C72.9208 41.6092 73.5518 41.6317 74.1601 41.5866C74.4806 41.5766 74.8145 41.5621 75.1597 41.5472H75.1597L75.1601 41.5472C75.5914 41.5285 76.0406 41.509 76.5036 41.4965ZM63.7743 36.7447C63.2335 36.2039 62.5575 35.7983 61.8365 35.5504C61.0478 35.3026 60.2141 35.2575 59.4029 35.4378C58.7269 35.5504 58.096 35.8208 57.5777 36.2715C57.1721 36.632 56.8116 37.0602 56.5187 37.5108C56.5187 37.3531 56.4961 37.2179 56.4511 37.0602L56.406 36.7898L56.3984 36.7373C56.3787 36.6013 56.3609 36.4791 56.3609 36.3391L49.7812 37.984C50.1868 39.4261 50.5924 40.9809 50.998 42.6258C51.2312 43.5438 51.4643 44.5465 51.7157 45.6279L51.716 45.6294L51.7163 45.6306L51.7164 45.6311C51.8536 46.2211 51.9962 46.8346 52.1472 47.4705C52.5754 49.2731 52.9584 51.1884 53.3415 53.1713L53.702 55.0416C53.8147 55.6725 53.9273 56.3034 54.04 56.9118L60.0564 55.312C60.0176 55.1378 59.9748 54.9553 59.9296 54.7628C59.8695 54.5069 59.8052 54.2332 59.7409 53.9375C59.6282 53.5093 59.5156 52.9911 59.4029 52.3827C59.3594 52.1869 59.3159 51.9841 59.2709 51.7744L59.2708 51.7741C59.176 51.332 59.0745 50.8589 58.9522 50.3547C59.3128 50.4674 59.7183 50.5575 60.1014 50.58C60.5521 50.6026 61.0253 50.58 61.4759 50.4899C62.3998 50.3322 63.2786 49.904 63.9771 49.2731C64.6531 48.6422 65.1939 47.876 65.5544 47.0198C65.9375 46.1185 66.1628 45.1721 66.2304 44.1806C66.3205 43.1666 66.253 42.1526 66.0502 41.1612C65.8924 40.3049 65.622 39.4712 65.239 38.6825C64.8559 37.9615 64.3602 37.308 63.7743 36.7447ZM59.5832 43.8652C59.5832 44.1806 59.5831 44.5186 59.5381 44.8341C59.493 45.0819 59.4254 45.3298 59.2902 45.5551C59.2001 45.7354 59.0198 45.8706 58.817 45.9157C58.6593 45.9607 58.5016 45.9382 58.3664 45.8481C58.2312 45.7805 58.1185 45.6678 58.0284 45.5551C57.9157 45.3974 57.8481 45.2397 57.7805 45.0594C57.7461 44.87 57.6985 44.6675 57.6478 44.4518L57.6477 44.4513L57.6476 44.4511L57.6476 44.451C57.632 44.3847 57.6161 44.3171 57.6002 44.2482C57.5731 44.1305 57.5423 44.0056 57.5108 43.8778L57.5107 43.8773L57.5107 43.8772L57.5107 43.8772L57.5106 43.8771C57.4638 43.6869 57.4153 43.4904 57.3749 43.3018L57.3579 43.2006C57.3188 42.9686 57.2819 42.7495 57.2622 42.5132C57.2397 42.2878 57.2622 42.04 57.2848 41.8146C57.3298 41.6118 57.42 41.409 57.5777 41.2738C57.7354 41.1161 57.9157 41.0035 58.141 40.9809C58.4565 40.9133 58.7269 41.0936 58.9748 41.4766C59.2226 41.9048 59.4029 42.4005 59.493 42.8962C59.5381 43.1892 59.5832 43.5272 59.5832 43.8652ZM41.4844 39.1972C43.6926 38.679 46.0361 39.3099 47.681 40.8872C48.4696 41.6533 49.1456 42.5547 49.6414 43.5461C50.1596 44.5826 50.5201 45.6868 50.7455 46.8359C50.9933 48.1203 51.0609 49.4273 50.9483 50.7342C50.8581 51.996 50.5427 53.2128 50.047 54.3845C49.5738 55.4887 48.8752 56.4801 47.9964 57.2913C47.0726 58.125 45.9685 58.6658 44.7517 58.8686C43.625 59.1165 42.4308 59.0264 41.3266 58.6433C40.2901 58.2602 39.3212 57.6518 38.5325 56.8632C37.6763 56.0295 37.0003 55.0605 36.482 54.0015C35.9412 52.8974 35.5356 51.7031 35.3103 50.4863C35.0624 49.2695 34.9948 48.0302 35.1075 46.8134C35.1976 45.6417 35.4906 44.4925 35.9863 43.4109C36.4595 42.3744 37.1805 41.4505 38.0593 40.7295C39.0508 39.9408 40.2225 39.4 41.4844 39.1972ZM44.9995 52.1312C45.0671 51.613 45.0897 51.0722 45.0446 50.5539C44.9995 49.9681 44.9319 49.3822 44.7967 48.8414C44.7066 48.3231 44.5714 47.8274 44.4137 47.3317C44.2559 46.9035 44.0757 46.4754 43.8503 46.0924C43.6701 45.7769 43.4448 45.5065 43.1744 45.2812C42.949 45.1009 42.6561 45.0108 42.3632 45.0784C42.0477 45.1234 41.7998 45.3262 41.6421 45.5966C41.4393 45.9346 41.3041 46.3177 41.259 46.7007C41.1914 47.1739 41.1689 47.6697 41.1914 48.1654C41.259 49.2695 41.4618 50.3511 41.8224 51.3876C41.9801 51.8608 42.1604 52.3115 42.4082 52.7396C42.5885 53.0776 42.8364 53.3931 43.1293 53.6409C43.3546 53.8437 43.6475 53.9339 43.9405 53.8888C44.2559 53.8212 44.5038 53.6184 44.639 53.3255C44.8193 52.9424 44.9545 52.5368 44.9995 52.1312ZM25.421 42.3078C27.6292 41.7895 29.9727 42.443 31.6176 43.9978C32.4062 44.7639 33.0597 45.6652 33.5329 46.6792C34.0511 47.7157 34.4117 48.8198 34.637 49.969C34.8849 51.2534 34.9525 52.5603 34.8398 53.8673C34.7497 55.1291 34.4342 56.3459 33.9385 57.5176C33.4653 58.5992 32.7668 59.5907 31.888 60.4019C30.9641 61.2356 29.86 61.7764 28.6432 61.9792C27.5165 62.227 26.3223 62.1369 25.2182 61.7539C24.1816 61.3708 23.2127 60.7624 22.4241 59.9737C21.5903 59.14 20.9143 58.1711 20.3961 57.112C19.8553 56.0079 19.4497 54.8137 19.2244 53.5969C18.9765 52.3801 18.9089 51.1408 19.0216 49.924C19.1117 48.7523 19.4272 47.6031 19.9229 46.5215C20.3961 45.4849 21.1171 44.5611 21.9959 43.84C22.9874 43.0514 24.1591 42.5106 25.421 42.3078ZM28.9361 55.2418C29.0037 54.7235 29.0263 54.1827 28.9812 53.6645C28.9361 53.0786 28.8685 52.4927 28.7333 51.9294C28.6432 51.4112 28.508 50.9154 28.3503 50.4197C28.1925 49.9916 28.0123 49.5634 27.7869 49.1804C27.6067 48.8649 27.3813 48.5945 27.1109 48.3692C26.8856 48.1889 26.5927 48.0988 26.2998 48.1664C25.9843 48.234 25.7364 48.4143 25.5787 48.6847C25.3759 49.0226 25.2632 49.4057 25.1956 49.7888C25.128 50.262 25.1055 50.7577 25.128 51.2534C25.1956 52.3575 25.3984 53.4391 25.759 54.4757C25.9167 54.9489 26.1195 55.3995 26.3448 55.8276C26.5251 56.1656 26.773 56.4811 27.0659 56.729C27.2912 56.9318 27.5841 57.0219 27.8771 56.9768C28.1925 56.9092 28.4404 56.7064 28.5756 56.4135C28.7559 56.053 28.8911 55.6474 28.9361 55.2418ZM89.9516 46.2525C89.7037 45.9596 89.4108 45.7343 89.0728 45.5765C88.7122 45.3963 88.3067 45.2836 87.9011 45.2385C87.4504 45.1709 86.9997 45.1935 86.5716 45.2836C86.1209 45.3737 85.6928 45.5089 85.3098 45.7343C84.9492 45.937 84.6112 46.1849 84.3408 46.5004C84.093 46.7708 83.8902 47.1088 83.7775 47.4468C83.6648 47.7848 83.6423 48.1678 83.7099 48.5284C83.7775 48.8889 83.9352 49.2044 84.1606 49.4748C84.4084 49.7677 84.7014 50.0155 85.0394 50.1733C85.3999 50.3535 85.8055 50.4887 86.2111 50.5338C86.6617 50.6014 87.1124 50.5789 87.5631 50.4887C88.0137 50.3986 88.4193 50.2634 88.8249 50.0381C89.1854 49.8353 89.5009 49.5874 89.7713 49.272C90.0192 48.979 90.1994 48.6636 90.3346 48.303C90.4473 47.965 90.4698 47.6045 90.4022 47.244C90.3572 46.8609 90.1994 46.5454 89.9516 46.2525Z" fill="#FF9000"/>
                        </svg>
                    </div>

                    <!-- Title -->
                    <h3 class="wpuf-ai-error-title">
                        Oops...
                    </h3>
                    
                    <!-- Message -->
                    <p class="wpuf-ai-error-message">${message}</p>

                    <!-- Hint for invalid requests -->
                    ${errorType === 'invalid_request' ? `
                        <div class="wpuf-ai-error-hint">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="7" fill="#DBEAFE" stroke="#93C5FD" stroke-width="1"/>
                                <path d="M8 5.33333V8.66667" stroke="#1D4ED8" stroke-width="1.2" stroke-linecap="round"/>
                                <circle cx="8" cy="11" r="0.8" fill="#1D4ED8"/>
                            </svg>
                            <span>${i18n.nonFormRequest || 'I can only help with form creation. Try: "Create a contact form"'}</span>
                        </div>
                    ` : ''}
                    
                    <!-- Button -->
                    <button class="wpuf-ai-error-close">
                        ${i18n.tryAgain || 'Try Again'}
                    </button>
                </div>
            `;
            
            document.body.appendChild(errorContainer);
            
            // Add styles if not already present
            if (!document.getElementById('wpuf-ai-error-styles')) {
                const style = document.createElement('style');
                style.id = 'wpuf-ai-error-styles';
                style.textContent = `
                    .wpuf-ai-error-modal {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        z-index: 999999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        backdrop-filter: blur(4px);
                        animation: wpufFadeIn 0.2s ease-out;
                    }
                    .wpuf-ai-error-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(17, 24, 39, 0.7);
                        animation: wpufFadeIn 0.2s ease-out;
                    }
                    .wpuf-ai-error-content {
                        position: relative;
                        background: white;
                        padding: 56px 80px 56px;
                        border-radius: 8px;
                        width: 660px;
                        max-width: 90%;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                        text-align: center;
                        animation: wpufSlideUp 0.3s ease-out;
                        border: 1px solid #f3f4f6;
                    }
                    .wpuf-ai-error-icon {
                        display: flex;
                        justify-content: center;
                        margin-bottom: 24px;
                    }
                    .wpuf-ai-error-title {
                        color: #FF9000;
                        margin: 0 0 12px 0;
                        font-size: 30px;
                        font-weight: 800;
                        line-height: 36px;
                    }
                    .wpuf-ai-error-message {
                        color: #6B7280;
                        margin: 0 0 32px 0;
                        line-height: 28px;
                        font-size: 20px;
                        font-weight: 500;
                    }
                    .wpuf-ai-error-hint {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        color: #1d4ed8;
                        margin: 20px 0 24px 0;
                        padding: 16px;
                        background: #f0f9ff;
                        border: 1px solid #dbeafe;
                        border-radius: 12px;
                        font-size: 14px;
                        line-height: 1.5;
                        text-align: left;
                    }
                    .wpuf-ai-error-hint svg {
                        flex-shrink: 0;
                    }
                    .wpuf-ai-error-close {
                        background: #059669;
                        color: white;
                        border: none;
                        padding: 13px 23px 13px 25px;
                        border-radius: 6px;
                        font-size: 16px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.05);
                        width: 118px;
                        height: 50px;
                        line-height: 24px;
                        gap: 12px;
                    }
                    .wpuf-ai-error-close:hover {
                        background: #047857;
                        box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.05);
                    }
                    .wpuf-ai-error-close:active {
                        transform: translateY(0);
                    }
                    @keyframes wpufFadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes wpufSlideUp {
                        from { 
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to { 
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Close on button click
            errorContainer.querySelector('.wpuf-ai-error-close').addEventListener('click', () => {
                errorContainer.remove();
            });
            
            // Close on overlay click
            errorContainer.querySelector('.wpuf-ai-error-overlay').addEventListener('click', () => {
                errorContainer.remove();
            });
        },
        
        handleProFieldWarning(result) {
            // Only show warning if Pro is not active
            const config = window.wpufAIFormBuilder || {};
            const isProActive = config.isProActive || false;
            
            if (result.form_data) {
                this.generatedFormData = result.form_data;
                this.formTitle = result.form_data.form_title || 'Generated Form';
                // Use wpuf_fields directly from the response
                this.formFields = result.form_data.wpuf_fields || result.form_data.fields || [];

                // Notify processing stage that AI response is received
                if (this.$refs.processingStage) {
                    this.$refs.processingStage.onAIResponseReceived();
                }

                // Processing stage will handle the transition timing
                // Show pro field warning if Pro is not active after transition
                if (!isProActive) {
                    // Show the warning after the success stage is loaded
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.showProFieldModal(result.message);
                        }, 500);
                    });
                }
            }
        },
        
        showProFieldModal(message) {
            const config = window.wpufAIFormBuilder || {};
            const i18n = config.i18n || {};
            
            // Create modal overlay
            const overlay = document.createElement('div');
            overlay.className = 'wpuf-pro-modal-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 100000;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            // Create modal content
            const modal = document.createElement('div');
            modal.className = 'wpuf-pro-modal';
            modal.style.cssText = `
                background: white;
                border-radius: 8px;
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            `;
            
            modal.innerHTML = `
                <div style="padding: 24px;">
                    <div style="display: flex; align-items: center; margin-bottom: 16px;">
                        <span style="color: #f39c12; font-size: 24px; margin-right: 12px;">⚡</span>
                        <h3 style="margin: 0; font-size: 20px; color: #333;">${i18n.proFieldWarning || 'Pro Feature Required'}</h3>
                    </div>
                    <p style="color: #666; margin-bottom: 20px; line-height: 1.5;">
                        ${i18n.proFieldMessage || 'This field type requires WP User Frontend Pro. You can continue without it or upgrade to Pro for full functionality.'}
                    </p>
                    <div style="background: #f8f9fa; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; color: #666;">
                        ${message}
                    </div>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button id="wpuf-continue-without-pro" style="
                            padding: 8px 16px;
                            border: 1px solid #ddd;
                            background: white;
                            color: #666;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 14px;
                        ">${i18n.continueWithoutPro || 'Continue without Pro'}</button>
                        <a href="https://wedevs.com/wp-user-frontend-pro/pricing/" target="_blank" style="
                            padding: 8px 16px;
                            background: #0073aa;
                            color: white;
                            text-decoration: none;
                            border-radius: 4px;
                            font-size: 14px;
                        ">${i18n.upgradeToPro || 'Upgrade to Pro'}</a>
                    </div>
                </div>
            `;
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            // Close modal handlers
            const closeModal = () => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            };
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });
            
            document.getElementById('wpuf-continue-without-pro').addEventListener('click', closeModal);
            
            // Auto-close after 15 seconds
            setTimeout(closeModal, 15000);
        },
        
        // Simple helper to get field display type
        getFieldDisplayType(field) {
            // If field has template, use it
            if (field.template === 'post_title' || field.input_type === 'text') return 'text_field';
            if (field.template === 'post_content' || field.input_type === 'textarea') return 'textarea_field';
            if (field.input_type === 'select' || field.input_type === 'dropdown') return 'dropdown_field';
            if (field.input_type === 'radio') return 'radio_field';
            if (field.input_type === 'checkbox') return 'checkbox_field';
            if (field.input_type === 'date') return 'date_field';
            if (field.input_type === 'email') return 'email_address';
            return field.input_type || field.type || 'text_field';
        },
        
        getSessionId() {
            if (!this.sessionId) {
                this.sessionId = 'wpuf_ai_session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
            }
            return this.sessionId;
        },
        
        initializeChatData() {
            // Initialize chat messages based on the generated form data
            this.chatMessages = [
                {
                    type: 'user',
                    content: this.formDescription || 'Create a form'
                },
                {
                    type: 'ai',
                    content: "..."
                }
            ];
            
            if (this.generatedFormData) {
                const fieldsList = this.formFields.map(field => {
                    const requiredText = (field.required === 'yes' || field.required === true) ? ' (Required)' : '';
                    const fieldType = this.getFieldDisplayType(field);
                    return `<li>${field.label}${requiredText} - ${this.getFieldTypeDescription(fieldType)}</li>`;
                }).join('');
                
                const successMessage = {
                    type: 'ai',
                    content: `Perfect! I've created a "${this.formTitle}" form for you with the following fields:
                    <ul>${fieldsList}</ul>
                    ${this.generatedFormData.form_description || 'The form is ready and you can customize it further in the form builder!'}`,
                    showButtons: false,  // Don't show buttons for initial form creation
                    status: 'Successfully created the form.'
                };
                
                this.chatMessages.push(successMessage);
            }
        },
        
        getFieldTypeDescription(type) {
            const descriptions = {
                'text': 'Text input field',
                'email': 'Email address field',
                'tel': 'Phone number field',
                'url': 'Website URL field',
                'number': 'Numeric input field',
                'textarea': 'Multi-line text area',
                'select': 'Dropdown selection',
                'radio': 'Single choice selection',
                'checkbox': 'Multiple choice selection',
                'file': 'File upload field',
                'date': 'Date picker field',
                'time': 'Time picker field',
                'datetime': 'Date and time picker'
            };
            return descriptions[type] || 'Input field';
        },
        
        
        handleSendMessage(message) {
            // Handle sending message to AI backend
            // TODO: Implement API call
        },
        
        async applyForm() {
            if (!this.generatedFormData) {
                alert(this.__('No form data available. Please generate a form first.'));
                return;
            }

            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.rest_url || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';
                const formType = config.formType || 'post';

                // Always use wpuf_fields from generated data - they already have correct WPUF format
                const wpufFields = this.generatedFormData.wpuf_fields || [];

                // Prepare form data with wpuf_fields
                const formDataToSend = {
                    ...this.generatedFormData,
                    form_title: this.formTitle || this.generatedFormData.form_title,
                    wpuf_fields: wpufFields  // Use wpuf_fields directly, no conversion needed
                };

                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        form_data: formDataToSend,
                        form_type: formType
                    })
                });

                if (!response.ok) {
                    // Get error details from response
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                }

                const result = await response.json();

                if (result.success && result.form_id) {
                    // Redirect to forms list based on form type
                    window.location.href = result.list_url || 'admin.php?page=wpuf-post-forms';
                } else {
                    throw new Error(result.message || 'Failed to create form');
                }

            } catch (error) {
                alert(this.__('Error applying form: ') + error.message);
            }
        },
        
        rejectForm() {
            // TODO: Implement form rejection logic
        },
        
        regenerateForm() {
            this.currentStage = 'input';
            this.formDescription = '';
            this.selectedPrompt = '';
        },
        
        async editInBuilder(eventData) {
            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.rest_url || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';
                const formType = config.formType || 'post';

                // Get formId and fields from the event data
                const formIdToUse = eventData.formId || this.formId;
                const fieldsToUse = eventData.formFields || this.formFields;

                // Use current form fields from event
                const currentFormData = {
                    form_title: this.formTitle,
                    form_description: this.generatedFormData?.form_description || '',
                    wpuf_fields: fieldsToUse, // Use fields from event
                    form_settings: this.generatedFormData?.form_settings || {}
                };

                if (formIdToUse) {
                    // Form exists, just redirect to edit based on form type
                    const page = (formType === 'profile' || formType === 'registration') ? 'wpuf-profile-forms' : 'wpuf-post-forms';
                    window.location.href = `admin.php?page=${page}&action=edit&id=${formIdToUse}`;
                    return;
                }

                // Create new form
                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        form_data: currentFormData,
                        form_type: formType
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                }

                const result = await response.json();

                if (result.success && result.form_id) {
                    this.formId = result.form_id;
                    window.location.href = result.edit_url;
                } else {
                    throw new Error(result.message || 'Failed to create form');
                }

            } catch (error) {
                alert(this.__('Error: ') + error.message);
            }
        },
        
        editWithBuilder(eventData) {
            this.editInBuilder(eventData);
        },
        
        handleFormUpdated(updatedFormData) {
            // Update form data when changes are made in the chat
            if (updatedFormData) {
                // Update the generated form data with the changes from chat
                this.generatedFormData = {
                    ...this.generatedFormData,
                    ...updatedFormData
                };

                // DON'T update this.formFields here - the child component manages its own state
                // and emits the raw wpuf_fields format that we store in generatedFormData
                // If we update this.formFields, it will trigger the child's watcher and overwrite
                // the child's converted preview fields with raw API data

                // Update form title if changed
                if (updatedFormData.form_title) {
                    this.formTitle = updatedFormData.form_title;
                }
            }
        },
        
        handleTitleUpdated(newTitle) {
            // Update form title when changed in chat
            this.formTitle = newTitle;
        }
    },
    
    mounted() {
        // Get data from localized script
        const localData = window.wpufAIFormBuilder || {};
        
        // Set initial data from localized script
        this.currentStage = localData.stage || 'input';
        this.formId = localData.formId || '';
        // Only set formTitle if it's not already set from API response
        if (!this.formTitle || this.formTitle === 'Generated Form') {
            this.formTitle = localData.formTitle || 'Generated Form';
        }
        this.formDescription = localData.description || '';
        this.selectedPrompt = localData.prompt || '';
        
        // If we're in success stage, initialize data
        if (this.currentStage === 'success' && localData.formTitle) {
            // If we have a form title from localized data in success stage, use it
            this.formTitle = localData.formTitle;
            this.initializeChatData();
        } else if (this.currentStage === 'success') {
            this.initializeChatData();
        }
    }
};
</script>
