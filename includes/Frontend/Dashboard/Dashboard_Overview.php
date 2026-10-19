<?php
/**
 * The overview blocks of the `[wpuf_dashboard]` page
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Dashboard;

use WeDevs\Wpuf\Frontend\Account\Account_Service;
use WeDevs\Wpuf\Frontend\Subscriptions\Subscription_Schema;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Query;

/**
 * What the dashboard shows around the posts list: the header facts, the
 * status cards, what needs the member's attention, quick actions, the plan
 * card, recent comments on their posts and recent payments. Each block has
 * its own setting under Settings > Frontend Posting > Dashboard and is left
 * out (null / empty) when off. Everything is about the current user.
 *
 * @since WPUF_SINCE
 */
class Dashboard_Overview {

    /**
     * Statuses with a card, in order, with their card tone.
     *
     * @since WPUF_SINCE
     */
    const STATUSES = [
        'publish' => 'green',
        'pending' => 'amber',
        'draft'   => 'gray',
        'future'  => 'blue',
    ];

    /**
     * The block settings and their defaults (all on).
     *
     * @since WPUF_SINCE
     */
    const BLOCKS = [
        'status_cards'    => 'dashboard_status_cards',
        'quick_actions'   => 'dashboard_quick_actions',
        'attention'       => 'dashboard_attention',
        'plan'            => 'dashboard_plan_card',
        'comments'        => 'dashboard_recent_comments',
        'payments'        => 'dashboard_recent_payments',
        'table_filters'   => 'dashboard_table_filters',
    ];

    /**
     * The posts list, for the shortcode's query.
     *
     * @var Dashboard_Service
     */
    private $service;

    /**
     * The plan card data (null when the plans service is not there).
     *
     * @var Subscription_Schema|null
     */
    private $plans;

    /**
     * The account page's profile data (the shared hero).
     *
     * @var Account_Service
     */
    private $account;

    /**
     * @since WPUF_SINCE
     *
     * @param Dashboard_Service        $service Dashboard service
     * @param Account_Service          $account Account service
     * @param Subscription_Schema|null $plans   Plan card data
     */
    public function __construct( Dashboard_Service $service, Account_Service $account, $plans = null ) {
        $this->service = $service;
        $this->account = $account;
        $this->plans   = $plans;
    }

    /**
     * Which blocks the owner switched on.
     *
     * @since WPUF_SINCE
     *
     * @return bool[] block => on
     */
    public function blocks() {
        $payments = $this->payments_enabled();
        $blocks   = [];

        foreach ( self::BLOCKS as $block => $option ) {
            $blocks[ $block ] = 'off' !== Stores::settings()->get( 'wpuf_dashboard', $option, 'on' );
        }

        // Plan and payments mean nothing on a site that takes no payments.
        $blocks['plan']     = $blocks['plan'] && $payments;
        $blocks['payments'] = $blocks['payments'] && $payments;

        return $blocks;
    }

    /**
     * Every block's data for the shortcode's attributes.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes (Dashboard_Service::attributes())
     *
     * @return array
     */
    public function build( array $atts ) {
        $blocks = $this->blocks();
        $plan   = $blocks['plan'] || $blocks['attention'] ? $this->plan( $atts ) : null;

        $data = [
            'blocks'    => $blocks,
            'profile'   => $this->profile( $plan ),
            'stats'     => $blocks['status_cards'] || $blocks['table_filters'] ? $this->stats( $atts ) : [],
            'actions'   => $blocks['quick_actions'] ? $this->actions() : [],
            'attention' => $blocks['attention'] ? $this->attention( $atts, $plan ) : [],
            'plan'      => $blocks['plan'] ? $plan : null,
            'comments'  => $blocks['comments'] ? $this->comments( $atts ) : null,
            'payments'  => $blocks['payments'] ? $this->payments() : null,
        ];

        /**
         * Filters the overview blocks of the React `[wpuf_dashboard]` page.
         *
         * @since WPUF_SINCE
         *
         * @param array $data Blocks data
         * @param array $atts Shortcode attributes
         */
        return apply_filters( 'wpuf_dashboard_overview', $data, $atts );
    }

    /**
     * The hero: the account page's profile (avatar, cover, name, username,
     * email, role, `wpuf_account_profile` filter) plus the dashboard's own
     * facts: member since, the bio (Settings > Dashboard > Show user bio),
     * the public posts link and the active plan.
     *
     * @since WPUF_SINCE
     *
     * @param array|null $plan Plan card data
     *
     * @return array
     */
    public function profile( $plan = null ) {
        $profile = $this->account->profile();
        $user    = wp_get_current_user();

        unset( $profile['logout_url'] );

        $profile['bio']          = 'on' === Stores::settings()->get( 'wpuf_dashboard', 'show_user_bio', 'on' ) ? $profile['bio'] : '';
        $profile['member_since'] = $user->user_registered ? date_i18n( get_option( 'date_format' ), strtotime( $user->user_registered ) ) : '';
        $profile['author_url']   = (string) get_author_posts_url( $user->ID );
        $profile['plan']         = $plan && ! empty( $plan['pack']['title'] ) && 'active' === $plan['state'] ? (string) $plan['pack']['title'] : '';

        return $profile;
    }

    /**
     * The status cards: how many of the list's posts are in each status the
     * list shows (the shortcode's own query, so `form_id` and the rest apply).
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return array[] [ status, label, count, tone ]
     */
    public function stats( array $atts ) {
        $allowed = $this->service->statuses( $atts );
        $cards   = [];

        foreach ( self::STATUSES as $status => $tone ) {
            if ( ! in_array( $status, $allowed, true ) ) {
                continue;
            }

            $args                   = $this->service->query_args( $atts, 1 );
            $args['post_status']    = [ $status ];
            $args['posts_per_page'] = 1;
            $args['fields']         = 'ids';
            $query                  = new WP_Query( $args );

            $cards[] = [
                'status' => $status,
                'label'  => $this->status_label( $status ),
                'count'  => (int) $query->found_posts,
                'tone'   => $tone,
            ];
        }

        return $cards;
    }

    /**
     * Quick actions: submit a new post, edit the profile, the account page.
     *
     * @since WPUF_SINCE
     *
     * @return array[] [ key, label, url, primary ]
     */
    public function actions() {
        $account = (int) Stores::settings()->get( 'wpuf_my_account', 'account_page' );
        $account = $account ? (string) get_permalink( $account ) : '';
        $new     = (int) Stores::settings()->get( 'wpuf_dashboard', 'dashboard_new_post_page' );
        $new_url = $new ? (string) get_permalink( $new ) : '';

        // No page chosen: the account page's Submit Post section, when it has one.
        if ( ! $new_url && $account && 'on' === Stores::settings()->get( 'wpuf_my_account', 'allow_post_submission', 'on' ) ) {
            $new_url = add_query_arg( 'section', 'submit-post', $account );
        }

        $actions = [];

        if ( $new_url ) {
            $actions[] = [
                'key'     => 'new_post',
                'label'   => __( 'Submit new post', 'wp-user-frontend' ),
                'url'     => $new_url,
                'primary' => true,
            ];
        }

        if ( $account ) {
            $actions[] = [
                'key'     => 'edit_profile',
                'label'   => __( 'Edit profile', 'wp-user-frontend' ),
                'url'     => add_query_arg( 'section', 'edit-profile', $account ),
                'primary' => false,
            ];
            $actions[] = [
                'key'     => 'account',
                'label'   => __( 'My account', 'wp-user-frontend' ),
                'url'     => $account,
                'primary' => false,
            ];
        }

        return $actions;
    }

    /**
     * What needs the member's attention: posts waiting for payment, posts
     * that expire within a week, a plan that expired or has no posts left,
     * editing locked by an admin.
     *
     * @since WPUF_SINCE
     *
     * @param array      $atts Attributes
     * @param array|null $plan Plan card data
     *
     * @return array[] [ key, tone, text, action: { label, url } | null ]
     */
    public function attention( array $atts, $plan = null ) {
        $items = [];
        $user  = wpuf_get_user();

        if ( $user->edit_post_locked() ) {
            $reason  = (string) $user->edit_post_lock_reason();
            $items[] = [
                'key'    => 'locked',
                'tone'   => 'danger',
                'text'   => $reason ? $reason : __( 'Your post edit access has been locked by an administrator.', 'wp-user-frontend' ),
                'action' => null,
            ];
        }

        if ( $this->payments_enabled() ) {
            $args                   = $this->service->query_args( $atts, 1 );
            $args['posts_per_page'] = 1;
            $args['meta_query']     = array_merge(
                isset( $args['meta_query'] ) ? (array) $args['meta_query'] : [],
                [
                    [
                        'key'     => '_wpuf_payment_status',
                        'value'   => [ '', 'completed' ],
                        'compare' => 'NOT IN',
                    ],
                ]
            );
            $unpaid                 = new WP_Query( $args );

            if ( $unpaid->found_posts ) {
                $first   = $unpaid->posts[0];
                $pay_url = get_permalink( Stores::settings()->get( 'wpuf_payment', 'payment_page' ) );
                $items[] = [
                    'key'    => 'unpaid',
                    'tone'   => 'warning',
                    'text'   => sprintf(
                        /* translators: %d: number of posts */
                        _n( '%d post is waiting for payment.', '%d posts are waiting for payment.', (int) $unpaid->found_posts, 'wp-user-frontend' ),
                        (int) $unpaid->found_posts
                    ),
                    'action' => $pay_url ? [
                        'label' => __( 'Pay now', 'wp-user-frontend' ),
                        'url'   => add_query_arg(
                            [
                                'action'  => 'wpuf_pay',
                                'type'    => 'post',
                                'post_id' => $first->ID,
                            ],
                            trailingslashit( $pay_url )
                        ),
                    ] : null,
                ];
            }
        }

        $expiring = $this->expiring( $atts );

        if ( $expiring ) {
            $items[] = [
                'key'    => 'expiring',
                'tone'   => 'warning',
                'text'   => sprintf(
                    /* translators: 1: number of posts, 2: first post title */
                    _n( '%1$d post goes offline within 7 days: %2$s', '%1$d posts go offline within 7 days, first: %2$s', count( $expiring ), 'wp-user-frontend' ),
                    count( $expiring ),
                    get_the_title( $expiring[0] )
                ),
                'action' => null,
            ];
        }

        if ( $plan && 'expired' === $plan['state'] ) {
            $items[] = [
                'key'    => 'plan_expired',
                'tone'   => 'danger',
                'text'   => __( 'Your subscription has expired. Pick a plan to keep posting.', 'wp-user-frontend' ),
                'action' => ! empty( $plan['packs_url'] ) ? [
                    'label' => __( 'Renew subscription', 'wp-user-frontend' ),
                    'url'   => $plan['packs_url'],
                ] : null,
            ];
        } elseif ( $plan && 'active' === $plan['state'] && $this->plan_used_up( $atts ) ) {
            $items[] = [
                'key'    => 'plan_used',
                'tone'   => 'warning',
                'text'   => __( 'You have used all the posts your plan allows.', 'wp-user-frontend' ),
                'action' => ! empty( $plan['packs_url'] ) ? [
                    'label' => __( 'Change plan', 'wp-user-frontend' ),
                    'url'   => $plan['packs_url'],
                ] : null,
            ];
        }

        return $items;
    }

    /**
     * The plan card (the account Subscription section's data), or null. Its
     * "what is left" lists the list's post types (0 left included) and
     * featured items, not every post type the plan names.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return array|null
     */
    public function plan( array $atts = [] ) {
        if ( ! $this->plans ) {
            return null;
        }

        $info = $this->plans->info( false );
        unset( $info['slots'], $info['cancel'] );

        if ( 'active' !== $info['state'] ) {
            return $info;
        }

        $pack   = ( new \WeDevs\Wpuf\User_Subscription( wpuf_get_user() ) )->current_pack();
        $posts  = ! is_wp_error( $pack ) && isset( $pack['posts'] ) && is_array( $pack['posts'] ) ? $pack['posts'] : [];
        $limits = [];

        foreach ( $atts ? $this->service->post_types( $atts ) : array_keys( $posts ) as $type ) {
            $object = get_post_type_object( $type );

            if ( ! $object || ! isset( $posts[ $type ] ) ) {
                continue;
            }

            $left     = (int) $posts[ $type ];
            $limits[] = [
                'label' => (string) $object->labels->name,
                'value' => -1 === $left ? __( 'Unlimited', 'wp-user-frontend' ) : (string) max( 0, $left ),
                'empty' => 0 === $left,
            ];
        }

        if ( ! is_wp_error( $pack ) && isset( $pack['total_feature_item'] ) && '' !== (string) $pack['total_feature_item'] && '0' !== (string) $pack['total_feature_item'] ) {
            $featured = (int) $pack['total_feature_item'];
            $limits[] = [
                'label' => __( 'Featured items', 'wp-user-frontend' ),
                'value' => -1 === $featured ? __( 'Unlimited', 'wp-user-frontend' ) : (string) max( 0, $featured ),
                'empty' => false,
            ];
        }

        $info['limits'] = $limits;

        return $info;
    }

    /**
     * The last approved comments on the member's posts of the list's types.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return array[]
     */
    public function comments( array $atts ) {
        $comments = get_comments(
            [
                'post_author' => get_current_user_id(),
                'post_type'   => $this->service->post_types( $atts ),
                'status'      => 'approve',
                'number'      => 5,
            ]
        );
        $rows     = [];

        foreach ( $comments as $comment ) {
            $rows[] = [
                'id'      => (int) $comment->comment_ID,
                'author'  => (string) $comment->comment_author,
                'avatar'  => (string) get_avatar_url( $comment, [ 'size' => 64 ] ),
                'excerpt' => wp_trim_words( wp_strip_all_tags( $comment->comment_content ), 18 ),
                'post'    => (string) get_the_title( (int) $comment->comment_post_ID ),
                'url'     => (string) get_comment_link( $comment ),
                'when'    => sprintf(
                    /* translators: %s: time difference, e.g. "2 hours" */
                    __( '%s ago', 'wp-user-frontend' ),
                    human_time_diff( strtotime( $comment->comment_date_gmt . ' UTC' ) )
                ),
            ];
        }

        return $rows;
    }

    /**
     * The member's last payments, and where all of them are listed.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function payments() {
        $user_id = get_current_user_id();
        $rows    = Stores::transactions()->rows_for_user( $user_id, [ 'id', 'status', 'cost', 'post_id', 'pack_id', 'created' ], 3, 0, true );
        $items   = [];

        foreach ( $rows as $row ) {
            $item = '';

            if ( ! empty( $row['pack_id'] ) ) {
                $item = get_the_title( (int) $row['pack_id'] );
            } elseif ( ! empty( $row['post_id'] ) ) {
                $item = get_the_title( (int) $row['post_id'] );
            }

            $items[] = [
                'id'     => (int) $row['id'],
                'item'   => html_entity_decode( wp_strip_all_tags( (string) $item ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                'amount' => html_entity_decode( wp_strip_all_tags( wpuf_format_price( (float) $row['cost'] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
                'status' => (string) $row['status'],
                'date'   => $row['created'] ? date_i18n( get_option( 'date_format' ), strtotime( $row['created'] ) ) : '',
            ];
        }

        $account = (int) Stores::settings()->get( 'wpuf_my_account', 'account_page' );
        $all     = '';

        if ( $account && array_key_exists( 'invoices', wpuf_get_account_sections() ) ) {
            $all = add_query_arg( 'section', 'invoices', get_permalink( $account ) );
        }

        return [
            'items'    => $items,
            'total'    => Stores::transactions()->count_for_user( $user_id ),
            'all_url'  => $all,
        ];
    }

    /**
     * The list's live posts whose expiry date (Pro post expiration) falls
     * in the next 7 days.
     *
     * @param array $atts Attributes
     *
     * @return int[] Post IDs, soonest first
     */
    private function expiring( array $atts ) {
        $args                   = $this->service->query_args( $atts, 1 );
        $args['post_status']    = [ 'publish' ];
        $args['posts_per_page'] = 20;
        $args['fields']         = 'ids';
        $args['orderby']        = 'meta_value';
        $args['meta_key']       = 'wpuf-post_expiration_date'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
        $args['order']          = 'ASC';
        $args['meta_query']     = array_merge(
            isset( $args['meta_query'] ) ? (array) $args['meta_query'] : [],
            [
                [
                    'key'     => 'wpuf-post_expiration_date',
                    'value'   => [ current_time( 'Y-m-d' ), gmdate( 'Y-m-d', current_time( 'timestamp' ) + 7 * DAY_IN_SECONDS ) ], // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- the expiry dates are site-local Y-m-d.
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE',
                ],
            ]
        );

        return array_map( 'intval', ( new WP_Query( $args ) )->posts );
    }

    /**
     * Whether the member's plan has no posts left for any of the list's types.
     *
     * @param array $atts Attributes
     *
     * @return bool
     */
    private function plan_used_up( array $atts ) {
        $subscription = wpuf_get_user()->subscription();

        foreach ( $this->service->post_types( $atts ) as $type ) {
            if ( $subscription->has_post_count( $type ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the site takes payments.
     *
     * @return bool
     */
    private function payments_enabled() {
        return 'on' === Stores::settings()->get( 'wpuf_payment', 'enable_payment', 'on' );
    }

    /**
     * The card label of a status.
     *
     * @param string $status Status
     *
     * @return string
     */
    private function status_label( $status ) {
        $labels = [
            'publish' => __( 'Live', 'wp-user-frontend' ),
            'pending' => __( 'Pending review', 'wp-user-frontend' ),
            'draft'   => __( 'Drafts', 'wp-user-frontend' ),
            'future'  => __( 'Scheduled', 'wp-user-frontend' ),
        ];

        return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
    }
}
