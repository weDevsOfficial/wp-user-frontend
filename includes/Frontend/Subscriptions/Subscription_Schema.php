<?php
/**
 * Boot data of the React plans app
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Subscriptions;

use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\User_Subscription;

/**
 * What `[wpuf_sub_pack]` and `[wpuf_sub_info]` (and the account page's
 * Subscription section) render, as data for the React plans app:
 *
 * - packs(): the cards, from the same values the classic template prints
 *   (Pack_Display::pack_view_data(), wpuf_subscription_pack_features()), the
 *   notices of the classic list, the user's active pack with the classic
 *   cancel form fields, and the output of the listing hooks as slots;
 * - info(): the user's subscription (state, price, dates, what is left),
 *   plus the output of other callbacks on `wpuf_account_content_subscription`.
 *
 * Buying stays the classic link (registration or the payment page) and
 * cancelling the classic POST, so every handler and hook runs as before.
 *
 * @since WPUF_SINCE
 */
class Subscription_Schema {

    /**
     * The account handler (its subscription_section() prints the classic card).
     *
     * @var object|null
     */
    private $account;

    /**
     * @since WPUF_SINCE
     *
     * @param object|null $account Frontend_Account
     */
    public function __construct( $account = null ) {
        $this->account = $account;
    }

    /**
     * Boot data of the pack list.
     *
     * @since WPUF_SINCE
     *
     * @param array|string $atts Shortcode attributes (include, exclude, order, orderby)
     *
     * @return array
     */
    public function packs( $atts = [] ) {
        $atts     = is_array( $atts ) ? $atts : [];
        $args     = wp_parse_args(
            $atts, [
				'include' => '',
				'exclude' => '',
				'order'   => '',
				'orderby' => '',
			]
        );
        $query    = [];

        foreach ( [ 'order', 'orderby', 'include', 'exclude' ] as $key ) {
            if ( ! empty( $atts[ $key ] ) ) {
                $query[ $key ] = $args[ $key ];
            }
        }

        $subscription = wpuf()->subscription;
        $packs        = (array) $subscription->get_subscriptions( $query );
        $details_meta = $subscription->get_details_meta_value();
        $user_id      = get_current_user_id();
        $current_pack = Subscription::get_user_pack( $user_id );

        /** This filter is documented in includes/Admin/Subscription.php (the classic list's template args). */
        $listing      = (array) apply_filters(
            'wpuf_subscription_listing_args',
            [
                'subscription' => $subscription,
                'args'         => $args,
                'packs'        => $packs,
                'pack_order'   => '',
                'details_meta' => $details_meta,
                'current_pack' => $current_pack,
            ]
        );
        $packs        = isset( $listing['packs'] ) ? (array) $listing['packs'] : $packs;
        $details_meta = isset( $listing['details_meta'] ) ? $listing['details_meta'] : $details_meta;
        $current_pack = isset( $listing['current_pack'] ) ? $listing['current_pack'] : $current_pack;
        $current_id   = isset( $current_pack['pack_id'] ) ? (int) $current_pack['pack_id'] : 0;

        // `include` keeps its own order, as the classic list.
        if ( '' !== $args['include'] ) {
            $order = array_map( 'intval', explode( ',', $args['include'] ) );
            usort(
                $packs, function ( $a, $b ) use ( $order ) {
					return array_search( (int) $a->ID, $order, true ) - array_search( (int) $b->ID, $order, true );
				}
            );
        }

        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
        $cards  = [];

        // On the payment page the classic cards print nothing (pack-details.php gate).
        if ( 'wpuf_pay' !== $action ) {
            foreach ( $packs as $pack ) {
                $cards[] = $this->card( $subscription->pack_view_data( $pack, $details_meta, $current_id ? (string) $current_id : '' ) );
            }
        }

        $button_color = sanitize_hex_color( (string) Stores::settings()->get( 'wpuf_subscription_settings', 'button_color', '' ) );

        $data = [
            'id'      => 0,
            'view'    => 'packs',
            'cards'   => $cards,
            // The pay page lists no cards (and so no "no packs" line either).
            'pay'     => 'wpuf_pay' === $action,
            'notices' => $this->list_notices( $action ),
            'active'  => $this->active_pack( $current_pack, $user_id ),
            'color'   => $button_color ? $button_color : '',
            'slots'   => [
                'before' => $this->capture( 'wpuf_before_subscription_listing', $packs ),
                'after'  => $this->capture( 'wpuf_after_subscription_listing', $packs ),
            ],
        ];

        /**
         * Filters the boot data of the React pack list.
         *
         * @since WPUF_SINCE
         *
         * @param array $data  Boot data
         * @param array $packs Packs
         * @param array $atts  Shortcode attributes
         */
        return apply_filters( 'wpuf_plans_schema', $data, $packs, $atts );
    }

    /**
     * Boot data of the user's subscription card.
     *
     * @since WPUF_SINCE
     *
     * @param bool $with_slot Capture other `wpuf_account_content_subscription` callbacks
     *
     * @return array
     */
    public function info( $with_slot = true ) {
        $data = array_merge(
            [
                'id'    => 0,
                'view'  => 'info',
                'state' => 'none',
            ],
            $this->subscription_facts()
        );

        $data['packs_url'] = $this->packs_url();
        $data['slots']     = [ 'extra' => $with_slot ? $this->other_section_output() : '' ];

        /**
         * Filters the boot data of the React subscription card.
         *
         * @since WPUF_SINCE
         *
         * @param array $data Boot data
         */
        return apply_filters( 'wpuf_plans_info_schema', $data );
    }

    /**
     * A pack title as plain text for the app (React escapes it): tags
     * stripped, entities decoded (`&amp;` from get_the_title() or the editor
     * would show as written).
     *
     * @since WPUF_SINCE
     *
     * @param string $value Title
     *
     * @return string
     */
    private static function text( $value ) {
        return html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, get_bloginfo( 'charset' ) );
    }

    /**
     * One card from Pack_Display::pack_view_data().
     *
     * @param array $view View data (after wpuf_subscription_pack_details_args)
     *
     * @return array
     */
    private function card( array $view ) {
        $pack   = $view['pack'];
        $amount = isset( $view['billing_amount'] ) ? (string) $view['billing_amount'] : '0.00';
        $free   = '0.00' === $amount || 0.0 === (float) $amount;
        $args   = isset( $view['query_args'] ) && is_array( $view['query_args'] ) ? $view['query_args'] : [];
        $url    = isset( $view['query_url'] ) ? (string) $view['query_url'] : '';

        return [
            'id'          => (int) $pack->ID,
            'title'       => self::text( (string) $pack->post_title ),
            'description' => wp_kses_post( wpautop( (string) $pack->post_content ) ),
            // The pack's "Highlight this plan" setting (or a `featured` flag a filter set).
            'featured'    => ! empty( $pack->featured ) || ( isset( $pack->meta_value['_wpuf_highlight'] ) && 'on' === $pack->meta_value['_wpuf_highlight'] ),
            'badge'       => isset( $pack->meta_value['_wpuf_highlight_label'] ) ? self::text( (string) $pack->meta_value['_wpuf_highlight_label'] ) : '',
            'free'        => $free,
            // Plain text for React: wpuf_format_price() writes the symbol as an entity (&#36;).
            'price'       => $free ? __( 'Free', 'wp-user-frontend' ) : self::text( wpuf_format_price( $amount ) ),
            'cycle'       => trim( wp_strip_all_tags( isset( $view['recurring_des'] ) ? (string) $view['recurring_des'] : '' ) ),
            'trial'       => isset( $view['trial_des'] ) ? wp_strip_all_tags( (string) $view['trial_des'] ) : '',
            'recurring'   => isset( $pack->meta_value['recurring_pay'] ) && wpuf_is_checkbox_or_toggle_on( $pack->meta_value['recurring_pay'] ),
            'button'      => isset( $view['button_name'] ) ? wp_strip_all_tags( (string) $view['button_name'] ) : '',
            'url'         => $url ? esc_url_raw( add_query_arg( $args, $url ) ) : '',
            // The classic card disables every button while the user has a completed pack.
            'disabled'    => isset( $view['current_pack_status'] ) && 'completed' === $view['current_pack_status'],
            // Only an active (paid) pack is the current one, not a pending order.
            'current'     => ! empty( $view['current_pack_id'] ) && (int) $view['current_pack_id'] === (int) $pack->ID && isset( $view['current_pack_status'] ) && 'completed' === $view['current_pack_status'],
            'features'    => array_values( array_map( 'strval', wpuf_subscription_pack_features( $pack ) ) ),
        ];
    }

    /**
     * The notices the classic list prints above the cards.
     *
     * @param string $action ?action
     *
     * @return array[] kind, title, text
     */
    private function list_notices( $action ) {
        $notices  = [];
        $pack_msg = isset( $_GET['pack_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['pack_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
        $ppp_msg  = isset( $_GET['ppp_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['ppp_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.

        if ( 'wpuf_paypal_success' === $action ) {
            $notices[] = [
                'kind'  => 'success',
                'title' => __( 'Payment is complete', 'wp-user-frontend' ),
                'text'  => __( 'Congratulations, your payment has been completed!', 'wp-user-frontend' ),
            ];
        }

        if ( 'buy_pack' === $pack_msg || 'pay_per_post' === $ppp_msg ) {
            $notices[] = [
                'kind'  => 'info',
                'title' => '',
                'text'  => __( 'Please buy a subscription pack to post', 'wp-user-frontend' ),
            ];
        }

        return $notices;
    }

    /**
     * The user's completed pack with the classic cancel form fields, or null.
     *
     * @param array $current_pack Subscription::get_user_pack()
     * @param int   $user_id      User id
     *
     * @return array|null
     */
    private function active_pack( $current_pack, $user_id ) {
        if ( ! $user_id || empty( $current_pack['pack_id'] ) || ! isset( $current_pack['status'] ) || 'completed' !== $current_pack['status'] ) {
            return null;
        }

        return [
            'pack_id' => (int) $current_pack['pack_id'],
            'title'   => self::text( get_the_title( (int) $current_pack['pack_id'] ) ),
            'cancel'  => $this->cancel_fields( $user_id ),
        ];
    }

    /**
     * The fields of the classic cancel form (Payment_Flow::user_subscription_cancel()).
     *
     * @param int $user_id User id
     *
     * @return array
     */
    private function cancel_fields( $user_id ) {
        $gateway = Stores::transactions()->last_completed_gateway( $user_id );

        return [
            '_wpnonce'                 => wp_create_nonce( 'wpuf-sub-cancel-' . $user_id ),
            '_wp_http_referer'         => esc_url_raw( wp_unslash( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- esc_url_raw().
            'user_id'                  => (string) $user_id,
            'gateway'                  => $gateway ? strtolower( (string) $gateway ) : '',
            'wpuf_cancel_subscription' => 'Cancel',
        ];
    }

    /**
     * The user's subscription: state and what the card shows.
     *
     * @return array
     */
    private function subscription_facts() {
        if ( ! is_user_logged_in() ) {
            return [ 'state' => 'guest' ];
        }

        $wpuf_user = wpuf_get_user();
        $pack_id   = (int) $wpuf_user->subscription()->current_pack_id();

        if ( ! $pack_id ) {
            $pending = $this->pending_order_pack( get_current_user_id() );

            return $pending ? [
                'state' => 'pending',
                'pack'  => $pending,
            ] : [ 'state' => 'none' ];
        }

        $user_sub = ( new User_Subscription( $wpuf_user ) )->current_pack();

        if ( ! is_wp_error( $user_sub ) && isset( $user_sub['status'] ) && 'completed' !== $user_sub['status'] && 'Free' !== $user_sub['status'] ) {
            return [ 'state' => 'pending' ];
        }

        $pack = wpuf()->subscription->get_subscription( $pack_id );

        if ( ! $pack ) {
            return [ 'state' => 'missing' ];
        }

        $meta      = is_array( $pack->meta_value ) ? $pack->meta_value : [];
        $amount    = isset( $meta['billing_amount'] ) ? (float) $meta['billing_amount'] : 0.0;
        $recurring = isset( $meta['recurring_pay'] ) && wpuf_is_checkbox_or_toggle_on( $meta['recurring_pay'] );
        $facts     = [
            'state'     => is_wp_error( $user_sub ) ? 'expired' : 'active',
            'pack'      => [
                'id'    => $pack_id,
                'title' => self::text( (string) $pack->post_title ),
            ],
            'price'     => $amount > 0 ? self::text( wpuf_format_price( $amount ) ) : __( 'Free', 'wp-user-frontend' ),
            'cycle'     => '',
            'recurring' => $recurring,
            'dates'     => [],
            'limits'    => [],
            // What the plan includes (the packs page's feature list).
            'features'  => function_exists( 'wpuf_subscription_pack_features' ) ? array_values( array_map( 'strval', wpuf_subscription_pack_features( $pack ) ) ) : [],
            'cancel'    => null,
        ];

        if ( $recurring ) {
            $number         = isset( $meta['billing_cycle_number'] ) ? (string) $meta['billing_cycle_number'] : '1';
            $facts['cycle'] = trim(
                sprintf(
                    /* translators: 1: billing cycle number, 2: billing cycle period */
                    __( 'Every %1$s %2$s', 'wp-user-frontend' ),
                    '1' === $number ? '' : $number,
                    Subscription::get_cycle_label( isset( $meta['cycle_period'] ) ? $meta['cycle_period'] : 'month', $number )
                )
            );
        }

        if ( 'expired' === $facts['state'] ) {
            return $facts;
        }

        $facts['dates']  = $this->dates( $user_sub, $recurring );
        $facts['limits'] = $this->limits( $user_sub );

        // As the classic section: a recurring pack can be cancelled (a one-time pack runs out).
        if ( 'completed' === $user_sub['status'] && isset( $user_sub['recurring'] ) && 'yes' === $user_sub['recurring'] ) {
            $facts['cancel'] = $this->cancel_fields( get_current_user_id() );
        }

        return $facts;
    }

    /**
     * The pack of the user's newest bank payment still waiting for the admin
     * (a `wpuf_order` post the bank gateway created), or null.
     *
     * @param int $user_id User id
     *
     * @return array|null id, title
     */
    private function pending_order_pack( $user_id ) {
        $orders = get_posts(
            [
                'post_type'      => 'wpuf_order',
                'post_status'    => 'publish',
                'author'         => (int) $user_id,
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'fields'         => 'ids',
            ]
        );

        foreach ( $orders as $order_id ) {
            $data = get_post_meta( $order_id, '_data', true );

            if ( is_array( $data ) && isset( $data['type'] ) && 'pack' === $data['type'] && ! empty( $data['item_number'] ) ) {
                return [
                    'id'    => (int) $data['item_number'],
                    'title' => self::text( get_the_title( (int) $data['item_number'] ) ),
                ];
            }
        }

        return null;
    }

    /**
     * Expiry or next payment (the classic card's header lines).
     *
     * @param array $user_sub  User_Subscription::current_pack()
     * @param bool  $recurring Recurring pack
     *
     * @return array[] label, value
     */
    private function dates( array $user_sub, $recurring ) {
        $dates = [];

        if ( ! $recurring || ( isset( $user_sub['recurring'] ) && 'yes' !== $user_sub['recurring'] ) ) {
            if ( ! empty( $user_sub['expire'] ) ) {
                $dates[] = [
                    'label' => __( 'Expires', 'wp-user-frontend' ),
                    'value' => 'unlimited' === $user_sub['expire'] ? __( 'Unlimited', 'wp-user-frontend' ) : wpuf_get_date( wpuf_date2mysql( $user_sub['expire'] ) ),
                ];
            }

            return $dates;
        }

        if ( ! function_exists( 'wpuf_dashboard_get_subscription_data' ) ) {
            return $dates;
        }

        $data = wpuf_dashboard_get_subscription_data( $user_sub );

        if ( empty( $data ) || empty( $data['last_payment_date'] ) ) {
            return $dates;
        }

        if ( ! empty( $data['trial_status'] ) && wpuf_is_checkbox_or_toggle_on( $data['trial_status'] ) ) {
            $dates[] = [
                'label' => __( 'Next payment', 'wp-user-frontend' ),
                'value' => gmdate( 'M d, Y', strtotime( '+' . $data['trial_duration'] . ' ' . $data['trial_duration_type'], strtotime( $data['last_payment_date'] ) ) ),
            ];
        } elseif ( $data['cycle_period'] && ! empty( $data['cycle_number'] ) && -1 !== intval( $data['cycle_number'] ) ) {
            $dates[] = [
                'label' => __( 'Next payment', 'wp-user-frontend' ),
                'value' => gmdate( 'M d, Y', strtotime( '+' . $data['cycle_number'] . ' ' . $data['cycle_period'], strtotime( $data['last_payment_date'] ) ) ),
            ];
        }

        return $dates;
    }

    /**
     * What is left of the pack: posts per post type, featured items.
     *
     * @param array $user_sub User_Subscription::current_pack()
     *
     * @return array[] label, value
     */
    private function limits( array $user_sub ) {
        $limits = [];

        foreach ( ( isset( $user_sub['posts'] ) && is_array( $user_sub['posts'] ) ? $user_sub['posts'] : [] ) as $type => $value ) {
            $value  = intval( $value );
            $object = get_post_type_object( $type );

            if ( 0 === $value || ! $object ) {
                continue;
            }

            $limits[] = [
                'label' => (string) $object->labels->name,
                'value' => -1 === $value ? __( 'Unlimited', 'wp-user-frontend' ) : (string) $value,
            ];
        }

        if ( isset( $user_sub['total_feature_item'] ) && '' !== (string) $user_sub['total_feature_item'] && '0' !== (string) $user_sub['total_feature_item'] ) {
            $limits[] = [
                'label' => __( 'Featured items', 'wp-user-frontend' ),
                'value' => -1 === intval( $user_sub['total_feature_item'] ) ? __( 'Unlimited', 'wp-user-frontend' ) : (string) intval( $user_sub['total_feature_item'] ),
            ];
        }

        return $limits;
    }

    /**
     * The subscription packs page.
     *
     * @return string
     */
    private function packs_url() {
        $page = (int) Stores::settings()->get( 'wpuf_payment', 'subscription_page', 0 );

        return $page ? (string) get_permalink( $page ) : '';
    }

    /**
     * What other callbacks print on `wpuf_account_content_subscription` (ours
     * renders the card itself).
     *
     * @return string
     */
    private function other_section_output() {
        $callback = $this->account ? [ $this->account, 'subscription_section' ] : null;
        $priority = $callback ? has_action( 'wpuf_account_content_subscription', $callback ) : false;

        if ( false !== $priority ) {
            remove_action( 'wpuf_account_content_subscription', $callback, $priority );
        }

        $html = $this->capture( 'wpuf_account_content_subscription', wpuf_get_account_sections(), 'subscription' );

        if ( false !== $priority ) {
            add_action( 'wpuf_account_content_subscription', $callback, $priority, 2 );
        }

        return $html;
    }

    /**
     * The output of an action.
     *
     * @param string $hook Action
     * @param mixed  ...$args Arguments
     *
     * @return string
     */
    private function capture( $hook, ...$args ) {
        ob_start();
        do_action( $hook, ...$args );

        return trim( (string) ob_get_clean() );
    }
}
