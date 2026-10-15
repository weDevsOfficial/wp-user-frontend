<?php
/**
 * Registration Forms screen without Pro
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Free\Pro_Prompt;

/**
 * User Frontend > Registration Forms while Pro is not active
 * (`page=wpuf-profile-forms`, Free_Loader): the free registration shortcode
 * and what Pro adds. In the admin app it is the React route
 * `#/registration-forms` on the shared components (the old URL redirects
 * there).
 *
 * @since WPUF_SINCE
 */
class RegistrationPromo extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-profile-forms';
    }

    /**
     * Free_Loader prints the page and enqueues its assets.
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
                'id'             => 'registration-forms',
                'path'           => '/registration-forms',
                'title'          => __( 'Registration Forms', 'wp-user-frontend' ),
                'app'            => 'registration-promo',
                'boot'           => 'registration_promo',
                'in_app'         => true,
                'menuLink'       => true,
                'container'      => 'wpuf-registration-promo',
                'containerClass' => 'px-[20px]',
                // Develop's page showed the admin notices (as Pro's list route does).
                'notices'        => true,
                'page'           => 'admin.php?page=wpuf-profile-forms',
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
        return '/registration-forms';
    }

    /**
     * On the app page: the React page, and the old page's load hook.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        // Shared components' styles come with the React settings sheet.
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-settings-screen' );
        wp_enqueue_script( 'wpuf-registration-promo' );

        /** This action is documented in includes/Free/Free_Loader.php */
        do_action( 'wpuf_load_registration_forms' );
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
            return WPUF_ASSET_URI . '/images/' . $file;
        };

        return [
            'wpufRegistrationPromo' => [
                'shortcode'    => '[wpuf-registration]',
                'banner'       => $image( 'form-banner.svg' ),
                'setupUrl'     => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/how-to-setup-registrationlogin-page/',
                'learnUrl'     => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/',
                // Pro_Prompt escapes this link for HTML; the app opens the plain link.
                'upgradeUrl'   => html_entity_decode( Pro_Prompt::get_upgrade_to_pro_popup_url(), ENT_QUOTES, 'UTF-8' ),
                'features'     => [
                    [ 'icon' => $image( 'icon-doc.svg' ), 'title' => __( 'Registration form builder', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-profile.svg' ), 'title' => __( 'Profile form builder', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-money.svg' ), 'title' => __( 'Create & Sell Subscription Package', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-templates.svg' ), 'title' => __( 'Pre-defined Templates', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-checked.svg' ), 'title' => __( 'Approval System after Registration', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-mention.svg' ), 'title' => __( 'Email Notifications', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-settings.svg' ), 'title' => __( 'Custom Field', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-buddypress.svg' ), 'title' => __( 'BuddyPress Support', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'icon-groups.svg' ), 'title' => __( 'Social Login & Registration', 'wp-user-frontend' ) ],
                ],
                // The modules page's icons (assets/images/modules).
                'integrations' => [
                    [ 'icon' => $image( 'modules/wpuf-mailchimp.svg' ), 'title' => __( 'Mailchimp', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'modules/getresponse.svg' ), 'title' => __( 'GetResponse', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'modules/convertkit.svg' ), 'title' => __( 'Kit (formerly ConvertKit)', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'modules/campaign_monitor.svg' ), 'title' => __( 'Campaign Monitor', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'modules/wpuf-mailpoet.svg' ), 'title' => __( 'MailPoet', 'wp-user-frontend' ) ],
                    [ 'icon' => $image( 'modules/mailpoet3.svg' ), 'title' => __( 'MailPoet 3', 'wp-user-frontend' ) ],
                ],
            ],
        ];
    }
}
