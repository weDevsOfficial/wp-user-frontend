<?php
/**
 * Form Builder framework
 */

class WPUF_Admin_Form_Builder {

    /**
     * Form Settings
     *
     * @since 2.5
     *
     * @var string
     */
    private $settings = array();

    /**
     * Class contructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct( $settings ) {
        global $post;

        $defaults = array(
            'form_type'         => '', // e.g 'post', 'profile' etc
            'post_type'         => '', // e.g 'wpuf_forms', 'wpuf_profile' etc,
            'form_settings_key' => '',
            'post_id'           => 0,
            'shortcodes'        => array() // [ [ 'name' => 'wpuf_form', 'type' => 'profile' ], [ 'name' => 'wpuf_form', 'type' => 'registration' ] ]
        );

        $this->settings = wp_parse_args( $settings, $defaults );

        // set post data to global $post
        $post = get_post( $this->settings['post_id'] );

        // if we have an existing post, then let's start
        if ( ! empty( $post->ID ) ) {
            add_action( 'in_admin_header', array( $this, 'remove_admin_notices' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
            add_action( 'admin_footer', array( $this, 'custom_dequeue' ) );
            add_action( 'admin_footer', array( $this, 'admin_footer' ) );
            add_action( 'wpuf-admin-form-builder', array( $this, 'include_form_builder' ) );
        }
    }

    /**
     * Remove all kinds of admin notices
     *
     * Since we don't have much space left on top of the page,
     * we have to remove all kinds of admin notices
     *
     * @since 2.5
     *
     * @return void
     */
    public function remove_admin_notices() {
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
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

        /**
         * CSS
         */
        wp_enqueue_style( 'wpuf-css', WPUF_ASSET_URI . '/css/frontend-forms.css' );
        wp_enqueue_style( 'wpuf-font-awesome', WPUF_ASSET_URI . '/vendor/font-awesome/css/font-awesome.min.css', array(), WPUF_VERSION );
        wp_enqueue_style( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.css', array(), WPUF_VERSION );
        wp_enqueue_style( 'wpuf-selectize', WPUF_ASSET_URI . '/vendor/selectize/css/selectize.default.css', array(), WPUF_VERSION );
        wp_enqueue_style( 'wpuf-toastr', WPUF_ASSET_URI . '/vendor/toastr/toastr.min.css', array(), WPUF_VERSION );
        wp_enqueue_style( 'wpuf-tooltip', WPUF_ASSET_URI . '/vendor/tooltip/tooltip.css', array(), WPUF_VERSION );

        $form_builder_css_deps = apply_filters( 'wpuf-form-builder-css-deps', array(
            'wpuf-css', 'wpuf-font-awesome', 'wpuf-sweetalert2', 'wpuf-selectize', 'wpuf-toastr', 'wpuf-tooltip'
        ) );

        wp_enqueue_style( 'wpuf-form-builder', WPUF_ASSET_URI . '/css/wpuf-form-builder.css', $form_builder_css_deps, WPUF_VERSION );

        wp_enqueue_style( 'jquery-ui', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );

        do_action( 'wpuf-form-builder-enqueue-style' );

        /**
         * JavaScript
         */
        $prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-vuex', WPUF_ASSET_URI . '/vendor/vuex/vuex' . $prefix . '.js', array( 'wpuf-vue' ), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.js', array(), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-jquery-scrollTo', WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo' . $prefix . '.js', array( 'jquery' ), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-selectize', WPUF_ASSET_URI . '/vendor/selectize/js/standalone/selectize' . $prefix . '.js', array( 'jquery' ), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-toastr', WPUF_ASSET_URI . '/vendor/toastr/toastr' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-clipboard', WPUF_ASSET_URI . '/vendor/clipboard/clipboard' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_enqueue_script( 'wpuf-tooltip', WPUF_ASSET_URI . '/vendor/tooltip/tooltip' . $prefix . '.js', array(), WPUF_VERSION, true );

        $form_builder_js_deps = apply_filters( 'wpuf-form-builder-js-deps', array(
            'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'underscore',
            'wpuf-vue', 'wpuf-vuex', 'wpuf-sweetalert2', 'wpuf-jquery-scrollTo',
            'wpuf-selectize', 'wpuf-toastr', 'wpuf-clipboard', 'wpuf-tooltip'
        ) );

        wp_enqueue_script( 'wpuf-form-builder-mixins', WPUF_ASSET_URI . '/js/wpuf-form-builder-mixins.js', $form_builder_js_deps, WPUF_VERSION, true );

        do_action( 'wpuf-form-builder-enqueue-after-mixins' );

        wp_enqueue_script( 'wpuf-form-builder-components', WPUF_ASSET_URI . '/js/wpuf-form-builder-components.js', array( 'wpuf-form-builder-mixins' ), WPUF_VERSION, true );

        do_action( 'wpuf-form-builder-enqueue-after-components' );

        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );

        wp_enqueue_script( 'wpuf-form-builder', WPUF_ASSET_URI . '/js/wpuf-form-builder.js', array( 'wpuf-form-builder-components' ), WPUF_VERSION, true );

        do_action( 'wpuf-form-builder-enqueue-after-main-instance' );

        /*
         * Data required for building the form
         */
        require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings.php';
        require_once WPUF_ROOT . '/includes/free/prompt.php';

        $wpuf_form_builder = apply_filters( 'wpuf-form-builder-localize-script', array(
            'i18n'              => $this->i18n(),
            'post'              => $post,
            'form_fields'       => wpuf_get_form_fields( $post->ID ),
            'panel_sections'    => wpuf()->fields->get_field_groups(),
            'field_settings'    => wpuf()->fields->get_js_settings(),
            'notifications'     => wpuf_get_form_notifications( $post->ID ),
            'pro_link'          => WPUF_Pro_Prompt::get_pro_url(),
            'site_url'          => site_url('/'),
            'recaptcha_site'    => wpuf_get_option( 'recaptcha_public', 'wpuf_general' ),
            'recaptcha_secret'  => wpuf_get_option( 'recaptcha_private', 'wpuf_general' ),
        ) );

        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_form_builder', $wpuf_form_builder );

        // mixins
        $wpuf_mixins = array(
            'root'           => apply_filters( 'wpuf-form-builder-js-root-mixins', array() ),
            'builder_stage'  => apply_filters( 'wpuf-form-builder-js-builder-stage-mixins', array() ),
            'form_fields'    => apply_filters( 'wpuf-form-builder-js-form-fields-mixins', array() ),
            'field_options'  => apply_filters( 'wpuf-form-builder-js-field-options-mixins', array() ),
        );

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
                    var promise_polyfill = document.createElement('script');
                    promise_polyfill.setAttribute('src','https://cdn.polyfill.io/v2/polyfill.min.js');
                    document.head.appendChild(promise_polyfill);
                }
            </script>

            <script>
                var wpuf_form_builder_mixins = function(mixins, mixin_parent) {
                    if (!mixins || !mixins.length) {
                        return [];
                    }

                    if (!mixin_parent) {
                        mixin_parent = window;
                    }

                    return mixins.map(function (mixin) {
                        return mixin_parent[mixin];
                    });
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

        do_action( 'wpuf-form-builder-add-js-templates' );
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
        $form_id            = $this->settings['post_id'];
        $form_type          = $this->settings['form_type'];
        $post_type          = $this->settings['post_type'];
        $form_settings_key  = $this->settings['form_settings_key'];
        $shortcodes         = $this->settings['shortcodes'];

        $forms = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any' ) );

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
        return apply_filters( 'wpuf-form-builder-i18n', array(
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
            'pro_feature_msg'       => __( 'Please upgrade to the Pro version to unlock all these awesome features', 'wp-user-frontend' ),
            'upgrade_to_pro'        => __( 'Get the Pro version', 'wp-user-frontend' ),
            'select'                => __( 'Select', 'wp-user-frontend' ),
            'saved_form_data'       => __( 'Saved form data', 'wp-user-frontend' ),
            'unsaved_changes'       => __( 'You have unsaved changes.', 'wp-user-frontend' ),
            'copy_shortcode'        => __( 'Click to copy shortcode', 'wp-user-frontend' ),
        ) );
    }

    /**
     * Save form data
     *
     * @since 2.5
     *
     * @param array $data Contains form_fields, form_settings, form_settings_key data
     *
     * @return boolean
     */
    public static function save_form( $data ) {
        $saved_wpuf_inputs = array();

        wp_update_post( array( 'ID' => $data['form_id'], 'post_status' => 'publish', 'post_title' => $data['post_title'] ) );

        $existing_wpuf_input_ids = get_children( array(
            'post_parent' => $data['form_id'],
            'post_status' => 'publish',
            'post_type'   => 'wpuf_input',
            'numberposts' => '-1',
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
            'fields'      => 'ids'
        ) );

        $new_wpuf_input_ids = array();

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
                wp_delete_post( $delete_id , true );
            }
        }

        update_post_meta( $data['form_id'], $data['form_settings_key'], $data['form_settings'] );
        update_post_meta( $data['form_id'], 'notifications', $data['notifications'] );
        update_post_meta( $data['form_id'], 'integrations', $data['integrations'] );

        return $saved_wpuf_inputs;
    }
}
