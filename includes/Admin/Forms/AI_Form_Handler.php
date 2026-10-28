<?php

namespace WeDevs\Wpuf\Admin\Forms;

use WeDevs\Wpuf\Admin\Screens\AiFormBuilder;

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
     *
     * @since 4.2.1
     * @since WPUF_SINCE Prints the React screen (Admin\Screens\AiFormBuilder).
     *
     * @return void
     */
    public function handle_ai_form_template() {
        $this->render_page( 'wpuf_create_from_template', 'wpuf-ai-form-generation', __( 'Create Form with AI', 'wp-user-frontend' ), 'wpuf-ai-form-generation' );
    }

    /**
     * Handle AI form generating action
     *
     * @since 4.2.1
     *
     * @return void
     */
    public function handle_ai_form_generating() {
        $this->render_page( 'wpuf_ai_generate_form', 'wpuf-ai-form-generating', __( 'Generating Form', 'wp-user-frontend' ), 'wpuf-ai-form-generation' );
    }

    /**
     * Handle AI form success action
     *
     * @since 4.2.1
     *
     * @return void
     */
    public function handle_ai_form_success() {
        $this->render_page( 'wpuf_ai_success', 'wpuf-ai-form-success', __( 'Form Created Successfully', 'wp-user-frontend' ), 'wpuf-ai-form-success' );
    }

    /**
     * Verify the request, then print the AI form builder page and exit: nonce
     * and capability, notices removed, `wpuf_load_ai_form_builder_page` (Admin
     * enqueues the app there), admin header, the screen, admin footer.
     *
     * @since WPUF_SINCE
     *
     * @param string $nonce_action Nonce action of the URL
     * @param string $screen_id    Current screen id
     * @param string $page_title   Admin page title
     * @param string $submenu      Highlighted submenu file
     *
     * @return void
     */
    private function render_page( $nonce_action, $screen_id, $page_title, $submenu ) {
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
            wp_die( esc_html__( 'Security check failed', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-user-frontend' ) );
        }

        $form_type = $this->get_form_type_from_referer();

        // The admin app opens the AI form builder as a route: after the checks
        // above, go there with the stage keys of this request.
        $this->redirect_to_app_route( $form_type );

        // Remove admin notices for this page
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );

        // Let Admin enqueue + localize assets consistently
        do_action( 'wpuf_load_ai_form_builder_page', $form_type );

        // Set up proper admin page variables
        set_current_screen( $screen_id );
        global $title, $parent_file, $submenu_file;
        // phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- admin-header.php reads these.
        $title        = $page_title;
        $parent_file  = ( 'profile' === $form_type ) ? 'wpuf-profile-forms' : 'wpuf-post-forms';
        $submenu_file = $submenu;
        // phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

        require_once ABSPATH . 'wp-admin/admin-header.php';

        $this->screen()->render();

        require_once ABSPATH . 'wp-admin/admin-footer.php';
        exit;
    }

    /**
     * Redirect to the admin app's AI form builder route when the app runs it.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type post|profile
     *
     * @return void
     */
    private function redirect_to_app_route( $form_type ) {
        $route_id = 'profile' === $form_type ? 'registration-forms-ai' : 'post-forms-ai';
        $route    = null;

        foreach ( wpuf()->platform()->get( \WeDevs\Wpuf\Admin\App\AppPage::class )->routes() as $item ) {
            if ( $route_id === $item['id'] && 'app' === $item['mode'] ) {
                $route = $item;
            }
        }

        if ( ! $route ) {
            return;
        }

        $stage = $this->screen()->stage_data();
        $args  = array_filter(
            [
                'stage'       => 'input' === $stage['stage'] ? '' : $stage['stage'],
                'description' => $stage['description'],
                'prompt'      => $stage['prompt'],
                'form_id'     => $stage['formId'],
                'form_title'  => $stage['formTitle'],
            ],
            'strlen'
        );

        wp_safe_redirect( wpuf_admin_app_url( $route['path'], $args ) );
        exit;
    }

    /**
     * The AI form builder screen from the platform container.
     *
     * @since WPUF_SINCE
     *
     * @return AiFormBuilder
     */
    private function screen() {
        return wpuf()->platform()->get( AiFormBuilder::class );
    }
}
