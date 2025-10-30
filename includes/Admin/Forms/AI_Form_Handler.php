<?php

namespace WeDevs\Wpuf\Admin\Forms;

/**
 * AI Form Handler
 *
 * Handles AI form generation functionality
 *
 * @since 4.2.1
 */
class AI_Form_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook to handle the generating page
        add_action( 'admin_action_wpuf_ai_form_generating', [ $this, 'handle_ai_form_generating' ] );
        // Hook to handle the success page
        add_action( 'admin_action_wpuf_ai_form_success', [ $this, 'handle_ai_form_success' ] );
    }

    /**
     * Determine the form type based on the HTTP referer or action parameter
     *
     * @return string 'post' or 'profile'
     */
    private function get_form_type_from_referer() {
        // First check the action parameter - this is more reliable
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

        if ( $action === 'wpuf_profile_form_template' ) {
            return 'profile';
        }

        if ( $action === 'post_form_template' ) {
            return 'post';
        }

        // Fallback to referer check
        $referer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

        if ( strpos( $referer, 'wpuf-profile-forms' ) !== false ) {
            return 'profile';
        }

        return 'post'; // Default to post forms
    }

    /**
     * Handle AI form template action
     */
    public function handle_ai_form_template() {
        // Verify nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_create_from_template' ) ) {
            wp_die( __( 'Security check failed', 'wp-user-frontend' ) );
        }

        // Check permissions
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-user-frontend' ) );
        }

        // Determine form type
        $form_type = $this->get_form_type_from_referer();

        // Remove admin notices for this page
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );

        // Let Admin enqueue + localize assets consistently
        do_action( 'wpuf_load_ai_form_builder_page', $form_type );

        // Set up proper admin page variables
        set_current_screen( 'wpuf-ai-form-generation' );
        global $title, $parent_file, $submenu_file;
        $title = __( 'Create Form with AI', 'wp-user-frontend' );
        $parent_file = ( $form_type === 'profile' ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';
        $submenu_file = 'wpuf-ai-form-generation';

        // Include admin header
        require_once ABSPATH . 'wp-admin/admin-header.php';

        // Include the unified AI form builder template
        include WPUF_ROOT . '/includes/Admin/template-parts/ai-form-builder.php';

        // Include admin footer
        require_once ABSPATH . 'wp-admin/admin-footer.php';
        exit;
    }

    /**
     * Handle AI form generating action
     */
    public function handle_ai_form_generating() {
        // Verify nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_ai_generate_form' ) ) {
            wp_die( __( 'Security check failed', 'wp-user-frontend' ) );
        }

        // Check permissions
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-user-frontend' ) );
        }

        // Determine form type
        $form_type = $this->get_form_type_from_referer();

        // Remove admin notices for this page
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );

        // Let Admin enqueue + localize assets consistently
        do_action( 'wpuf_load_ai_form_builder_page', $form_type );

        // Set up proper admin page variables
        set_current_screen( 'wpuf-ai-form-generating' );
        global $title, $parent_file, $submenu_file;
        $title = __( 'Generating Form', 'wp-user-frontend' );
        $parent_file = ( $form_type === 'profile' ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';
        $submenu_file = 'wpuf-ai-form-generation';

        // Include admin header
        require_once ABSPATH . 'wp-admin/admin-header.php';

        // Include the unified AI form builder template
        include WPUF_ROOT . '/includes/Admin/template-parts/ai-form-builder.php';

        // Include admin footer
        require_once ABSPATH . 'wp-admin/admin-footer.php';
        exit;
    }

    /**
     * Handle AI form success action
     */
    public function handle_ai_form_success() {
        // Verify nonce
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wpuf_ai_success' ) ) {
            wp_die( __( 'Security check failed', 'wp-user-frontend' ) );
        }

        // Check permissions
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'wp-user-frontend' ) );
        }

        // Determine form type
        $form_type = $this->get_form_type_from_referer();

        // Remove admin notices for this page
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );

        // Let Admin enqueue + localize assets consistently
        do_action( 'wpuf_load_ai_form_builder_page', $form_type );

        // Set up proper admin page variables
        set_current_screen( 'wpuf-ai-form-success' );
        global $title, $parent_file, $submenu_file;
        $title = __( 'Form Created Successfully', 'wp-user-frontend' );
        $parent_file = ( $form_type === 'profile' ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';
        $submenu_file = 'wpuf-ai-form-success';

        // Include admin header
        require_once ABSPATH . 'wp-admin/admin-header.php';

        // Include the unified AI form builder template
        include WPUF_ROOT . '/includes/Admin/template-parts/ai-form-builder.php';

        // Include admin footer
        require_once ABSPATH . 'wp-admin/admin-footer.php';
        exit;
    }
}

// Initialize the handler
new AI_Form_Handler();
