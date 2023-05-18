<?php

namespace Wp\User\Frontend;

class Assets {
    private $suffix;
    // private $scheme;
    public $form_builder_css_deps = [];

    public function __construct() {
        $this->suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        // $this->scheme = is_ssl() ? 'https' : 'http';

        $this->form_builder_css_deps = apply_filters( 'wpuf-form-builder-css-deps', [
            'wpuf-frontend-forms',
            'wpuf-font-awesome',
            'wpuf-sweetalert2',
            'wpuf-selectize',
            'wpuf-toastr',
            'wpuf-tooltip',
        ] );

        add_action( 'init', [ $this, 'register_all_scripts' ] );
    }

    public function register_all_scripts() {
        $styles  = $this->get_styles();
        $scripts = $this->get_scripts();

        do_action( 'wpuf_before_register_scripts', $scripts, $styles );

        $this->register_styles( $styles );
        $this->register_scripts( $scripts );

        do_action( 'wpuf_after_register_scripts', $scripts, $styles );
    }

    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps    = ! empty( $style['deps'] ) ? $style['deps'] : [];
            $version = ! empty( $style['version'] ) ? $style['version'] : WPUF_VERSION;
            $media   = ! empty( $style['media'] ) ? $style['media'] : 'all';

            wp_register_style( 'wpuf-' . $handle, $style['src'], $deps, $version, $media );
        }
    }

    public function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : [];
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
            $version   = isset( $script['version'] ) ? $script['version'] : WPUF_VERSION;

            wp_register_script( 'wpuf-' . $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    public function get_styles() {
        $styles = [
            'frontend-forms' => [
                'src' => WPUF_ASSET_URI . '/css/frontend-forms.css',
            ],
            'layout1'        => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout1.css',
            ],
            'layout2'        => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout2.css',
            ],
            'layout3'        => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout3.css',
            ],
            'layout4'        => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout4.css',
            ],
            'layout5'        => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout5.css',
            ],
            'jquery-ui'      => [
                'src' => WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css',
                'version' => '1.9.1',
            ],
            'sweetalert2'    => [
                'src' => WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.css',
                'version' => '11.4.19',
            ],
            'font-awesome'   => [
                'src' => WPUF_ASSET_URI . '/vendor/font-awesome/css/font-awesome.min.css',
                'version' => '4.7.0',
            ],
            'selectize'      => [
                'src' => WPUF_ASSET_URI . '/vendor/selectize/css/selectize.default.css',
                'version' => '0.12.4',
            ],
            'toastr'         => [
                'src' => WPUF_ASSET_URI . '/vendor/toastr/toastr.min.css',
                'version' => '2.1.3',
            ],
            'tooltip'        => [
                'src' => WPUF_ASSET_URI . '/vendor/tooltip/tooltip.css',
                'version' => '3.3.7',
            ],
            'form-builder'   => [
                'src'  => WPUF_ASSET_URI . '/css/wpuf-form-builder.css',
                'deps' => $this->form_builder_css_deps,
            ],
            'admin'   => [
                'src'  => WPUF_ASSET_URI . '/css/admin.css',
            ],
        ];

        return apply_filters( 'wpuf_styles_to_register', $styles );
    }

    public function get_scripts() {
//        global $post;
        $form_builder_js_deps = apply_filters( 'wpuf-form-builder-js-deps', [
            'jquery',
            'jquery-ui-sortable',
            'jquery-ui-draggable',
            'jquery-ui-droppable',
            'underscore',
            'wpuf-vue',
            'wpuf-vuex',
            'wpuf-sweetalert2',
            'wpuf-jquery-scrollTo',
            'wpuf-selectize',
            'wpuf-toastr',
            'wpuf-clipboard',
            'wpuf-tooltip',
        ] );
//        /*
//         * Data required for building the form
//         */
//        require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings.php';
//        require_once WPUF_ROOT . '/includes/free/prompt.php';
//        $wpuf_form_builder = apply_filters( 'wpuf-form-builder-localize-script', [
//            'post'              => $post,
//            'form_fields'       => wpuf_get_form_fields( $post->ID ),
//            'field_settings'    => wpuf()->fields->get_js_settings(),
//            'notifications'     => wpuf_get_form_notifications( $post->ID ),
//            'pro_link'          => WPUF_Pro_Prompt::get_pro_url(),
//            'site_url'          => site_url( '/' ),
//            'recaptcha_site'    => wpuf_get_option( 'recaptcha_public', 'wpuf_general' ),
//            'recaptcha_secret'  => wpuf_get_option( 'recaptcha_private', 'wpuf_general' ),
//        ] );
//        $wpuf_form_builder = wpuf_unset_conditional( $wpuf_form_builder );
//        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_form_builder', $wpuf_form_builder );
//        // mixins
//        $wpuf_mixins = [
//            'root'           => apply_filters( 'wpuf-form-builder-js-root-mixins', [] ),
//            'builder_stage'  => apply_filters( 'wpuf-form-builder-js-builder-stage-mixins', [] ),
//            'form_fields'    => apply_filters( 'wpuf-form-builder-js-form-fields-mixins', [] ),
//            'field_options'  => apply_filters( 'wpuf-form-builder-js-field-options-mixins', [] ),
//        ];
//        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_mixins', $wpuf_mixins );
        $scripts = [
            'vue'                     => [
                'src'       => WPUF_ASSET_URI . '/vendor/vue/vue' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '2.2.4',
            ],
            'vuex'                    => [
                'src'       => WPUF_ASSET_URI . '/vendor/vue/vuex' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '2.2.1',
            ],
            'sweetalert2'             => [
                'src'       => WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.js' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '11.4.19',
            ],
            'jquery-scrollTo'         => [
                'src'       => WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo' . $this->suffix . '.js',
                'in_footer' => true,
                'deps'      => [ 'jquery' ],
                'version'      => '11.4.19',
            ],
            'selectize'               => [
                'src'       => WPUF_ASSET_URI . '/vendor/selectize/js/standalone/selectize' . $this->suffix . '.js',
                'in_footer' => true,
                'deps'      => [ 'jquery' ],
                'version'      => '0.12.4',
            ],
            'toastr'                  => [
                'src'       => WPUF_ASSET_URI . '/vendor/toastr/toastr' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '2.1.3',
            ],
            'clipboard'               => [
                'src'       => WPUF_ASSET_URI . '/vendor/clipboard/clipboard' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '1.6.0',
            ],
            'tooltip'                 => [
                'src'       => WPUF_ASSET_URI . '/vendor/tooltip/tooltip' . $this->suffix . '.js',
                'in_footer' => true,
                'version' => '3.3.7',
            ],
            'form-builder-mixins'     => [
                'src'       => WPUF_ASSET_URI . 'js/wpuf-form-builder-mixins.js' . $this->suffix . '.js',
                'deps'      => $form_builder_js_deps,
                'in_footer' => true,
            ],
            'form-builder-components' => [
                'src'       => WPUF_ASSET_URI . 'js/wpuf-form-builder-components.js' . $this->suffix . '.js',
                'deps'      => [ 'wpuf-form-builder-mixins' ],
                'in_footer' => true,
            ],
            'form-builder'            => [
                'src'       => WPUF_ASSET_URI . '/js/wpuf-form-builder.js' . $this->suffix . '.js',
                'deps'      => [ 'wpuf-form-builder-components' ],
                'in_footer' => true,
            ],
            'admin'                   => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-admin.js' . $this->suffix . '.js',
                'deps' => [ 'jquery' ],
            ],
            'subscriptions'                   => [
                'src'  => WPUF_ASSET_URI . '/js/subscriptions.js' . $this->suffix . '.js',
                'deps' => [ 'jquery' ],
                'in_footer' => true,
            ],
        ];

        return apply_filters( 'wpuf_scripts_to_register', $scripts );
    }
}
