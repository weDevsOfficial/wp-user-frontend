<?php
// DESCRIPTION: Bootstraps the WP Job Manager integration. Loads only when
// WP Job Manager is active and wires the meta mapper to WPUF submission hooks.

namespace WeDevs\Wpuf\Integrations\WP_Job_Manager;

/**
 * Main WP Job Manager Integration Class
 *
 * @since WPUF_SINCE
 */
class WPUF_WP_Job_Manager_Integration {

    /**
     * Meta mapper instance
     *
     * @var Meta_Mapper
     */
    public $meta_mapper;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_handlers();
    }

    /**
     * Initialize handlers
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function init_handlers() {
        if ( ! $this->is_wpjm_active() ) {
            return;
        }

        /**
         * Kill-switch to disable the WPUF × WP Job Manager integration.
         *
         * @since WPUF_SINCE
         *
         * @param bool $enabled Whether the integration should run.
         */
        if ( ! apply_filters( 'wpuf_enable_wpjm_integration', true ) ) {
            return;
        }

        $this->meta_mapper = new Meta_Mapper();
        $this->meta_mapper->register_hooks();

        // the free "Post a Job" template, Pro ships its own
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            add_filter( 'wpuf_get_post_form_templates', [ $this, 'register_free_template' ] );
        }

        /**
         * Fires once the WP Job Manager integration has initialized.
         *
         * @since WPUF_SINCE
         *
         * @param WPUF_WP_Job_Manager_Integration $integration The integration instance.
         */
        do_action( 'wpuf_wpjm_integration_ready', $this );
    }

    /**
     * Add the free "Post a Job" form template
     *
     * @since WPUF_SINCE
     *
     * @param array $templates Post form templates.
     *
     * @return array
     */
    public function register_free_template( $templates ) {
        $templates['post_form_template_wp_job_manager'] = new Template_Free();

        return $templates;
    }

    /**
     * Check if WP Job Manager is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function is_wpjm_active() {
        return class_exists( 'WP_Job_Manager' );
    }
}
