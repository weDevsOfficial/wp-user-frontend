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
                console.warn('Translation function error:', error);
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
                const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
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
                        provider: config.provider || 'predefined'
                        // Note: temperature and max_tokens are now handled by backend based on model configuration
                    })
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    // Try to get detailed error from response
                    let errorDetails = `HTTP ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        if (errorData.message) {
                            errorDetails = `${errorDetails} - ${errorData.message}`;
                        }
                        if (errorData.data && errorData.data.details) {
                            errorDetails = `${errorDetails} (${errorData.data.details})`;
                        }
                    } catch (e) {
                        // If can't parse JSON, use original error
                    }
                    throw new Error(errorDetails);
                }
                
                const result = await response.json();
                console.log('API Response:', result);
                
                if (result.success) {
                    // Store everything as-is from API
                    this.generatedFormData = result.data;
                    this.formTitle = result.data.form_title || 'Generated Form';
                    this.formFields = result.data.wpuf_fields || [];

                    // Notify processing stage that AI response is received
                    if (this.$refs.processingStage) {
                        this.$refs.processingStage.onAIResponseReceived();
                    }

                    // Processing stage will handle the transition timing
                } else {
                    console.error('Form generation failed:', result);
                    
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
                console.error('API call failed:', error);
                let errorMessage = 'Network error occurred';
                
                if (error.message.includes('HTTP')) {
                    errorMessage = `Server error: ${error.message}`;
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
                    <!-- Error Icon -->
                    <div class="wpuf-ai-error-icon">
                        ${errorType === 'invalid_request' ? `
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="20" fill="#FEF3F2" stroke="#FEE4E2" stroke-width="2"/>
                                <path d="M24 16V24" stroke="#F97066" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="24" cy="30" r="1.5" fill="#F97066"/>
                            </svg>
                        ` : `
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="20" fill="#FEF2F2" stroke="#FECACA" stroke-width="2"/>
                                <path d="M18 18L30 30M30 18L18 30" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        `}
                    </div>
                    
                    <!-- Title -->
                    <h3 class="wpuf-ai-error-title">
                        ${errorType === 'invalid_request' ? (i18n.invalidRequest || 'Invalid Request') : (i18n.errorTitle || 'Error')}
                    </h3>
                    
                    <!-- Message -->
                    <p class="wpuf-ai-error-message">${message}</p>
                    
                    <!-- Detailed Error Information -->
                    ${errorData ? `
                        <div class="wpuf-ai-error-details">
                            <details>
                                <summary>Technical Details</summary>
                                <div class="wpuf-ai-error-details-content">
                                    ${errorData.code ? `<p><strong>Error Code:</strong> ${errorData.code}</p>` : ''}
                                    ${errorData.data ? `<p><strong>Details:</strong> ${JSON.stringify(errorData.data, null, 2)}</p>` : ''}
                                    ${errorData.stack ? `<p><strong>Stack:</strong> <pre>${errorData.stack}</pre></p>` : ''}
                                </div>
                            </details>
                        </div>
                    ` : ''}
                    
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
                        padding: 40px;
                        border-radius: 16px;
                        max-width: 480px;
                        width: 90%;
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
                        color: #111827;
                        margin: 0 0 16px 0;
                        font-size: 24px;
                        font-weight: 600;
                        line-height: 1.3;
                    }
                    .wpuf-ai-error-message {
                        color: #6b7280;
                        margin: 0 0 24px 0;
                        line-height: 1.6;
                        font-size: 16px;
                    }
                    .wpuf-ai-error-details {
                        margin: 16px 0 24px 0;
                        padding: 16px;
                        background: #f9fafb;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        text-align: left;
                    }
                    .wpuf-ai-error-details details {
                        font-size: 14px;
                    }
                    .wpuf-ai-error-details summary {
                        cursor: pointer;
                        font-weight: 600;
                        color: #374151;
                        padding: 8px 0;
                        border-bottom: 1px solid #e5e7eb;
                        margin-bottom: 12px;
                    }
                    .wpuf-ai-error-details summary:hover {
                        color: #111827;
                    }
                    .wpuf-ai-error-details-content {
                        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                        font-size: 12px;
                        line-height: 1.5;
                        color: #374151;
                    }
                    .wpuf-ai-error-details-content p {
                        margin: 8px 0;
                    }
                    .wpuf-ai-error-details-content strong {
                        color: #111827;
                    }
                    .wpuf-ai-error-details-content pre {
                        background: #f3f4f6;
                        padding: 8px;
                        border-radius: 4px;
                        overflow-x: auto;
                        white-space: pre-wrap;
                        word-break: break-all;
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
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        border: none;
                        padding: 12px 32px;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        min-width: 120px;
                    }
                    .wpuf-ai-error-close:hover {
                        background: linear-gradient(135deg, #059669 0%, #047857 100%);
                        transform: translateY(-1px);
                        box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.15);
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
            
            console.warn('Pro field requested:', result.message);
            
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
                } else {
                    console.log('Pro is active, no need to show warning');
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
            console.log('Sending message:', message);
            // TODO: Implement API call
        },
        
        async applyForm() {
            if (!this.generatedFormData) {
                alert(this.__('No form data available. Please generate a form first.'));
                return;
            }

            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';

                console.log('Creating and applying form...');
                
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
                        form_data: formDataToSend
                    })
                });

                if (!response.ok) {
                    // Get error details from response
                    const errorData = await response.json().catch(() => ({}));
                    console.error('API Error Response:', errorData);
                    throw new Error(`HTTP ${response.status}: ${errorData.message || response.statusText}`);
                }

                const result = await response.json();

                if (result.success && result.form_id) {
                    console.log('Form created and applied successfully with ID:', result.form_id);
                    // Redirect to forms list to show the new form
                    window.location.href = 'admin.php?page=wpuf-post-forms';
                } else {
                    throw new Error(result.message || 'Failed to create form');
                }

            } catch (error) {
                console.error('Failed to apply form:', error);
                alert(this.__('Error applying form: ') + error.message);
            }
        },
        
        rejectForm() {
            console.log('Form rejected');
            // TODO: Implement form rejection logic
        },
        
        regenerateForm() {
            console.log('Regenerating form');
            this.currentStage = 'input';
            this.formDescription = '';
            this.selectedPrompt = '';
        },
        
        async editInBuilder(eventData) {
            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';

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
                    // Form exists, just redirect to edit
                    window.location.href = `admin.php?page=wpuf-post-forms&action=edit&id=${formIdToUse}`;
                    return;
                }

                // Create new form
                console.log('Creating form with fields:', fieldsToUse);

                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        form_data: currentFormData
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
                console.error('Failed to create/edit form:', error);
                alert(this.__('Error: ') + error.message);
            }
        },
        
        editWithBuilder(eventData) {
            this.editInBuilder(eventData);
        },
        
        handleFormUpdated(updatedFormData) {
            // Update form data when changes are made in the chat
            console.log('Form updated from chat:', updatedFormData);
            
            if (updatedFormData) {
                // Update the generated form data with the changes from chat
                this.generatedFormData = {
                    ...this.generatedFormData,
                    ...updatedFormData
                };
                
                // Update formFields directly - no conversion needed
                if (updatedFormData.wpuf_fields) {
                    this.formFields = updatedFormData.wpuf_fields;
                } else if (updatedFormData.fields) {
                    this.formFields = updatedFormData.fields;
                }
                
                // Update form title if changed
                if (updatedFormData.form_title) {
                    this.formTitle = updatedFormData.form_title;
                }
            }
        },
        
        handleTitleUpdated(newTitle) {
            // Update form title when changed in chat
            console.log('Form title updated:', newTitle);
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
