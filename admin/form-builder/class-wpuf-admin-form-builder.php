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
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_footer', array( $this, 'admin_footer' ) );
            add_action( 'wpuf-admin-form-builder', array( $this, 'include_form_builder' ) );
        }
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

        // css
        wp_enqueue_style( 'wpuf-font-awesome', WPUF_ASSET_URI . '/vendor/font-awesome/css/font-awesome.min.css', array(), WPUF_VERSION );

        $form_builder_css_deps = apply_filters( 'wpuf-form-builder-css-deps', array(
            'wpuf-font-awesome'
        ) );

        wp_enqueue_style( 'wpuf-form-builder', WPUF_ASSET_URI . '/css/wpuf-form-builder.css', $form_builder_css_deps, WPUF_VERSION );

        // js
        wp_enqueue_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue.js', array(), WPUF_VERSION, true );

        $form_builder_js_deps = apply_filters( 'wpuf-form-builder-js-deps', array(
            'jquery', 'wpuf-vue'
        ) );

        wp_enqueue_script( 'wpuf-form-builder', WPUF_ASSET_URI . '/js/wpuf-form-builder.js', $form_builder_js_deps, WPUF_VERSION, true );

        $wpufFormBuilder = array(
            'post' => $post
        );

        wp_localize_script( 'wpuf-form-builder', 'wpufFormBuilder', $wpufFormBuilder );
    }

    /**
     * Include vue component templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_footer() {
        // html templates of vue components
        $templates = apply_filters( 'wpuf-form-builder-js-templates', array(
            'form-fields', 'field-options'
        ) );

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
        $file_path = $file_path ? untrailingslashit( $file_path ) : WPUF_ROOT . '/admin/form-builder/js/components/' . $template;
        $file = $file_path . '/' . $template . '.php';

        if ( file_exists( $file ) ) {
            echo '<script type="text/x-template" id="wpuf-tmpl-' . $template . '">' . "\n";
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
}
