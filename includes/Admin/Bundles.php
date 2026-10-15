<?php
/**
 * React admin assets registry
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Platform\Contracts\Hookable;

/**
 * Registry of the React admin bundles: the shared layer (`wpuf-admin-runtime`,
 * `wpuf-admin-ui`) and the screen bundles (forms list, builder, settings,
 * subscriptions), with dependencies and versions from their generated
 * `*.asset.php`, plus the React screens' stylesheet switch and body classes.
 *
 * The legacy `WeDevs\Wpuf\Assets` registry still registers every handle (one
 * list, the `wpuf_scripts_to_register` / `wpuf_styles_to_register` filters,
 * same order): it merges these entries in. Old handles stay there.
 *
 * @since WPUF_SINCE Named Admin\Assets until the clean-up (the legacy registry is WeDevs\Wpuf\Assets).
 */
class Bundles implements Hookable {

    /**
     * Hook the stylesheet switch and body classes. Translations are set by the
     * legacy registry right after registration (set_translations()).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        add_filter( 'style_loader_src', [ $this, 'use_forms_styles' ], 10, 2 );
        add_filter( 'admin_body_class', [ $this, 'forms_body_class' ] );
    }

    /**
     * Dependencies and version of a React bundle from its generated
     * `assets/js/screens/{name}.asset.php`, or the fallbacks when it is missing.
     *
     * @since WPUF_SINCE
     *
     * @param string $name         Bundle name
     * @param array  $dependencies Fallback dependencies
     *
     * @return array { dependencies, version }
     */
    public function bundle_asset( $name, $dependencies ) {
        $file = WPUF_ROOT . '/assets/js/screens/' . $name . '.asset.php';

        return file_exists( $file )
            ? require $file
            : [
                'dependencies' => $dependencies,
                'version'      => WPUF_VERSION,
            ];
    }

    /**
     * React bundle scripts, keyed by handle without the `wpuf-` prefix, in
     * registration order.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function scripts() {
        $forms_list_asset_file = WPUF_ROOT . '/assets/js/screens/forms-list-screen.asset.php';
        $forms_list_asset      = file_exists( $forms_list_asset_file ) ? require $forms_list_asset_file : [
			'dependencies' => [],
			'version' => WPUF_VERSION,
		];
        $settings_asset        = $this->bundle_asset( 'settings-screen', [ 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-i18n', 'wp-hooks', 'wp-components', 'wpuf-admin-runtime' ] );
        $subscriptions_asset   = $this->bundle_asset( 'subscriptions', [ 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-i18n', 'wp-hooks', 'wp-components', 'wp-primitives', 'wpuf-admin-runtime' ] );
        $form_builder_asset    = $this->bundle_asset( 'form-builder', [] );
        $ai_form_builder_asset = $this->bundle_asset( 'ai-form-builder', [ 'wp-element', 'wp-i18n', 'wp-hooks', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $admin_app_asset       = $this->bundle_asset( 'admin-app', [ 'wp-element', 'wp-hooks', 'wp-i18n', 'wpuf-admin-runtime' ] );
        $reg_promo_asset       = $this->bundle_asset( 'registration-promo', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $onboarding_asset      = $this->bundle_asset( 'onboarding', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $welcome_asset         = $this->bundle_asset( 'welcome', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $help_asset            = $this->bundle_asset( 'help', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $tools_asset           = $this->bundle_asset( 'tools', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $transactions_asset    = $this->bundle_asset( 'transactions', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        $coupons_promo_asset   = $this->bundle_asset( 'coupons-promo', [ 'wp-element', 'wp-i18n', 'wpuf-admin-runtime', 'wpuf-admin-ui' ] );
        // The shared React admin layer (design.md D24): screens and Pro get
        // these as dependencies through the `@wpuf/*` / plugin-ui externals.
        $admin_runtime_asset = $this->bundle_asset( 'admin-runtime', [ 'wp-api-fetch', 'wp-element', 'wp-hooks', 'wp-url' ] );

        // The frontend apps (Frontend\Renderer enqueues them, only on a page that mounts one).
        $frontend_deps    = [ 'wp-element', 'wp-i18n', 'wp-api-fetch', 'wp-hooks', 'react-jsx-runtime' ];
        $frontend_runtime = $this->frontend_asset( 'runtime', $frontend_deps );
        $frontend_forms   = $this->frontend_asset( 'forms', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_account = $this->frontend_asset( 'account-page', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $frontend_login   = $this->frontend_asset( 'login', array_merge( $frontend_deps, [ 'wpuf-frontend-runtime' ] ) );
        $admin_ui_asset      = $this->bundle_asset( 'admin-ui', [ 'react', 'react-dom', 'react-jsx-runtime', 'wp-components', 'wp-element', 'wp-i18n', 'wp-plugins' ] );

        return [
            'forms-list-screen'          => [
                'src'       => WPUF_ASSET_URI . '/js/screens/forms-list-screen.js',
                'deps'      => $forms_list_asset['dependencies'],
                'version'   => $forms_list_asset['version'],
                'in_footer' => true,
            ],
            'settings-screen'            => [
                'src'       => WPUF_ASSET_URI . '/js/screens/settings-screen.js',
                'deps'      => $settings_asset['dependencies'],
                'version'   => $settings_asset['version'],
                'in_footer' => true,
            ],
            'subscriptions-screen' => [
                'src'       => WPUF_ASSET_URI . '/js/screens/subscriptions.js',
                'deps'      => $subscriptions_asset['dependencies'],
                'version'   => $subscriptions_asset['version'],
                'in_footer' => true,
            ],
            'admin-runtime'             => [
                'src'       => WPUF_ASSET_URI . '/js/screens/admin-runtime.js',
                'deps'      => $admin_runtime_asset['dependencies'],
                'version'   => $admin_runtime_asset['version'],
                'in_footer' => true,
            ],
            // @wedevs/plugin-ui, loaded once; the page also needs the wp-components style.
            'admin-ui'                  => [
                'src'       => WPUF_ASSET_URI . '/js/screens/admin-ui.js',
                'deps'      => array_values( array_unique( array_merge( [ 'wpuf-admin-runtime' ], $admin_ui_asset['dependencies'] ) ) ),
                'version'   => $admin_ui_asset['version'],
                'in_footer' => true,
            ],
            'form-builder-screen'        => [
                'src'       => WPUF_ASSET_URI . '/js/screens/form-builder.js',
                'deps'      => $form_builder_asset['dependencies'],
                'version'   => $form_builder_asset['version'],
                'in_footer' => true,
            ],
            // Shell of the single React admin app (Admin\App\AppPage, task 5d).
            'admin-app'                 => [
                'src'       => WPUF_ASSET_URI . '/js/screens/admin-app.js',
                'deps'      => $admin_app_asset['dependencies'],
                'version'   => $admin_app_asset['version'],
                'in_footer' => true,
            ],
            // Registration Forms without Pro (Admin\Screens\RegistrationPromo).
            'registration-promo'        => [
                'src'       => WPUF_ASSET_URI . '/js/screens/registration-promo.js',
                'deps'      => $reg_promo_asset['dependencies'],
                'version'   => $reg_promo_asset['version'],
                'in_footer' => true,
            ],
            // Setup wizard (Admin\Screens\Onboarding).
            'onboarding-screen'          => [
                'src'       => WPUF_ASSET_URI . '/js/screens/onboarding.js',
                'deps'      => $onboarding_asset['dependencies'],
                'version'   => $onboarding_asset['version'],
                'in_footer' => true,
            ],
            // Welcome page and one-time welcome (Admin\Screens\Welcome).
            'welcome'                   => [
                'src'       => WPUF_ASSET_URI . '/js/screens/welcome.js',
                'deps'      => $welcome_asset['dependencies'],
                'version'   => $welcome_asset['version'],
                'in_footer' => true,
            ],
            // Help (Admin\Screens\Help).
            'help'                      => [
                'src'       => WPUF_ASSET_URI . '/js/screens/help.js',
                'deps'      => $help_asset['dependencies'],
                'version'   => $help_asset['version'],
                'in_footer' => true,
            ],
            // Tools (Admin\Screens\Tools).
            'tools'                     => [
                'src'       => WPUF_ASSET_URI . '/js/screens/tools.js',
                'deps'      => $tools_asset['dependencies'],
                'version'   => $tools_asset['version'],
                'in_footer' => true,
            ],
            // Transactions (Admin\Screens\Transactions).
            'transactions'              => [
                'src'       => WPUF_ASSET_URI . '/js/screens/transactions.js',
                'deps'      => $transactions_asset['dependencies'],
                'version'   => $transactions_asset['version'],
                'in_footer' => true,
            ],
            // Coupons without Pro (Admin\Screens\CouponsPromo).
            'coupons-promo'             => [
                'src'       => WPUF_ASSET_URI . '/js/screens/coupons-promo.js',
                'deps'      => $coupons_promo_asset['dependencies'],
                'version'   => $coupons_promo_asset['version'],
                'in_footer' => true,
            ],
            // AI form builder, under the Vue app's handle (Admin\Screens\AiFormBuilder).
            'ai-form-builder'           => [
                'src'       => WPUF_ASSET_URI . '/js/screens/ai-form-builder.js',
                'deps'      => $ai_form_builder_asset['dependencies'],
                'version'   => $ai_form_builder_asset['version'],
                'in_footer' => true,
            ],
            // Frontend React runtime and apps (Frontend\Renderer).
            'frontend-runtime'          => [
                'src'       => WPUF_ASSET_URI . '/js/frontend/runtime.js',
                // wp-api-request prints wpApiSettings (REST root + nonce) for the upload XHR.
                'deps'      => array_values( array_unique( array_merge( $frontend_runtime['dependencies'], [ 'wp-api-request' ] ) ) ),
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
     * The `.asset.php` of a frontend bundle (assets/js/frontend/<name>.asset.php),
     * or the given dependencies when the bundle is not built.
     *
     * @since WPUF_SINCE
     *
     * @param string $name         Bundle name
     * @param array  $dependencies Fallback dependencies
     *
     * @return array
     */
    public function frontend_asset( $name, $dependencies ) {
        $file = WPUF_ROOT . '/assets/js/frontend/' . $name . '.asset.php';

        return file_exists( $file )
            ? require $file
            : [
                'dependencies' => $dependencies,
                'version'      => WPUF_VERSION,
            ];
    }

    /**
     * Version of a React admin sheet: its file's modification time (a sheet can
     * be rebuilt without its script), else the plugin version.
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

    /**
     * React screen stylesheets, keyed by handle without the `wpuf-` prefix, in
     * registration order.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function styles() {
        return [
            'settings-screen'      => [
                'src'     => WPUF_ASSET_URI . '/css/settings-screen.css',
                'version' => $this->css_version( 'css/settings-screen.css' ),
                // settings-screen-rtl.css (built by tools/admin-css) on RTL sites.
                'rtl' => true,
            ],
            'subscriptions-screen' => [
                'src'     => WPUF_ASSET_URI . '/css/subscriptions.css',
                // The sheets change without the scripts: version each by its own file.
                'version' => $this->css_version( 'css/subscriptions.css' ),
                // subscriptions-rtl.css (built by tools/admin-css) on RTL sites.
                'rtl'     => true,
            ],
            // Setup wizard and welcome sheet (tools/admin-css).
            'onboarding-screen'    => [
                'src'     => WPUF_ASSET_URI . '/css/admin/onboarding-screen.css',
                'version' => $this->css_version( 'css/admin/onboarding-screen.css' ),
                'rtl'     => true,
            ],
            // Help, Tools, Transactions and the Coupons screens (tools/admin-css `pages`).
            'admin-pages'         => [
                'src'     => WPUF_ASSET_URI . '/css/admin/pages-screen.css',
                'version' => $this->css_version( 'css/admin/pages-screen.css' ),
                'rtl'     => true,
            ],
            // AI form builder sheet (tools/admin-css), under the Vue app's handle.
            'ai-form-builder'     => [
                'src'     => WPUF_ASSET_URI . '/css/ai-form-builder-screen.css',
                'version' => $this->css_version( 'css/ai-form-builder-screen.css' ),
                'rtl'     => true,
            ],
            // Frontend React sheets (Frontend\Renderer enqueues them with their app).
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
     * The shared admin components (window.wpuf.components) print their own
     * strings, so they load translations like the screens do.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function set_translations() {
        wp_set_script_translations( 'wpuf-admin-ui', 'wp-user-frontend', WPUF_ROOT . '/languages' );
    }

    /**
     * Whether the current admin page is a React forms list or builder.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_forms_page() {
        global $plugin_page;

        return is_admin() && in_array( $plugin_page, [ 'wpuf-post-forms', 'wpuf-profile-forms' ], true );
    }

    /**
     * The React forms list and builder sheets are registered under the old
     * handles (`wpuf-forms-list` for the lists, `wpuf-admin-form-builder` for
     * the builder) so Pro and modules that depend on those handles keep working.
     * Both handles get enqueued on these pages; the one the page does not use
     * prints nothing.
     *
     * @since WPUF_SINCE
     *
     * @param string $src    Stylesheet URL
     * @param string $handle Handle
     *
     * @return string|false
     */
    public function use_forms_styles( $src, $handle = '' ) {
        if ( ! in_array( $handle, [ 'wpuf-admin-form-builder', 'wpuf-forms-list' ], true ) || ! $this->is_forms_page() ) {
            return $src;
        }

        $action  = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
        $builder = in_array( $action, [ 'edit', 'add-new' ], true );
        $serves  = $builder ? 'wpuf-admin-form-builder' : 'wpuf-forms-list';

        return $handle === $serves ? $src : false;
    }

    /**
     * Body classes of the React forms list and builder.
     *
     * @since WPUF_SINCE
     *
     * @param string $classes Admin body classes
     *
     * @return string
     */
    public function forms_body_class( $classes ) {
        if ( ! $this->is_forms_page() ) {
            return $classes;
        }

        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.

        if ( in_array( $action, [ 'edit', 'add-new' ], true ) ) {
            $classes .= ' wpuf-builder-screen';
        }

        return trim( $classes . ' wpuf-admin-screen' );
    }
}
