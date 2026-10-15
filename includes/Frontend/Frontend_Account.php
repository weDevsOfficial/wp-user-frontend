<?php

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Frontend\Account\Account_Service;

use stdClass;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\User_Subscription;

/**
 * Dashboard class
 *
 * @author Tareq Hasan
 */
class Frontend_Account {
    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'wpuf_account_content_dashboard', [ $this, 'dashboard_section' ], 10, 2 );
        foreach ( $this->get_allowed_cpt() as $post_type ) {
            add_action( 'wpuf_account_content_' . $post_type, [ $this, 'posts_section' ], 10, 2 );
        }
        add_action( 'wpuf_account_content_subscription', [ $this, 'subscription_section' ], 10, 2 );
        add_action( 'wpuf_account_content_edit-profile', [ $this, 'edit_profile_section' ], 10, 2 );
        add_action( 'wpuf_account_content_change-password', [ $this, 'change_password_section' ], 10, 2 );
        add_action( 'wpuf_account_content_billing-address', [ $this, 'billing_address_section' ], 10, 2 );

        add_filter( 'wpuf_account_sections', [ $this, 'add_account_sections' ] );
        add_action( 'wpuf_account_content_submit-post', [ $this, 'submit_post_section' ], 10, 2 );
        add_action( 'pre_get_posts', [ $this, 'process_user_for_previewing_post' ] );
    }

    /**
     * Show/Hide frontend post submission menu depending on option
     *
     * @since 2.9.0
     * @return array $sections
     */
    public function add_account_sections( $sections ) {
        $allow_post_submission = wpuf_get_option( 'allow_post_submission', 'wpuf_my_account', 'on' );
        $submission_label      = wpuf_get_option(
            'post_submission_label', 'wpuf_my_account',
            __( 'Submit Post', 'wp-user-frontend' )
        );
        if ( ! is_array( $sections ) ) {
            $sections = (array) $sections;
        }
        if ( $allow_post_submission === 'on' ) {
            $sections = array_merge(
                $sections, [
					'submit-post' => $submission_label,
				]
            );
        }

        return $sections;
    }

    /**
     * Display the submit post section
     *
     * @since 2.9.0
     *
     * @param string $current_section
     *
     * @param array  $sections
     *
     * @return void
     */
    public function submit_post_section( $sections, $current_section ) {
        $allow_post_submission = wpuf_get_option( 'allow_post_submission', 'wpuf_my_account', 'on' );
        if ( $allow_post_submission !== 'on' ) {
            return;
        }
        wpuf_load_template(
            'submit-post.php', [
				'sections'        => $sections,
				'current_section' => $current_section,
			]
        );
    }

    /**
     * Handle's user account functionality
     *
     * Insert shortcode [wpuf_account] in a page to
     * show the user account
     *
     * @since 2.4.2
     */
    public function shortcode( $atts, $content = '', $tag = 'wpuf_account' ) {
        //phpcs:ignore
        extract( shortcode_atts( [], $atts ) );

        // [wpuf_editprofile] shares this handler and stays classic; the account shortcode may mount the React app.
        if ( 'wpuf_account' === $tag && is_user_logged_in() && $this->renderer()->mounts( 'account' ) ) {
            return $this->mount_account();
        }

        ob_start();
        if ( is_user_logged_in() ) {
            $default_active_tab = wpuf_get_option( 'account_page_active_tab', 'wpuf_my_account', 'dashboard' );
            $section            = isset( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : $default_active_tab;
            $sections           = wpuf_get_account_sections();
            $current_section    = [];
            foreach ( $sections as $slug => $label ) {
                if ( $section === $slug ) {
                    $current_section = $slug;
                    break;
                }
            }
            $template_args = [
                'sections'        => $sections,
                'current_section' => $current_section,
            ];

            /**
             * Filters the template arguments passed to account.php
             *
             * @since 4.3.1
             *
             * @param array $template_args Template arguments.
             */
            $template_args = apply_filters( 'wpuf_account_template_args', $template_args );

            wpuf_load_template( 'account.php', $template_args );
        } else {
            $message = wpuf_get_option( 'un_auth_msg', 'wpuf_dashboard' );
            wpuf_load_template( 'unauthorized.php', [ 'message' => $message ] );
        }
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Display the dashboard section
     *
     * @since  2.4.2
     *
     * @param string $current_section
     *
     * @param array  $sections
     *
     * @return void
     */
    public function dashboard_section( $sections, $current_section ) {
        wpuf_load_template(
            'dashboard/dashboard.php', [
				'sections'        => $sections,
				'current_section' => $current_section,
			]
        );
    }

    /**
     * Display the posts section
     *
     * @since  2.4.2
     *
     * @param string $current_section
     *
     * @param array  $sections
     *
     * @return void
     */
    public function posts_section( $sections, $current_section ) {
        wpuf_load_template(
            'dashboard/posts.php', [
				'sections'        => $sections,
				'current_section' => $current_section,
			]
        );
    }

    /**
     * Display the subscription section
     *
     * @since  2.4.2
     *
     * @param string $current_section
     *
     * @param array  $sections
     *
     * @return void
     */
    public function subscription_section( $sections, $current_section ) {
        $wpuf_user = wpuf_get_user();
        $sub_id    = $wpuf_user->subscription()->current_pack_id();
        if ( ! $sub_id ) {
            echo '<p>' . esc_html__( 'You have not subscribed to any package yet.', 'wp-user-frontend' ) . '</p>';

            return;
        }
        $user_subscription = new User_Subscription( $wpuf_user );
        $user_sub          = $user_subscription->current_pack();
        if ( ! is_wp_error( $user_sub ) && $user_sub['status'] !== 'completed' && $user_sub['status'] !== 'Free' ) {
            echo '<p>' . esc_html__( 'You may have processed your payment, but the pack is not active yet.', 'wp-user-frontend' ) . '</p>';

            return;
        }
        $pack = wpuf()->subscription->get_subscription( $sub_id );

        if ( ! $pack ) {
            echo wp_kses_post(
                sprintf(
                // translators: %1$s and %2$s are HTML tags
                    __( '%1$sYour subscription pack is not exists. Please contact admin.%2$s', 'wp-user-frontend' ), '<p>', '</p>'
                )
            );

            return;
        }

        $details_meta['payment_page'] = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
        $details_meta['onclick']      = '';
        $details_meta['symbol']       = wpuf_get_currency( 'symbol' );
        $recurring_des = '';
        $billing_amount = ( intval( $pack->meta_value['billing_amount'] ) > 0 ) ? $details_meta['symbol'] . $pack->meta_value['billing_amount'] : __(
            'Free',
            'wp-user-frontend'
        );
        if ( wpuf_is_checkbox_or_toggle_on( $pack->meta_value['recurring_pay'] ) ) {
            /* translators: %s: billing cycle number, %s: billing cycle period */
            $recurring_des = sprintf(
                __( 'For each', 'wp-user-frontend' ) . ' %s %s',
                $pack->meta_value['billing_cycle_number'],
                Subscription::get_cycle_label(
                    $pack->meta_value['cycle_period'],
                    $pack->meta_value['billing_cycle_number']
                ),
                $pack->meta_value['_trial_duration_type']
            );
            $recurring_des .= ! empty( $pack->meta_value['billing_limit'] ) && -1 === $pack->meta_value['billing_limit'] ? sprintf(
                /* translators: %s: number of installments */
                __(
                    ', for %s installments',
                    'wp-user-frontend'
                ),
                $pack->meta_value['billing_limit']
            ) : '';
        }
        ob_start();
        wpuf_load_template(
            'dashboard/subscription.php', [
                'sections'        => $sections,
                'current_section' => $current_section,
                'userdata'        => $wpuf_user->user,
                'user_sub'        => $user_sub,
                'pack'            => $pack,
                'billing_amount'  => $billing_amount,
                'recurring_des'   => $recurring_des,
            ]
        );
        ob_end_flush();
    }

    /**
     * Display the edit profile section
     *
     * @since  2.4.2
     *
     * @param string $current_section
     *
     * @param array  $sections
     *
     * @return void
     */
    public function edit_profile_section( $sections, $current_section ) {
        wpuf_load_template(
            'dashboard/edit-profile.php', [
				'sections'        => $sections,
				'current_section' => $current_section,
			]
        );
    }

    /**
     * Display the change password section.
     *
     * @since 4.3.9
     *
     * @param array  $sections        Account sections.
     * @param string $current_section Active section slug.
     *
     * @return void
     */
    public function change_password_section( $sections, $current_section ) {
        wpuf_load_template(
            'dashboard/change-password.php', [
				'sections'        => $sections,
				'current_section' => $current_section,
			]
        );
    }

    /**
     * Handle change password AJAX request.
     *
     * @since 4.3.9
     *
     * @return void Sends JSON response.
     */
    public function change_password() {
        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf-account-change-password' ) ) {
            wp_send_json_error( __( 'Security check failed.', 'wp-user-frontend' ) );
            wp_die();
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( __( 'You must be logged in.', 'wp-user-frontend' ) );
            wp_die();
        }

        // The body lives in Account_Service::change_password() (shared with REST).
        $result = $this->account()->change_password( wp_unslash( $_POST ) );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
            return;
        }

        wp_send_json_success( __( 'Password updated successfully!', 'wp-user-frontend' ) );
    }

    /**
     * Display the billing address section
     *
     * @param array  $sections
     * @param string $current_section
     *
     * @return void
     */
    public function billing_address_section( $sections, $current_section ) {
        wpuf_load_template(
            'dashboard/billing-address.php',
            [
                'sections'        => $sections,
                'current_section' => $current_section,
            ]
        );
    }

    /**
     * Update profile via Ajax
     *
     * @since  2.4.2
     *
     * @return json
     */
    public function update_profile() {
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-account-update-profile' ) ) {
            wp_send_json_error( __( 'Nonce failure', 'wp-user-frontend' ) );
        }

        // The body lives in Account_Service::update_profile() (shared with REST).
        $result = $this->account()->update_profile( wp_unslash( $_POST ) );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success();
    }

    /**
     * The React or classic decision for this request.
     *
     * @since WPUF_SINCE
     *
     * @return Renderer
     */
    protected function renderer() {
        return wpuf()->platform()->get( Renderer::class );
    }

    /**
     * The account data and actions.
     *
     * @since WPUF_SINCE
     *
     * @return Account_Service
     */
    protected function account() {
        return wpuf()->platform()->get( Account_Service::class );
    }

    /**
     * The markup the React account app mounts into: the classic container
     * class, the profile, sections and stats as boot data, a skeleton of the
     * sidebar and content. Sections served as server HTML still need the
     * classic form bundle (billing, subscription, submit post), so it is
     * enqueued when any such section is listed.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    protected function mount_account() {
        $account  = $this->account();
        $sections = $account->sections();
        $boot     = [
            'id'       => get_current_user_id(),
            'profile'  => $account->profile(),
            'sections' => $sections,
            'stats'    => $account->stats(),
            'settings' => [
                'page_url'    => $account->page_url(),
                'default_tab' => $account->default_tab(),
                'per_page'    => (int) wpuf_get_option( 'per_page', 'wpuf_dashboard', 5 ),
                'post_types'  => $account->allowed_post_types(),
            ],
            'nonces'   => [
                'profile'  => wp_create_nonce( 'wpuf-account-update-profile' ),
                'password' => wp_create_nonce( 'wpuf-account-change-password' ),
            ],
        ];

        foreach ( $sections as $section ) {
            if ( 'html' === $section['kind'] ) {
                wpuf()->frontend->enqueue_form_assets();
                wp_enqueue_style( 'wpuf-frontend-account-sections-style' );
                break;
            }
        }

        $slugs = wp_list_pluck( $sections, 'slug' );

        /**
         * Filters whether the React account page loads the post form app and
         * the WordPress editor up front, for sections that show a post form
         * (the editor prints through footer hooks, so a section loaded later
         * without a page load cannot bring it).
         *
         * @since WPUF_SINCE
         *
         * @param bool  $preload Whether to preload
         * @param array $slugs   Section slugs
         */
        if ( apply_filters( 'wpuf_account_preload_post_form', in_array( 'submit-post', $slugs, true ), $slugs ) ) {
            $this->renderer()->enqueue( 'forms' );
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        // A section served as PHP output renders with the page, like the classic
        // account: the scripts and sheets it enqueues (editor, chat, billing)
        // reach the page. The app shows it without a REST round trip.
        $current = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : $boot['settings']['default_tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        foreach ( $sections as $section ) {
            if ( $current === $section['slug'] && 'html' === $section['kind'] ) {
                $pagenum = isset( $_GET['pagenum'] ) ? absint( wp_unslash( $_GET['pagenum'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $html    = $account->section_html( $current, max( 1, $pagenum ) );

                if ( ! is_wp_error( $html ) ) {
                    $boot['section_html'] = [
                        'slug' => $current,
                        'html' => $html,
                    ];
                }

                break;
            }
        }

        $items = '';

        foreach ( array_slice( $sections, 0, 6 ) as $section ) {
            $items .= '<li><span class="wpuf-account-nav-item wpuf-skeleton-row"><span class="wpuf-skeleton wpuf-skeleton-icon"></span><span class="wpuf-skeleton wpuf-skeleton-label"></span></span></li>';
        }

        $skeleton = sprintf(
            '<div class="wpuf-account-container wpuf-account-boot" aria-busy="true"><aside class="wpuf-account-sidebar"><div class="wpuf-profile-section"><span class="wpuf-skeleton wpuf-skeleton-avatar"></span><span class="wpuf-skeleton wpuf-skeleton-label"></span></div><nav class="wpuf-account-nav"><ul>%s</ul></nav></aside><div class="wpuf-account-content"><span class="wpuf-skeleton wpuf-skeleton-card"></span></div></div>',
            $items
        );

        /**
         * Whether the account page uses the theme's wide alignment. Block themes
         * cap `alignwide` at their wide size; classic themes turn it into
         * negative side margins that run past the screen (Hello Elementor,
         * Neve on phones), so there it stays in the content column.
         *
         * @since WPUF_SINCE
         *
         * @param bool $align_wide Whether to add `alignwide`
         */
        $align_wide = (bool) apply_filters( 'wpuf_account_align_wide', function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );

        return $this->renderer()->markup( 'account', $boot, $skeleton, 'wpuf-account-root' . ( $align_wide ? ' alignwide' : '' ) );
    }

    /**
     * Get CPT for shwoing in dashboard area
     *
     * @return mixed|string
     */
    public function get_allowed_cpt() {
        return wpuf_get_option( 'cp_on_acc_page', 'wpuf_my_account', [ 'post' ] );
    }

    /**
     * Check if the user is the post author and give permission for previewing
     *
     * @since 3.5.27
     *
     * @return void
     */
    public function process_user_for_previewing_post( $query ) {
        if ( current_user_can( 'edit_posts' ) ) {
            return;
        }
        if ( ! $query->is_main_query() && ! $query->is_preview && ! $query->get( 'p' ) ) {
            return;
        }
        $current_user_id = get_current_user_id();
        $current_post_id = absint( $query->get( 'p' ) );
        if ( $current_user_id === absint( get_post_field( 'post_author', $current_post_id ) ) ) {
            add_filter( 'user_has_cap', [ $this, 'add_temporary_capability' ], 10, 3 );
        }
    }

    /**
     * Add a temporary edit_posts capability to the current user
     * for previewing post
     *
     * @since 3.5.27
     *
     * @param $all_caps
     *
     * @return array
     */
    public function add_temporary_capability( $all_caps ) {
        $all_caps['edit_posts'] = true;

        return $all_caps;
    }
}
