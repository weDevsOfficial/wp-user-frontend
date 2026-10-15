<?php
/**
 * Welcome screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Free\Pro_Prompt;
use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Welcome to WP User Frontend (`index.php?page=wpuf-welcome`, Admin_Welcome)
 * as the admin app route `#/welcome`, on the shared components. Its bundle
 * also brings the one-time welcome (like FlyHR's): a full-screen hello the
 * first time each admin opens the admin app, unless they ran the setup.
 *
 * @since WPUF_SINCE
 */
class Welcome extends Screen {

    /**
     * Menu slug (Admin_Welcome)
     */
    const SLUG = 'wpuf-welcome';

    /**
     * User meta: the admin has had the one-time welcome (or ran the setup).
     */
    const SEEN_META = 'wpuf_welcome_seen';

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return self::SLUG;
    }

    /**
     * Who may open it: as the dashboard page.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function capability() {
        return Caps::capability( Caps::MANAGE_SITE );
    }

    /**
     * The page always opens the app route.
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
                'id'             => 'welcome',
                'path'           => '/welcome',
                'title'          => __( 'Welcome to WP User Frontend', 'wp-user-frontend' ),
                'app'            => 'welcome',
                'boot'           => 'welcome',
                'in_app'         => true,
                'notices'        => true,
                'container'      => 'wpuf-welcome-root',
                'containerClass' => 'px-[20px]',
                'page'           => 'index.php?page=' . self::SLUG,
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
        return '/welcome';
    }

    /**
     * Nothing to do on the old page before the hop.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_before_redirect() {}

    /**
     * On the app page: the welcome app (page and one-time welcome) and its sheet.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-onboarding-screen' );
        wp_enqueue_script( 'wpuf-welcome' );

        if ( $this->take_intro() ) {
            wp_add_inline_script( 'wpuf-welcome', 'window.wpufWelcomeIntro = ' . wp_json_encode( $this->intro() ) . ';', 'before' );
        }
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
        $doc   = function ( $path ) {
            return 'https://wedevs.com/docs/wp-user-frontend-pro/' . $path . '?utm_source=wpuf-welcome&utm_medium=welcome-page';
        };
        $is_pro = wpuf_is_pro_active();

        return [
            'wpufWelcome' => [
                'isPro'      => $is_pro,
                'logo'       => $image( 'onboarding-logo.svg' ),
                'video'      => [
                    'id'    => 'ATf1iXX4QHk',
                    'thumb' => $image( 'welcome/welcome-video.png' ),
                ],
                'urls'       => [
                    'postForms'  => wpuf_admin_app_url( '/post-forms' ),
                    'onboarding' => admin_url( 'index.php?page=wpuf-onboarding' ),
                    'guide'      => $doc( 'getting-started/' ),
                    'features'   => 'https://wedevs.com/wp-user-frontend-pro/features/?utm_source=wpuf-welcome&utm_medium=welcome-page',
                    // Pro_Prompt escapes this link for HTML; the app opens the plain link.
                    'upgrade'    => html_entity_decode( Pro_Prompt::get_upgrade_to_pro_popup_url(), ENT_QUOTES, 'UTF-8' ),
                ],
                'sections'   => [
                    [
                        'title' => __( 'Frontend Posting', 'wp-user-frontend' ),
                        'items' => [
                            [
								'icon' => $image( 'welcome/Form-Builder.svg' ),
								'title' => __( 'Post Form Builder', 'wp-user-frontend' ),
								'text' => __( 'Design your forms with a drag and drop builder and live preview.', 'wp-user-frontend' ),
								'url' => $doc( 'posting-forms/creating-posting-forms/' ),
							],
                            [
								'icon' => $image( 'welcome/Support.svg' ),
								'title' => __( 'Custom Field Support', 'wp-user-frontend' ),
								'text' => __( 'Build submission forms with 30+ custom field types.', 'wp-user-frontend' ),
								'url' => $doc( 'posting-forms/form-elements/' ),
							],
                            [
								'icon' => $image( 'welcome/Post-Taxonomies.svg' ),
								'title' => __( 'Post Types & Taxonomies', 'wp-user-frontend' ),
								'text' => __( 'Publish to custom post types and taxonomies.', 'wp-user-frontend' ),
								'url' => $doc( 'posting-forms/different-custom-post-type-submission-2/' ),
							],
                            [
								'icon' => $image( 'welcome/Guest-Posting.svg' ),
								'title' => __( 'Guest Posting', 'wp-user-frontend' ),
								'text' => __( 'Let guests post from the frontend with full capabilities.', 'wp-user-frontend' ),
								'url' => $doc( 'posting-forms/guest-posting/' ),
							],
                        ],
                    ],
                    [
                        'title' => __( 'Registration & Profile Builder', 'wp-user-frontend' ),
                        'items' => [
                            [
								'icon' => $image( 'welcome/Registration-form.svg' ),
								'title' => __( 'Registration Form Builder', 'wp-user-frontend' ),
								'text' => __( 'Create frontend registration forms with the form builder.', 'wp-user-frontend' ),
								'url' => $doc( 'registration-profile-forms/registration-forms/' ),
							],
                            [
								'icon' => $image( 'welcome/Profile-Builder.svg' ),
								'title' => __( 'User Profile Builder', 'wp-user-frontend' ),
								'text' => __( 'Publish frontend profile and profile edit pages with shortcodes.', 'wp-user-frontend' ),
								'url' => $doc( 'registration-profile-forms/wordpress-edit-user-profile-from-front-end/' ),
							],
                            [
								'icon' => $image( 'welcome/My-Account.svg' ),
								'title' => __( 'My Account on Frontend', 'wp-user-frontend' ),
								'text' => __( 'Give members a frontend account page for posts, profile and subscription.', 'wp-user-frontend' ),
								'url' => $doc( 'frontend/how-to-create-my-account-page/' ),
							],
                            [
								'icon' => $image( 'welcome/Create-Database.svg' ),
								'title' => __( 'Login Page', 'wp-user-frontend' ),
								'text' => __( 'Themed login and registration pages for one user experience.', 'wp-user-frontend' ),
								'url' => $doc( 'registration-profile-forms/how-to-setup-registrationlogin-page/' ),
							],
                        ],
                    ],
                    [
                        'title' => __( 'Subscriptions', 'wp-user-frontend' ),
                        'items' => [
                            [
								'icon' => $image( 'welcome/User.svg' ),
								'title' => __( 'Charge for Posting', 'wp-user-frontend' ),
								'text' => __( 'Accept payments from several gateways for post submissions.', 'wp-user-frontend' ),
								'url' => $doc( 'subscription-payment/' ),
							],
                            [
								'icon' => $image( 'welcome/pay-per-post.svg' ),
								'title' => __( 'Pay per Post', 'wp-user-frontend' ),
								'text' => __( 'Earn from each guest post with different subscription packs.', 'wp-user-frontend' ),
								'url' => $doc( 'subscription-payment/how-to-charge-for-each-post-submission/' ),
							],
                            [
								'icon' => $image( 'welcome/Content-Locking.svg' ),
								'title' => __( 'Content Locking', 'wp-user-frontend' ),
								'text' => __( 'Lock your best content for subscribed members.', 'wp-user-frontend' ),
								'url' => $doc( 'content-restriction/' ),
							],
                            [
								'icon' => $image( 'welcome/Subscription-Signup.svg' ),
								'title' => __( 'Subscription Signup', 'wp-user-frontend' ),
								'text' => __( 'Build a membership site where people sign up with a plan.', 'wp-user-frontend' ),
								'url' => $doc( 'registration-profile-forms/paid-membership-registration/' ),
							],
                        ],
                    ],
                ],
                'proFeatures' => $is_pro ? [] : [
                    __( 'Unlock More Fields', 'wp-user-frontend' ),
                    __( 'Registration Forms', 'wp-user-frontend' ),
                    __( 'Content Restriction', 'wp-user-frontend' ),
                    __( 'Menu Restriction', 'wp-user-frontend' ),
                    __( 'Email Notification', 'wp-user-frontend' ),
                    __( 'Discount Coupons', 'wp-user-frontend' ),
                    __( 'Custom Post Types', 'wp-user-frontend' ),
                    __( 'Multistep Form', 'wp-user-frontend' ),
                    __( 'Stripe Payment', 'wp-user-frontend' ),
                    __( 'Much More', 'wp-user-frontend' ),
                ],
            ],
        ];
    }

    /**
     * Whether this admin gets the one-time welcome now. Asking uses it up.
     *
     * @return bool
     */
    private function take_intro() {
        $user_id = get_current_user_id();

        if ( ! $user_id || Stores::users()->meta( $user_id, self::SEEN_META ) ) {
            return false;
        }

        /**
         * Whether the admin app shows the one-time welcome to this admin.
         *
         * @since WPUF_SINCE
         *
         * @param bool $show    Show it.
         * @param int  $user_id Current user.
         */
        if ( ! apply_filters( 'wpuf_admin_welcome_intro', true, $user_id ) ) {
            return false;
        }

        Stores::users()->set_meta( $user_id, self::SEEN_META, 1 );

        return true;
    }

    /**
     * What the one-time welcome shows.
     *
     * @return array
     */
    private function intro() {
        $user = wp_get_current_user();
        $name = $user->exists() ? trim( (string) $user->first_name ) : '';

        if ( '' === $name && $user->exists() ) {
            $name = (string) strtok( trim( (string) $user->display_name ), ' ' );
        }

        return [
            'name'       => $name,
            'logo'       => WPUF_ASSET_URI . '/images/onboarding-logo.svg',
            'onboarding' => admin_url( 'index.php?page=wpuf-onboarding' ),
        ];
    }
}
