<?php

namespace Wp\User\Frontend;

use Wp\User\Frontend\Admin\WPUF_Admin_Tools;

/**
 * The Admin class which will hold all the starting point of WordPress dashboard admin operations for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since WPUF_SINCE
 */

class Admin {
    public function __construct() {
        wpuf()->add_to_container( 'menu', new Admin\Menu() );
        wpuf()->add_to_container( 'form_template', new Admin\Forms\Post\Templates\Admin_Form_Template() );
        wpuf()->add_to_container( 'admin_form', new Admin\Forms\Admin_Form() );
        wpuf()->add_to_container( 'admin_form_handler', new Admin\Forms\WPUF_Admin_Form_Handler() );
        wpuf()->add_to_container( 'admin_subscription', new Admin\WPUF_Admin_Subscription() );
        wpuf()->add_to_container( 'admin_installer', new Admin\WPUF_Admin_Installer() );
        wpuf()->add_to_container( 'settings', new Admin\WPUF_Admin_Settings() );
        wpuf()->add_to_container( 'frontend_account', new Frontend\WPUF_Frontend_Account() );

        // post form submenu operations
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_post_form_scripts' ] );

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_post_form_template', [ $this, 'create_post_form_from_template' ] );

        // tools page
        add_action( 'admin_init', [ $this, 'handle_tools_action' ] );
        add_filter( 'wp_handle_upload_prefilter', [ $this, 'enable_json_upload' ], 1 );
        add_action( 'wp_ajax_wpuf_import_forms', [ $this, 'import_forms' ] );
        add_filter( 'upload_mimes', [ $this, 'add_json_mime_type' ] );
    }

    /**
     * Create post form templates depending on the action
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function create_post_form_from_template() {
        wpuf()->form_template->create_post_form_from_template();
    }

    /**
     * Enqueue the scripts needed for wpuf post form
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_post_form_scripts() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-subscriptions' );
    }

    /**
     * Handle tools page action
     *
     * @return void
     */
    public function handle_tools_action() {
        if ( ! isset( $_GET['wpuf_action'] ) ) {
            return;
        }
        check_admin_referer( 'wpuf-tools-action' );
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        global $wpdb;
        $action  = isset( $_GET['wpuf_action'] ) ? sanitize_text_field( wp_unslash( $_GET['wpuf_action'] ) ) : '';
        $message = 'del_forms';
        switch ( $action ) {
            case 'clear_settings':
                delete_option( 'wpuf_general' );
                delete_option( 'wpuf_dashboard' );
                delete_option( 'wpuf_profile' );
                delete_option( 'Wp\User\Frontend\WPUF_Payment' );
                delete_option( '_wpuf_page_created' );
                $message = 'settings_cleared';
                break;
            case 'del_post_forms':
                $this->delete_post_type( 'wpuf_forms' );
                break;
            case 'del_pro_forms':
                $this->delete_post_type( 'wpuf_profile' );
                break;
            case 'del_subs':
                $this->delete_post_type( 'wpuf_subscription' );
                break;
            case 'del_coupon':
                $this->delete_post_type( 'wpuf_coupon' );
                break;
            case 'clear_transaction':
                $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wpuf_transaction" );
                $message = 'del_trans';
                break;
            default:
                // code...
                break;
        }
        wp_safe_redirect( add_query_arg( [ 'msg' => $message ], admin_url( 'admin.php?page=wpuf_tools&action=tools' ) ) );
        exit;
    }

    /**
     * Delete all posts by a post type
     *
     * @param string $post_type
     *
     * @return void
     */
    public function delete_post_type( $post_type ) {
        $query = new \WP_Query( [
                                   'post_type'      => $post_type,
                                   'posts_per_page' => - 1,
                                   'post_status'    => [ 'publish', 'draft', 'pending', 'trash' ],
                               ] );
        $posts = $query->get_posts();
        if ( $posts ) {
            foreach ( $posts as $item ) {
                wp_delete_post( $item->ID, true );
            }
        }
        wp_reset_postdata();
    }

    /**
     * Enable json file upload via ajax in tools page
     *
     * @since 3.2.0
     *
     * @param array $file
     *
     * @return array
     * @todo  Move this method to WPUF_Admin_Tools class
     *
     */
    public function enable_json_upload( $file ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && 'upload-attachment' === $_POST['action'] && isset( $_POST['type'] ) && 'wpuf-form-uploader' === $_POST['type'] ) {
            // @see wp_ajax_upload_attachment
            check_ajax_referer( 'media-form' );
            add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_filetype_and_ext' ] );
        }

        return $file;
    }

    /**
     * Ajax handler to import WPUF form
     *
     * @since 3.2.0
     *
     * @return void
     * @todo  Move this method to WPUF_Admin_Tools class
     *
     */
    public function import_forms() {
        check_ajax_referer( 'wpuf_admin_tools' );
        if ( ! isset( $_POST['file_id'] ) ) {
            wp_send_json_error( new WP_Error( 'wpuf_ajax_import_forms_error',
                                              __( 'Missing file_id param', 'wp-user-frontend' ) ),
                                WP_Http::BAD_REQUEST );
        }
        $file_id = absint( wp_unslash( $_POST['file_id'] ) );
        $file    = get_attached_file( $file_id );
        if ( empty( $file ) ) {
            wp_send_json_error( new WP_Error( 'wpuf_ajax_import_forms_error',
                                              __( 'JSON file not found', 'wp-user-frontend' ) ), WP_Http::NOT_FOUND );
        }
        $filetype = wp_check_filetype( $file, [ 'json' => 'application/json' ] );
        if ( ! isset( $filetype['type'] ) || 'application/json' !== $filetype['type'] ) {
            wp_send_json_error( new WP_Error( 'wpuf_ajax_import_forms_error',
                                              __( 'Provided file is not a JSON file.', 'wp-user-frontend' ) ),
                                WP_Http::UNSUPPORTED_MEDIA_TYPE );
        }
        if ( ! class_exists( 'WPUF_Admin_Tools' ) ) {
            require_once WPUF_ROOT . '/admin/class-tools.php';
        }
        $imported = WPUF_Admin_Tools::import_json_file( $file );
        if ( is_wp_error( $imported ) ) {
            wp_send_json_error( $imported, WP_Http::UNPROCESSABLE_ENTITY );
        }
        wp_send_json_success( [
                                  'message' => __( 'Forms imported successfully.', 'wp-user-frontend' ),
                              ] );
    }

    /**
     * Add json file mime type to upload in WP Media
     *
     * @since 3.2.0
     *
     * @param array $mime_types
     *
     * @return array
     * @todo  Move this method to WPUF_Admin_Tools class
     *
     */
    public function add_json_mime_type( $mime_types ) {
        $mime_types['json'] = 'application/json';

        return $mime_types;
    }

    /**
     * Allow json file to upload with async uploader
     *
     * @since 3.2.0
     *
     * @param array $info
     *
     * @return array
     */
    public function check_filetype_and_ext( $info ) {
        $info['ext']  = 'json';
        $info['type'] = 'application/json';

        return $info;
    }
}
