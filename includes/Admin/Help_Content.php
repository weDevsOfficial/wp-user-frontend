<?php
/**
 * Help page content
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin;

/**
 * What User Frontend > Help shows: the topics (nav label, icon, title, body,
 * docs button, related articles), the help cards and the newsletter form.
 * Used by the admin app's `#/help`
 * route (Admin\Screens\Help), so both show the same text.
 *
 * @since WPUF_SINCE
 */
class Help_Content {

    /**
     * Related docs articles per topic key.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function articles() {
        return [
            'setup' => [
                [
                    'title' => __( 'How to Install', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/',
                ],
                [
                    'title' => __( 'License Activation', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/troubleshoot/license-activation/',
                ],
                [
                    'title' => __( 'Shortcodes', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/wpuf-shortcodes/',
                ],
                [
                    'title' => __( 'User Dashboard', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/user-dashboard/',
                ],
            ],
            'posting' => [
                [
                    'title' => __( 'Creating Posting Forms', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/creating-posting-forms/',
                ],
                [
                    'title' => __( 'Available Form Elements', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/form-elements/',
                ],
                [
                    'title' => __( 'Creating Forms Using The Form Templates', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/form-templates/',
                ],
                [
                    'title' => __( 'How to Allow Guest Posting', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/',
                ],
                [
                    'title' => __( 'Setup Automatic Post Expiration', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/using-post-expiration-wp-user-frontend/',
                ],
                [
                    'title' => __( 'How to create Multistep forms', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/how-to-add-multi-step-form/',
                ],
            ],
            'dashboard' => [
                [
                    'title' => __( 'Setting up Frontend Dashboard for Users', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/configuring-dashboard-settings/',
                ],
                [
                    'title' => __( 'Unified My Account Page', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/how-to-create-my-account-page/',
                ],
                [
                    'title' => __( 'Showing meta fields in frontend', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/showing-meta-fields-in-frontend/',
                ],
            ],
            'settings' => [
                [
                    'title' => __( 'General Options', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-general-options/',
                ],
                [
                    'title' => __( 'Dashboard Settings', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-dashboard-settings/',
                ],
                [
                    'title' => __( 'Login Registration Settings', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/login-registration-settings/',
                ],
                [
                    'title' => __( 'Payment Settings', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-payment-settings/',
                ],
            ],
            'registration' => [
                [
                    'title' => __( 'Creating Registration Form', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/',
                ],
                [
                    'title' => __( 'Creating a Multistep Registration Form', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/creating-a-multistep-registration-form/',
                ],
                [
                    'title' => __( 'Setting Up Confirmation Message', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/setup-confirmation-message/',
                ],
                [
                    'title' => __( 'Paid Membership Registration', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/paid-membership-registration/',
                ],
                [
                    'title' => __( 'Setting Up Email Verification for New Users', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/setting-up-email-verification-for-new-users/',
                ],
            ],
            'profile' => [
                [
                    'title' => __( 'Creating a Profile Editing Form', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/wordpress-edit-user-profile-from-front-end/',
                ],
            ],
            'subscription' => [
                [
                    'title' => __( 'Creating Subscription Packs', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/creating-subscription-packs/',
                ],
                [
                    'title' => __( 'Payment & Gateway Settings', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/configuring-payment-settings/',
                ],
                [
                    'title' => __( 'Setting Up Recurring Payment', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/setting-up-recurring-payment/',
                ],
                [
                    'title' => __( 'Forcing Subscription Pack For Post Submission', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/forcing-subscription-pack-for-post-submission/',
                ],
                [
                    'title' => __( 'How to Charge for Each Post Submission?', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/how-to-charge-for-each-post-submission/',
                ],
                [
                    'title' => __( 'Creating Coupons', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/coupons/',
                ],
            ],

            'developer' => [
                [
                    'title' => __( 'Action Hook Field', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/action-hook-field/',
                ],
                [
                    'title' => __( 'Add a New Tab on My Account Page', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/add-a-new-tab-on-my-account-page/',
                ],
                [
                    'title' => __( 'Insert/update checkbox or radio field data as serialize', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/insertupdate-checkbox-or-radio-field-data-as-serialize/',
                ],
                [
                    'title' => __( 'Filters', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/filters/',
                ],
                [
                    'title' => __( 'Actions', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/actions/',
                ],
                [
                    'title' => __( 'Changelog', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/changelog/',
                ],
            ],
            'restriction' => [
                [
                    'title' => __( 'Content Restriction for Logged in Users', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/content-restriction/',
                ],
                [
                    'title' => __( 'Restricting Content by User Roles', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/restricting-content-by-user-roles/',
                ],
                [
                    'title' => __( 'Restricting Contents for Different Subscription Packs', 'wp-user-frontend' ),
                    'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/restricting-contents-for-different-subscription-packs/',
                ],
            ],
        ];
    }

    /**
     * The topics, in nav order.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function topics() {
        return [
            [
                'id'       => 'setup',
                'label'    => __( 'Plugin Setup', 'wp-user-frontend' ),
                'icon'     => 'dashicons-admin-home',
                'title'    => __( 'Plugin Setup Guide', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Installation', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=how-to-install',
                ],
                'articles' => 'setup',
            ],
            [
                'id'       => 'frontend-posting',
                'label'    => __( 'Frontend Posting', 'wp-user-frontend' ),
                'icon'     => 'dashicons-media-text',
                'title'    => __( 'Frontend Posting', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Frontend Posting', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=frontend-posting',
                ],
                'articles' => 'posting',
            ],
            [
                'id'       => 'frontend-dashboard',
                'label'    => __( 'Frontend Dashboard', 'wp-user-frontend' ),
                'icon'     => 'dashicons-dashboard',
                'title'    => __( 'Frontend Dashboard', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Frontend Dashboard', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/configuring-dashboard-settings/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=frontend-dashboard',
                ],
                'articles' => 'dashboard',
            ],
            [
                'id'       => 'user-registration',
                'label'    => __( 'User Registration', 'wp-user-frontend' ),
                'icon'     => 'dashicons-admin-users',
                'title'    => __( 'User Registration', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Registration', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=registration-profile-forms',
                ],
                'articles' => 'registration',
            ],
            [
                'id'       => 'login-page',
                'label'    => __( 'User Login', 'wp-user-frontend' ),
                'icon'     => 'dashicons-lock',
                'title'    => __( 'Login Page', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Login', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/user-login/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=learn-more-login',
                ],
                'articles' => '',
            ],
            [
                'id'       => 'profile-editing',
                'label'    => __( 'Profile Editing', 'wp-user-frontend' ),
                'icon'     => 'dashicons-edit',
                'title'    => __( 'Creating a Profile Editing Form', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Profile Editing', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=registration-profile-forms',
                ],
                'articles' => 'profile',
            ],
            [
                'id'       => 'subscription-payment',
                'label'    => __( 'Subscription &amp; Payment', 'wp-user-frontend' ),
                'icon'     => 'dashicons-cart',
                'title'    => __( 'Subscription Payment', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Payments', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=subscription-payment',
                ],
                'articles' => 'subscription',
            ],
            [
                'id'       => 'content-restriction',
                'label'    => __( 'Content Restriction', 'wp-user-frontend' ),
                'icon'     => 'dashicons-unlock',
                'title'    => __( 'Content Restriction', 'wp-user-frontend' ),
                'button'   => [
                    'label' => __( 'Learn More About Content Restriction', 'wp-user-frontend' ),
                    'url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=content-restriction',
                ],
                'articles' => 'restriction',
            ],
        ];
    }

    /**
     * A topic's body, printed from its partial (views/help/<id>.php).
     *
     * @since WPUF_SINCE
     *
     * @param string $id Topic id
     *
     * @return void
     */
    public function print_body( $id ) {
        $file = WPUF_INCLUDES . '/Admin/views/help/' . sanitize_file_name( $id ) . '.php';

        if ( file_exists( $file ) ) {
            include $file;
        }
    }

    /**
     * A topic's body as HTML (kses'd).
     *
     * @since WPUF_SINCE
     *
     * @param string $id Topic id
     *
     * @return string
     */
    public function body( $id ) {
        ob_start();
        $this->print_body( $id );

        return wp_kses_post( trim( (string) ob_get_clean() ) );
    }

    /**
     * A related article's link, with the Help page's utm tags.
     *
     * @since WPUF_SINCE
     *
     * @param array $article title, link
     *
     * @return string
     */
    public function article_url( $article ) {
        return add_query_arg(
            [
                'utm_source'   => 'wpuf-help-page',
                'utm_medium'   => 'help-links',
                'utm_campaign' => 'wpuf-help',
                'utm_term'     => rawurlencode( $article['title'] ),
            ],
            trailingslashit( $article['link'] )
        );
    }

    /**
     * The help cards under the topics.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function blocks() {
        return [
            [
                'image'  => WPUF_ASSET_URI . '/images/help/like.svg',
                'title'  => __( 'Like The Plugin?', 'wp-user-frontend' ),
                'text'   => __( 'Your Review is very important to us as it helps us to grow more.', 'wp-user-frontend' ),
                'button' => __( 'Review Us on WP.org', 'wp-user-frontend' ),
                'url'    => 'https://wordpress.org/support/plugin/wp-user-frontend/reviews/?rate=5#new-post',
            ],
            [
                'image'  => WPUF_ASSET_URI . '/images/help/bugs.svg',
                'title'  => __( 'Found Any Bugs?', 'wp-user-frontend' ),
                'text'   => __( 'Report any Bug that you Discovered, Get Instant Solutions.', 'wp-user-frontend' ),
                'button' => __( 'Report to GitHub', 'wp-user-frontend' ),
                'url'    => 'https://github.com/weDevsOfficial/wp-user-frontend/?utm_source=wpuf-help-page&utm_medium=help-block&utm_campaign=found-bugs',
            ],
            [
                'image'  => WPUF_ASSET_URI . '/images/help/support.svg',
                'title'  => __( 'Need Any Assistance?', 'wp-user-frontend' ),
                'text'   => __( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-user-frontend' ),
                'button' => __( 'Contact Support', 'wp-user-frontend' ),
                'url'    => 'https://wedevs.com/account/tickets/?utm_source=wpuf-help-page&utm_medium=help-block&utm_campaign=need-assistance',
            ],
        ];
    }

    /**
     * All documentation link.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function docs_url() {
        return 'https://wedevs.com/docs/wp-user-frontend-pro/?utm_source=wpuf-help-page&utm_medium=button-primary&utm_campaign=view-all-docs';
    }

    /**
     * The weMail newsletter form (posted straight to weMail, as before).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function newsletter() {
        return [
            'action' => 'https://api.getwemail.io/v1/embed/subscribe/8da67b42-c367-4ad3-ae70-5cf63635a832',
            'tag'    => '698f5d31-4ef9-430a-a6f3-7f4bb24cdaf9',
        ];
    }
}
