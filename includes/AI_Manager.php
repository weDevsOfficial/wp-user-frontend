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
        // The REST routes register through the platform (Platform\Providers\AiServiceProvider, REST\Manager).
        // The React AI form builder screen localizes its own `wpufAIFormBuilder`
        // (Admin\Screens\AiFormBuilder); the Vue builder's copies are gone.
    }

    /**
     * Initialize classes
     */
    private function init_classes() {
        // Initialize Form Generator
        $this->form_generator = new FormGenerator();
    }

    /**
     * Initialize REST API
     *
     * @since WPUF_SINCE No longer hooked: REST\Manager registers the same
     *                   controller (AiServiceProvider). Kept for direct callers.
     */
    public function init_rest_api() {
        // Register REST API routes for AI form builder
        $this->get_rest_controller()->register_routes();
    }

    /**
     * Localized `wpufAIFormBuilder` onto the Vue builder's script on every
     * frontend page. Nothing reads it there; no longer hooked, kept for callers.
     *
     * @since 4.2.1
     * @since WPUF_SINCE Does nothing.
     *
     * @return void
     */
    public function enqueue_scripts() {}

    /**
     * Localized `wpufAIFormBuilder` for the Vue form builder. The React AI form
     * builder screen localizes its own copy; no longer hooked, kept for callers.
     *
     * @since 4.2.1
     * @since WPUF_SINCE Does nothing.
     *
     * @param string $hook Admin page hook.
     *
     * @return void
     */
    public function enqueue_admin_scripts( $hook = '' ) {}

    /**
     * Get AI settings
     *
     * @return array
     */
    public function get_ai_settings() {
        $status = wpuf()->platform()->get( \WeDevs\Wpuf\AI\Services\Provider_Settings::class )->status();

        return [
            'provider'    => '' !== $status['provider'] ? $status['provider'] : 'openai',
            'model'       => '' !== $status['model'] ? $status['model'] : 'gpt-3.5-turbo',
            'temperature' => $status['temperature'],
            'max_tokens'  => $status['max_tokens'],
            'has_api_key' => $status['has_api_key'],
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
        // The platform builds it with its services (AiServiceProvider).
        return wpuf()->platform()->get( RestController::class );
    }

    /**
     * Get prompt templates for AI form builder
     *
     * Returns templates organized by form type and integration.
     * Pro plugin can extend this via the wpuf_ai_prompt_templates filter.
     *
     * @since 4.2.9
     *
     * @param string $form_type   Form type ('post' or 'profile')
     * @param string $integration Integration identifier (empty for no integration)
     *
     * @return array Templates array with id and label
     */
    public function get_prompt_templates( $form_type = 'post', $integration = '' ) {
        $templates = [];

        // Free plugin provides post form templates
        if ( 'profile' === $form_type && empty( $integration ) ) {
            // Default registration/profile form templates (no integration)
            $templates = [
                [
                    'id'    => 'basic_registration',
                    'label' => __( 'Basic User Registration', 'wp-user-frontend' ),
                ],
                [
                    'id'    => 'member_directory',
                    'label' => __( 'Member Directory Profile', 'wp-user-frontend' ),
                ],
                [
                    'id'    => 'job_applicant',
                    'label' => __( 'Job Applicant Registration', 'wp-user-frontend' ),
                ],
                [
                    'id'    => 'blog_author_signup',
                    'label' => __( 'Blog Author Signup', 'wp-user-frontend' ),
                ],
                [
                    'id'    => 'community_member_join',
                    'label' => __( 'Community Member Join', 'wp-user-frontend' ),
                ],
                [
                    'id'    => 'freelancer_profile_signup',
                    'label' => __( 'Freelancer Profile Signup', 'wp-user-frontend' ),
                ],
            ];
        }

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
         * @since 4.2.9
         *
         * @param array  $templates   Array of template objects with 'id' and 'label' keys
         * @param string $form_type   Form type ('post' or 'profile')
         * @param string $integration Integration identifier (empty for no integration)
         */
        $templates = apply_filters( 'wpuf_ai_prompt_templates', $templates, $form_type, $integration );

        return $this->unique_prompt_templates( $templates );
    }

    /**
     * Drop prompt templates whose id is already listed
     *
     * The free plugin and the wpuf_ai_prompt_templates filter can both supply
     * the same template (Pro adds the default registration prompts again), which
     * rendered every prompt button twice. A later entry replaces an earlier one
     * with the same id but keeps its position, so Pro can still override a free
     * template's label.
     *
     * @since 4.3.13
     *
     * @param array $templates Templates with 'id' and 'label' keys
     *
     * @return array Templates with unique ids
     */
    protected function unique_prompt_templates( $templates ) {
        if ( ! is_array( $templates ) ) {
            return [];
        }

        $positions = [];
        $unique    = [];

        foreach ( $templates as $template ) {
            $id = isset( $template['id'] ) ? (string) $template['id'] : '';

            if ( '' !== $id && isset( $positions[ $id ] ) ) {
                $unique[ $positions[ $id ] ] = $template;
                continue;
            }

            if ( '' !== $id ) {
                $positions[ $id ] = count( $unique );
            }

            $unique[] = $template;
        }

        return $unique;
    }

    /**
     * Get AI instructions for prompt templates
     *
     * Returns AI instructions mapped by template ID.
     * Pro plugin can extend this via the wpuf_ai_prompt_instructions filter.
     *
     * @since 4.2.9
     *
     * @param string $form_type   Form type ('post' or 'profile')
     * @param string $integration Integration identifier (empty for no integration)
     *
     * @return array Instructions array keyed by template ID
     */
    public function get_prompt_ai_instructions( $form_type = 'post', $integration = '' ) {
        $instructions = [];

        // Free plugin provides post form instructions
        if ( 'profile' === $form_type && empty( $integration ) ) {
            // Default registration/profile form instructions (no integration)
            $instructions = [
                'basic_registration'      => __( 'Create a Basic User Registration form with email, name, username, password', 'wp-user-frontend' ),
                'member_directory'        => __( 'Create a Member Directory Profile form with name, email, bio, profile photo', 'wp-user-frontend' ),
                'job_applicant'           => __( 'Create a Job Applicant Registration form with name, email, phone, resume upload', 'wp-user-frontend' ),
                'blog_author_signup'      => __( 'Create a registration form for new blog authors. Collect their login details, public display information, a short introduction about themselves, a profile photo or avatar, and an optional personal website link.', 'wp-user-frontend' ),
                'community_member_join'   => __( 'Create a registration form for new community members. Collect their basic personal details, login information, a public name, a nickname, their interests (as checkboxes), a short personal introduction, and a profile picture', 'wp-user-frontend' ),
                'freelancer_profile_signup' => __( 'Create a registration form for freelancers that captures their professional details, skills, experience summary, portfolio information, and profile photo.', 'wp-user-frontend' ),
            ];
        }

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
         * @since 4.2.9
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
     * @since 4.2.9
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
     * @since 4.2.9
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
