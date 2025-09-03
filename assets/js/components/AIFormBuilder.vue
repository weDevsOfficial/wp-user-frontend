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
            // Translation function placeholder
            return window.__ ? window.__(text, 'wp-user-frontend') : text;
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
                
                console.log('AI Form Builder Config:', config);
                console.log('Using REST URL:', restUrl + 'wpuf/v1/ai-form-builder/generate');
                
                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        session_id: this.getSessionId(),
                        provider: config.provider || 'predefined',
                        temperature: config.temperature || 0.7,
                        max_tokens: config.maxTokens || 2000
                    })
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('API Response:', result);
                
                if (result.success) {
                    // Store the generated form data
                    this.generatedFormData = result.data;
                    this.formTitle = result.data.form_title || 'Generated Form';
                    this.formFields = this.convertFieldsToPreview(result.data.fields || []);
                    
                    // Simulate processing delay for UX
                    setTimeout(() => {
                        this.handleGenerationComplete();
                    }, 1000);
                } else {
                    console.error('Form generation failed:', result);
                    this.handleGenerationError(result.message || 'Form generation failed');
                }
            } catch (error) {
                console.error('API call failed:', error);
                let errorMessage = 'Network error occurred';
                
                if (error.message.includes('HTTP')) {
                    errorMessage = `Server error: ${error.message}`;
                } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Cannot connect to server. Please check if WordPress REST API is accessible.';
                }
                
                this.handleGenerationError(errorMessage);
            }
        },
        
        handleGenerationError(message) {
            this.isGenerating = false;
            this.currentStage = 'input';
            alert(this.__('Error: ') + message);
        },
        
        convertFieldsToPreview(fields) {
            return fields.map(field => ({
                id: field.id,
                type: field.type,
                label: field.label,
                placeholder: field.placeholder || field.help_text || '',
                required: field.required || false,
                options: field.options || []
            }));
        },
        
        getSessionId() {
            if (!this.sessionId) {
                this.sessionId = 'wpuf_ai_session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
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
                    const requiredText = field.required ? ' (Required)' : '';
                    return `<li>${field.label}${requiredText} - ${this.getFieldTypeDescription(field.type)}</li>`;
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

                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        form_data: this.generatedFormData
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
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
        
        async editInBuilder() {
            if (this.formId) {
                // Form already exists, just redirect to edit
                window.location.href = `admin.php?page=wpuf-post-forms&action=edit&id=${this.formId}`;
                return;
            }

            // Need to create form first from AI generated data
            if (!this.generatedFormData) {
                alert(this.__('No form data available. Please generate a form first.'));
                return;
            }

            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.restUrl || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';

                console.log('Creating form from AI data...');

                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/create-form', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        form_data: this.generatedFormData
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                if (result.success && result.form_id) {
                    console.log('Form created successfully with ID:', result.form_id);
                    // Redirect to form builder with the new form ID
                    window.location.href = result.edit_url;
                } else {
                    throw new Error(result.message || 'Failed to create form');
                }

            } catch (error) {
                console.error('Failed to create form:', error);
                alert(this.__('Error creating form: ') + error.message);
            }
        },
        
        editWithBuilder() {
            this.editInBuilder();
        }
    },
    
    mounted() {
        // Get data from localized script
        const localData = window.wpufAIFormBuilder || {};
        
        // Set initial data from localized script
        this.currentStage = localData.stage || 'input';
        this.formId = localData.formId || '';
        this.formTitle = localData.formTitle || 'Portfolio Submission';
        this.formDescription = localData.description || '';
        this.selectedPrompt = localData.prompt || '';
        
        // If we're in success stage, initialize data
        if (this.currentStage === 'success') {
            this.initializeChatData();
        }
    }
};
</script>
