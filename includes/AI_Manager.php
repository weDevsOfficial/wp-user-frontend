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
 * @since 4.2.1
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
        // Register REST API routes for AI form builder
        $this->rest_controller->register_routes();
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        // Safe fetch of AI settings
        $wpuf_ai = get_option('wpuf_ai', []);

        // Localize the data for Vue components to the main form builder script
        wp_localize_script('wpuf-form-builder-mixins', 'wpufAIFormBuilder', [
            'rest_url' => get_rest_url(null, '/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'provider' => $wpuf_ai['ai_provider'] ?? 'openai',
            'temperature' => $wpuf_ai['temperature'] ?? 0.7,
            'maxTokens' => $wpuf_ai['max_tokens'] ?? 2000,
            'assetUrl' => WPUF_ASSET_URI,
            'isProActive' => class_exists('WP_User_Frontend_Pro'),
            'strings' => [
                'generating' => __('Generating form...', 'wp-user-frontend'),
                'error' => __('Error occurred while generating form', 'wp-user-frontend'),
                'success' => __('Form generated successfully', 'wp-user-frontend'),
            ]
        ]);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Fetch AI settings once and cast to array to avoid "array offset on bool" notices
        $wpuf_ai = (array) get_option('wpuf_ai', []);

        // Get provider with fallback
        $provider = $wpuf_ai['ai_provider'] ?? 'openai';

        // Check if API key is available
        $hasApiKey = !empty($wpuf_ai['ai_api_key']);

        $localization_data = [
            'rest_url' => get_rest_url(null, '/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'provider' => $provider,
            'hasApiKey' => $hasApiKey,
            'isProActive' => class_exists('WP_User_Frontend_Pro'),
            'strings' => [
                'testConnection' => __('Test Connection', 'wp-user-frontend'),
                'connectionSuccess' => __('Connection successful', 'wp-user-frontend'),
                'connectionFailed' => __('Connection failed', 'wp-user-frontend'),
            ]
        ];

        // Localize to the main form builder script
        wp_localize_script('wpuf-form-builder-mixins', 'wpufAIFormBuilder', $localization_data);

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
        $settings = get_option('wpuf_ai', []);

        return [
            'provider'   => $settings['ai_provider'] ?? 'openai',
            'model'      => $settings['ai_model'] ?? 'gpt-3.5-turbo',
            'temperature'=> isset($settings['temperature']) ? floatval($settings['temperature']) : 0.7,
            'max_tokens' => isset($settings['max_tokens']) ? intval($settings['max_tokens']) : 2000,
            'has_api_key'=> !empty($settings['ai_api_key']),
        ];
    }

    /**
     * Check if AI functionality is available
     *
     * @return bool
     */
    public function is_ai_available() {
        $settings = $this->get_ai_settings();

        // All providers require API key
        return $settings['has_api_key'];
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
