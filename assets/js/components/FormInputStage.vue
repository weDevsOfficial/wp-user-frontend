<template>
    <div class="wpuf-ai-form-wrapper wpuf-font-sans wpuf-w-full wpuf-h-screen wpuf-overflow-hidden wpuf-relative" style="background-color: white;">
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
                        class="wpuf-w-full wpuf-px-4 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-lg wpuf-text-gray-500 wpuf-resize-none focus:wpuf-outline-none focus:wpuf-border-emerald-700 focus:wpuf-ring-2 focus:wpuf-ring-emerald-200 wpuf-transition-all"
                        rows="6"
                        :maxlength="maxDescriptionLength"
                        :placeholder="__('Describe your form', 'wp-user-frontend')"
                    ></textarea>
                </div>
                <div class="wpuf-text-right wpuf-mt-2 wpuf-text-sm wpuf-text-gray-600">
                    {{ formDescription.length }}/{{ maxDescriptionLength }} {{ __('Characters', 'wp-user-frontend') }}
                </div>
            </div>

            <!-- Integration Selector (For Post and Registration Forms) -->
            <div v-if="(availableIntegrations.length > 0 || loadingIntegrations)" class="wpuf-mb-6">
                <label class="wpuf-block wpuf-text-gray-900 wpuf-mb-2 wpuf-text-[16px] wpuf-font-medium">
                    {{ __('Form Type (Optional)', 'wp-user-frontend') }}
                </label>
                <select
                    v-model="selectedIntegration"
                    :disabled="loadingIntegrations"
                    class="wpuf-block !wpuf-w-full wpuf-max-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-normal !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-emerald-500 focus:!wpuf-border-emerald-500 hover:wpuf-border-gray-400 !wpuf-text-base !wpuf-leading-6 wpuf-bg-white wpuf-transition-all disabled:wpuf-opacity-50 disabled:wpuf-cursor-not-allowed"
                    style="width: 100% !important; max-width: 100% !important;"
                    @change="onIntegrationChange"
                >
                    <option value="" v-if="loadingIntegrations">{{ __('Loading integrations...', 'wp-user-frontend') }}</option>
                    <option value="" v-else>{{ __('Regular Form (No Integration)', 'wp-user-frontend') }}</option>
                    <option
                        v-for="integration in availableIntegrations"
                        :key="integration.id"
                        :value="integration.id"
                    >
                        {{ integration.label }}
                    </option>
                </select>
                <p class="wpuf-text-sm wpuf-text-gray-500 wpuf-mt-2">
                    <span v-if="loadingIntegrations" class="wpuf-inline-flex wpuf-items-center wpuf-gap-2">
                        <svg class="wpuf-animate-spin wpuf-h-4 wpuf-w-4 wpuf-text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="wpuf-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="wpuf-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Loading available integrations...', 'wp-user-frontend') }}
                    </span>
                    <span v-else>{{ __('Choose a form type if you want to create a form for a specific integration', 'wp-user-frontend') }}</span>
                </p>
            </div>

            <!-- Prompt Templates -->
            <div class="wpuf-mb-6">
                <p class="wpuf-text-gray-900 wpuf-mb-4 wpuf-text-[16px]">
                    {{ __('Or create using our Prompts:', 'wp-user-frontend') }}
                </p>
                <div class="wpuf-flex wpuf-flex-wrap wpuf-gap-4">
                    <button
                        v-for="tpl in promptTemplates"
                        :key="tpl.id"
                        @click="selectPrompt(tpl)"
                        :class="selectedPrompt?.id === tpl.id ? 'wpuf-prompt-btn-active wpuf-bg-emerald-600 wpuf-text-white wpuf-border-emerald-600 hover:wpuf-text-emerald-200 wpuf-px-4 wpuf-py-2 wpuf-rounded-md wpuf-transition-all wpuf-text-sm wpuf-font-medium' : 'wpuf-px-4 wpuf-py-2 wpuf-border wpuf-border-gray-200 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 hover:wpuf-border-emerald-600 hover:wpuf-text-emerald-700 wpuf-transition-all wpuf-text-sm wpuf-font-medium'"
                    >
                        {{ tpl.label }}
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
        initialIntegration: {
            type: String,
            default: ''
        },
        initialSelectedPrompt: {
            type: [String, Object],
            default: ''
        },
        generating: {
            type: Boolean,
            default: false
        }
    },
    data() {
        // Get form type from wpufAIFormBuilder.formType (localized from PHP)
        const formType = (window.wpufAIFormBuilder && window.wpufAIFormBuilder.formType) || 'post';

        // Define prompts based on form type
        let promptTemplates = [];
        let promptAIInstructions = {};

        if (formType === 'profile' || formType === 'registration') {
            // Registration/Profile form prompts - will be populated by updatePromptTemplates() in mounted()
            // based on whether integrations are available
            promptTemplates = [];
            promptAIInstructions = {};
        } else {
            // Post form prompts - will be populated by updatePromptTemplates() in mounted()
            promptTemplates = [];
            promptAIInstructions = {};
        }

        // Handle selectedPrompt initialization
        let selectedPrompt = '';
        if (typeof this.initialSelectedPrompt === 'object' && this.initialSelectedPrompt !== null) {
            // Already an object, keep it as-is
            selectedPrompt = this.initialSelectedPrompt;
        } else if (typeof this.initialSelectedPrompt === 'string' && this.initialSelectedPrompt !== '') {
            // It's a string ID, look up the corresponding template object
            const foundTemplate = promptTemplates.find(tpl => tpl.id === this.initialSelectedPrompt);
            selectedPrompt = foundTemplate || '';
        }

        return {
            formDescription: this.initialDescription,
            selectedPrompt: selectedPrompt,
            isGenerating: this.generating,
            maxDescriptionLength: 300,
            formType: formType,
            promptTemplates: promptTemplates,
            promptAIInstructions: promptAIInstructions,
            selectedIntegration: this.initialIntegration || '',
            availableIntegrations: [],
            loadingIntegrations: false
        };
    },

    mounted() {
        // Fetch available integrations when component mounts
        this.fetchAvailableIntegrations();
        // Emit initial integration to parent if provided
        if (this.initialIntegration) {
            this.$emit('update:selectedIntegration', this.selectedIntegration);
        }
        // Initialize prompt templates based on integration
        this.updatePromptTemplates();
        
        // Rehydrate selectedPrompt from initialSelectedPrompt after promptTemplates are populated
        // This handles the case where initialSelectedPrompt is a string ID that needs to be resolved
        // after templates exist (initialSelectedPrompt, selectedPrompt, promptTemplates)
        if (typeof this.initialSelectedPrompt === 'string' && this.initialSelectedPrompt !== '' && !this.selectedPrompt) {
            const foundTemplate = this.promptTemplates.find(tpl => tpl.id === this.initialSelectedPrompt);
            if (foundTemplate) {
                this.selectedPrompt = foundTemplate;
                this.$emit('update:selectedPrompt', this.selectedPrompt.id);
            }
        }
    },
    watch: {
        generating(newVal) {
            this.isGenerating = newVal;
        },

        formDescription(newVal, oldVal) {
            // If user manually edits the description, deselect any selected prompt
            if (this.selectedPrompt && oldVal && newVal !== oldVal) {
                // Check if the new value matches any of our predefined prompts
                const matchesPrompt = Object.values(this.promptAIInstructions).includes(newVal);

                // If it doesn't match any prompt, deselect the current prompt
                if (!matchesPrompt) {
                    this.selectedPrompt = '';
                    this.$emit('update:selectedPrompt', '');
                }
            }
        }
    },
    methods: {
        __: window.__ || ((text) => text),

        async fetchAvailableIntegrations() {
            this.loadingIntegrations = true;

            try {
                const config = window.wpufAIFormBuilder || {};
                const restUrl = config.rest_url || (window.location.origin + '/wp-json/');
                const nonce = config.nonce || '';

                const response = await fetch(restUrl + 'wpuf/v1/ai-form-builder/integrations?form_type=' + this.formType, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.integrations) {
                        this.availableIntegrations = result.integrations;
                    }
                }
            } catch (error) {
                console.error('Error fetching integrations:', error);
            } finally {
                this.loadingIntegrations = false;
            }
        },

        onIntegrationChange() {
            // Update prompt templates based on selected integration
            this.updatePromptTemplates();
            
            // Clear current prompt selection when integration changes
            this.selectedPrompt = '';
            this.formDescription = '';
            
            // Emit the selected integration to parent
            this.$emit('update:selectedIntegration', this.selectedIntegration);
            this.$emit('update:selectedPrompt', '');
            this.$emit('update:formDescription', '');
        },

        updatePromptTemplates() {
            // Handle registration/profile form integrations
            if (this.formType === 'profile' || this.formType === 'registration') {
                if (this.selectedIntegration === 'dokan') {
                    // Dokan vendor registration prompts
                    this.promptTemplates = [
                        { id: 'dokan_basic', label: this.__('Basic Vendor Registration', 'wp-user-frontend') },
                        { id: 'dokan_complete', label: this.__('Complete Vendor Profile', 'wp-user-frontend') },
                        { id: 'dokan_marketplace', label: this.__('Marketplace Seller', 'wp-user-frontend') },
                        { id: 'dokan_simple', label: this.__('Simple Vendor Signup', 'wp-user-frontend') }
                    ];

                    this.promptAIInstructions = {
                        dokan_basic: 'Create a Dokan basic vendor registration form with first name, last name, email, shop name, password',
                        dokan_complete: 'Create a Dokan complete vendor registration form with first name, last name, email, shop name, shop URL, profile picture, shop banner, phone number, address, store location map, password',
                        dokan_marketplace: 'Create a Dokan marketplace seller registration form with first name, last name, email, shop name, shop URL, store description, profile picture, shop banner, phone number, PayPal email, password',
                        dokan_simple: 'Create a Dokan simple vendor signup form with email, shop name, password'
                    };
                } else if (this.selectedIntegration === 'wc_vendors') {
                    // WC Vendors registration prompts
                    this.promptTemplates = [
                        { id: 'wcv_basic', label: this.__('Basic Vendor Registration', 'wp-user-frontend') },
                        { id: 'wcv_complete', label: this.__('Complete Vendor Profile', 'wp-user-frontend') },
                        { id: 'wcv_seller', label: this.__('Seller Application', 'wp-user-frontend') },
                        { id: 'wcv_simple', label: this.__('Simple Vendor Signup', 'wp-user-frontend') }
                    ];

                    this.promptAIInstructions = {
                        wcv_basic: 'Create a WC Vendors basic registration form with email, PayPal address, shop name, password',
                        wcv_complete: 'Create a WC Vendors complete registration form with email, first name, last name, PayPal address, shop name, seller info, shop description, shop logo, password',
                        wcv_seller: 'Create a WC Vendors seller application form with email, first name, last name, PayPal address, shop name, seller info, shop description, password',
                        wcv_simple: 'Create a WC Vendors simple vendor signup form with email, shop name, PayPal address, password'
                    };
                } else if (this.selectedIntegration === 'wcfm') {
                    // WCFM Membership registration prompts
                    this.promptTemplates = [
                        { id: 'wcfm_basic', label: this.__('Basic Vendor Registration', 'wp-user-frontend') },
                        { id: 'wcfm_complete', label: this.__('Complete Vendor Profile', 'wp-user-frontend') },
                        { id: 'wcfm_marketplace', label: this.__('Marketplace Vendor', 'wp-user-frontend') },
                        { id: 'wcfm_multistep', label: this.__('Multi-Step Registration', 'wp-user-frontend') }
                    ];

                    this.promptAIInstructions = {
                        wcfm_basic: 'Create a WCFM basic vendor registration form with email, store name (username), password',
                        wcfm_complete: 'Create a WCFM complete vendor registration form with email, store name, first name, last name, phone number, store logo, store banner, store description, store address, social links (Facebook, Twitter, Instagram, LinkedIn), password',
                        wcfm_marketplace: 'Create a WCFM marketplace vendor registration form with email, store name, first name, last name, phone, store logo, store description, address, password',
                        wcfm_multistep: 'Create a WCFM multi-step vendor registration form with section breaks: Step 1 (email, store name, password), Step 2 (store logo, store banner, store description), Step 3 (phone, address, social links)'
                    };
                } else {
                    // Default registration prompts (no integration)
                    this.promptTemplates = [
                        { id: 'basic_registration', label: this.__('Basic User Registration', 'wp-user-frontend') },
                        { id: 'member_directory', label: this.__('Member Directory Profile', 'wp-user-frontend') },
                        { id: 'job_applicant', label: this.__('Job Applicant Registration', 'wp-user-frontend') },
                        { id: 'blog_author_signup', label: this.__('Blog Author Signup', 'wp-user-frontend') },
                        { id: 'community_member_join', label: this.__('Community Member Join', 'wp-user-frontend') },
                        { id: 'freelancer_profile_signup', label: this.__('Freelancer Profile Signup', 'wp-user-frontend') }
                    ];

                    this.promptAIInstructions = {
                        basic_registration: 'Create a Basic User Registration form with email, name, username, password',
                        member_directory: 'Create a Member Directory Profile form with name, email, bio, profile photo',
                        job_applicant: 'Create a Job Applicant Registration form with name, email, phone, resume upload',
                        blog_author_signup: 'Create a registration form for new blog authors. Collect their login details, public display information, a short introduction about themselves, a profile photo or avatar, and an optional personal website link.',
                        community_member_join: 'Create a registration form for new community members. Collect their basic personal details, login information, a public name, a nickname, their interests (as checkboxes), a short personal introduction, and a profile picture',
                        freelancer_profile_signup: 'Create a registration form for freelancers that captures their professional details, skills, experience summary, portfolio information, and profile photo.'
                    };
                }
                return;
            }

            // Handle post form integrations
            if (this.selectedIntegration === 'woocommerce') {
                // WooCommerce-specific prompts
                this.promptTemplates = [
                    { id: 'woo_simple_product', label: this.__('Simple Product', 'wp-user-frontend') },
                    { id: 'woo_digital_product', label: this.__('Digital Product', 'wp-user-frontend') },
                    { id: 'woo_service_listing', label: this.__('Service Listing', 'wp-user-frontend') },
                    { id: 'woo_handmade_product', label: this.__('Handmade Product', 'wp-user-frontend') }
                ];

                this.promptAIInstructions = {
                    woo_simple_product: 'Create a WooCommerce Simple Product form with product title, product description, short description, regular price, sale price, featured image, product gallery, catalog visibility',
                    woo_digital_product: 'Create a WooCommerce Digital Product form with product title, product description, regular price, featured image, SKU, Downloadable Product, Downloadable Files',
                    woo_service_listing: 'Create a WooCommerce Service form with product title, product description, regular price, featured image, purchase note',
                    woo_handmade_product: 'Create a WooCommerce Handmade Product form with product title, product description, short description, regular price, featured image, product gallery, product attributes for size and color, weight, dimensions'
                };
            } else if (this.selectedIntegration === 'edd') {
                // Easy Digital Downloads prompts
                this.promptTemplates = [
                    { id: 'edd_digital_download', label: this.__('Digital Download', 'wp-user-frontend') },
                    { id: 'edd_software', label: this.__('Software Product', 'wp-user-frontend') },
                    { id: 'edd_ebook', label: this.__('eBook', 'wp-user-frontend') },
                    { id: 'edd_course', label: this.__('Online Course', 'wp-user-frontend') }
                ];

                this.promptAIInstructions = {
                    edd_digital_download: 'Create an EDD Digital Download form with download title, download description, short description, price, downloadable files, featured image, download categories',
                    edd_software: 'Create an EDD Software Product form with download title, download description, price, downloadable files, product notes, featured image, download categories',
                    edd_ebook: 'Create an EDD eBook form with download title, download description, short description, price, downloadable files, featured image, download categories',
                    edd_course: 'Create an EDD Online Course form with download title, download description, price, downloadable files, product notes, featured image, download categories'
                };
            } else if (this.selectedIntegration === 'events_calendar') {
                // The Events Calendar prompts
                this.promptTemplates = [
                    { id: 'event_conference', label: this.__('Conference Event', 'wp-user-frontend') },
                    { id: 'event_workshop', label: this.__('Workshop/Training', 'wp-user-frontend') },
                    { id: 'event_meetup', label: this.__('Meetup/Networking', 'wp-user-frontend') },
                    { id: 'event_webinar', label: this.__('Webinar', 'wp-user-frontend') }
                ];

                this.promptAIInstructions = {
                    event_conference: 'Create a Conference Event form with event title, event details, event start date, event end date, venue name, event address, event city, event cost, currency symbol, featured image, event categories',
                    event_workshop: 'Create a Workshop/Training Event form with event title, event details, event start date, event end date, venue name, event address, event cost, featured image, event categories',
                    event_meetup: 'Create a Meetup/Networking Event form with event title, event details, short description, event start date, event end date, venue name, event address, event website, featured image, event categories',
                    event_webinar: 'Create a Webinar Event form with event title, event details, event start date, event end date, event website, event cost, currency symbol, featured image, event categories'
                };
            } else {
                // Default post form prompts (no integration selected)
                this.promptTemplates = [
                    { id: 'paid_guest_post', label: this.__('Paid Guest Post', 'wp-user-frontend') },
                    { id: 'portfolio_submission', label: this.__('Portfolio Submission', 'wp-user-frontend') },
                    { id: 'classified_ads', label: this.__('Classified Ads', 'wp-user-frontend') },
                    { id: 'coupon_submission', label: this.__('Coupon Submission', 'wp-user-frontend') },
                    { id: 'real_estate', label: this.__('Real Estate Property Listing', 'wp-user-frontend') },
                    { id: 'news_press', label: this.__('News/Press Release Submission', 'wp-user-frontend') },
                ];

                this.promptAIInstructions = {
                    paid_guest_post: 'Create a Paid Guest Post submission form with title, content, author name, email, category',
                    portfolio_submission: 'Create a Portfolio Submission form with title, description, name, email, skills, portfolio files',
                    classified_ads: 'Create a Classified Ads submission form with title, description, category, price, address field, contact email',
                    coupon_submission: 'Create a Coupon Submission form with title, description, business name, discount amount, expiration date',
                    real_estate: 'Create a Real Estate Property Listing form with title, description, address field, price, bedrooms, bathrooms, images',
                    news_press: 'Create a News/Press Release submission form with headline, content, author, contact email, category'
                };
            }
        },

        selectPrompt(tpl) {
            this.selectedPrompt = tpl;
            // Use the detailed AI instruction instead of just the template name
            const aiInstruction = this.promptAIInstructions[tpl.id] || tpl.label;

            // Enforce UI max length
            if (aiInstruction.length > this.maxDescriptionLength) {
                this.formDescription = aiInstruction.substring(0, this.maxDescriptionLength);
            } else {
                this.formDescription = aiInstruction;
            }

            this.$emit('update:selectedPrompt', tpl.id);
            this.$emit('update:formDescription', this.formDescription);
        },

        goBack() {
            this.$emit('go-back');
        },

        startGeneration() {
            if (!this.formDescription.trim() || this.isGenerating) return;

            this.$emit('start-generation', {
                description: this.formDescription,
                selectedPrompt: this.selectedPrompt?.id || this.selectedPrompt || '',
                integration: this.selectedIntegration
            });
        }
    }
};
</script>

<style scoped>
textarea:focus {
    outline: none !important;
    border-color: #059669 !important;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1) !important;
}
</style>
