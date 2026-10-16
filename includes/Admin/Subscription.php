<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Admin\Subscriptions\Pack_Display;
use WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type;
use WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Post;

/**
 * WPUF subscription manager
 *
 * @since 0.2
 *
 * @author Tareq Hasan
 */
class Subscription {

    /**
     * @var Pack_Post_Type|null
     */
    private $pack_post_type = null;

    /**
     * @var Payment_Flow|null
     */
    private $payment_flow = null;

    /**
     * @var Pack_Display|null
     */
    private $pack_display = null;

    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_filter( 'wpuf_add_post_args', [ $this, 'set_pending' ], 10, 4 );
        add_filter( 'wpuf_add_post_redirect', [ $this, 'post_redirect' ], 10, 4 );

        add_filter( 'wpuf_addpost_notice', [ $this, 'force_pack_notice' ], 20, 3 );
        add_filter( 'wpuf_can_post', [ $this, 'force_pack_permission' ], 20, 3 );
        add_action( 'wpuf_add_post_form_top', [ $this, 'add_post_info' ], 10, 2 );

        add_action( 'wpuf_add_post_after_insert', [ $this, 'monitor_new_post' ], 10, 3 );
        add_action( 'wpuf_add_post_after_insert', [ $this, 'reset_user_subscription_data' ], 10, 4 );
        add_action( 'wpuf_draft_post_after_insert', [ $this, 'monitor_new_draft_post' ], 10, 3 );
        add_action( 'wpuf_payment_received', [ $this, 'payment_received' ], 10, 2 );

        add_action( 'admin_enqueue_scripts', [ $this, 'subscription_script' ] );

        // Packs have no WordPress screens any more: old links open the app.
        foreach ( [ 'load-edit.php', 'load-post.php', 'load-post-new.php' ] as $screen_hook ) {
            add_action( $screen_hook, [ $this, 'redirect_classic_pack_screens' ] );
        }

        add_action( 'user_register', [ $this, 'after_registration' ], 10, 1 );

        add_action( 'register_form', [ $this, 'register_form' ] );
        add_action( 'wpuf_add_post_form_top', [ $this, 'register_form' ] );
        add_filter( 'wpuf_user_register_redirect', [ $this, 'subs_redirect_pram' ], 10, 2 );

        add_filter( 'template_redirect', [ $this, 'user_subscription_cancel' ] );

        add_action( 'wpuf_draft_post_after_insert', [ $this, 'reset_user_subscription_data' ], 10, 4 );

        add_filter( 'wpuf_get_subscription_meta', [ $this, 'reset_trial' ] );
        //Handle non recurring subscription when expired
        add_action( 'wp', [ $this, 'handle_non_recur_subs' ] );
        add_action( 'non_recur_subs_daily', [ $this, 'cancel_non_recurring_subscription' ] );
    }

    /**
     * Handle subscription cancel request from the user
     *
     * @return void
     */
    public static function subscriber_cancel( $user_id, $pack_id ) {
        $transaction_id = Stores::transactions()->transaction_id_for_pack( $user_id, $pack_id );

        Stores::subscribers()->cancel( $user_id, $pack_id, null === $transaction_id ? 'Free' : $transaction_id );
        Stores::user_packs()->delete( $user_id );
    }

    /**
     * Get all subscription packs
     *
     * @return array
     */
    public function get_subscriptions( $args = [] ) {
        return Stores::subscriptions()->ordered( (array) $args );
    }

    /**
     * Set meta fields on a subscription pack
     *
     * @since 2.2
     *
     * @param int      $subscription_id
     * @param WP_Post $pack_post
     *
     * @return array
     */
    public static function get_subscription_meta( $subscription_id, $pack_post = null ) {
        $meta['post_content']               = isset( $pack_post->post_content ) ? $pack_post->post_content : '';
        $meta['post_title']                 = isset( $pack_post->post_title ) ? $pack_post->post_title : '';

        // keeping the '_' in the key for backward compatibility. Example $meta['_billing_amount']
        $meta['billing_amount']             = get_post_meta( $subscription_id, '_billing_amount', true );
        $meta['_billing_amount']            = $meta['billing_amount'];
        $meta['expiration_number']          = get_post_meta( $subscription_id, '_expiration_number', true );
        $meta['_expiration_number']         = $meta['expiration_number'];
        $meta['expiration_period']          = get_post_meta( $subscription_id, '_expiration_period', true );
        $meta['_expiration_period']         = $meta['expiration_period'];
        $meta['recurring_pay']              = get_post_meta( $subscription_id, '_recurring_pay', true );
        $meta['_recurring_pay']             = $meta['recurring_pay'];
        $meta['billing_cycle_number']       = get_post_meta( $subscription_id, '_billing_cycle_number', true );
        $meta['_billing_cycle_number']      = $meta['billing_cycle_number'];
        $meta['enable_billing_limit']       = get_post_meta( $subscription_id, '_enable_billing_limit', true );
        $meta['_enable_billing_limit']      = $meta['enable_billing_limit'];
        $meta['cycle_period']               = get_post_meta( $subscription_id, '_cycle_period', true );
        $meta['_cycle_period']              = $meta['cycle_period'];
        $meta['billing_limit']              = get_post_meta( $subscription_id, '_billing_limit', true );
        $meta['_billing_limit']             = $meta['billing_limit'];
        $meta['trial_status']               = get_post_meta( $subscription_id, '_trial_status', true );
        $meta['_trial_status']              = $meta['trial_status'];
        $meta['trial_duration']             = get_post_meta( $subscription_id, '_trial_duration', true );
        $meta['_trial_duration']            = $meta['trial_duration'];
        $meta['trial_duration_type']        = get_post_meta( $subscription_id, '_trial_duration_type', true );
        $meta['_trial_duration_type']       = $meta['trial_duration_type'];
        $meta['post_type_name']             = get_post_meta( $subscription_id, '_post_type_name', true );
        $meta['_post_type_name']            = $meta['post_type_name'];
        $meta['additional_cpt_options']     = get_post_meta( $subscription_id, 'additional_cpt_options', true );
        $meta['_enable_post_expiration']    = get_post_meta( $subscription_id, '_enable_post_expiration', true );
        $meta['_post_expiration_time']      = get_post_meta( $subscription_id, '_post_expiration_time', true );
        $meta['_post_expiration_number']    = get_post_meta( $subscription_id, '_post_expiration_number', true );
        $meta['_post_expiration_period']    = get_post_meta( $subscription_id, '_post_expiration_period', true );
        $meta['_expired_post_status']       = get_post_meta( $subscription_id, '_expired_post_status', true );
        $meta['_enable_mail_after_expired'] = get_post_meta( $subscription_id, '_enable_mail_after_expired', true );
        $meta['_post_expiration_message']   = get_post_meta( $subscription_id, '_post_expiration_message', true );
        $meta['_total_feature_item']        = get_post_meta( $subscription_id, '_total_feature_item', true );
        $meta['_remove_feature_item']       = get_post_meta( $subscription_id, '_remove_feature_item', true );
        $meta['_sort_order']                = get_post_meta( $subscription_id, '_sort_order', true );

        // Taxonomy restriction meta keys
        $meta['_sub_allowed_term_ids']      = get_post_meta( $subscription_id, '_sub_allowed_term_ids', true );
        $meta['_sub_view_allowed_term_ids'] = get_post_meta( $subscription_id, '_sub_view_allowed_term_ids', true );

        $meta = apply_filters( 'wpuf_get_subscription_meta', $meta, $subscription_id );

        return $meta;
    }

    /**
     * Get a subscription row from database
     *
     *
     * @param int $sub_id subscription pack id
     *
     * @return object|bool
     */
    public static function get_subscription( $sub_id ) {
        $pack = get_post( $sub_id );

        if ( ! $pack ) {
            return false;
        }

        $pack->meta_value = self::get_subscription_meta( $sub_id, $pack );

        return $pack;
    }

    /**
     * A pack visitors may buy or activate: a published wpuf_subscription
     *
     * The plain get_subscription() reads any post (the admin edits drafts), so
     * checkout and payment use this one: a draft, private or trashed pack, or
     * any other post id, is not for sale.
     *
     * @since WPUF_SINCE
     *
     * @param int $sub_id Pack id.
     *
     * @return object|bool
     */
    public static function get_purchasable_subscription( $sub_id ) {
        $post = get_post( $sub_id );

        if ( ! $post || 'wpuf_subscription' !== $post->post_type || 'publish' !== $post->post_status ) {
            return false;
        }

        return self::get_subscription( $sub_id );
    }

    /**
     * Update user meta
     *
     * If data = 0, means 'unlimited'
     *
     * @param int   $user_id
     * @param array $data
     */
    public static function update_user_subscription_meta( $user_id, $user_meta ) {
        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user( $user_id )->subscription()->update_meta( $user_meta );' );

        wpuf_get_user( $user_id )->subscription()->update_meta( $user_meta );
    }

    public static function post_by_orderid( $order_id ) {
        return Stores::submissions()->find_by_order( $order_id );
    }

    /**
     * Get cycle label
     *
     *@since 2.8.10
     *
     *@return string $labels[$cycle_period]
     */
    public static function get_cycle_label( $cycle_period, $cycle_number ) {
        $labels = [
            'day'   => _n( 'Day', 'Days', $cycle_number, 'wp-user-frontend' ),
            'week'  => _n( 'Week', 'Weeks', $cycle_number, 'wp-user-frontend' ),
            'month' => _n( 'Month', 'Months', $cycle_number, 'wp-user-frontend' ),
            'year'  => _n( 'Year', 'Years', $cycle_number, 'wp-user-frontend' ),
        ];

        return apply_filters( 'wpuf_subscription_cycle_label', $labels[ $cycle_period ] );
    }

    public static function get_user_pack( $user_id, $status = true ) {
        return Stores::user_packs()->read( $user_id, $status );
    }

    /**
     * Get all users who have a subscription pack
     *
     * @param int $pack_id
     * @param int $status
     *
     * @return array|bool
     */
    public function subscription_pack_users( $pack_id = '', $status = '' ) {
        $user_ids = Stores::subscribers()->user_ids( $pack_id, $status );

        if ( empty( $user_ids ) ) {
            return $user_ids;
        }

        return get_users( [ 'include' => $user_ids ] );
    }

    /**
     * Checks against the user, if he is valid for posting new post
     *
     * @global object $userdata
     *
     * @return bool
     */
    public static function has_user_error( $form_settings = null ) {

        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user()->subscription()->has_error( $form_settings = null );' );

        return wpuf_get_user()->subscription()->has_error( $form_settings );
    }

    /**
     * Determine if the user has used a Free pack before
     *
     * @since 2.1.8
     *
     * @param int $user_id
     * @param int $pack_id
     *
     * @return bool
     */
    public static function has_used_free_pack( $user_id, $pack_id ) {
        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user( $user_id )->subscription()->used_free_pack( $pack_id );' );

        wpuf_get_user( $user_id )->subscription()->used_free_pack( $pack_id );
    }

    /**
     * Add a Free used pack to the user account
     *
     * @since 2.1.8
     *
     * @param int $user_id
     * @param int $pack_id
     */
    public static function add_free_pack( $user_id, $pack_id ) {
        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user( $user_id )->subscription()->add_free_pack( $pack_id );' );

        wpuf_get_user( $user_id )->subscription()->add_free_pack( $user_id, $pack_id );
    }

    /**
     * Total count of subscriptions by status
     *
     * @since 4.0.11
     *
     * @return string|null Database query result (as string), or null on failure.
     */
    public function total_subscriptions_count_by_status( $status = 'all' ) {
        return Stores::subscriptions()->count_status( $status );
    }

    /**
     * Total count of subscriptions by status
     *
     * @since 4.0.11
     *
     * @return array
     */
    public function total_subscriptions_count_array() {
        return Stores::subscriptions()->counts_by_status();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::register_post_type()
     *
     * @since WPUF_SINCE
     */
    public function register_post_type() {
        return $this->pack_post_type()->register_post_type();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::subscription_script()
     *
     * @since WPUF_SINCE
     */
    public function subscription_script() {
        return $this->pack_post_type()->subscription_script();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::get_all_post_type()
     *
     * @since WPUF_SINCE
     */
    public function get_all_post_type() {
        return $this->pack_post_type()->get_all_post_type();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::register_form()
     *
     * @since WPUF_SINCE
     */
    public function register_form() {
        return $this->pack_post_type()->register_form();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::after_registration()
     *
     * @since WPUF_SINCE
     */
    public function after_registration( $user_id ) {
        return $this->pack_post_type()->after_registration( $user_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::redirect_classic_screens()
     *
     * @since WPUF_SINCE
     */
    public function redirect_classic_pack_screens() {
        return $this->pack_post_type()->redirect_classic_screens();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::subs_redirect_pram()
     *
     * @since WPUF_SINCE
     */
    public function subs_redirect_pram( $response, $user_id ) {
        return $this->pack_post_type()->subs_redirect_pram( $response, $user_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::set_pending()
     *
     * @since WPUF_SINCE
     */
    public function set_pending( $postdata, $form_id, $form_settings, $form_vars ) {
        return $this->payment_flow()->set_pending( $postdata, $form_id, $form_settings, $form_vars );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::monitor_new_post()
     *
     * @since WPUF_SINCE
     */
    public function monitor_new_post( $post_id, $form_id, $form_settings ) {
        return $this->payment_flow()->monitor_new_post( $post_id, $form_id, $form_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::monitor_new_draft_post()
     *
     * @since WPUF_SINCE
     */
    public function monitor_new_draft_post( $post_id, $form_id, $form_settings ) {
        return $this->payment_flow()->monitor_new_draft_post( $post_id, $form_id, $form_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::post_redirect()
     *
     * @since WPUF_SINCE
     */
    public function post_redirect( $response, $post_id, $form_id, $form_settings ) {
        return $this->payment_flow()->post_redirect( $response, $post_id, $form_id, $form_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::payment_received()
     *
     * @since WPUF_SINCE
     */
    public function payment_received( $info, $recurring ) {
        return $this->payment_flow()->payment_received( $info, $recurring );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::new_subscription()
     *
     * @since WPUF_SINCE
     */
    public function new_subscription( $user_id, $pack_id, $profile_id, $recurring, $status = null ) {
        return $this->payment_flow()->new_subscription( $user_id, $pack_id, $profile_id, $recurring, $status );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::handle_post_publish()
     *
     * @since WPUF_SINCE
     */
    public function handle_post_publish( $order_id ) {
        return $this->payment_flow()->handle_post_publish( $order_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::set_post_status()
     *
     * @since WPUF_SINCE
     */
    public function set_post_status( $post_id ) {
        return $this->payment_flow()->set_post_status( $post_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::get_payment_status()
     *
     * @since WPUF_SINCE
     */
    public function get_payment_status( $post_id ) {
        return $this->payment_flow()->get_payment_status( $post_id );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::user_subscription_cancel()
     *
     * @since WPUF_SINCE
     */
    public function user_subscription_cancel() {
        return $this->payment_flow()->user_subscription_cancel();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::handle_non_recur_subs()
     *
     * @since WPUF_SINCE
     */
    public function handle_non_recur_subs() {
        return $this->payment_flow()->handle_non_recur_subs();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::cancel_non_recurring_subscription()
     *
     * @since WPUF_SINCE
     */
    public function cancel_non_recurring_subscription() {
        return $this->payment_flow()->cancel_non_recurring_subscription();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::reset_user_subscription_data()
     *
     * @since WPUF_SINCE
     */
    public function reset_user_subscription_data( $post_id, $form_id, $form_settings, $form_vars ) {
        return $this->payment_flow()->reset_user_subscription_data( $post_id, $form_id, $form_settings, $form_vars );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::reset_trial()
     *
     * @since WPUF_SINCE
     */
    public function reset_trial( $sub_meta ) {
        return $this->payment_flow()->reset_trial( $sub_meta );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Payment_Flow::insert_free_pack_subscribers()
     *
     * @since WPUF_SINCE
     */
    public function insert_free_pack_subscribers( $pack_id, $userdata ) {
        return $this->payment_flow()->insert_free_pack_subscribers( $pack_id, $userdata );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::subscription_packs()
     *
     * @since WPUF_SINCE
     */
    public function subscription_packs( $atts = null ) {
        return $this->pack_display()->subscription_packs( $atts );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::pack_details()
     *
     * @since WPUF_SINCE
     */
    public function pack_details( $pack, $details_meta, $current_pack_id = '', $coupon_status = false ) {
        return $this->pack_display()->pack_details( $pack, $details_meta, $current_pack_id, $coupon_status );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::pack_view_data()
     *
     * @since WPUF_SINCE
     */
    public function pack_view_data( $pack, $details_meta, $current_pack_id = '', $coupon_status = false ) {
        return $this->pack_display()->pack_view_data( $pack, $details_meta, $current_pack_id, $coupon_status );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::packdropdown()
     *
     * @since WPUF_SINCE
     */
    public function packdropdown( $packs, $selected = '' ) {
        return $this->pack_display()->packdropdown( $packs, $selected );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::add_post_info()
     *
     * @since WPUF_SINCE
     */
    public function add_post_info( $form_id, $form_settings ) {
        return $this->pack_display()->add_post_info( $form_id, $form_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::subscription_info()
     *
     * @since WPUF_SINCE
     */
    public function subscription_info() {
        return $this->pack_display()->subscription_info();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::get_details_meta_value()
     *
     * @since WPUF_SINCE
     */
    public function get_details_meta_value() {
        return $this->pack_display()->get_details_meta_value();
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::force_pack_notice()
     *
     * @since WPUF_SINCE
     */
    public function force_pack_notice( $text, $id, $form_settings ) {
        return $this->pack_display()->force_pack_notice( $text, $id, $form_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Admin\Subscriptions\Pack_Display::force_pack_permission()
     *
     * @since WPUF_SINCE
     */
    public function force_pack_permission( $perm, $id, $form_settings ) {
        return $this->pack_display()->force_pack_permission( $perm, $id, $form_settings );
    }

    /**
     * The pack post type group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return Pack_Post_Type
     */
    private function pack_post_type() {
        if ( ! $this->pack_post_type ) {
            $this->pack_post_type = new Pack_Post_Type( $this );
        }

        return $this->pack_post_type;
    }

    /**
     * The payment flow group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return Payment_Flow
     */
    private function payment_flow() {
        if ( ! $this->payment_flow ) {
            $this->payment_flow = new Payment_Flow( $this );
        }

        return $this->payment_flow;
    }

    /**
     * The pack display group (built on first use).
     *
     * @since WPUF_SINCE
     *
     * @return Pack_Display
     */
    private function pack_display() {
        if ( ! $this->pack_display ) {
            $this->pack_display = new Pack_Display( $this );
        }

        return $this->pack_display;
    }
}
