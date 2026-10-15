<?php
/**
 * Modules screen without Pro
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Free\Pro_Prompt;

/**
 * User Frontend > Modules while Pro is not active (`page=wpuf-modules`):
 * the free modules with their switch, the Pro modules locked. In the admin
 * app it is the React route `#/modules` (src/admin/apps/modules-preview),
 * laid out like Pro's modules screen; the old URL redirects there. The
 * switches call `wpuf/v1/admin/free-modules/{module}`.
 *
 * @since WPUF_SINCE
 */
class ModulesPreview extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-modules';
    }

    /**
     * The page's load step redirects to the route; nothing to print.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {}

    /**
     * Admin app route.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'modules',
                'path'           => '/modules',
                'title'          => __( 'Modules', 'wp-user-frontend' ),
                'app'            => 'modules-preview',
                'boot'           => 'modules_preview',
                'in_app'         => true,
                'menuLink'       => true,
                'container'      => 'wpuf-modules-preview',
                'containerClass' => 'px-[20px]',
                'notices'        => true,
                'page'           => 'admin.php?page=wpuf-modules',
            ],
        ];
    }

    /**
     * The old page opens the route.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        return '/modules';
    }

    /**
     * On the app page: the React page, and the old page's load hook.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-admin-pages' );
        wp_enqueue_script( 'wpuf-modules-preview' );

        /** This action is documented in includes/Free/Modules_Preview.php */
        do_action( 'wpuf_load_module_page' );
    }

    /**
     * Window globals of the route.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        $image = function ( $file ) {
            return WPUF_ASSET_URI . '/images/modules/' . $file;
        };
        $free  = [];
        $pro   = [];

        foreach ( wpuf_free_get_modules() as $id => $module ) {
            $free[] = [
                'id'          => (string) $id,
                'name'        => (string) $module['name'],
                'description' => (string) $module['description'],
                'docs'        => esc_url_raw( $module['plugin_uri'] ),
                'thumbnail'   => $image( $module['thumbnail'] ),
                'active'      => wpuf_free_is_module_active( $id ),
            ];
        }

        foreach ( wpuf()->free_loader->pro_modules_info() as $slug => $module ) {
            $pro[] = [
                'id'          => (string) $slug,
                'name'        => (string) $module['name'],
                'description' => (string) $module['description'],
                'docs'        => esc_url_raw( $module['plugin_uri'] ),
                'thumbnail'   => $image( $module['thumbnail'] ),
            ];
        }

        return [
            'wpufModulesPreview' => [
                'free'       => $free,
                'pro'        => $pro,
                // Pro_Prompt escapes this link for HTML; the app opens the plain link.
                'upgradeUrl' => html_entity_decode( Pro_Prompt::get_upgrade_to_pro_popup_url(), ENT_QUOTES, 'UTF-8' ),
                'docsUrl'    => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/',
            ],
        ];
    }
}
