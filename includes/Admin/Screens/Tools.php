<?php
/**
 * Tools screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Tools\ToolsService;

/**
 * User Frontend > Tools: the admin app route `#/tools` (tabs Tools, Import,
 * Export, Shortcodes) on Platform\REST\Controllers\ToolsController. The old
 * URL redirects there after `wpuf_load_tools`, so develop's GET / POST handlers
 * (Admin_Tools) still run on it.
 *
 * @since WPUF_SINCE
 */
class Tools extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_tools';
    }

    /**
     * Nothing to print: the old URL always redirects to the app route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {}

    /**
     * The admin app route.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'tools',
                'path'           => '/tools',
                'title'          => __( 'Tools', 'wp-user-frontend' ),
                'app'            => 'tools',
                'boot'           => 'tools',
                'in_app'         => true,
                'menuLink'       => true,
                'notices'        => true,
                'container'      => 'wpuf-tools-root',
                'containerClass' => 'px-[20px]',
            ],
        ];
    }

    /**
     * The route the old URL opens: its tab and the classic handlers' message.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view state.
        $query = array_filter(
            [
                'tab' => isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '',
                'msg' => isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '',
            ]
        );
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return '/tools' . ( $query ? '?' . http_build_query( $query ) : '' );
    }

    /**
     * The old URL: fire `wpuf_load_tools` as the classic page did, so its
     * handlers (Admin_Tools: reset / delete links, logout menu form) and
     * other listeners still run before the app opens.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_before_redirect() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_tools' );
    }

    /**
     * The app page: `wpuf_load_tools` fires as on the classic page (the
     * screens' load hooks fire in the app, as Post Forms and Subscriptions
     * do), then the route's assets.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        /** This action is documented in includes/Admin/Screens/Tools.php */
        do_action( 'wpuf_load_tools' );

        wp_enqueue_style( 'wpuf-admin-pages' );
        wp_enqueue_script( 'wpuf-tools' );
    }

    /**
     * What the route needs up front (forms and shortcodes load from REST on
     * their tab).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        $admin      = wpuf()->admin;
        $tools      = wpuf()->platform()->get( ToolsService::class );
        $onboarding = is_object( $admin ) && $admin->onboarding ? $tools->onboarding_entry() : null;
        $is_block = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

        return [
            'wpufTools' => [
                'canManageSite' => Caps::can( Caps::MANAGE_SITE ),
                'isPro'         => wpuf_is_pro_active(),
                'onboarding'    => $onboarding,
                'deletable'     => ToolsService::DELETABLE,
                'menus'         => $tools->menus(),
                'isBlockTheme'  => $is_block,
                'logoutUrl'     => html_entity_decode( wpuf_get_logout_url(), ENT_QUOTES, 'UTF-8' ),
                'menusUrl'      => admin_url( 'nav-menus.php' ),
                'siteEditorUrl' => admin_url( 'site-editor.php?path=%2Fnavigation' ),
                'upgradeUrl'    => 'https://wedevs.com/wp-user-frontend-pro/',
            ],
        ];
    }
}
