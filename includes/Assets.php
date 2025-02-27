<?php

namespace WeDevs\Wpuf;

/**
 * The assets handler for WPUF. All the styles and scripts should register from here first.
 * Then we will enqueue them from the related pages.
 *
 * @since 4.0.0
 */
class Assets {

    /**
     * Suffix for the scripts. add `.min` if we are in production
     *
     * @since 4.0.0
     *
     * @var string
     */
    protected $suffix;
    protected $scheme;

    /**
     * The css dependencies list for form builder
     *
     * @since 4.0.0
     *
     * @var array|mixed|null
     */
    public $form_builder_css_deps = [];

    public function __construct() {
        $this->suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $this->scheme = is_ssl() ? 'https' : 'http';
        $this->form_builder_css_deps = apply_filters(
            'wpuf_form_builder_css_deps',
            [
                'wpuf-frontend-forms',
                'wpuf-font-awesome',
                'wpuf-sweetalert2',
                'wpuf-selectize',
                'wpuf-toastr',
                'wpuf-tooltip',
                'buttons',
            ]
        );
        add_action( 'init', [ $this, 'register_all_scripts' ] );
    }

    /**
     * Register all the css and js from here
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_all_scripts() {
        $styles  = $this->get_styles();
        $scripts = $this->get_scripts();
        do_action( 'wpuf_before_register_scripts', $scripts, $styles );
        $this->register_styles( $styles );
        $this->register_scripts( $scripts );
        do_action( 'wpuf_after_register_scripts', $scripts, $styles );
    }

    /**
     * Register the CSS from here. Need to define the JS first from get_styles()
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps    = ! empty( $style['deps'] ) ? $style['deps'] : [];
            $version = ! empty( $style['version'] ) ? $style['version'] : WPUF_VERSION;
            $media   = ! empty( $style['media'] ) ? $style['media'] : 'all';

            wp_register_style( 'wpuf-' . $handle, $style['src'], $deps, $version, $media );
        }
    }

    /**
     * Register the JS from here. Need to define the JS first from get_scripts()
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = ! empty( $script['deps'] ) ? $script['deps'] : [];
            $in_footer = ! empty( $script['in_footer'] ) ? $script['in_footer'] : true;
            $version   = ! empty( $script['version'] ) ? $script['version'] : WPUF_VERSION;

            wp_register_script( 'wpuf-' . $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Returns the list of styles
     *
     * @since 4.0.0
     *
     * @return mixed|null
     */
    public function get_styles() {
        $styles = [
            'frontend-forms'     => [
                'src' => WPUF_ASSET_URI . '/css/frontend-forms.css',
            ],
            'layout1'            => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout1.css',
            ],
            'layout2'            => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout2.css',
            ],
            'layout3'            => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout3.css',
            ],
            'layout4'            => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout4.css',
            ],
            'layout5'            => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout5.css',
            ],
            'jquery-ui'          => [
                'src'     => WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css',
                'version' => '1.9.1',
            ],
            'sweetalert2'        => [
                'src'     => WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2.css',
                'version' => '11.4.19',
            ],
            'font-awesome'       => [
                'src'     => WPUF_ASSET_URI . '/vendor/font-awesome/css/font-awesome.min.css',
                'version' => '4.7.0',
            ],
            'selectize'          => [
                'src'     => WPUF_ASSET_URI . '/vendor/selectize/css/selectize.default.css',
                'version' => '0.12.4',
            ],
            'toastr'             => [
                'src'     => WPUF_ASSET_URI . '/vendor/toastr/toastr.min.css',
                'version' => '2.1.3',
            ],
            'tooltip'            => [
                'src'     => WPUF_ASSET_URI . '/vendor/tooltip/tooltip.css',
                'version' => '3.3.7',
            ],
            'form-builder'       => [
                'src'  => WPUF_ASSET_URI . '/css/wpuf-form-builder.css',
                'deps' => $this->form_builder_css_deps,
            ],
            'admin'              => [
                'src' => WPUF_ASSET_URI . '/css/admin.css',
            ],
            'admin-subscriptions'              => [
                'src' => WPUF_ASSET_URI . '/css/admin/subscriptions.min.css',
            ],
            'registration-forms' => [
                'src' => WPUF_ASSET_URI . '/css/registration-forms.css',
            ],
            'module'             => [
                'src' => WPUF_ASSET_URI . '/css/admin/wpuf-module.css',
            ],
            'swiffy-slider'      => [
                'src'     => WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider.min.css',
                'version' => '1.6.0',
            ],
            'setup'              => [
                'src'  => WPUF_ASSET_URI . '/css/admin/wpuf-setup.css',
                'deps' => [ 'dashicons', 'install' ],
            ],
            'whats-new'          => [
                'src'  => WPUF_ASSET_URI . '/css/admin/whats-new.css',
            ],
        ];

        return apply_filters( 'wpuf_styles_to_register', $styles );
    }

    /**
     * Returns the list of JS
     *
     * @since 4.0.0
     *
     * @return mixed|null
     */
    public function get_scripts() {
        $this->scheme         = is_ssl() ? 'https' : 'http';
        $api_key              = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );
        $form_builder_js_deps = apply_filters(
            'wpuf_form_builder_js_deps',
            [
                'jquery',
                'jquery-ui-sortable',
                'jquery-ui-draggable',
                'jquery-ui-droppable',
                'jquery-ui-resizable',
                'underscore',
                'wpuf-vue',
                'wpuf-vuex',
                'wpuf-sweetalert2',
                'wpuf-jquery-scrollTo',
                'wpuf-selectize',
                'wpuf-toastr',
                'wpuf-clipboard',
                'wpuf-tooltip',
            ]
        );
        $scripts = [
            'vue'                      => [
                'src'       => WPUF_ASSET_URI . '/vendor/vue/vue' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '2.2.4',
            ],
            'vue-3'                    => [
                'src'       => WPUF_ASSET_URI . '/vendor/vue-3/vue.esm-browser.js',
                'in_footer' => true,
                'version'   => '3.4.19',
            ],
            'vuex'                     => [
                'src'       => WPUF_ASSET_URI . '/vendor/vuex/vuex' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '2.2.1',
            ],
            'sweetalert2'              => [
                'src'       => WPUF_ASSET_URI . '/vendor/sweetalert2/dist/sweetalert2' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '11.4.19',
                'deps'      => [ 'jquery' ],
            ],
            'jquery-scrollTo'          => [
                'src'       => WPUF_ASSET_URI . '/vendor/jquery.scrollTo/jquery.scrollTo' . $this->suffix . '.js',
                'in_footer' => true,
                'deps'      => [ 'jquery' ],
                'version'   => '11.4.19',
            ],
            'selectize'                => [
                'src'       => WPUF_ASSET_URI . '/vendor/selectize/js/standalone/selectize' . $this->suffix . '.js',
                'in_footer' => true,
                'deps'      => [ 'jquery' ],
                'version'   => '0.12.4',
            ],
            'toastr'                   => [
                'src'       => WPUF_ASSET_URI . '/vendor/toastr/toastr' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '2.1.3',
            ],
            'clipboard'                => [
                'src'       => WPUF_ASSET_URI . '/vendor/clipboard/clipboard' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '1.6.0',
            ],
            'tooltip'                  => [
                'src'       => WPUF_ASSET_URI . '/vendor/tooltip/tooltip' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '3.3.7',
            ],
            'form-builder-mixins'      => [
                'src'       => WPUF_ASSET_URI . '/js/wpuf-form-builder-mixins.js',
                'deps'      => $form_builder_js_deps,
                'in_footer' => true,
            ],
            'form-builder-components'  => [
                'src'       => WPUF_ASSET_URI . '/js/wpuf-form-builder-components.js',
                'deps'      => [ 'wpuf-form-builder-mixins' ],
                'in_footer' => true,
            ],
            'form-builder'             => [
                'src'       => WPUF_ASSET_URI . '/js/wpuf-form-builder.js',
                'deps'      => [ 'wpuf-form-builder-components' ],
                'in_footer' => true,
            ],
            'admin'                    => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-admin.js',
                'deps' => [ 'jquery' ],
            ],
            'subscriptions'            => [
                'src'       => WPUF_ASSET_URI . '/js/subscriptions-old.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true,
            ],
            'admin-subscriptions'      => [
                'src'       => WPUF_ASSET_URI . '/js/subscriptions.min.js',
                'in_footer' => true,
            ],
            'timepicker'               => [
                'src'       => WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js',
                'deps'      => [ 'jquery-ui-datepicker' ],
                'version'   => '1.2',
            ],
            'form-builder-wpuf-forms'  => [
                'src'       => WPUF_ASSET_URI . '/js/wpuf-form-builder-wpuf-forms.js',
                'deps'      => [ 'jquery', 'underscore', 'wpuf-vue', 'wpuf-vuex' ],
                'in_footer' => true,
            ],
            'registration-forms'       => [
                'src'       => WPUF_ASSET_URI . '/js/registration-forms.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true,
            ],
            'module'                   => [
                'src'       => WPUF_ASSET_URI . '/js/admin/wpuf-module.js',
                'deps'      => [ 'wpuf-swiffy-slider', 'wpuf-swiffy-slider-extensions' ],
                'in_footer' => true,
            ],
            'swiffy-slider'            => [
                'src'       => WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => '1.6.0',
                'in_footer' => true,
            ],
            'swiffy-slider-extensions' => [
                'src'       => WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider-extensions.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => '1.6.0',
                'in_footer' => true,
            ],
            'admin-shortcode'          => [
                'src'  => WPUF_ASSET_URI . '/js/admin-shortcode.js',
                'deps' => [ 'jquery' ],
            ],
            'billing-address'          => [
                'src'  => WPUF_ASSET_URI . '/js/billing-address.js',
                'deps' => [ 'jquery' ],
            ],
            'metabox-tabs'             => [
                'src'  => WPUF_ASSET_URI . '/js/metabox-tabs.js',
                'deps' => [ 'jquery' ],
            ],
            'admin-tools'              => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-admin-tools.js',
                'deps' => [ 'jquery', 'wpuf-vue' ],
            ],
            'settings'                 => [
                'src' => WPUF_ASSET_URI . '/js/admin/settings.js',
            ],
            'ajax-script'              => [
                'src'  => WPUF_ASSET_URI . '/js/billing-address.js',
                'deps' => [ 'jquery' ],
            ],
            'jquery-blockui'           => [
                'src'     => WPUF_ASSET_URI . '/js/jquery-blockui/jquery.blockUI.min.js',
                'deps'    => [ 'jquery' ],
                'version' => '2.70',
            ],
            'selectWoo'                => [
                'src'     => WPUF_ASSET_URI . '/js/selectWoo/selectWoo.full.min.js',
                'deps'    => [ 'jquery' ],
                'version' => '1.0.1',
            ],
            'enhanced-select'          => [
                'src'  => WPUF_ASSET_URI . '/js/admin/wpuf-enhanced-select' . $this->suffix . '.min.js',
                'deps' => [ 'jquery', 'selectWoo' ],
            ],
            'setup'                    => [
                'src'  => WPUF_ASSET_URI . '/js/admin/wpuf-setup' . $this->suffix . '.js',
                'deps' => [ 'jquery', 'wpuf-enhanced-select', 'jquery-blockui' ],
            ],
            'frontend-form'            => [
                'src'  => WPUF_ASSET_URI . '/js/frontend-form' . $this->suffix . '.js',
                'deps' => [ 'jquery' ],
            ],
            'upload'                   => [
                'src'  => WPUF_ASSET_URI . '/js/upload' . $this->suffix . '.js',
                'deps' => [ 'jquery', 'plupload-handlers', 'jquery-ui-sortable' ],
            ],
            'ajax_login'               => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-login-widget.js',
                'deps' => [ 'jquery' ],
            ],
            'headway'                  => [
                'src'  => '//cdn.headwayapp.co/widget.js',
            ],
            'turnstile'                  => [
                'src'  => 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback',
            ],
        ];

        if ( ! empty( $api_key ) ) {
            $scripts['google-maps'] = [
                'src' => $this->scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key,
            ];
        }

        return apply_filters( 'wpuf_scripts_to_register', $scripts );
    }
}
