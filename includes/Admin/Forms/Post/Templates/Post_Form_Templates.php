<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Post form templates: the picker's assets, creating a form from a template
 * and the template's own submission hooks. (Not the template base class
 * Admin\Forms\Form_Template, which each template extends.)
 *
 * @since 2.4
 * @since WPUF_SINCE Renamed from Admin\Forms\Post\Templates\Form_Template.
 */
class Post_Form_Templates {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'deregister_scripts' ], 99 );

        // frontend insert/update
        add_action( 'wpuf_add_post_after_insert', [ $this, 'post_form_submission' ], 10, 3 );
        add_action( 'wpuf_edit_post_after_update', [ $this, 'post_form_submission' ], 10, 3 );
    }

    /**
     * Deregister conflicting JS and CSS files of other plugins
     *
     * @since 3.6.4
     *
     * @return void
     */
    public function deregister_scripts() {
        global $wp_styles;

        if ( ! $this->should_display() ) {
            return;
        }

        $sources = wp_list_pluck( $wp_styles->registered, 'src' );

        // Look for any pre-existing learn-press style
        $result = array_key_exists( 'learn-press-admin', $sources );

        if ( $result ) {
            wp_deregister_style( 'learn-press-admin' );
        }
    }

    /**
     * Should a form displayed or script enqueued?
     *
     * @return bool
     */
    public function should_display() {
        $current_screen    = get_current_screen();
        $all_submenu_hooks = wpuf()->admin->menu->get_all_submenu_hooks();
        $wpuf_pages        = [
            'post_forms',
        ];

        foreach ( $wpuf_pages as $page ) {
            if ( ! array_key_exists( $page, $all_submenu_hooks ) ) {
                return false;
            }
        }

        return $current_screen->id === $all_submenu_hooks['post_forms'];
    }

    public function enqueue_scripts() {
        if ( ! $this->should_display() ) {
            return;
        }

        $form_builder_css_deps = wpuf()->assets->form_builder_css_deps;

        foreach ( $form_builder_css_deps as $deps ) {
            wp_enqueue_style( $deps );
        }

        wp_enqueue_style( 'wpuf-admin-form-builder' );
    }

    /**
     * Get a template object by name from the registry
     *
     * @param string $template
     *
     * @return bool|Form_Template
     */
    public function get_template_object( $template ) {
        $registry = wpuf_get_post_form_templates();

        if ( ! array_key_exists( $template, $registry ) ) {
            return false;
        }

        $template_object = $registry[ $template ];

        if ( ! is_a( $template_object, 'WeDevs\Wpuf\Admin\Forms\Form_Template' ) ) {
            return false;
        }

        return $template_object;
    }

    /**
     * Create a posting form from a post template
     *
     * @since 2.4
     *
     * @return void
     */
    public function create_post_form_from_template() {
        $result = check_admin_referer( 'wpuf_create_from_template' );

        if ( ! $result ) {
            return;
        }

        $template_name = isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : '';

        if ( ! $template_name ) {
            return;
        }

        $form_id = $this->create_from_template( $template_name );

        if ( ! $form_id || is_wp_error( $form_id ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wpuf-post-forms&action=edit&id=' . $form_id ) );

        exit;
    }

    /**
     * Create a post form from a template: the form with the template's title,
     * settings and fields, as the template link did (also used by the admin
     * forms REST route, so the React picker stays in the app).
     *
     * @since WPUF_SINCE
     *
     * @param string $template_name Template key
     *
     * @return int|false|\WP_Error Form id, false for an unknown or empty template
     */
    public function create_from_template( $template_name ) {
        $template_object = $this->get_template_object( $template_name );

        if ( false === $template_object ) {
            return false;
        }

        $form_fields = $template_object->get_form_fields();

        // A template without fields creates nothing (no orphan form).
        if ( ! $form_fields ) {
            return false;
        }

        // The form store writes the post, then settings and version, then the
        // fields as the template gives them (no unslash), as before (task 2.4a).
        $form_id = Stores::forms()->create(
            [
                'post_title'           => $template_object->get_title(),
                'post_type'            => 'wpuf_forms',
                'post_status'          => 'publish',
                'post_author'          => get_current_user_id(),
                'fields'               => $form_fields ? $form_fields : [],
                'unslash_fields'       => false,
                'settings'             => $template_object->get_form_settings(),
                'store_empty_settings' => true,
                'settings_first'       => true,
            ]
        );

        if ( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        return (int) $form_id;
    }

    /**
     * Call the integration functions on form submission/update
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     *
     * @return void
     */
    public function post_form_submission( $post_id, $form_id, $form_settings ) {
        $template = isset( $form_settings['form_template'] ) ? $form_settings['form_template'] : '';

        if ( ! $template ) {
            return;
        }

        $template_object = $this->get_template_object( $template );

        if ( false === $template_object ) {
            return;
        }

        $current_action = current_action();

        if ( 'wpuf_add_post_after_insert' === $current_action ) {
            $template_object->after_insert( $post_id, $form_id, $form_settings );
        } elseif ( 'wpuf_edit_post_after_update' === $current_action ) {
            $template_object->after_update( $post_id, $form_id, $form_settings );
        }
    }
}
