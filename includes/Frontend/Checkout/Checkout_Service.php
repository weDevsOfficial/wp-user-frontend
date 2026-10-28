<?php
/**
 * The pay page's checkout
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Checkout;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Data\Country_State;
use WeDevs\Wpuf\Frontend\Renderer;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\WPUF_User;
use WP_Post;

/**
 * What the pay page (`?action=wpuf_pay`) shows and charges, for both renders
 * of it: the React checkout app and the classic markup (Payment::payment_page()).
 * It reads the request, decides the gate (login, no pay page, no pack, free
 * pack, no gateway), activates a free pack, builds the order (with the
 * `wpuf_payment_amount` / `wpuf_billing_data` filters), the active gateways,
 * the tax row, the coupon and billing address settings, and for React the
 * boot data with the output of the checkout's printing hooks. Paying stays
 * with Payment::send_to_gateway() (the form posts to the page, as before).
 *
 * @since WPUF_SINCE
 */
class Checkout_Service {

    /**
     * The frontend app renderer.
     *
     * @var Renderer
     */
    private $renderer;

    /**
     * Billing address fields: key => [ setting, posted name, classic input name ].
     *
     * @since WPUF_SINCE
     */
    const ADDRESS_FIELDS = [
        'country'    => [ 'country', 'billing_country', 'wpuf_biiling_country' ],
        'state'      => [ 'state', 'billing_state', 'wpuf_biiling_state' ],
        'add_line_1' => [ 'address_1', 'billing_add_line1', 'wpuf_biiling_add_line_1' ],
        'add_line_2' => [ 'address_2', 'billing_add_line2', 'wpuf_biiling_add_line_2' ],
        'city'       => [ 'city', 'billing_city', 'wpuf_biiling_city' ],
        'zip_code'   => [ 'zip', 'billing_zip', 'wpuf_biiling_zip_code' ],
    ];

    /**
     * @param Renderer $renderer The frontend app renderer
     */
    public function __construct( Renderer $renderer ) {
        $this->renderer = $renderer;
    }

    /**
     * Whether the checkout of this page is the React app: the pay page itself
     * (not one built with Elementor: page builders keep their own render) with
     * the app built, outside the admin.
     *
     * @since WPUF_SINCE
     *
     * @param WP_Post|null $post The page
     *
     * @return bool
     */
    public function renders_react( $post ) {
        return $post instanceof WP_Post
            && $this->renderer->mounts( 'checkout' )
            && ! ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $post->ID ) );
    }

    /**
     * The React checkout's markup (boot data built with the classic asset
     * listeners quiet).
     *
     * @since WPUF_SINCE
     *
     * @param array $resolved resolve() with status `open`
     *
     * @return string
     */
    public function react_markup( array $resolved ) {
        $data = $this->renderer->build(
            'checkout',
            function () use ( $resolved ) {
                return $this->schema( $resolved );
            }
        );

        $skeleton = '<div class="wpuf-checkout-wrap wpuf-checkout-boot" aria-busy="true"><div class="wpuf-checkout-columns"><div class="wpuf-checkout-left"><span class="wpuf-skeleton wpuf-skeleton-card"></span><span class="wpuf-skeleton wpuf-skeleton-card"></span></div><div class="wpuf-checkout-right"><span class="wpuf-skeleton wpuf-skeleton-card wpuf-skeleton-card--tall"></span></div></div></div>';

        /**
         * Whether the checkout uses the theme's wide alignment (as the account
         * page: block themes cap `alignwide` at their wide size, classic themes
         * would run it past the screen).
         *
         * @since WPUF_SINCE
         *
         * @param bool $align_wide Whether to add `alignwide`
         */
        $align_wide = (bool) apply_filters( 'wpuf_checkout_align_wide', function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );
        $markup     = $this->renderer->markup( 'checkout', $data, $skeleton, 'wpuf-checkout' . ( $align_wide ? ' alignwide' : '' ) );

        // Gateway scripts (Pro Stripe, add-on gateways) read the classic page's
        // `wpuf_frontend` (nonce, REST data); the classic bundle is off here.
        wp_localize_script(
            'wpuf-frontend-checkout',
            'wpuf_frontend',
            apply_filters(
                'wpuf_frontend_object',
                [
                    'asset_url' => WPUF_ASSET_URI,
                    'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                    'nonce'     => wp_create_nonce( 'wpuf_nonce' ),
                    'rest'      => wpuf_rest_script_data(),
                ]
            )
        );

        return $markup;
    }

    /**
     * The pay page id (Settings > Payments).
     *
     * @since WPUF_SINCE
     *
     * @return int
     */
    public function pay_page_id() {
        return intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
    }

    /**
     * The pay page query.
     *
     * @since WPUF_SINCE
     *
     * @return array action, type ('pack' | 'post'), post_id, pack_id, user_id, gateway (the one to pre-select)
     */
    public function request() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only pay page query; paying is nonce-checked in Payment::send_to_gateway().
        $type    = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
        $gateway = isset( $_REQUEST['wpuf_payment_submit'], $_REQUEST['wpuf_payment_method'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpuf_payment_method'] ) ) : 'paypal';

        $request = [
            'action'  => isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '',
            'type'    => 'post' === $type ? 'post' : 'pack',
            'post_id' => isset( $_REQUEST['post_id'] ) ? intval( wp_unslash( $_REQUEST['post_id'] ) ) : 0,
            'pack_id' => isset( $_REQUEST['pack_id'] ) ? intval( wp_unslash( $_REQUEST['pack_id'] ) ) : 0,
            'user_id' => isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : 0,
            'gateway' => $gateway,
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return $request;
    }

    /**
     * Whether this request is the pay page with a checkout to show (not a gate).
     * Read before the page renders (asset loading), so it has no side effects.
     *
     * @since WPUF_SINCE
     *
     * @param WP_Post|null $post The queried page
     *
     * @return bool
     */
    public function is_checkout( $post ) {
        $request = $this->request();

        return $post instanceof WP_Post
            && 'wpuf_pay' === $request['action']
            && $post->ID === $this->pay_page_id()
            && is_user_logged_in();
    }

    /**
     * What the page shows for this request, in the classic page's order of checks.
     * A free pack is activated here (as the classic page did on view).
     *
     * @since WPUF_SINCE
     *
     * @param WP_Post $post The page being rendered
     *
     * @return array `status`: skip (the page's own content) | no_page | login | no_pack |
     *               free (`message`) | no_gateways | open (`request`, `pack`, `gateways`)
     */
    public function resolve( WP_Post $post ) {
        $request  = $this->request();
        $pay_page = $this->pay_page_id();
        $paying   = 'wpuf_pay' === $request['action'];

        if ( $paying && ! is_user_logged_in() && 'post' !== $request['type'] ) {
            return [ 'status' => 'login' ];
        }

        if ( $paying && 0 === $pay_page ) {
            return [ 'status' => 'no_page' ];
        }

        // Paying for a pack OR a post, never both.
        if ( ! $paying || $post->ID !== $pay_page || ( $request['post_id'] > 0 && $request['pack_id'] > 0 ) ) {
            return [ 'status' => 'skip' ];
        }

        $pack    = null;
        $is_free = false;

        if ( $request['pack_id'] ) {
            $pack = wpuf()->subscription->get_purchasable_subscription( $request['pack_id'] );

            if ( ! $pack ) {
                return [ 'status' => 'no_pack' ];
            }

            $is_free = empty( $pack->meta_value['billing_amount'] ) || $pack->meta_value['billing_amount'] <= 0;
        }

        $gateways = $this->gateways();

        if ( $pack && in_array( $this->recurring_pay( $pack ), [ 'yes', 'on' ], true ) ) {
            $gateways = $this->recurring_gateways( $gateways );
        }

        // A guest paying for a post pays from the account the submission made
        // for them (they get its password mail): ask them to log in instead
        // of showing the bare page content.
        if ( ! is_user_logged_in() && 'post' === $request['type'] && $request['post_id'] > 0 ) {
            // Without "Require Name and Email" no account is made and the post
            // goes to the default post owner: the guest has nothing to log in to.
            $owner = (int) get_post_field( 'post_author', $request['post_id'] );

            return [
                'status'      => 'guest_post',
                'post_id'     => $request['post_id'],
                'has_account' => $owner > 0 && (int) wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 ) !== $owner,
            ];
        }

        if ( ! is_user_logged_in() || ( $request['user_id'] > 0 && get_current_user_id() !== $request['user_id'] ) ) {
            return [ 'status' => 'skip' ];
        }

        if ( $pack && $is_free ) {
            return [
                'status'  => 'free',
                'message' => $this->activate_free_pack( $request['pack_id'] ),
            ];
        }

        if ( ! count( $gateways ) ) {
            return [ 'status' => 'no_gateways' ];
        }

        return [
            'status'   => 'open',
            'request'  => $request,
            'pack'     => $pack,
            'gateways' => $gateways,
        ];
    }

    /**
     * A gateway could not start the payment: log the reason (it is for the
     * site owner: API keys, a gateway answer) and send the customer back to
     * the checkout, which shows a caution (notice()).
     *
     * @since WPUF_SINCE
     *
     * @param string $gateway Gateway id
     * @param string $reason  What went wrong (never shown to customers)
     *
     * @return void
     */
    public function return_with_error( $gateway, $reason ) {
        \WP_User_Frontend::log( $gateway, $reason );

        if ( get_current_user_id() ) {
            set_transient( 'wpuf_payment_error_' . get_current_user_id(), (string) $reason, 10 * MINUTE_IN_SECONDS );
        }

        // The checkout the customer came from: the pay page with the posted item.
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- the payment form was nonce-checked by send_to_gateway().
        $type = isset( $_POST['type'] ) && 'post' === sanitize_key( wp_unslash( $_POST['type'] ) ) ? 'post' : 'pack';
        $id   = 'post' === $type ? ( isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0 ) : ( isset( $_POST['pack_id'] ) ? absint( wp_unslash( $_POST['pack_id'] ) ) : 0 );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        $back = add_query_arg(
            [
                'action'             => 'wpuf_pay',
                'type'               => $type,
                $type . '_id'        => $id,
                'wpuf_payment_error' => sanitize_key( $gateway ),
            ],
            (string) get_permalink( $this->pay_page_id() )
        );

        wp_safe_redirect( $back );
        exit;
    }

    /**
     * The caution after a gateway sent the customer back: a message for
     * customers; administrators also read the logged reason.
     *
     * @since WPUF_SINCE
     *
     * @return array|null `message`, `detail` (administrators only)
     */
    public function notice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag in the return URL.
        if ( empty( $_GET['wpuf_payment_error'] ) ) {
            return null;
        }

        $key    = 'wpuf_payment_error_' . get_current_user_id();
        $detail = current_user_can( 'manage_options' ) ? (string) get_transient( $key ) : '';

        delete_transient( $key );

        return [
            'message' => __( 'We could not start your payment. Please try again or choose another payment method.', 'wp-user-frontend' ),
            'detail'  => $detail,
        ];
    }

    /**
     * Give the current user a free pack once.
     *
     * @since WPUF_SINCE
     *
     * @param int $pack_id Pack
     *
     * @return string The message (`wpuf_fp_activated_msg` / `wpuf_fp_activated_error`)
     */
    public function activate_free_pack( $pack_id ) {
        $user_id = get_current_user_id();
        $user    = new WPUF_User( $user_id );

        if ( ! $user->subscription()->used_free_pack( $pack_id ) ) {
            wpuf_get_user( $user_id )->subscription()->add_pack( $pack_id, null, false, 'Free' );
            $user->subscription()->add_free_pack( $user_id, $pack_id );

            return apply_filters( 'wpuf_fp_activated_msg', __( 'Your Free package has been activated. Enjoy!', 'wp-user-frontend' ) );
        }

        return apply_filters( 'wpuf_fp_activated_error', __( 'You already have activated a Free package previously.', 'wp-user-frontend' ) );
    }

    /**
     * The active gateways that may show on the checkout.
     *
     * @since WPUF_SINCE
     *
     * @return array id => gateway (`label`, `icon`, ...)
     */
    public function gateways() {
        $active   = wpuf_get_option( 'active_gateways', 'wpuf_payment' );
        $active   = is_array( $active ) ? $active : [];
        $gateways = [];

        foreach ( wpuf_get_gateways( 'checkout' ) as $id => $gateway ) {
            // Pro-preview gateways are upsell placeholders of the settings screen.
            if ( empty( $gateway['is_pro_preview'] ) && array_key_exists( $id, $active ) ) {
                $gateways[ $id ] = $gateway;
            }
        }

        /**
         * The gateways offered at checkout: ticked in Settings > Payments. A gateway
         * that cannot take a payment yet (Pro Stripe without its keys) leaves itself out.
         *
         * @since WPUF_SINCE
         *
         * @param array $gateways id => gateway (`label`, `icon`, ...)
         */
        return (array) apply_filters( 'wpuf_checkout_gateways', $gateways );
    }

    /**
     * The gateways that can take a recurring payment: a gateway registered with
     * the gateway manager says so (`supports_subscription`, Bank does not); one
     * added only through `wpuf_payment_gateways` (Pro Stripe) keeps its place,
     * it handles recurring packs itself.
     *
     * @since WPUF_SINCE
     *
     * @param array $gateways id => gateway
     *
     * @return array
     */
    public function recurring_gateways( array $gateways ) {
        if ( ! function_exists( 'wpuf_get_gateway' ) ) {
            return $gateways;
        }

        return array_filter(
            $gateways,
            function ( $id ) {
                $gateway = wpuf_get_gateway( $id );

                return ! $gateway || $gateway->supports_subscription();
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * The order: what is paid, its price and the total (`wpuf_payment_amount`,
     * then `wpuf_billing_data`).
     *
     * @since WPUF_SINCE
     *
     * @param array       $request request()
     * @param object|null $pack    The pack (pack payments)
     *
     * @return array type, id, title, subtotal, billing_amount (+ recurring_pay for a pack)
     */
    public function billing_data( array $request, $pack = null ) {
        if ( $request['pack_id'] && $pack ) {
            return apply_filters(
                'wpuf_billing_data',
                [
                    'type'           => 'pack',
                    'id'             => $request['pack_id'],
                    'title'          => $pack->post_title,
                    'subtotal'       => $pack->meta_value['billing_amount'],
                    'billing_amount' => apply_filters( 'wpuf_payment_amount', $pack->meta_value['billing_amount'] ),
                    'recurring_pay'  => $this->recurring_pay( $pack ),
                ]
            );
        }

        if ( ! $request['post_id'] ) {
            return [];
        }

        $post_id      = $request['post_id'];
        $form         = new Form( Stores::submissions()->form_id( $post_id ) );
        $current_pack = wpuf_get_user()->subscription()->current_pack();

        if ( $form->is_enabled_force_pack() && ! is_wp_error( $current_pack ) && $form->is_enabled_fallback_cost() ) {
            $cost = (float) $form->get_subs_fallback_cost();
        } else {
            $cost = (float) $form->get_pay_per_post_cost();
        }

        $post = get_post( $post_id );

        return apply_filters(
            'wpuf_billing_data',
            [
                'type'           => 'post',
                'id'             => $post_id,
                'title'          => $post ? $post->post_title : __( 'Post Submission', 'wp-user-frontend' ),
                'subtotal'       => $cost,
                'billing_amount' => apply_filters( 'wpuf_payment_amount', $cost, $post_id ),
            ]
        );
    }

    /**
     * A pack's recurring setting.
     *
     * @since WPUF_SINCE
     *
     * @param object $pack Pack
     *
     * @return string
     */
    public function recurring_pay( $pack ) {
        return isset( $pack->meta_value['recurring_pay'] ) ? $pack->meta_value['recurring_pay'] : 'no';
    }

    /**
     * The tax row (Pro's tax, when on).
     *
     * @since WPUF_SINCE
     *
     * @return array|null `rate` (e.g. "10%"), null when tax is off
     */
    public function tax() {
        if ( ! wpuf_is_pro_active() || ! class_exists( 'WeDevs\Wpuf\Pro\Coupons' ) || ! function_exists( 'wpuf_pro' ) ) {
            return null;
        }

        $coupons = wpuf_pro()->coupons;

        if (
            ! is_object( $coupons )
            || ! method_exists( $coupons, 'wpuf_tax_enabled' )
            || ! method_exists( $coupons, 'wpuf_current_tax_rate' )
            || ! $coupons->wpuf_tax_enabled()
        ) {
            return null;
        }

        return [ 'rate' => $coupons->wpuf_current_tax_rate() . '%' ];
    }

    /**
     * Whether a coupon can be entered (Pro, pack payments).
     *
     * @since WPUF_SINCE
     *
     * @param array $request request()
     *
     * @return bool
     */
    public function coupon_enabled( array $request ) {
        return $request['pack_id'] && wpuf_is_pro_active() && class_exists( 'WeDevs\Wpuf\Pro\Coupons' );
    }

    /**
     * Whether the checkout asks for the billing address.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function billing_enabled() {
        return wpuf_get_option( 'show_address', 'wpuf_address_options', false ) && is_user_logged_in();
    }

    /**
     * The billing address fields with their setting (required / optional /
     * hidden; a hidden country hides the state), the user's saved address
     * (the base country / state when none) and the country / state lists.
     *
     * @since WPUF_SINCE
     *
     * @return array fields (key => name, input, mode), values (key => value), countries, states
     */
    public function billing_address() {
        $address = wpuf_map_address_fields( (array) wpuf_get_user_address() );
        $base    = wp_parse_args(
            (array) get_option( 'wpuf_base_country_state', [] ),
            [
                'country' => '',
                'state'   => '',
            ]
        );
        $fields  = [];
        $values  = [];

        foreach ( self::ADDRESS_FIELDS as $key => $field ) {
            $mode = (string) wpuf_get_option( $field[0], 'wpuf_address_options', '' );

            $fields[ $key ] = [
                'name'  => $field[1],
                'input' => $field[2],
                'mode'  => in_array( $mode, [ 'required', 'hidden' ], true ) ? $mode : 'optional',
            ];
            $values[ $key ] = isset( $address[ $key ] ) ? (string) $address[ $key ] : '';
        }

        if ( 'hidden' === $fields['country']['mode'] ) {
            $fields['state']['mode'] = 'hidden';
        }

        $values['country'] = '' !== $values['country'] ? $values['country'] : (string) $base['country'];
        $values['state']   = '' !== $values['state'] ? $values['state'] : (string) $base['state'];

        $places = new Country_State();

        return [
            'fields'    => $fields,
            'values'    => $values,
            'countries' => $this->options( $places->countries() ),
            'states'    => $this->options( $places->getStates( $values['country'] ) ),
        ];
    }

    /**
     * The React checkout's boot data: the order, gateways with their forms,
     * the summary rows and what the checkout's printing hooks print (fired in
     * the classic page's order with the same arguments).
     *
     * @since WPUF_SINCE
     *
     * @param array $resolved resolve() with status `open`
     *
     * @return array
     */
    public function schema( array $resolved ) {
        $request  = $resolved['request'];
        $pack     = $resolved['pack'];
        $billing  = $this->billing_data( $request, $pack );
        $gateways = [];
        $slots    = [];
        $selected = $request['gateway'];

        $slots['before_gateways'] = $this->capture( 'wpuf_before_payment_gateway' );

        foreach ( $resolved['gateways'] as $id => $gateway ) {
            $instruction = wpuf_get_option( 'gate_instruct_' . $id, 'wpuf_payment' );

            $gateways[] = [
                'id'          => (string) $id,
                'label'       => isset( $gateway['label'] ) ? (string) $gateway['label'] : (string) $id,
                'icon'        => ! empty( $gateway['icon'] ) ? esc_url_raw( $gateway['icon'] ) : '',
                'instruction' => $instruction ? wp_strip_all_tags( (string) $instruction ) : '',
                // The gateway's own form (Stripe's card field), printed as before.
                'form'        => $this->capture( 'wpuf_gateway_form_' . $id, $request['type'], $request['post_id'], $request['pack_id'] ),
            ];
        }

        $slots['after_gateways'] = $this->capture( 'wpuf_after_payment_gateway' );

        if ( ! empty( $billing ) ) {
            $slots['summary_before']       = $this->capture( 'wpuf_order_summary_before', $billing );
            $slots['summary_after_item']   = $this->capture( 'wpuf_order_summary_after_item', $billing );
            $slots['summary_before_total'] = $this->capture( 'wpuf_before_pack_payment_total' );
            $slots['summary_after_total']  = $this->capture( 'wpuf_order_summary_after_total', $billing );
        }

        $slots['after_summary'] = $this->capture( 'wpuf_checkout_after_order_summary', $billing );

        $ids = wp_list_pluck( $gateways, 'id' );

        if ( ! in_array( $selected, $ids, true ) ) {
            $selected = isset( $ids[0] ) ? $ids[0] : '';
        }

        $recurring = $pack ? $this->recurring_pay( $pack ) : '';
        $tax       = $this->tax();

        return [
            'id'        => $request['pack_id'] ? $request['pack_id'] : $request['post_id'],
            'type'      => $request['type'],
            'pack_id'   => $request['pack_id'],
            'post_id'   => $request['post_id'],
            'user_id'   => $request['pack_id'] ? get_current_user_id() : 0,
            'recurring' => $recurring,
            'nonce'     => wp_create_nonce( 'wpuf_payment_gateway' ),
            // wp_nonce_field()'s referer field of the classic form.
            'referer'   => remove_query_arg( [ '_wp_http_referer', 'wpuf_payment_error' ] ),
            'notice'    => $this->notice(),
            'restNonce' => wp_create_nonce( 'wpuf_nonce' ),
            'order'     => [
                'title'     => isset( $billing['title'] ) ? (string) $billing['title'] : '',
                'subtotal'  => isset( $billing['subtotal'] ) ? $this->price( $billing['subtotal'] ) : '',
                'total'     => isset( $billing['billing_amount'] ) ? $this->price( $billing['billing_amount'] ) : '',
                'pack'      => 'pack' === ( isset( $billing['type'] ) ? $billing['type'] : '' ),
                'recurring' => in_array( $recurring, [ 'yes', 'on' ], true ),
            ],
            'tax'       => $tax,
            'gateways'  => $gateways,
            'selected'  => $selected,
            'slots'     => array_filter( $slots ),
            'coupon'    => $this->coupon_enabled( $request ),
            'billing'   => $this->billing_enabled() ? $this->billing_address() : null,
        ];
    }

    /**
     * Fire a printing hook and return what it printed.
     *
     * @param string $hook    Action
     * @param mixed  ...$args Its arguments
     *
     * @return string
     */
    private function capture( $hook, ...$args ) {
        ob_start();
        // As do_action(): a hook fired without arguments passes ''.
        do_action_ref_array( $hook, $args ? $args : [ '' ] );

        return trim( (string) ob_get_clean() );
    }

    /**
     * A price the way the classic page prints it (plain text).
     *
     * @param mixed $amount Amount
     *
     * @return string
     */
    private function price( $amount ) {
        return html_entity_decode( wp_strip_all_tags( wpuf_format_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
    }

    /**
     * A code => name list as { value, label } options.
     *
     * @param mixed $list List
     *
     * @return array
     */
    private function options( $list ) {
        $options = [];

        foreach ( is_array( $list ) ? $list : [] as $value => $label ) {
            $options[] = [
                'value' => (string) $value,
                'label' => html_entity_decode( (string) $label, ENT_QUOTES, 'UTF-8' ),
            ];
        }

        return $options;
    }
}
