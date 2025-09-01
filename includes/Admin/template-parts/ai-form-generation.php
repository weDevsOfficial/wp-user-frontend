<?php
/**
 * AI Form Generation Template
 * 
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<?php
// Include WordPress admin header
require_once ABSPATH . 'wp-admin/admin-header.php';
?>

<div class="wrap wpuf-ai-form-wrapper" id="wpuf-ai-form-generation">
    <div class="wpuf-ai-form-container">
        <div class="wpuf-ai-form-content wpuf-bg-white wpuf-rounded-lg wpuf-p-8">
            <!-- Header -->
            <div class="wpuf-text-center wpuf-mb-8">
                <h2 class="wpuf-text-3xl wpuf-font-semibold !wpuf-text-black wpuf-mb-2">
                    <?php esc_html_e( 'Create Form with AI', 'wp-user-frontend' ); ?>
                </h2>
                <p class="wpuf-text-lg wpuf-text-gray-500">
                    <?php esc_html_e( 'Automatically generate smart, customizable forms using AI.', 'wp-user-frontend' ); ?>
                </p>
            </div>

            <!-- Form Description -->
            <div class="wpuf-mb-8">
                <div class="wpuf-relative">
                    <textarea 
                        v-model="formDescription"
                        class="wpuf-w-full wpuf-px-4 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-text-gray-500 wpuf-resize-none focus:wpuf-outline-none focus:wpuf-border-emerald-500 focus:wpuf-ring-2 focus:wpuf-ring-emerald-200 wpuf-transition-all"
                        rows="8"
                        maxlength="500"
                        placeholder="<?php esc_attr_e( 'Describe your form', 'wp-user-frontend' ); ?>"
                    ></textarea>
                </div>
                <div class="wpuf-text-right wpuf-mt-2 wpuf-text-sm wpuf-text-gray-600">
                    {{ formDescription.length }}/500 <?php esc_html_e( 'Characters', 'wp-user-frontend' ); ?>
                </div>
            </div>

            <!-- Prompt Templates -->
            <div class="wpuf-mb-12">
                <p class="wpuf-text-gray-900 wpuf-mb-4 wpuf-text-lg">
                    <?php esc_html_e( 'Or create using our Prompts:', 'wp-user-frontend' ); ?>
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
                    <?php esc_html_e( 'Back', 'wp-user-frontend' ); ?>
                </button>
                <button 
                    @click="generateForm"
                    :disabled="!formDescription.trim() || isGenerating"
                    class="wpuf-px-8 wpuf-py-4 wpuf-bg-emerald-600 hover:wpuf-bg-emerald-700 wpuf-text-white wpuf-rounded-lg wpuf-transition-colors wpuf-flex wpuf-items-center wpuf-gap-2 disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed"
                >
                    <span v-if="!isGenerating" class="wpuf-text-base wpuf-leading-6"><?php esc_html_e( 'Generate Form', 'wp-user-frontend' ); ?></span>
                    <span v-else class="wpuf-font-medium wpuf-text-base wpuf-leading-6"><?php esc_html_e( 'Generating...', 'wp-user-frontend' ); ?></span>
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
</div>

<style>
.wpuf-ai-form-wrapper {
    position: fixed !important;
    top: 32px !important; 
    left: 160px !important;
    right: 0 !important;
    bottom: 0 !important;
    background: white !important;
    overflow: auto !important;
}

@media (max-width: 782px) {
    .wpuf-ai-form-wrapper {
        left: 36px!important;
    }
}

@media (max-width: 600px) {
    .wpuf-ai-form-wrapper {
        left: 0 !important;
        top: 46px !important;
    }
}

.wpuf-ai-form-container {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.wpuf-ai-form-content {
    width: 100%;
    max-width: 896px;
    padding: 48px;
    background: white;
    border-radius: 8px;
}

.wpuf-prompt-btn-active {
    background-color: #059669;
    color: white;
    border-color: #059669;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.wpuf-animate-spin {
    animation: spin 1s linear infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Vue === 'undefined') {
        return;
    }
    
    new Vue({
        el: '#wpuf-ai-form-generation',
    
    data: {
        formDescription: '',
        selectedPrompt: '',
        isGenerating: false,
        currentStep: 0,
        promptTemplates: [
            'Paid Guest Post',
            'Portfolio Submission', 
            'Classified Ads',
            'Coupon Submission',
            'Real Estate Property Listing',
            'News/Press Release Submission',
            'Product Listing'
        ]
    },
    
    methods: {
        selectPrompt: function(template) {
            this.selectedPrompt = template;
            this.formDescription = 'Create a form for ' + template;
        },
        
        goBack: function() {
            window.history.back();
        },
        
        generateForm: function() {
            var self = this;
            
            if (!this.formDescription.trim()) {
                alert('<?php echo esc_js( __( 'Please describe your form or select a prompt template.', 'wp-user-frontend' ) ); ?>');
                return;
            }
            
            // Prepare the form data
            var formData = {
                description: this.formDescription,
                prompt: this.selectedPrompt,
                _wpnonce: '<?php echo wp_create_nonce( 'wpuf_ai_generate_form' ); ?>'
            };
            
            // Redirect to the generation page with the form data
            var params = new URLSearchParams(formData);
            window.location.href = 'admin.php?action=wpuf_ai_form_generating&' + params.toString();
        }
    }
    });
});
</script>

<?php
// Include WordPress admin footer
require_once ABSPATH . 'wp-admin/admin-footer.php';
?>