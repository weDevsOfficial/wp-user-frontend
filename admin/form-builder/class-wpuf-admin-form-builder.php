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
            'form_type'       => '', // e.g 'post', 'profile' etc
            'post_type'       => '', // e.g 'wpuf_forms', 'wpuf_profile' etc
            'post_id'         => 0,
            'shortcode_attrs' => array() // [ 'type' => [ 'profile' => 'Profile', 'registration' => 'Registration' ] ]
        );

        $this->settings = wp_parse_args( $settings, $defaults );

        // set post data to global $post
        $post = get_post( $this->settings['post_id'] );

        // if we have an existing post, then let's start
        if ( ! empty( $post->ID ) ) {
            add_action( 'in_admin_header', array( $this, 'remove_admin_notices' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
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
        wp_enqueue_style( 'wpuf-sweetalert', WPUF_ASSET_URI . '/vendor/sweetalert/sweetalert.css', array(), WPUF_VERSION );

        $form_builder_css_deps = apply_filters( 'wpuf-form-builder-css-deps', array(
            'wpuf-css', 'wpuf-font-awesome', 'wpuf-sweetalert'
        ) );

        wp_enqueue_style( 'wpuf-form-builder', WPUF_ASSET_URI . '/css/wpuf-form-builder.css', $form_builder_css_deps, WPUF_VERSION );

        /**
         * JavaScript
         */
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            wp_enqueue_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue.js', array(), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-vuex', WPUF_ASSET_URI . '/vendor/vuex/vuex.js', array( 'wpuf-vue' ), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-sweetalert', WPUF_ASSET_URI . '/vendor/sweetalert/sweetalert-dev.js', array(), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-jquery-scrollTo', WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo.js', array( 'jquery' ), WPUF_VERSION, true );

        } else {
            wp_enqueue_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue.min.js', array(), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-vuex', WPUF_ASSET_URI . '/vendor/vuex/vuex.min.js', array( 'wpuf-vue' ), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-sweetalert', WPUF_ASSET_URI . '/vendor/sweetalert/sweetalert.min.js', array(), WPUF_VERSION, true );
            wp_enqueue_script( 'wpuf-jquery-scrollTo', WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo.min.js', array( 'jquery' ), WPUF_VERSION, true );
        }


        $form_builder_js_deps = apply_filters( 'wpuf-form-builder-js-deps', array(
            'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'underscore',
            'wpuf-vue', 'wpuf-vuex', 'wpuf-sweetalert', 'wpuf-jquery-scrollTo'
        ) );

        wp_enqueue_script( 'wpuf-form-builder', WPUF_ASSET_URI . '/js/wpuf-form-builder.js', $form_builder_js_deps, WPUF_VERSION, true );

        /*
         * Data required for building the form
         */
        require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings.php';

        $wpuf_form_builder = apply_filters( 'wpuf-form-builder-localize-script', array(
            'i18n'              => $this->i18n(),
            'post'              => $post,
            'form_fields'       => wpuf_get_form_fields( $post->ID ),
            'panel_sections'    => $this->get_panel_sections(),
            'field_settings'    => WPUF_Form_Builder_Field_Settings::get_field_settings(),
        ) );

        wp_localize_script( 'wpuf-form-builder', 'wpuf_form_builder', $wpuf_form_builder );

        // mixins
        $wpuf_mixins = array(
            'root'           => apply_filters( 'wpuf-form-builder-js-root-mixins', array() ),
            'builder_stage'  => apply_filters( 'wpuf-form-builder-js-builder-stage-mixins', array() ),
        );

        wp_localize_script( 'wpuf-form-builder', 'wpuf_mixins', $wpuf_mixins );
    }

    /**
     * Print scripts in admin head
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_print_scripts() {
        ?>
            <script>
                var wpuf_form_builder_mixins = function(mixins) {
                    if (!mixins || !mixins.length) {
                        return [];
                    }

                    return mixins.map(function (mixin) {
                        return window[mixin];
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
        $path = WPUF_ROOT . '/admin/form-builder/assets/js/components';

        $components = array();

        // directory handle
        $dir = dir( $path );

        while ( $entry = $dir->read() ) {
            if ( $entry !== '.' && $entry !== '..' ) {
               if ( is_dir( $path . '/' . $entry ) ) {
                    $components[] = $entry;
               }
            }
        }

        // html templates of vue components
        $templates = apply_filters( 'wpuf-form-builder-js-templates', $components );

        foreach ( $templates as $template ) {
            $this->include_js_template( $template );
        }
    }

    /**
     * Embed a Vue.js component template
     *
     * @since 2.5
     *
     * @param string $template
     * @param string $file_path
     *
     * @return void
     */
    public function include_js_template( $template, $file_path = '' ) {
        $file_path = $file_path ? untrailingslashit( $file_path ) : WPUF_ROOT . '/admin/form-builder/assets/js/components/' . $template;
        $file = $file_path . '/template.php';

        if ( file_exists( $file ) ) {
            echo '<script type="text/html" id="tmpl-wpuf-' . $template . '">' . "\n";
            include $file;
            echo "\n" . '</script>' . "\n";
        }
    }

    /**
     * Include form builder view template
     *
     * @since 2.5
     *
     * @return void
     */
    public function include_form_builder() {
        $form_type = $this->settings['form_type'];
        include WPUF_ROOT . '/admin/form-builder/views/form-builder.php';
    }

    /**
     * Add Fields panel sections
     *
     * @since 2.5
     *
     * @return array
     */
    private function get_panel_sections() {
        $before_custom_fields = apply_filters( 'wpuf-form-builder-fields-section-before', array() );

        $sections = array_merge( $before_custom_fields, $this->get_custom_fields() );
        $sections = array_merge( $sections, $this->get_others_fields() );

        $after_custom_fields = apply_filters( 'wpuf-form-builder-fields-section-after', array() );

        $sections = array_merge( $sections, $after_custom_fields );

        return $sections;
    }

    /**
     * Custom field section
     *
     * @since 2.5
     *
     * @return array
     */
    private function get_custom_fields() {
        $fields = apply_filters( 'wpuf-form-builder-fields-custom-fields', array(
            'text_field', 'textarea_field', 'dropdown_field', 'multiple_select',
            'radio_field', 'checkbox_field'
        ) );

        return array(
            array(
                'title'     => __( 'Custom Fields', 'wpuf' ),
                'fields'    => $fields
            )
        );
    }

    /**
     * Others field section
     *
     * @since 2.5
     *
     * @return array
     */
    private function get_others_fields() {
        $fields = apply_filters( 'wpuf-form-builder-fields-others-fields', array(
            'text_field', 'textarea_field'
        ) );

        return array(
            array(
                'title'     => __( 'Others', 'wpuf' ),
                'fields'    => $fields
            )
        );
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
            'advanced_options'      => __( 'Advanced Options', 'wpuf' ),
            'delete_field_warn_msg' => __( 'Are you sure you want to delete this field?', 'wpuf' ),
            'yes_delete_it'         => __( 'Yes, delete it', 'wpuf' ),
            'no_cancel_it'          => __( 'No, cancel it', 'wpuf' ),
            'ok'                    => __( 'OK', 'wpuf' ),
            'cancel'                => __( 'Cancel', 'wpuf' ),
            'last_choice_warn_msg'  => __( 'This field must contain at least one choice', 'wpuf' ),
            'option'                => __( 'Option', 'wpuf' ),
        ) );
    }
}
