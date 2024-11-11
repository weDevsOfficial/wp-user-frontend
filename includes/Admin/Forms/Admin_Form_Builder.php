<?php

namespace WeDevs\Wpuf\Admin\Forms;

use WeDevs\Wpuf\Free\Pro_Prompt;

/**
 * Form Builder framework
 */
class Admin_Form_Builder {

    /**
     * Form Settings
     *
     * @since 2.5
     *
     * @var string
     */
    private $settings = [];

    /**
     * Class contructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct( $settings ) {
        global $post;
        $defaults = [
            'form_type'         => '',
            // e.g 'post', 'profile' etc
            'post_type'         => '',
            // e.g 'wpuf_forms', 'wpuf_profile' etc,
            'form_settings_key' => '',
            'post_id'           => 0,
            'shortcodes'        => [],
            // [ [ 'name' => 'wpuf_form', 'type' => 'profile' ], [ 'name' => 'wpuf_form', 'type' => 'registration' ] ]
        ];
        $this->settings = wp_parse_args( $settings, $defaults );
        // set post data to global $post
        $post = get_post( $this->settings['post_id'] );
        // if we have an existing post, then let's start
        if ( ! empty( $post->ID ) ) {
            add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
            add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
            add_action( 'admin_print_scripts', [ $this, 'admin_print_scripts' ] );
            add_action( 'admin_footer', [ $this, 'custom_dequeue' ] );
            add_action( 'admin_footer', [ $this, 'admin_footer' ] );
            add_action( 'wpuf_admin_form_builder', [ $this, 'include_form_builder' ] );
        }

        add_action( 'wpuf_form_builder_template_builder_stage_submit_area', [ $this, 'add_form_submit_area' ] );
    }

    /**
     * Add buttons in form submit area
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_form_submit_area() {
        ?>
        <input @click.prevent="" type="submit" name="submit" :value="post_form_settings.submit_text">

        <a
            v-if="post_form_settings.draft_post"
            @click.prevent=""
            href="#"
            class="btn"
            id="wpuf-post-draft"
        >
            <?php esc_html_e( 'Save Draft', 'wp-user-frontend' ); ?>
        </a>
        <?php
    }

    /**
     * Enqueue admin scripts
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        global $post;

        wp_enqueue_style( 'wpuf-font-awesome' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_style( 'wpuf-selectize' );
        wp_enqueue_style( 'wpuf-toastr' );
        wp_enqueue_style( 'wpuf-tooltip' );
        wp_enqueue_style( 'wpuf-jquery-ui' );
        wp_enqueue_style( 'wp-color-picker' );
        do_action( 'wpuf_form_builder_enqueue_style' );

        wp_enqueue_script( 'wpuf-vue' );
        wp_enqueue_script( 'wpuf-vuex' );
        wp_enqueue_script( 'wpuf-subscriptions' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-jquery-scrollTo' );
        wp_enqueue_script( 'wpuf-selectize' );
        wp_enqueue_script( 'wpuf-toastr' );
        wp_enqueue_script( 'wpuf-clipboard' );
        wp_enqueue_script( 'wpuf-tooltip' );
        wp_enqueue_script( 'wpuf-timepicker' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'zxcvbn' );
        wp_enqueue_script( 'password-strength-meter' );
        wp_enqueue_script( 'wpuf-form-builder-wpuf-forms' );
        $single_objects = [
            'post_title',
            'post_content',
            'post_excerpt',
            'featured_image',
            'user_login',
            'first_name',
            'last_name',
            'nickname',
            'user_email',
            'user_url',
            'user_bio',
            'password',
            'user_avatar',
            'taxonomy',
            'cloudflare_turnstile',
        ];
        $taxonomy_terms = array_keys( get_taxonomies() );
        $single_objects = array_merge( $single_objects, $taxonomy_terms );
        wp_enqueue_script( 'wpuf-form-builder-mixins' );

        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_single_objects', $single_objects );

        do_action( 'wpuf_form_builder_enqueue_after_mixins' );

        wp_enqueue_script( 'wpuf-form-builder-components' );

        do_action( 'wpuf_form_builder_enqueue_after_components' );

        wp_enqueue_script( 'wpuf-form-builder' );
        wp_enqueue_script( 'wp-color-picker' );

        do_action( 'wpuf_form_builder_enqueue_after_main_instance' );
        /*
         * Data required for building the form
         */
        wpuf_require_once( WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings.php' );
        wpuf_require_once( WPUF_ROOT . '/includes/Free/Pro_Prompt.php' );

        $wpuf_form_builder = apply_filters(
            'wpuf_form_builder_localize_script',
            [
                'i18n'             => $this->i18n(),
                'post'             => $post,
                'form_fields'      => wpuf_get_form_fields( $post->ID ),
                'panel_sections'   => wpuf()->fields->get_field_groups(),
                'field_settings'   => wpuf()->fields->get_js_settings(),
                'form_settings'    => wpuf_get_form_settings( $post->ID ),
                'notifications'    => wpuf_get_form_notifications( $post->ID ),
                'pro_link'         => Pro_Prompt::get_pro_url(),
                'site_url'         => site_url( '/' ),
                'asset_url'        => WPUF_ASSET_URI,
                'recaptcha_site'   => wpuf_get_option( 'recaptcha_public', 'wpuf_general' ),
                'recaptcha_secret' => wpuf_get_option( 'recaptcha_private', 'wpuf_general' ),
                'turnstile_site'   => wpuf_get_option( 'turnstile_site_key', 'wpuf_general' ),
                'turnstile_secret' => wpuf_get_option( 'turnstile_secret_key', 'wpuf_general' ),
                'nonce'            => wp_create_nonce( 'form-builder-setting-nonce' ),
            ]
        );
        $wpuf_form_builder = wpuf_unset_conditional( $wpuf_form_builder );
        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_form_builder', $wpuf_form_builder );
        // mixins
        $wpuf_mixins = [
            'root'          => apply_filters( 'wpuf_form_builder_js_root_mixins', [] ),
            'builder_stage' => apply_filters( 'wpuf_form_builder_js_builder_stage_mixins', [] ),
            'form_fields'   => apply_filters( 'wpuf_form_builder_js_form_fields_mixins', [] ),
            'field_options' => apply_filters( 'wpuf_form_builder_js_field_options_mixins', [] ),
        ];
        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_mixins', $wpuf_mixins );
    }

    /**
     * Print js scripts in admin head
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_print_scripts() {
        ?>
        <script>
            if (!window.Promise) {
                var promise_polyfill = document.createElement( 'script' );
                promise_polyfill.setAttribute( 'src', 'https://cdnjs.cloudflare.com/polyfill/v3/polyfill.js?version=4.8.0&features=default' );
                document.head.appendChild( promise_polyfill );
            }
        </script>
        <script>
            var wpuf_form_builder_mixins = function ( mixins, mixin_parent ) {
                if (!mixins || !mixins.length) {
                    return [];
                }

                if (!mixin_parent) {
                    mixin_parent = window;
                }

                return mixins.map( function ( mixin ) {
                    return mixin_parent[mixin];
                } );
            };
        </script>
        <?php
    }

    /**
     * Include vue component templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_footer() {
        // get all vue component names
        include WPUF_ROOT . '/assets/js-templates/form-components.php';
        do_action( 'wpuf_form_builder_add_js_templates' );
    }

    /**
     * Dequeue style and script to avoid conflict with Imagify Image Optimizer plugin
     *
     * @since 2.5
     *
     * @param string $template
     * @param string $file_path
     *
     * @return void
     */
    public static function custom_dequeue() {
        wp_dequeue_style( 'imagify-css-sweetalert' );
        wp_deregister_style( 'imagify-css-sweetalert' );
        wp_dequeue_script( 'imagify-js-sweetalert' );
        wp_deregister_script( 'imagify-js-sweetalert' );
    }

    /**
     * Include form builder view template
     *
     * @since 2.5
     *
     * @return void
     */
    public function include_form_builder() {
        $form_id           = $this->settings['post_id'];
        $form_type         = $this->settings['form_type'];
        $post_type         = $this->settings['post_type'];
        $form_settings_key = $this->settings['form_settings_key'];
        $shortcodes        = $this->settings['shortcodes'];
        $forms             = get_posts( [ 'post_type' => $post_type, 'post_status' => 'any' ] );
        include WPUF_ROOT . '/admin/form-builder/views/form-builder.php';
    }

    /**
     * i18n translatable strings
     *
     * @since 2.5
     *
     * @return array
     */
    private function i18n() {
        return apply_filters(
            'wpuf_form_builder_i18n', [
                'advanced_options'      => __( 'Advanced Options', 'wp-user-frontend' ),
                'delete_field_warn_msg' => __( 'Are you sure you want to delete this field?', 'wp-user-frontend' ),
                'yes_delete_it'         => __( 'Yes, delete it', 'wp-user-frontend' ),
                'no_cancel_it'          => __( 'No, cancel it', 'wp-user-frontend' ),
                'ok'                    => __( 'OK', 'wp-user-frontend' ),
                'cancel'                => __( 'Cancel', 'wp-user-frontend' ),
                'close'                 => __( 'Close', 'wp-user-frontend' ),
                'last_choice_warn_msg'  => __( 'This field must contain at least one choice', 'wp-user-frontend' ),
                'option'                => __( 'Option', 'wp-user-frontend' ),
                'column'                => __( 'Column', 'wp-user-frontend' ),
                'last_column_warn_msg'  => __( 'This field must contain at least one column', 'wp-user-frontend' ),
                'is_a_pro_feature'      => __( 'is available in Pro version', 'wp-user-frontend' ),
                'pro_feature_msg'       => __(
                    'Please upgrade to the Pro version to unlock all these awesome features', 'wp-user-frontend'
                ),
                'upgrade_to_pro'        => __( 'Get the Pro version', 'wp-user-frontend' ),
                'select'                => __( 'Select', 'wp-user-frontend' ),
                'saved_form_data'       => __( 'Saved form data', 'wp-user-frontend' ),
                'unsaved_changes'       => __( 'You have unsaved changes.', 'wp-user-frontend' ),
                'copy_shortcode'        => __( 'Click to copy shortcode', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Save form data
     *
     * @since 2.5
     *
     * @param array $data Contains form_fields, form_settings, form_settings_key data
     *
     * @return bool
     */
    public static function save_form( $data ) {
        $saved_wpuf_inputs = [];
        wp_update_post( [ 'ID' => $data['form_id'], 'post_status' => 'publish', 'post_title' => $data['post_title'] ] );
        $existing_wpuf_input_ids = get_children( [
                                                     'post_parent' => $data['form_id'],
                                                     'post_status' => 'publish',
                                                     'post_type'   => 'wpuf_input',
                                                     'numberposts' => '-1',
                                                     'orderby'     => 'menu_order',
                                                     'order'       => 'ASC',
                                                     'fields'      => 'ids',
                                                 ] );
        $new_wpuf_input_ids = [];
        if ( ! empty( $data['form_fields'] ) ) {
            foreach ( $data['form_fields'] as $order => $field ) {
                if ( ! empty( $field['is_new'] ) ) {
                    unset( $field['is_new'] );
                    unset( $field['id'] );
                    $field_id = 0;
                } else {
                    $field_id = $field['id'];
                }
                $field_id = wpuf_insert_form_field( $data['form_id'], $field, $field_id, $order );
                $new_wpuf_input_ids[] = $field_id;
                $field['id'] = $field_id;
                $saved_wpuf_inputs[] = $field;
            }
        }
        $inputs_to_delete = array_diff( $existing_wpuf_input_ids, $new_wpuf_input_ids );
        if ( ! empty( $inputs_to_delete ) ) {
            foreach ( $inputs_to_delete as $delete_id ) {
                wp_delete_post( $delete_id, true );
            }
        }
        update_post_meta( $data['form_id'], $data['form_settings_key'], $data['form_settings'] );
        update_post_meta( $data['form_id'], 'notifications', $data['notifications'] );
        update_post_meta( $data['form_id'], 'integrations', $data['integrations'] );

        return $saved_wpuf_inputs;
    }
}
