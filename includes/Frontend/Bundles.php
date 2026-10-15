<?php
/**
 * Frontend apps assets registry
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend;

/**
 * Registry of the frontend React apps: the runtime (the kit on
 * `window.wpuf.frontend` and its base sheet) and the apps (forms, account,
 * login), with dependencies and versions from their generated
 * `assets/js/frontend/*.asset.php`. Frontend\Renderer enqueues them, only on a
 * page that mounts one.
 *
 * The legacy `WeDevs\Wpuf\Assets` registry still registers every handle (one
 * list, the `wpuf_scripts_to_register` / `wpuf_styles_to_register` filters,
 * same order): it merges these entries in after the admin ones (Admin\Bundles).
 *
 * @since WPUF_SINCE Moved out of Admin\Bundles.
 */
class Bundles {

    /**
     * Dependencies and version of a frontend bundle from its generated
     * `assets/js/frontend/{name}.asset.php`, or the fallbacks when it is not built.
     *
     * @since WPUF_SINCE
     *
     * @param string $name         Bundle name
     * @param array  $dependencies Fallback dependencies
     *
     * @return array { dependencies, version }
     */
    public function asset( $name, $dependencies ) {
        $file = WPUF_ROOT . '/assets/js/frontend/' . $name . '.asset.php';

        return file_exists( $file )
            ? require $file
            : [
                'dependencies' => $dependencies,
                'version'      => WPUF_VERSION,
            ];
    }

    /**
     * Frontend app scripts, keyed by handle without the `wpuf-` prefix, in
     * registration order.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function scripts() {
        $frontend_deps    = [ 'wp-element', 'wp-i18n', 'wp-api-fetch', 'wp-hooks', 'react-jsx-runtime' ];
        $frontend_runtime = $this->asset( 'runtime', $frontend_deps );
        $frontend_forms   = $this->asset( 'forms', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_account = $this->asset( 'account-page', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_login   = $this->asset( 'login', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_plans   = $this->asset( 'plans', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_pay     = $this->asset( 'checkout', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );

        return [
            'frontend-runtime'          => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/runtime.js',
                // wp-api-request prints wpApiSettings (REST root + nonce) for the upload XHR;
                // wpuf-dialog is the kit's confirm / alert.
                'deps'      => array_values( array_unique( array_merge( $frontend_runtime['dependencies'], [ 'wp-api-request', 'wpuf-dialog' ] ) ) ),
                'version'   => $frontend_runtime['version'],
                'in_footer' => true,
            ],
            'frontend-forms'            => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/forms.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-frontend-runtime' ], $frontend_forms['dependencies'] ) ) ),
                'version'   => $frontend_forms['version'],
                'in_footer' => true,
            ],
            'frontend-account'          => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/account-page.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-frontend-runtime' ], $frontend_account['dependencies'] ) ) ),
                'version'   => $frontend_account['version'],
                'in_footer' => true,
            ],
            'frontend-login'            => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/login.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-frontend-runtime' ], $frontend_login['dependencies'] ) ) ),
                'version'   => $frontend_login['version'],
                'in_footer' => true,
            ],
            // Subscription packs and the user's subscription card (`[wpuf_sub_pack]`, `[wpuf_sub_info]`).
            'frontend-plans'            => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/plans.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-frontend-runtime' ], $frontend_plans['dependencies'] ) ) ),
                'version'   => $frontend_plans['version'],
                'in_footer' => true,
            ],
            // The pay page's checkout. jQuery and wpuf-rest load with it for the
            // gateways' own scripts (Pro Stripe), as the classic bundle loaded them.
            'frontend-checkout'         => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/checkout.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-frontend-runtime', 'jquery', 'wpuf-rest' ], $frontend_pay['dependencies'] ) ) ),
                'version'   => $frontend_pay['version'],
                'in_footer' => true,
            ],
        ];
    }

    /**
     * The @font-face rules of the admin app's font (Plus Jakarta Sans, shipped
     * in assets/fonts), so the frontend apps use the same type as the admin.
     *
     * @since WPUF_SINCE
     *
     * @return string CSS
     */
    private function font_face() {
        $base = WPUF_ASSET_URI . '/fonts/plus-jakarta-sans/';
        $css  = '';
        $sets = [
            'latin-ext' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF',
            'latin'     => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD',
        ];

        foreach ( $sets as $set => $range ) {
            $css .= sprintf(
                '@font-face{font-family:"Plus Jakarta Sans";font-style:normal;font-weight:400 800;font-display:swap;src:url("%1$s") format("woff2");unicode-range:%2$s;}',
                esc_url( $base . 'PlusJakartaSans-' . $set . '.woff2' ),
                $range
            );
        }

        return $css;
    }

    /**
     * Frontend app stylesheets, keyed by handle without the `wpuf-` prefix,
     * in registration order.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function styles() {
        return [
            'frontend-runtime-style' => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/runtime.css',
                // The admin app's font (assets/fonts), for the frontend apps' --wpuf-fe-font.
                'inline'  => $this->font_face(),
                'version' => $this->css_version( 'css/frontend/runtime.css' ),
                'rtl'     => true,
            ],
            'frontend-forms-style'   => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/forms.css',
                'version' => $this->css_version( 'css/frontend/forms.css' ),
                'rtl'     => true,
            ],
            'frontend-account-style' => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/account-page.css',
                'version' => $this->css_version( 'css/frontend/account-page.css' ),
                'rtl'     => true,
            ],
            'frontend-login-style'   => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/login.css',
                'version' => $this->css_version( 'css/frontend/login.css' ),
                'rtl'     => true,
            ],
            'frontend-plans-style'   => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/plans.css',
                'version' => $this->css_version( 'css/frontend/plans.css' ),
                'rtl'     => true,
            ],
            'frontend-checkout-style' => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/checkout.css',
                'version' => $this->css_version( 'css/frontend/checkout.css' ),
                'rtl'     => true,
            ],
            // The classic account sheet scoped to the PHP sections of the React account (tools/frontend-css/account-sections.mjs).
            'frontend-account-sections-style' => [
                'src'     => WPUF_ASSET_URI . '/css/frontend/account-sections.css',
                'version' => $this->css_version( 'css/frontend/account-sections.css' ),
            ],
        ];
    }

    /**
     * Version of a sheet: its file's modification time (a sheet can be
     * rebuilt without its script), else the plugin version.
     *
     * @since WPUF_SINCE
     *
     * @param string $path Path under assets/.
     *
     * @return string
     */
    private function css_version( $path ) {
        $file = WPUF_ROOT . '/assets/' . $path;

        return file_exists( $file ) ? (string) filemtime( $file ) : WPUF_VERSION;
    }
}
