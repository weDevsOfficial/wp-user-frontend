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
        ];
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
