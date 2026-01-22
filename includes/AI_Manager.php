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
        add_action( 'rest_api_init', [ $this, 'init_rest_api' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
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

        // Determine form type based on current admin page
        $form_type = 'post'; // Default to post
        if ( isset( $_GET['page'] ) ) {
            $page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
            if ( 'wpuf-profile-forms' === $page ) {
                $form_type = 'profile';
            } elseif ( 'wpuf-post-forms' === $page ) {
                $form_type = 'post';
            }
        }

        $localization_data = [
            'rest_url'              => get_rest_url( null, '/' ),
            'nonce'                 => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
            'provider'              => $provider,
            'hasApiKey'             => $hasApiKey,
            'formType'              => $form_type,
            'isProActive'           => class_exists( 'WP_User_Frontend_Pro' ),
            'promptTemplates'       => $this->get_all_prompt_templates(),
            'promptAIInstructions'  => $this->get_all_prompt_ai_instructions(),
            'strings'               => [
                'testConnection'    => __( 'Test Connection', 'wp-user-frontend' ),
                'connectionSuccess' => __( 'Connection successful', 'wp-user-frontend' ),
                'connectionFailed'  => __( 'Connection failed', 'wp-user-frontend' ),
            ],
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
        if ( isset( $_GET['page'] ) ) {
            $page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
            if ( strpos( $page, 'wpuf' ) !== false ) {
                return true;
            }
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

    /**
     * Get prompt templates for AI form builder
     *
     * Returns templates organized by form type and integration.
     * Pro plugin can extend this via the wpuf_ai_prompt_templates filter.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type   Form type ('post' or 'profile')
     * @param string $integration Integration identifier (empty for no integration)
     *
     * @return array Templates array with id and label
     */
    public function get_prompt_templates( $form_type = 'post', $integration = '' ) {
        $templates = [];

        // Free plugin provides post form templates
        if ( 'post' === $form_type ) {
            if ( empty( $integration ) ) {
                // Regular post form templates (no integration)
                $templates = [
                    [
                        'id'    => 'paid_guest_post',
                        'label' => __( 'Paid Guest Post', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'portfolio_submission',
                        'label' => __( 'Portfolio Submission', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'classified_ads',
                        'label' => __( 'Classified Ads', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'coupon_submission',
                        'label' => __( 'Coupon Submission', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'real_estate',
                        'label' => __( 'Real Estate Property Listing', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'news_press',
                        'label' => __( 'News/Press Release Submission', 'wp-user-frontend' ),
                    ],
                ];
            } elseif ( 'woocommerce' === $integration ) {
                // WooCommerce product form templates
                $templates = [
                    [
                        'id'    => 'woo_simple_product',
                        'label' => __( 'Simple Product', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'woo_digital_product',
                        'label' => __( 'Digital Product', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'woo_service_listing',
                        'label' => __( 'Service Listing', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'woo_handmade_product',
                        'label' => __( 'Handmade Product', 'wp-user-frontend' ),
                    ],
                ];
            } elseif ( 'events_calendar' === $integration ) {
                // Events Calendar form templates
                $templates = [
                    [
                        'id'    => 'event_conference',
                        'label' => __( 'Conference Event', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'event_workshop',
                        'label' => __( 'Workshop/Training', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'event_meetup',
                        'label' => __( 'Meetup/Networking', 'wp-user-frontend' ),
                    ],
                    [
                        'id'    => 'event_webinar',
                        'label' => __( 'Webinar', 'wp-user-frontend' ),
                    ],
                ];
            }
        }

        /**
         * Filter prompt templates for AI form builder
         *
         * Allows pro plugin to add additional templates based on form type and integration.
         *
         * @since WPUF_SINCE
         *
         * @param array  $templates   Array of template objects with 'id' and 'label' keys
         * @param string $form_type   Form type ('post' or 'profile')
         * @param string $integration Integration identifier (empty for no integration)
         */
        return apply_filters( 'wpuf_ai_prompt_templates', $templates, $form_type, $integration );
    }

    /**
     * Get AI instructions for prompt templates
     *
     * Returns AI instructions mapped by template ID.
     * Pro plugin can extend this via the wpuf_ai_prompt_instructions filter.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type   Form type ('post' or 'profile')
     * @param string $integration Integration identifier (empty for no integration)
     *
     * @return array Instructions array keyed by template ID
     */
    public function get_prompt_ai_instructions( $form_type = 'post', $integration = '' ) {
        $instructions = [];

        // Free plugin provides post form instructions
        if ( 'post' === $form_type ) {
            if ( empty( $integration ) ) {
                // Regular post form instructions (no integration)
                $instructions = [
                    'paid_guest_post'      => __( 'Create a Paid Guest Post submission form with title, content, author name, email, category', 'wp-user-frontend' ),
                    'portfolio_submission' => __( 'Create a Portfolio Submission form with title, description, name, email, skills, portfolio files', 'wp-user-frontend' ),
                    'classified_ads'       => __( 'Create a Classified Ads submission form with title, description, category, price, address field, contact email', 'wp-user-frontend' ),
                    'coupon_submission'    => __( 'Create a Coupon Submission form with title, description, business name, discount amount, expiration date', 'wp-user-frontend' ),
                    'real_estate'          => __( 'Create a Real Estate Property Listing form with title, description, address field, price, bedrooms, bathrooms, images', 'wp-user-frontend' ),
                    'news_press'           => __( 'Create a News/Press Release submission form with headline, content, author, contact email, category', 'wp-user-frontend' ),
                ];
            } elseif ( 'woocommerce' === $integration ) {
                // WooCommerce product form instructions
                $instructions = [
                    'woo_simple_product'   => __( 'Create a Simple Product submission form with product name, description, regular price, sale price, product image, gallery, category', 'wp-user-frontend' ),
                    'woo_digital_product'  => __( 'Create a Digital Product submission form with product name, description, price, downloadable file, product image', 'wp-user-frontend' ),
                    'woo_service_listing'  => __( 'Create a Service Listing form with service name, description, pricing, duration, availability, featured image', 'wp-user-frontend' ),
                    'woo_handmade_product' => __( 'Create a Handmade Product form with product name, description, materials, price, images, customization options', 'wp-user-frontend' ),
                ];
            } elseif ( 'events_calendar' === $integration ) {
                // Events Calendar form instructions
                $instructions = [
                    'event_conference' => __( 'Create a Conference Event form with event title, description, start date, end date, venue, speakers, registration link', 'wp-user-frontend' ),
                    'event_workshop'   => __( 'Create a Workshop/Training form with title, description, date, time, location, instructor, capacity, price', 'wp-user-frontend' ),
                    'event_meetup'     => __( 'Create a Meetup/Networking event form with title, description, date, time, venue, event image, RSVP link', 'wp-user-frontend' ),
                    'event_webinar'    => __( 'Create a Webinar form with title, description, date, time, host name, registration URL, featured image', 'wp-user-frontend' ),
                ];
            }
        }

        /**
         * Filter AI instructions for prompt templates
         *
         * Allows pro plugin to add additional instructions based on form type and integration.
         *
         * @since WPUF_SINCE
         *
         * @param array  $instructions Array of instructions keyed by template ID
         * @param string $form_type    Form type ('post' or 'profile')
         * @param string $integration  Integration identifier (empty for no integration)
         */
        return apply_filters( 'wpuf_ai_prompt_instructions', $instructions, $form_type, $integration );
    }

    /**
     * Get all prompt templates organized by form type and integration
     *
     * Returns a complete structure for JavaScript localization.
     *
     * @since WPUF_SINCE
     *
     * @return array Complete templates structure
     */
    public function get_all_prompt_templates() {
        $form_types   = [ 'post', 'profile' ];
        $integrations = [ '', 'woocommerce', 'edd', 'events_calendar', 'dokan', 'wc_vendors', 'wcfm' ];
        $all_templates = [];

        foreach ( $form_types as $form_type ) {
            $all_templates[ $form_type ] = [];

            foreach ( $integrations as $integration ) {
                $templates = $this->get_prompt_templates( $form_type, $integration );

                // Only add if templates exist for this combination
                if ( ! empty( $templates ) ) {
                    $all_templates[ $form_type ][ $integration ] = $templates;
                }
            }

            // Ensure at least an empty integration key exists
            if ( ! isset( $all_templates[ $form_type ][''] ) ) {
                $all_templates[ $form_type ][''] = [];
            }
        }

        return $all_templates;
    }

    /**
     * Get all AI instructions organized by template ID
     *
     * Returns a flat structure for JavaScript localization.
     *
     * @since WPUF_SINCE
     *
     * @return array Complete instructions keyed by template ID
     */
    public function get_all_prompt_ai_instructions() {
        $form_types       = [ 'post', 'profile' ];
        $integrations     = [ '', 'woocommerce', 'edd', 'events_calendar', 'dokan', 'wc_vendors', 'wcfm' ];
        $all_instructions = [];

        foreach ( $form_types as $form_type ) {
            foreach ( $integrations as $integration ) {
                $instructions = $this->get_prompt_ai_instructions( $form_type, $integration );

                // Merge instructions into flat structure
                if ( ! empty( $instructions ) ) {
                    $all_instructions = array_merge( $all_instructions, $instructions );
                }
            }
        }

        return $all_instructions;
    }
}
