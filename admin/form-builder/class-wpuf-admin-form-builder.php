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

        /*
         * localized data required for the building
         */

        require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-fields.php';

        $wpuf_form_builder = array(
            'post'              => $post,
            'form_fields'       => wpuf_get_form_fields( $post->ID ),
            // 'builder_fields'    =>
        );

        wp_localize_script( 'wpuf-form-builder', 'wpuf_form_builder', $wpuf_form_builder );
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
}
