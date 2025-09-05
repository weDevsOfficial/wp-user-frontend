<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\AI\RestController;
use WeDevs\Wpuf\AI\FormGenerator;

/**
 * AI Manager Class
 * 
 * Manages AI form builder functionality
 * Initializes REST API endpoints and handles AI client integration
 * 
 * @since 1.0.0
 */
class AI_Manager {

    /**
     * REST Controller instance
     *
     * @var RestController
     */
    private $rest_controller;

    /**
     * Form Generator instance
     *
     * @var FormGenerator
     */
    private $form_generator;


    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('rest_api_init', [$this, 'init_rest_api']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    /**
     * Initialize classes
     */
    private function init_classes() {
        // Initialize Form Generator
        $this->form_generator = new FormGenerator();
        
        // Initialize REST Controller
        $this->rest_controller = new RestController();
    }

    /**
     * Initialize REST API
     */
    public function init_rest_api() {
        // REST API is already initialized in RestController constructor
        // This hook ensures it runs at the right time
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        // Always localize the data for Vue components that might be loaded dynamically
        wp_localize_script('wpuf-form-builder-mixins', 'wpufAIFormBuilder', [
            'restUrl' => rest_url('/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'provider' => get_option('wpuf_ai_settings')['provider'] ?? 'predefined',
            'temperature' => get_option('wpuf_ai_settings')['temperature'] ?? 0.7,
            'maxTokens' => get_option('wpuf_ai_settings')['max_tokens'] ?? 2000,
            'assetUrl' => WPUF_ASSET_URI,
            'strings' => [
                'generating' => __('Generating form...', 'wp-user-frontend'),
                'error' => __('Error occurred while generating form', 'wp-user-frontend'),
                'success' => __('Form generated successfully', 'wp-user-frontend'),
            ]
        ]);
        
        // Also try to localize to any existing WPUF scripts that might be loaded
        $scripts_to_try = ['wpuf-form-builder-mixins', 'wpuf-admin-script', 'wpuf-main-script'];
        foreach ($scripts_to_try as $script_handle) {
            if (wp_script_is($script_handle, 'enqueued') || wp_script_is($script_handle, 'registered')) {
                wp_localize_script($script_handle, 'wpufAIFormBuilder', [
                    'restUrl' => rest_url('/'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'provider' => get_option('wpuf_ai_settings')['provider'] ?? 'predefined',
                    'temperature' => get_option('wpuf_ai_settings')['temperature'] ?? 0.7,
                    'maxTokens' => get_option('wpuf_ai_settings')['max_tokens'] ?? 2000,
                    'assetUrl' => WPUF_ASSET_URI,
                ]);
                break;
            }
        }
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Always localize for admin pages that might have Vue components
        $admin_scripts_to_try = ['wpuf-form-builder-mixins', 'wpuf-admin-script', 'wpuf-vue-admin'];
        
        $localization_data = [
            'restUrl' => rest_url('/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'provider' => get_option('wpuf_ai_settings')['provider'] ?? 'predefined',
            'hasApiKey' => !empty(get_option('wpuf_ai_settings')['api_key']),
            'strings' => [
                'testConnection' => __('Test Connection', 'wp-user-frontend'),
                'connectionSuccess' => __('Connection successful', 'wp-user-frontend'),
                'connectionFailed' => __('Connection failed', 'wp-user-frontend'),
            ]
        ];
        
        foreach ($admin_scripts_to_try as $script_handle) {
            if (wp_script_is($script_handle, 'enqueued') || wp_script_is($script_handle, 'registered')) {
                wp_localize_script($script_handle, 'wpufAIFormBuilder', $localization_data);
                break;
            }
        }
        
        // Also add a fallback by injecting directly into the page
        if ($this->is_ai_form_builder_admin_page($hook)) {
            add_action('admin_footer', function() use ($localization_data) {
                echo '<script type="text/javascript">';
                echo 'window.wpufAIFormBuilder = ' . wp_json_encode($localization_data) . ';';
                echo '</script>';
            });
        }
    }

    /**
     * Check if current page is AI form builder page
     *
     * @return bool
     */
    private function is_ai_form_builder_page() {
        // Check if we're on a page that uses AI form builder
        if (is_admin()) {
            return false;
        }

        // Add your conditions here for when AI form builder should be available
        // For example, specific pages or shortcodes
        return false; // Modify this based on your requirements
    }

    /**
     * Check if current admin page should load AI form builder assets
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function is_ai_form_builder_admin_page($hook) {
        // Check if we're on form builder admin pages
        $ai_pages = [
            'wpuf-post-forms', // Forms page
            'wpuf_page_wpuf-post-forms', // Forms page variations
            'toplevel_page_wpuf-post-forms',
        ];

        // Check by hook suffix
        if (in_array($hook, $ai_pages)) {
            return true;
        }

        // Check by page parameter
        if (isset($_GET['page']) && strpos($_GET['page'], 'wpuf') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get AI settings
     *
     * @return array
     */
    public function get_ai_settings() {
        return get_option('wpuf_ai_settings', [
            'provider' => 'predefined',
            'model' => 'predefined',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'api_key' => ''
        ]);
    }

    /**
     * Check if AI functionality is available
     *
     * @return bool
     */
    public function is_ai_available() {
        $settings = $this->get_ai_settings();
        
        // Predefined provider is always available
        if ($settings['provider'] === 'predefined') {
            return true;
        }

        // Other providers require API key
        return !empty($settings['api_key']);
    }

    /**
     * Get form generator instance
     *
     * @return FormGenerator
     */
    public function get_form_generator() {
        return $this->form_generator;
    }

    /**
     * Get REST controller instance
     *
     * @return RestController
     */
    public function get_rest_controller() {
        return $this->rest_controller;
    }
}