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
            formTitle: 'Portfolio Submission',
            formId: null,
            
            // Chat data
            chatMessages: [],
            
            // Form fields
            formFields: []
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
        },
        
        handleGenerationComplete() {
            this.currentStage = 'success';
            this.isGenerating = false;
            this.initializeChatData();
            this.initializeFormFields();
        },
        
        initializeChatData() {
            // Initialize with default chat messages or fetch from API
            this.chatMessages = [
                {
                    type: 'user',
                    content: this.formDescription || 'Create a contact form'
                },
                {
                    type: 'ai',
                    content: "I'll create a form for you. Processing your request..."
                },
                {
                    type: 'ai',
                    content: `Perfect! I've created a ${this.formTitle} form for you with the following fields:
                    <ul>
                        <li>First Name - Text input for personal identification</li>
                        <li>Email - Required field for communication</li>
                        <li>File Upload - For portfolio files (PDF, images)</li>
                        <li>Comment - Optional field for additional information</li>
                    </ul>
                    The form is ready and you can customize it further in the form builder!`,
                    showButtons: true,
                    status: 'Successfully created the form.'
                }
            ];
        },
        
        initializeFormFields() {
            // Initialize with default form fields or fetch from API
            this.formFields = [
                { id: 1, type: 'text', label: 'First Name', placeholder: 'Enter your first name' },
                { id: 2, type: 'email', label: 'Email', placeholder: 'Enter email address' },
                { id: 3, type: 'select', label: 'Select File Types', placeholder: 'Select File Types' },
                { id: 4, type: 'file', label: 'File Upload', placeholder: 'Only JPEG, PNG and PDF files' },
                { id: 5, type: 'textarea', label: 'Comment', placeholder: 'Write here your Comment' }
            ];
        },
        
        handleSendMessage(message) {
            // Handle sending message to AI backend
            console.log('Sending message:', message);
            // TODO: Implement API call
        },
        
        applyForm() {
            console.log('Form applied');
            // TODO: Implement form application logic
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
        
        // Set initial data from localized script
        this.currentStage = localData.stage || 'input';
        this.formId = localData.formId || '';
        this.formTitle = localData.formTitle || 'Portfolio Submission';
        this.formDescription = localData.description || '';
        this.selectedPrompt = localData.prompt || '';
        
        // If we're in success stage, initialize data
        if (this.currentStage === 'success') {
            this.initializeChatData();
            this.initializeFormFields();
        }
    }
};
</script>