<?php
/**
 * Coupons screen without Pro
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Free\Pro_Prompt;

/**
 * User Frontend > Coupons without Pro (`page=wpuf_coupon`, Free_Loader): in
 * the admin app the route `#/coupons`, a page that says what Pro's coupons
 * do. Pro registers its own `#/coupons` screen instead (Pro's
 * Platform\Screens\Coupons).
 *
 * @since WPUF_SINCE
 */
class CouponsPromo extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_coupon';
    }

    /**
     * The old page callback (Free_Loader::admin_coupon_page()); the old URL redirects to the app route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        wpuf()->free_loader->admin_coupon_page();
    }

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
                'id'             => 'coupons',
                'path'           => '/coupons',
                'title'          => __( 'Coupons', 'wp-user-frontend' ),
                'app'            => 'coupons-promo',
                'boot'           => 'coupons_promo',
                'in_app'         => true,
                'menuLink'       => true,
                'container'      => 'wpuf-coupons-promo',
                'containerClass' => 'px-[20px]',
            ],
        ];
    }

    /**
     * The route the old URL opens.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        return '/coupons';
    }

    /**
     * Assets of the route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-admin-pages' );
        wp_enqueue_script( 'wpuf-coupons-promo' );
    }

    /**
     * Window globals of the route.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        return [
            'wpufCouponsPromo' => [
                'upgradeUrl' => Pro_Prompt::get_pro_url(),
                'docsUrl'    => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/coupons/',
            ],
        ];
    }
}
