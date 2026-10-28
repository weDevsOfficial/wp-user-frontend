<?php
/**
 * Setup prerequisites the admin screens point out
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Setup;

use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Platform\Onboarding\Pages;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * What a feature needs from elsewhere before it works for site visitors:
 * the pages the settings pick must hold their shortcode, subscriptions need
 * payments, a plans page, a payment page and a gateway. Read only, except
 * add_page_shortcode() (the "Add shortcode" quick action).
 *
 * @since WPUF_SINCE
 */
class Prerequisites {

    /**
     * Page settings that show a WPUF screen through a shortcode:
     * setting => [ section, shortcode tag ].
     *
     * @var array
     */
    const PAGE_SHORTCODES = [
        'edit_page_id'      => [ 'wpuf_frontend_posting', 'wpuf_edit' ],
        'account_page'      => [ 'wpuf_my_account', 'wpuf_account' ],
        'reg_override_page' => [ 'wpuf_profile', 'wpuf-registration' ],
        'login_page'        => [ 'wpuf_profile', 'wpuf-login' ],
        'subscription_page' => [ 'wpuf_payment', 'wpuf_sub_pack' ],
    ];

    /**
     * Page lookups
     *
     * @var Pages
     */
    private $pages;

    /**
     * @param Pages|null $pages Page lookups (the onboarding service).
     */
    public function __construct( $pages = null ) {
        $this->pages = $pages ? $pages : new Pages();
    }

    /**
     * Which shortcode each page setting needs.
     *
     * @since WPUF_SINCE
     *
     * @return array setting => [ 'section' => id, 'tag' => tag, 'shortcode' => '[tag]' ]
     */
    public function page_requirements() {
        $out = [];

        foreach ( self::PAGE_SHORTCODES as $setting => $need ) {
            $out[ $setting ] = [
                'section'   => $need[0],
                'tag'       => $need[1],
                'shortcode' => '[' . $need[1] . ']',
            ];
        }

        return $out;
    }

    /**
     * The WPUF shortcodes of every page that holds one, and the pages built
     * with a page builder (their content is not in post_content, so they are
     * never reported as missing a shortcode).
     *
     * @since WPUF_SINCE
     *
     * @return array { pages: { id: [ tags ] }, builder: [ ids ] }
     */
    public function page_map() {
        global $wpdb;

        $tags = array_unique( wp_list_pluck( $this->page_requirements(), 'tag' ) );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one read of page content for the settings screen.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status NOT IN ( 'trash', 'auto-draft', 'inherit' ) AND post_content LIKE %s",
                '%' . $wpdb->esc_like( '[wpuf' ) . '%'
            )
        );

        $pages = [];

        foreach ( (array) $rows as $row ) {
            $found = [];

            foreach ( $tags as $tag ) {
                if ( $this->pages->content_has_shortcode( (string) $row->post_content, $tag ) ) {
                    $found[] = $tag;
                }
            }

            if ( $found ) {
                $pages[ (int) $row->ID ] = $found;
            }
        }

        return [
            'pages'   => (object) $pages,
            'builder' => $this->builder_pages(),
        ];
    }

    /**
     * Pages made with a page builder that keeps its content in post meta.
     *
     * @since WPUF_SINCE
     *
     * @return int[]
     */
    private function builder_pages() {
        global $wpdb;

        /**
         * Filters the post meta that marks a page built with a page builder
         * (meta key => value, '' for any value). Such pages are never reported
         * as missing a shortcode.
         *
         * @since WPUF_SINCE
         *
         * @param array $markers Meta key => value
         */
        $markers = (array) apply_filters(
            'wpuf_setup_page_builder_markers', [
                '_elementor_edit_mode' => 'builder',
                '_et_pb_use_builder'   => 'on',
                '_fl_builder_enabled'  => '1',
                'brizy_post_uid'       => '',
            ]
        );

        $ids = [];

        foreach ( $markers as $key => $value ) {
            $sql = '' === $value
                ? $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s", $key )
                : $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", $key, $value );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- prepared above.
            $ids = array_merge( $ids, array_map( 'intval', (array) $wpdb->get_col( $sql ) ) );
        }

        return array_values( array_unique( $ids ) );
    }

    /**
     * Whether a page shows what a page setting needs.
     *
     * @since WPUF_SINCE
     *
     * @param string $setting Page setting name (PAGE_SHORTCODES)
     * @param int    $page_id Page id
     *
     * @return string ok | missing_page | missing_shortcode | unknown (page builder)
     */
    public function page_status( $setting, $page_id ) {
        $page_id = absint( $page_id );

        if ( ! $page_id || ! $this->pages->is_page( $page_id ) ) {
            return 'missing_page';
        }

        if ( ! isset( self::PAGE_SHORTCODES[ $setting ] ) ) {
            return 'ok';
        }

        $map = $this->page_map();

        if ( in_array( $page_id, $map['builder'], true ) ) {
            return 'unknown';
        }

        $pages = (array) $map['pages'];

        return isset( $pages[ $page_id ] ) && in_array( self::PAGE_SHORTCODES[ $setting ][1], $pages[ $page_id ], true ) ? 'ok' : 'missing_shortcode';
    }

    /**
     * Append a page setting's shortcode to a page (the "Add shortcode" quick action).
     *
     * @since WPUF_SINCE
     *
     * @param string $setting Page setting name (PAGE_SHORTCODES)
     * @param int    $page_id Page id
     *
     * @return bool Whether the page changed
     */
    public function add_page_shortcode( $setting, $page_id ) {
        if ( ! isset( self::PAGE_SHORTCODES[ $setting ] ) ) {
            return false;
        }

        $tag = self::PAGE_SHORTCODES[ $setting ][1];

        return $this->pages->ensure_page_shortcode( absint( $page_id ), $tag, '[' . $tag . ']' );
    }

    /**
     * What the subscriptions need before visitors can buy a pack. Each check:
     * id, ok, severity (error | warning | info), title, description and the
     * quick action (settings section, page id for "Add shortcode" / "Edit page").
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function subscription_checks() {
        $settings = Stores::settings();
        $checks   = [];

        $payments_on = 'on' === $settings->get( 'wpuf_payment', 'enable_payment', 'on' );

        $checks[] = [
            'id'          => 'payments',
            'ok'          => $payments_on,
            'severity'    => 'error',
            'title'       => __( 'Payments are turned off', 'wp-user-frontend' ),
            'description' => __( 'Visitors cannot buy subscription packs until payments are enabled.', 'wp-user-frontend' ),
            'section'     => 'wpuf_payment',
        ];

        $plans_page = absint( $settings->get( 'wpuf_payment', 'subscription_page', 0 ) );
        $plans      = $this->page_status( 'subscription_page', $plans_page );
        // "Subscription at registration" sends every new user to this page.
        $at_signup = 'on' === $settings->get( 'wpuf_payment', 'register_subscription', 'off' );

        $checks[] = [
            'id'          => 'subscription_page',
            'ok'          => in_array( $plans, [ 'ok', 'unknown' ], true ),
            'severity'    => $at_signup ? 'error' : 'warning',
            'title'       => 'missing_page' === $plans
                ? __( 'No subscription pack page', 'wp-user-frontend' )
                : __( 'The subscription pack page has no [wpuf_sub_pack] shortcode', 'wp-user-frontend' ),
            'description' => $at_signup
                ? __( '"Subscription at registration" is on, so new users are sent to this page after they register. Until it lists the packs, they cannot pick one.', 'wp-user-frontend' )
                : ( 'missing_page' === $plans
                    ? __( 'Pick the page that lists your packs, so the plugin can link visitors to it.', 'wp-user-frontend' )
                    : __( 'The page picked in the settings does not list the packs. Add the shortcode to it.', 'wp-user-frontend' ) ),
            'section'     => 'wpuf_payment',
            'page'        => 'missing_shortcode' === $plans ? $plans_page : 0,
            'setting'     => 'subscription_page',
        ];

        $payment_page = absint( $settings->get( 'wpuf_payment', 'payment_page', 0 ) );

        $checks[] = [
            'id'          => 'payment_page',
            'ok'          => (bool) $this->pages->is_page( $payment_page ),
            'severity'    => 'warning',
            'title'       => __( 'No payment page', 'wp-user-frontend' ),
            'description' => __( 'Buying a paid pack opens the payment page. Pick one in the payment settings.', 'wp-user-frontend' ),
            'section'     => 'wpuf_payment',
        ];

        $gateways = $this->active_gateways();

        $checks[] = [
            'id'          => 'gateway',
            'ok'          => ! empty( $gateways ),
            'severity'    => 'warning',
            'title'       => __( 'No payment gateway is active', 'wp-user-frontend' ),
            'description' => __( 'Paid packs need at least one gateway, for example Bank Payment or PayPal.', 'wp-user-frontend' ),
            'section'     => 'wpuf_payment',
        ];

        if ( $gateways && $this->has_recurring_pack() ) {
            $checks[] = [
                'id'          => 'recurring_gateway',
                'ok'          => (bool) array_filter( wp_list_pluck( $gateways, 'supports_subscription' ) ),
                'severity'    => 'warning',
                'title'       => __( 'No active gateway takes recurring payments', 'wp-user-frontend' ),
                'description' => __( 'A published pack is recurring, but none of the active gateways can charge again. Activate one that supports recurring payments, such as PayPal or Stripe (Pro).', 'wp-user-frontend' ),
                'section'     => 'wpuf_payment',
            ];
        }

        if ( isset( $gateways['paypal'] ) ) {
            $checks[] = [
                'id'          => 'paypal_test_mode',
                'ok'          => 'test' !== $settings->get( 'wpuf_payment', 'paypal_test_mode', '' ),
                'severity'    => 'info',
                'title'       => __( 'PayPal is in test mode', 'wp-user-frontend' ),
                'description' => __( 'Payments go to the PayPal sandbox and no real money is charged. Switch PayPal to live mode when the site goes live.', 'wp-user-frontend' ),
                'section'     => 'wpuf_payment',
            ];

            $checks[] = [
                'id'          => 'paypal_credentials',
                'ok'          => '' !== trim( (string) $settings->get( 'wpuf_payment', 'paypal_email', '' ) ) || '' !== trim( (string) $settings->get( 'wpuf_payment', 'paypal_client_id', '' ) ),
                'severity'    => 'warning',
                'title'       => __( 'PayPal is active but not set up', 'wp-user-frontend' ),
                'description' => __( 'Add your PayPal email or API credentials in the payment settings.', 'wp-user-frontend' ),
                'section'     => 'wpuf_payment',
            ];
        }

        $checks[] = [
            'id'          => 'registration',
            'ok'          => $this->registration_open(),
            'severity'    => 'warning',
            'title'       => __( 'Visitors can\'t create an account to buy a plan', 'wp-user-frontend' ),
            'description' => __( 'Logged-out visitors are asked to sign up before they pay, but new user registration is turned off in WordPress. Turn on "Anyone can register".', 'wp-user-frontend' ),
            'actions'     => [
                [
                    'label' => __( 'Allow sign-ups', 'wp-user-frontend' ),
                    'url'   => is_multisite() ? network_admin_url( 'settings.php#registration' ) : admin_url( 'options-general.php#users_can_register' ),
                ],
            ],
        ];

        $uncovered = $this->forms_without_pack();

        if ( $uncovered ) {
            $first     = reset( $uncovered );
            $checks[] = [
                'id'          => 'forms_without_pack',
                'ok'          => false,
                'severity'    => 'warning',
                'title'       => sprintf(
                    /* translators: %d: number of post forms */
                    _n( '%d post form requires a subscription that no published pack offers', '%d post forms require a subscription that no published pack offers', count( $uncovered ), 'wp-user-frontend' ),
                    count( $uncovered )
                ),
                'description' => sprintf(
                    /* translators: 1: form title, 2: post type label */
                    __( '"%1$s" needs a pack that allows %2$s, so buyers would pay and still not be able to post. Publish a pack that allows it, or change the form\'s payment setting.', 'wp-user-frontend' ),
                    $first['title'],
                    $first['type']
                ),
                'actions'     => [
                    [
                        'label' => __( 'Add a pack', 'wp-user-frontend' ),
                        'route' => '/subscriptions?action=new',
                    ],
                    [
                        'label' => __( 'Open the form', 'wp-user-frontend' ),
                        'route' => '/post-forms/' . $first['id'] . '/edit?tab=settings&section=payment_settings',
                    ],
                ],
            ];
        }

        /**
         * Filters the subscription setup checks shown above the packs list.
         *
         * @since WPUF_SINCE
         *
         * @param array[] $checks Checks (id, ok, severity, title, description, section, page, setting,
         *                        actions: [ { label, url | route } ])
         */
        return array_values( (array) apply_filters( 'wpuf_subscription_setup_checks', $checks ) );
    }

    /**
     * The active gateways that exist on this site (a Pro gateway left
     * active after Pro was turned off does not count).
     *
     * @since WPUF_SINCE
     *
     * @return array id => [ 'label' => string, 'supports_subscription' => bool ]
     */
    public function active_gateways() {
        $active    = Stores::settings()->get( 'wpuf_payment', 'active_gateways', [] );
        $active    = is_array( $active ) ? array_keys( array_filter( $active ) ) : [];
        $available = class_exists( Payment::class ) ? (array) Payment::get_payment_gateways() : [];
        $out       = [];

        foreach ( $active as $id ) {
            if ( isset( $available[ $id ] ) && empty( $available[ $id ]['is_pro_preview'] ) ) {
                // As the checkout decides (Checkout_Service::recurring_gateways()): a
                // gateway registered with the manager says so (Bank does not); one added
                // only through `wpuf_payment_gateways` (Pro Stripe) handles recurring itself.
                $gateway    = function_exists( 'wpuf_get_gateway' ) ? wpuf_get_gateway( $id ) : null;
                $out[ $id ] = [
                    'label'                 => isset( $available[ $id ]['admin_label'] ) ? wp_strip_all_tags( (string) $available[ $id ]['admin_label'] ) : $id,
                    'supports_subscription' => ! $gateway || $gateway->supports_subscription(),
                ];
            }
        }

        return $out;
    }

    /**
     * Whether visitors may create an account (WordPress "Anyone can register",
     * or the network registration setting on multisite).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function registration_open() {
        if ( is_multisite() ) {
            return in_array( get_site_option( 'registration' ), [ 'user', 'all' ], true );
        }

        return (bool) get_option( 'users_can_register' );
    }

    /**
     * Published post forms set to "Mandatory Subscription" whose post type no
     * published pack allows (`_post_type_name[ type ]` unset or 0).
     *
     * @since WPUF_SINCE
     *
     * @return array[] id, title, type (label)
     */
    public function forms_without_pack() {
        $forms = get_posts(
            [
                'post_type'      => 'wpuf_forms',
                'post_status'    => 'publish',
                'posts_per_page' => 100,
                'fields'         => 'ids',
            ]
        );

        $needs = [];

        foreach ( $forms as $form_id ) {
            $settings = Stores::forms()->read_settings( $form_id );

            if ( ! empty( $settings['payment_options'] ) && wpuf_is_checkbox_or_toggle_on( $settings['payment_options'] ) && isset( $settings['choose_payment_option'] ) && 'force_pack_purchase' === $settings['choose_payment_option'] ) {
                $needs[ $form_id ] = ! empty( $settings['post_type'] ) ? (string) $settings['post_type'] : 'post';
            }
        }

        if ( ! $needs ) {
            return [];
        }

        $allowed = [];
        $packs   = get_posts(
            [
                'post_type'      => 'wpuf_subscription',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]
        );

        foreach ( $packs as $pack_id ) {
            $limits = maybe_unserialize( get_post_meta( $pack_id, '_post_type_name', true ) );

            foreach ( (array) $limits as $type => $limit ) {
                if ( '0' !== (string) $limit && '' !== (string) $limit ) {
                    $allowed[ $type ] = true;
                }
            }

            $extra = maybe_unserialize( get_post_meta( $pack_id, 'additional_cpt_options', true ) );

            foreach ( (array) $extra as $type => $limit ) {
                if ( '0' !== (string) $limit && '' !== (string) $limit ) {
                    $allowed[ $type ] = true;
                }
            }
        }

        $out = [];

        foreach ( $needs as $form_id => $type ) {
            if ( isset( $allowed[ $type ] ) ) {
                continue;
            }

            $object = get_post_type_object( $type );
            $out[]  = [
                'id'    => (int) $form_id,
                'title' => wp_strip_all_tags( get_the_title( $form_id ) ),
                'type'  => $object ? $object->labels->name : $type,
            ];
        }

        return $out;
    }

    /**
     * Whether a published pack charges again (recurring).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function has_recurring_pack() {
        $ids = get_posts(
            [
                'post_type'      => 'wpuf_subscription',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- a few packs, once per screen load.
                'meta_query'     => [
                    [
                        'key'   => '_recurring_pay',
                        'value' => [ 'yes', 'on' ],
                        'compare' => 'IN',
                    ],
                ],
            ]
        );

        return ! empty( $ids );
    }
}
