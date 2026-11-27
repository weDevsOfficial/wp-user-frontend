<?php
/**
 * Modules Preview (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

/**
 * The Modules page of the free plugin (the React route `#/modules`, Admin\Screens\ModulesPreview):
 * its load step, the Pro modules it previews (pro_modules_info()) and the old switch handler.
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Modules_Preview extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    /**
     * Load step of `page=wpuf-modules`: the registry opens the React route
     * `#/modules` (Admin\Screens\ModulesPreview), whose load step fires
     * `wpuf_load_module_page`.
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function module_menu_action() {
        if ( wpuf()->platform()->has( \WeDevs\Wpuf\Admin\Screens\Registry::class ) ) {
            wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Screens\Registry::class )->load( 'wpuf-modules' );
        }

        /**
         * Fires on the free Modules page (the React route `#/modules` in the admin app).
         *
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_module_page' );
    }

    /**
     * Get the info of the pro modules as an array
     *
     * @since 3.6.0
     *
     * @return string[][]
     */
    public function pro_modules_info() {
        return [
            'campaign-monitor/campaign-monitor.php' => [
                'name'        => 'Campaign Monitor',
                'description' => 'Subscribe a contact to Campaign Monitor when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/campaign-monitor/',
                'thumbnail'   => 'campaign_monitor.svg',
            ],
            'social-login/wpuf-social-login.php' => [
                'name'        => 'Social Login & Registration',
                'description' => 'Add Social Login and registration feature in WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/social-login-registration/',
                'thumbnail'   => 'Social-Media-Login.svg',
            ],
            'bp-profile/wpuf-bp.php' => [
                'name'        => 'BuddyPress Profile',
                'description' => 'Register and upgrade user profiles and sync data with BuddyPress',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/buddypress-profile-integration/',
                'thumbnail'   => 'wpuf-buddypress.svg',
            ],
            'comments/comments.php' => [
                'name'        => 'Comments Manager',
                'description' => 'Handle comments in frontend',
                'plugin_uri'  => 'https://wedevs.com/wp-user-frontend-pro/modules/comments-manager/',
                'thumbnail'   => 'wpuf-comment.svg',
            ],
            'mailpoet/wpuf-mailpoet.php' => [
                'name'        => 'Mailpoet',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet/',
                'thumbnail'   => 'wpuf-mailpoet.svg',
            ],
            'pmpro/wpuf-pmpro.php' => [
                'name'        => 'Paid Membership Pro Integration',
                'description' => 'Membership Integration of WP User Frontend PRO with Paid Membership Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/install-and-configure-pmpro-add-on-for-wpuf/',
                'thumbnail'   => 'wpuf-pmpro.svg',
            ],
            'sms-notification/wpuf-sms.php' => [
                'name'        => 'SMS Notification',
                'description' => 'SMS notification for post',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/sms-notification/',
                'thumbnail'   => 'wpuf-sms.svg',
            ],
            'email-templates/email-templates.php' => [
                'name'        => 'HTML Email Templates',
                'description' => 'Send Email Notifications with HTML Template',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/html-email-templates/',
                'thumbnail'   => 'email-templates.svg',
            ],
            'getresponse/getresponse.php' => [
                'name'        => 'GetResponse',
                'description' => 'Subscribe a contact to GetResponse when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/get-response/',
                'thumbnail'   => 'getresponse.svg',
            ],
            'zapier/zapier.php' => [
                'name'        => 'Zapier',
                'description' => 'Subscribe a contact to Zapier when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/zapier/',
                'thumbnail'   => 'zapier.svg',
            ],
            'convertkit/convertkit.php' => [
                'name'        => 'ConvertKit',
                'description' => 'Subscribe a contact to ConvertKit when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/convertkit/',
                'thumbnail'   => 'convertkit.svg',
            ],
            'private-message/private-message.php' => [
                'name'        => 'Private Message',
                'description' => 'User to user message from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/private-messaging/',
                'thumbnail'   => 'message.svg',
            ],
            'user-analytics/wpuf-user-analytics.php' => [
                'name'        => 'User Analytics',
                'description' => 'Show user tracking info during post and registration from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-analytics/',
                'thumbnail'   => 'wpuf-ua.svg',
            ],
            'mailchimp/wpuf-mailchimp.php' => [
                'name'        => 'Mailchimp',
                'description' => 'Add subscribers to Mailchimp mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/add-users-to-mailchimp-subscribers-list-upon-registration-from-frontend/',
                'thumbnail'   => 'wpuf-mailchimp.svg',
            ],
            'user-activity/user_activity.php' => [
                'name'        => 'User Activity',
                'description' => 'Handle user activity in frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-activity/',
                'thumbnail'   => 'wpuf-activity.svg',
            ],
            'report/wpuf-report.php' => [
                'name'        => 'Reports',
                'description' => 'Show various reports in WP User Frontend menu',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/reports/',
                'thumbnail'   => 'reports.svg',
            ],
            'qr-code-field/wpuf-qr-code.php' => [
                'name'        => 'QR Code',
                'description' => 'Post Qr code generator plugin',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/qr-code/',
                'thumbnail'   => 'wpuf-qr.svg',
            ],
            'mailpoet3/wpuf-mailpoet-3.php' => [
                'name'        => 'Mailpoet 3',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet3/',
                'thumbnail'   => 'mailpoet3.svg',
            ],
            'stripe/wpuf-stripe.php' => [
                'name'        => 'Stripe Payment',
                'description' => 'Stripe payment gateway for WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/stripe/',
                'thumbnail'   => 'wpuf-stripe.svg',
            ],
            'seo/wpuf-seo.php' => [
                'name'        => 'SEO Settings',
                'description' => 'SEO settings for user directory and profiles',
                'plugin_uri'  => 'https://wedevs.com/products/plugins/wp-user-frontend-pro/seo-settings/',
                'thumbnail'   => 'wpuf-seo.svg',
            ],
        ];
    }
}
