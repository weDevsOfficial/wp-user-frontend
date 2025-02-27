<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

/**
 * Admin form template handler
 *
 * Create forms based on form templates
 *
 * @since 2.4
 */
class Form_Template {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'deregister_scripts' ], 99 );

        // post form templates
        add_action( 'admin_footer', [ $this, 'render_post_form_templates' ] );

        // form settings
        add_action( 'wpuf_form_setting', [ $this, 'post_form_settings' ], 8, 2 );

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

        wp_enqueue_style( 'wpuf-form-builder' );
    }

    /**
     * Render the forms in the modal
     *
     * @return void
     */
    public function render_post_form_templates() {
        if ( ! $this->should_display() ) {
            return;
        }

        $registry       = wpuf_get_post_form_templates();
        $pro_templates  = wpuf_get_pro_form_previews();
        $blank_form_url = admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' );
        $action_name    = 'post_form_template';
        $footer_help    = sprintf( __( 'Want a new integration? <a href="%s" target="_blank">Let us know</a>.', 'wp-user-frontend' ), 'mailto:support@wedevs.com?subject=WPUF Custom Post Template Integration Request' );

        if ( ! $registry ) {
            return;
        }

        include WPUF_ROOT . '/includes/Admin/template-parts/modal.php';
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

        $template_object = $this->get_template_object( $template_name );

        if ( false === $template_object ) {
            return;
        }

        $current_user = get_current_user_id();

        $form_post_data = [
            'post_title'  => $template_object->get_title(),
            'post_type'   => 'wpuf_forms',
            'post_status' => 'publish',
            'post_author' => $current_user,
        ];

        $form_id = wp_insert_post( $form_post_data );

        if ( is_wp_error( $form_id ) ) {
            return;
        }

        // form has been created, lets setup
        update_post_meta( $form_id, 'wpuf_form_settings', $template_object->get_form_settings() );
        update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );

        $form_fields = $template_object->get_form_fields();

        if ( ! $form_fields ) {
            return;
        }

        foreach ( $form_fields as $menu_order => $field ) {
            wp_insert_post(
                [
                    'post_type'    => 'wpuf_input',
                    'post_status'  => 'publish',
                    'post_content' => maybe_serialize( $field ),
                    'post_parent'  => $form_id,
                    'menu_order'   => $menu_order,
                ]
            );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wpuf-post-forms&action=edit&id=' . $form_id ) );

        exit;
    }

    /**
     * Add settings field to override a form template
     *
     * @param array  $form_settings
     * @param object $post
     *
     * @return void
     */
    public function post_form_settings( $form_settings, $post ) {
        $registry = wpuf_get_post_form_templates();
        $selected = isset( $form_settings['form_template'] ) ? $form_settings['form_template'] : ''; ?>
        <tr>
            <th><?php esc_html_e( 'Form Template', 'wp-user-frontend' ); ?></th>
            <td>
                <select name="wpuf_settings[form_template]">
                    <option value=""><?php esc_html_e( '&mdash; No Template &mdash;', 'wp-user-frontend' ); ?></option>
                    <?php
                    if ( $registry ) {
                        foreach ( $registry as $key => $template ) {
                            printf( '<option value="%s"%s>%s</option>' . "\n", esc_attr( $key ), esc_attr( selected( $selected, $key, false ) ), esc_html( $template->get_title() ) );
                        }
                    }
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'If selected a form template, it will try to execute that integration options when new post created and updated.', 'wp-user-frontend' ); ?></p>
            </td>
        </tr>
        <?php
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
