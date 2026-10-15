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
                'wpuf-tooltip',
                'buttons',
            ]
        );
        add_action( 'init', [ $this, 'register_all_scripts' ] );
        // The React stylesheet switch and body classes are hooked by Admin\Bundles.
    }

    /**
     * React admin assets (Admin\Bundles).
     *
     * @since WPUF_SINCE
     *
     * @return \WeDevs\Wpuf\Admin\Bundles
     */
    protected function react() {
        return wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Bundles::class );
    }

    /**
     * Whether the current admin page is a React forms list or builder.
     *
     * @since WPUF_SINCE Forwards to Admin\Bundles.
     *
     * @return bool
     */
    protected function is_forms_page() {
        return $this->react()->is_forms_page();
    }

    /**
     * Serve the React forms stylesheet under the old handles.
     *
     * @since WPUF_SINCE Forwards to Admin\Bundles (which hooks it).
     *
     * @param string $src    Stylesheet URL
     * @param string $handle Handle
     *
     * @return string|false
     */
    public function use_forms_styles( $src, $handle = '' ) {
        return $this->react()->use_forms_styles( $src, $handle );
    }

    /**
     * Body classes of the React forms list and builder.
     *
     * @since WPUF_SINCE Forwards to Admin\Bundles (which hooks it).
     *
     * @param string $classes Admin body classes
     *
     * @return string
     */
    public function forms_body_class( $classes ) {
        return $this->react()->forms_body_class( $classes );
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

        // The shared admin components (window.wpuf.components) print their own
        // strings, so they load translations like the screens do.
        $this->react()->set_translations();

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
            $version = ! empty( $style['version'] ) ? $style['version'] : $this->local_file_version( $style['src'] );
            $media   = ! empty( $style['media'] ) ? $style['media'] : 'all';

            wp_register_style( 'wpuf-' . $handle, $this->existing_src( $style['src'] ), $deps, $version, $media );

            if ( ! empty( $style['rtl'] ) ) {
                wp_style_add_data( 'wpuf-' . $handle, 'rtl', 'replace' );
            }
        }
    }

    /**
     * Version of a plugin asset without its own: the file's modification time,
     * so a rebuilt sheet reaches the browser without a version bump; the plugin
     * version for anything else.
     *
     * @since WPUF_SINCE
     *
     * @param string $src Asset URL.
     *
     * @return string
     */
    protected function local_file_version( $src ) {
        if ( is_string( $src ) && 0 === strpos( $src, WPUF_ASSET_URI . '/' ) ) {
            $path = WPUF_ROOT . '/assets/' . substr( $src, strlen( WPUF_ASSET_URI . '/' ) );

            if ( file_exists( $path ) ) {
                return (string) filemtime( $path );
            }
        }

        return WPUF_VERSION;
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

            wp_register_script( 'wpuf-' . $handle, $this->existing_src( $script['src'] ), $deps, $version, $in_footer );
        }
    }

    /**
     * Source URL for a handle, or false when it points to a plugin file that is
     * not on disk (e.g. a removed legacy build). The handle is then registered
     * without a file: dependents and localized data keep working and the
     * browser does not load an HTML 404 page as a script or stylesheet.
     *
     * @since WPUF_SINCE
     *
     * @param string|false $src Source URL.
     *
     * @return string|false
     */
    protected function existing_src( $src ) {
        if ( ! is_string( $src ) || 0 !== strpos( $src, WP_PLUGIN_URL ) ) {
            return $src;
        }

        $path = WP_PLUGIN_DIR . strtok( substr( $src, strlen( WP_PLUGIN_URL ) ), '?' );

        return file_exists( $path ) ? $src : false;
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
            'frontend-forms'      => [
                'src' => WPUF_ASSET_URI . '/css/frontend-forms.css',
            ],
        ];

        // React screen stylesheets (Admin\Bundles), at their old place in the list.
        $styles = array_merge( $styles, $this->react()->styles() );

        $styles += [
            'elementor-frontend-forms'      => [
                'src' => WPUF_ASSET_URI . '/css/elementor-frontend-forms.css',
            ],
            'elementor-subscription-plans'      => [
                'src' => WPUF_ASSET_URI . '/css/elementor-subscription-plans.css',
            ],
            'frontend-subscriptions' => [
                'src' => WPUF_ASSET_URI . '/css/frontend-subscriptions.min.css',
            ],
            'layout1'             => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout1.css',
            ],
            'layout2'             => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout2.css',
            ],
            'layout3'             => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout3.css',
            ],
            'layout4'             => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout4.css',
            ],
            'layout5'             => [
                'src' => WPUF_ASSET_URI . '/css/frontend-form/layout5.css',
            ],
            'jquery-ui'           => [
                'src'     => WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css',
                'version' => '1.9.1',
            ],
            'sweetalert2'        => [
                'src'     => WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.css',
                'version' => '11.4.8',
            ],
            'font-awesome'        => [
                'src'     => WPUF_ASSET_URI . '/vendor/font-awesome/css/all.min.css',
                'version' => '7.0.1',
            ],
            'selectize'           => [
                'src'     => WPUF_ASSET_URI . '/vendor/selectize/css/selectize.default.css',
                'version' => '0.12.4',
            ],
            'tooltip'             => [
                'src'     => WPUF_ASSET_URI . '/vendor/tooltip/tooltip.css',
                'version' => '3.3.7',
            ],
            'form-builder'        => [
                'src'  => WPUF_ASSET_URI . '/css/wpuf-form-builder.css',
                'deps' => $this->form_builder_css_deps,
            ],
            // React builder sheet (tools/admin-css), under the old handle that
            // Pro and modules depend on.
            'admin-form-builder'  => [
                'src'  => WPUF_ASSET_URI . '/css/admin/forms-screen.css',
                'deps' => $this->form_builder_css_deps,
                'rtl'  => true,
            ],
            'admin'               => [
                'src' => WPUF_ASSET_URI . '/css/admin.css',
            ],
            'module'              => [
                'src' => WPUF_ASSET_URI . '/css/admin/wpuf-module.css',
            ],
            'swiffy-slider'       => [
                'src'     => WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider.min.css',
                'version' => '1.6.0',
            ],
            // React forms list sheet (tools/admin-css), under the old handle.
            'forms-list'           => [
                'src' => WPUF_ASSET_URI . '/css/admin/forms-list-screen.css',
                'rtl' => true,
            ],
            'account'              => [
                'src' => WPUF_ASSET_URI . '/css/frontend/account.css',
            ],
        ];

        return apply_filters( 'wpuf_styles_to_register', $styles );
    }

    /**
     * Dependencies and version of a React bundle from its generated
     * `assets/js/{name}.min.asset.php`, or the fallbacks when it is missing.
     *
     * @since WPUF_SINCE
     *
     * @param string $name         Bundle name
     * @param array  $dependencies Fallback dependencies
     *
     * @return array { dependencies, version }
     */
    protected function bundle_asset( $name, $dependencies ) {
        return $this->react()->bundle_asset( $name, $dependencies );
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

        $scripts = [
            'sweetalert2'              => [
                'src'       => WPUF_ASSET_URI . '/vendor/sweetalert2/sweetalert2.js',
                'in_footer' => true,
                'version'   => '11.4.8',
                'deps'      => [ 'jquery' ],
            ],
            'selectize'                => [
                'src'       => WPUF_ASSET_URI . '/vendor/selectize/js/standalone/selectize' . $this->suffix . '.js',
                'in_footer' => true,
                'deps'      => [ 'jquery' ],
                'version'   => '0.12.4',
            ],
            'tooltip'                  => [
                'src'       => WPUF_ASSET_URI . '/vendor/tooltip/tooltip' . $this->suffix . '.js',
                'in_footer' => true,
                'version'   => '3.3.7',
            ],
            // REST client of the classic scripts (wpuf/v1 with the wp_rest nonce, plain fetch).
            'rest'                     => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-rest.js',
                'deps' => [],
            ],
            'admin'                    => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-admin.js',
                'deps' => [ 'jquery', 'wpuf-rest' ],
            ],
            'subscriptions'            => [
                'src'       => WPUF_ASSET_URI . '/js/subscriptions-old.js',
                'deps'      => [ 'jquery', 'wpuf-rest' ],
                'in_footer' => true,
            ],
            // Alias of the React subscriptions script: data localized or inline
            // scripts added on this old handle still print on the page.
            'admin-subscriptions'      => [
                'src'       => false,
                'deps'      => [ 'wpuf-subscriptions-screen' ],
                'in_footer' => true,
            ],
            'timepicker'               => [
                'src'       => WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js',
                'deps'      => [ 'jquery-ui-datepicker' ],
                'version'   => '1.2',
            ],
            'registration-forms'       => [
                'src'       => WPUF_ASSET_URI . '/js/registration-forms.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true,
            ],
            'module'                   => [
                'src'       => WPUF_ASSET_URI . '/js/admin/wpuf-module.js',
                'deps'      => [ 'wpuf-swiffy-slider', 'wpuf-swiffy-slider-extensions', 'wpuf-rest' ],
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
                'deps' => [ 'jquery', 'wpuf-rest' ],
            ],
            'ajax-script'              => [
                'src'  => WPUF_ASSET_URI . '/js/billing-address.js',
                'deps' => [ 'jquery', 'wpuf-rest' ],
            ],
            'jquery-blockui'           => [
                'src'     => WPUF_ASSET_URI . '/js/jquery-blockui/jquery.blockUI.min.js',
                'deps'    => [ 'jquery' ],
                'version' => '2.70',
            ],
            'frontend-form'            => [
                'src'  => WPUF_ASSET_URI . '/js/frontend-form' . $this->suffix . '.js',
                'deps' => [ 'jquery', 'wpuf-rest' ],
            ],
            'account'                  => [
                'src'       => WPUF_ASSET_URI . '/js/account' . $this->suffix . '.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true,
            ],
            'upload'                   => [
                'src'  => WPUF_ASSET_URI . '/js/upload' . $this->suffix . '.js',
                'deps' => [ 'jquery', 'plupload-handlers', 'jquery-ui-sortable', 'wpuf-rest' ],
            ],
            'ajax_login'               => [
                'src'  => WPUF_ASSET_URI . '/js/wpuf-login-widget.js',
                'deps' => [ 'jquery', 'wpuf-rest' ],
            ],
            'turnstile'                  => [
                'src'  => 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback',
            ],
            'headway-script'         => [
                'src' => WPUF_ASSET_URI . '/vendor/headway.js',
                'deps' => [ 'jquery' ],
            ],
        ];

        // React admin bundles (Admin\Bundles), at their old place in the list, so
        // every screen enqueues by handle and Pro can depend on them.
        $scripts = array_merge( $scripts, $this->react()->scripts() );

        $scripts += [
            'frontend-subscriptions' => [
                'src'       => WPUF_ASSET_URI . '/js/frontend-subscriptions.min.js',
                'in_footer' => true,
            ],
            'elementor-subscription-plans' => [
                'src'       => WPUF_ASSET_URI . '/js/elementor-subscription-plans.js',
                'in_footer' => true,
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
