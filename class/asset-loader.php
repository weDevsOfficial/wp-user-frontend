<?php

/**
 * The Asset Loader Class
 */
class WPUF_Assets {

    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
    }

    /**
     * Register the scripts
     *
     * @return void
     */
    public function register_scripts() {
        global $post;
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $scheme = is_ssl() ? 'https' : 'http';
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        if ( !empty( $api_key ) ) {
            wp_register_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key, array(), null );
        }
        if ( isset ( $post->ID ) ) {
            ?>
            <script type="text/javascript" id="wpuf-language-script">
                var error_str_obj = {
                    'required' : '<?php esc_attr_e( 'is required', 'wpuf' ); ?>',
                    'mismatch' : '<?php esc_attr_e( 'does not match', 'wpuf' ); ?>',
                    'validation' : '<?php esc_attr_e( 'is not valid', 'wpuf' ); ?>'
                }
            </script>
            <?php
            wp_register_script( 'wpuf-form', WPUF_ASSET_URI . '/js/frontend-form' . $prefix . '.js', array('jquery') );
        }
        wp_register_script( 'wpuf-subscriptions', WPUF_ASSET_URI . '/js/subscriptions.js', array('jquery'), false, true );
        wp_register_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker') );
        wp_register_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', array('jquery', 'plupload-handlers') );

        wp_localize_script( 'wpuf-form', 'wpuf_frontend', array(
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'error_message' => __( 'Please fix the errors to proceed', 'wpuf' ),
            'nonce'         => wp_create_nonce( 'wpuf_nonce' ),
            'word_limit'    => __( 'Word limit reached', 'wpuf' )
        ) );

        wp_localize_script( 'wpuf-upload', 'wpuf_frontend_upload', array(
            'confirmMsg' => __( 'Are you sure?', 'wpuf' ),
            'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'plupload'   => array(
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => array(array('title' => __( 'Allowed Files', 'wpuf' ), 'extensions' => '*')),
                'multipart'        => true,
                'urlstream_upload' => true,
                'warning'          => __( 'Maximum number of files reached!', 'wpuf' ),
                'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wpuf' ),
                'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wpuf' )
            )
        ));

        wp_register_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_register_script( 'wpuf-vuex', WPUF_ASSET_URI . '/vendor/vuex/vuex' . $prefix . '.js', array( 'wpuf-vue' ), WPUF_VERSION, true );
        wp_register_script( 'wpuf-sweetalert2', WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.js', array(), WPUF_VERSION, true );
        wp_register_script( 'wpuf-jquery-scrollTo', WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo' . $prefix . '.js', array( 'jquery' ), WPUF_VERSION, true );
        wp_register_script( 'wpuf-selectize', WPUF_ASSET_URI . '/vendor/selectize/js/standalone/selectize' . $prefix . '.js', array( 'jquery' ), WPUF_VERSION, true );
        wp_register_script( 'wpuf-toastr', WPUF_ASSET_URI . '/vendor/toastr/toastr' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_register_script( 'wpuf-clipboard', WPUF_ASSET_URI . '/vendor/clipboard/clipboard' . $prefix . '.js', array(), WPUF_VERSION, true );
        wp_register_script( 'wpuf-tooltip', WPUF_ASSET_URI . '/vendor/tooltip/tooltip' . $prefix . '.js', array(), WPUF_VERSION, true );
    }

    /**
     * Register the styles
     *
     * @return void
     */
    public function register_styles() {
        wp_register_style( 'wpuf-css', WPUF_ASSET_URI . '/css/frontend-forms.css' );
        wp_register_style( 'jquery-ui', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );
    }
}