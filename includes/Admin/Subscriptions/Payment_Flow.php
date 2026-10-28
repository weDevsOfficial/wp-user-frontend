<?php
/**
 * Subscription payment flow
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\User_Subscription;

/**
 * What happens to a submitted post and a user's pack around payment: pending
 * status and order id on paid posts, publish on payment, pack assignment
 * when a pack is paid, quota and trial resets, user cancel link, free pack
 * subscribers, the daily expiry of non-recurring packs.
 *
 * @since WPUF_SINCE Moved out of Admin\Subscription, which keeps the hooks, the
 * static helpers and the data methods, and delegates.
 */
class Payment_Flow {

    /**
     * The facade: hook objects, callbacks and the other groups go through it.
     *
     * @var Subscription
     */
    protected $admin;

    /**
     * @since WPUF_SINCE
     *
     * @param Subscription $admin The facade.
     */
    public function __construct( Subscription $admin ) {
        $this->admin = $admin;
    }

    /**
     * Set the new post status if charging is active
     *
     * @param string $postdata
     *
     * @return string
     */
    public function set_pending( $postdata, $form_id, $form_settings, $form_vars ) {
        $form             = new Form( $form_id );
        $post_type        = ! empty( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';
        $payment_options  = $form->is_charging_enabled();
        $force_pack       = $form->is_enabled_force_pack();
        $pay_per_post     = $form->is_enabled_pay_per_post();
        $fallback_cost    = $form->is_enabled_fallback_cost();
        $current_user     = wpuf_get_user();
        $current_pack     = $current_user->subscription()->current_pack();
        $has_post         = $current_user->subscription()->has_post_count( $post_type );

        if ( $payment_options && $force_pack && ! is_wp_error( $current_pack ) && $fallback_cost && ! $has_post ) {
            $postdata['post_status'] = 'pending';
        }

        if ( $payment_options && ! $force_pack && ( $pay_per_post || ( $fallback_cost && ! $has_post ) ) ) {
            $postdata['post_status'] = 'pending';
        }

        return $postdata;
    }

    /**
     * Checks the posting validity after a new post
     *
     * @global object $userdata
     *
     * @param int $post_id
     */
    public function monitor_new_post( $post_id, $form_id, $form_settings ) {
        global $userdata;

        $post = get_post( $post_id );

        // bail out if charging is not enabled
        $form = new Form( $form_id );

        if ( ! $form->is_charging_enabled() ) {
            return;
        }

        $force_pack    = $form->is_enabled_force_pack();
        $pay_per_post  = $form->is_enabled_pay_per_post();
        $fallback_cost = $form->is_enabled_fallback_cost();
        $current_user  = wpuf_get_user();
        $current_pack  = $current_user->subscription()->current_pack();
        $has_post      = $current_user->subscription()->has_post_count( $form_settings['post_type'] );

        if ( $force_pack && ! is_wp_error( $current_pack ) && $has_post ) {
            $sub_info    = Subscription::get_user_pack( $userdata->ID );
            $post_type   = isset( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';
            $count       = isset( $sub_info['posts'][ $post_type ] ) ? intval( $sub_info['posts'][ $post_type ] ) : 0;
            $post_status = isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish';
            $featured_count = ! empty( $sub_info['total_feature_item'] ) ? intval( $sub_info['total_feature_item'] ) : 0;

            $old_status = $post->post_status;
            wp_transition_post_status( $post_status, $old_status, $post );

            // decrease the post count, if not unlimited. `published` means the
            // post was already counted: the submit handler fires this hook after
            // edits too, and an edit must not use up another post.
            $wpuf_post_status = Stores::submissions()->quota_flag( $post_id );

            if ( ! in_array( $wpuf_post_status, [ 'new_draft', 'published' ], true ) ) {
                if ( $count > 0 ) {
                    $sub_info['posts'][ $post_type ] = $count - 1;
                }

                $user_subscription = new User_Subscription( $current_user );
                $sub_info          = $user_subscription->handle_featured_item( $post_id, $sub_info );
                $this->admin->update_user_subscription_meta( $userdata->ID, $sub_info );
            }

            //meta added to make post have flag if post is published
            Stores::submissions()->set_quota_flag( $post_id, 'published' );
        } elseif ( $pay_per_post || ( $force_pack && $fallback_cost && ! $has_post ) ) {
            //there is some error and it needs payment
            //add a uniqid to track the post easily
            $order_id = uniqid( wp_rand( 10, 1000 ), false );
            Stores::submissions()->set_order_once( $post_id, $order_id );
            Stores::submissions()->set_payment_status( $post_id, 'pending' );
        }
    }

    /**
     * Check if the post is draft and charging is enabled
     *
     * @global object $userdata
     *
     * @param int $post_id
     */
    public function monitor_new_draft_post( $post_id, $form_id, $form_settings ) {
        global $userdata;

        // bail out if charging is not enabled
        $charging_enabled = '';
        $form             = new Form( $form_id );
        $payment_options  = $form->is_charging_enabled();

        if ( ! $payment_options || ! is_user_logged_in() ) {
            $charging_enabled = 'no';
        } else {
            $charging_enabled = 'yes';
        }
        //phpcs:ignore
        $userdata = get_userdata( get_current_user_id() );
        $order_id = uniqid( wp_rand( 10, 1000 ), false );

        if ( Subscription::has_user_error( $form_settings ) ) {
            Stores::submissions()->set_order_once( $post_id, $order_id );
        }

        if ( $form->is_enabled_pay_per_post() || ( $form->is_enabled_force_pack() && $form->is_enabled_fallback_cost() && ! wpuf_get_user()->subscription()->has_post_count( $form_settings['post_type'] ) ) ) {
            Stores::submissions()->set_order_once( $post_id, $order_id );
            Stores::submissions()->set_payment_status( $post_id, 'pending' );
        }
    }

    /**
     * Redirect to payment page after new post
     *
     * @param string $str
     * @param int   $post_id
     *
     * @return string
     */
    public function post_redirect( $response, $post_id, $form_id, $form_settings ) {
        // Admin users bypass payment redirect
        if ( current_user_can( wpuf_admin_role() ) ) {
            return $response;
        }

        $form             = new Form( $form_id );
        $payment_options  = $form->is_charging_enabled();
        $force_pack       = $form->is_enabled_force_pack();
        $fallback_cost    = $form->is_enabled_fallback_cost();
        $current_user     = wpuf_get_user();
        $current_pack     = $current_user->subscription()->current_pack();
        $has_pack         = $current_user->subscription()->has_post_count( $form_settings['post_type'] );
        $ppp_cost_enabled = $form->is_enabled_pay_per_post();
        $sub_expired      = $current_user->subscription()->expired();

        if ( ( $payment_options && ! $has_pack ) || ( $payment_options && $sub_expired ) ) {
            $order_id = Stores::submissions()->order_id( $post_id );

            // check if there is a order ID
            if ( $order_id || ( $payment_options && $fallback_cost ) ) {
                $response['show_message'] = false;
                $response['redirect_to']  = add_query_arg(
                    [
                        'action'  => 'wpuf_pay',
                        'type'    => 'post',
                        'post_id' => $post_id,
                    ], get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) )
                );
            }

            if ( ! $force_pack && $ppp_cost_enabled ) {
                $response['show_message'] = false;
                $response['redirect_to']  = add_query_arg(
                    [
                        'action'  => 'wpuf_pay',
                        'type'    => 'post',
                        'post_id' => $post_id,
                    ], get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) )
                );
            }
        }

        return $response;
    }

    /**
     * Perform actions when a new payment is made
     *
     * @param array $info payment info
     */
    public function payment_received( $info, $recurring ) {
        if ( $info['post_id'] ) {
            $order_id = Stores::submissions()->order_id( $info['post_id'] );

            $this->handle_post_publish( $order_id );
        } elseif ( $info['pack_id'] ) {
            if ( $recurring ) {
                $profile_id = $info['profile_id'] ?? null;
            } else {
                $profile_id = isset( $info['user_id'] ) ? $info['user_id'] : null;
            }

            wpuf_get_user( $info['user_id'] )->subscription()->add_pack( $info['pack_id'], $profile_id, $recurring, $info['status'] );

            if ( false === $recurring ) {
                Stores::user_packs()->clear_expiry_notices( $profile_id );
            }
        }
    }

    /**
     * Store new subscription info on user profile
     *
     * If data = 0, means 'unlimited'
     *
     * @param int $user_id
     * @param int $pack_id subscription pack id
     */
    public function new_subscription( $user_id, $pack_id, $profile_id, $recurring, $status = null ) {
        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user( $user_id )->subscription()->add_pack( $pack_id, $profile_id = null, $recurring, $status = null );' );

        wpuf_get_user( $user_id )->subscription()->add_pack( $pack_id, $profile_id = null, $recurring, $status = null );
    }

    /**
     * Publish the post if payment is made
     *
     * @param int $post_id
     */
    public function handle_post_publish( $order_id ) {
        $post = Subscription::post_by_orderid( $order_id );

        if ( $post ) {
            // set the payment status
            Stores::submissions()->set_payment_status( $post->ID, 'completed' );

            if ( $post->post_status !== 'publish' ) {
                $this->set_post_status( $post->ID );
            }
        }
    }

    /**
     * Maintain post status from the form settings
     *
     * @since 2.1.9
     *
     * @param int $post_id
     */
    public function set_post_status( $post_id ) {
        $post_status = 'publish';
        $form_id     = Stores::submissions()->form_id( $post_id );

        if ( $form_id ) {
            $form_settings = wpuf_get_form_settings( $form_id );
            $post_status   = $form_settings['post_status'];
        }

        Stores::submissions()->set_post_status( $post_id, $post_status );
    }

    /**
     * Returns the payment status of a post
     *
     * @since 2.5.9
     *
     * @param $post_id
     *
     * @return string
     */
    public function get_payment_status( $post_id ) {
        return Stores::submissions()->payment_status( $post_id );
    }

    /**
     * Handle subscription cancel request from the user
     *
     * @return Subscription|bool
     */
    public function user_subscription_cancel() {
        if ( isset( $_POST['wpuf_cancel_subscription'] ) ) {
            $nonce       = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
            $user_id     = isset( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : 0;
            $gateway     = isset( $_POST['gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway'] ) ) : 0;
            $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

            if ( empty( $nonce ) ) {
                return false;
            }

            if ( ! wp_verify_nonce( $nonce, 'wpuf-sub-cancel-' . $user_id ) ) {
                // Legacy nonce compat for theme-overridden templates; self-cancel only.
                if ( $user_id !== get_current_user_id() ) {
                    return false;
                }

                if ( ! wp_verify_nonce( $nonce, 'wpuf-sub-cancel' ) ) {
                    return false;
                }
            }

            if ( $user_id !== get_current_user_id() && ! current_user_can( wpuf_admin_role() ) ) {
                return false;
            }

            $current_pack = Subscription::get_user_pack( $user_id );

            $gateway = ( $gateway === 'bank/manual' ) ? 'bank' : $gateway;

            if ( 'bank' === $gateway ) {
                $this->admin->update_user_subscription_meta( $user_id, 'Cancel' );
            } else {
                do_action( "wpuf_cancel_subscription_{$gateway}", $_POST );
            }

            $this->admin::subscriber_cancel( $user_id, $current_pack['pack_id'] );

            wp_safe_redirect( $request_uri );
            exit;
        }
    }

    /**
     * Add daily cron for non recur subscritpion
     *
     * @since 3.5.14
     *
     * @return void
     */
    public function handle_non_recur_subs() {
        if ( ! wp_next_scheduled( 'non_recur_subs_daily' ) ) {
            wp_schedule_event( time(), 'daily', 'non_recur_subs_daily' );
        }
    }

    /**
     * Cancel non recurring subs if expired
     *
     * @since 3.5.14
     *
     * @return void
     */
    public function cancel_non_recurring_subscription() {
        $all_subscription = Stores::user_packs()->query();

        if ( empty( $all_subscription ) ) {
            return;
        }

        $current_time  = current_time( 'mysql' );
        $non_recurrent = array_filter(
            $all_subscription, function ( $pack ) use ( $current_time ) {
                $pack = maybe_unserialize( $pack->meta_value );
                return ! empty( $pack['recurring'] ) && $pack['recurring'] === 'no' && $current_time >= $pack['expire'];
            }
        );

        $remove_feature_item_by_author = [];

        foreach ( $non_recurrent as $ns ) {
            $user_id  = $ns->user_id;
            $sub_meta = 'cancel';
            $meta     = maybe_unserialize( $ns->meta_value );

            Subscription::update_user_subscription_meta( $user_id, $sub_meta );
            // remove feature item if sub expire
            if ( ! empty( $meta['remove_feature_item'] ) && 'on' === $meta['remove_feature_item'] ) {
                array_push( $remove_feature_item_by_author, $user_id );
            }
        }

        if ( ! empty( $remove_feature_item_by_author ) ) {
            $stickies = get_option( 'sticky_posts' );

            $post_ids = get_posts(
                [
                    'author__in'  => $remove_feature_item_by_author,
                    'numberposts' => -1,
                    'post_status' => [ 'draft', 'pending', 'private', 'publish' ],
                    'fields'      => 'ids',
                ]
            );

            foreach ( $post_ids as $post_id ) {
                if ( in_array( $post_id, $stickies, true ) ) {
                    unstick_post( $post_id );
                }
            }
        }
    }

    /**
     * Reset the post count of a subscription of a user
     *
     * @since 2.3.11
     *
     * @param $post_id
     * @param $form_id
     * @param $form_settings
     * @param $form_vars
     */
    public function reset_user_subscription_data( $post_id, $form_id, $form_settings, $form_vars ) {
        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user()->subscription()->reset_subscription_data( $post_id, $form_id, $form_settings, $form_vars );' );

        wpuf_get_user()->subscription()->reset_subscription_data( $post_id, $form_id, $form_settings, $form_vars );
    }

    /**
     * Reset trials data if used once
     *
     * @since 3.5.14
     *
     * @param $sub_meta
     *
     * @return mixed
     */
    public function reset_trial( $sub_meta ) {
        if ( Stores::user_packs()->used_trial( get_current_user_id() ) ) {
            unset( $sub_meta['trial_status'] );
            unset( $sub_meta['trial_duration'] );
            unset( $sub_meta['trial_duration_type'] );
        }
        return $sub_meta;
    }

    /**
     * Insert Free pack users to subscribers list
     *
     * @since 2.8.8
     *
     * @param $post_id
     *
     * @return void
     */
    public function insert_free_pack_subscribers( $pack_id, $userdata ) {
        $subscription = wpuf()->subscription->get_subscription( $pack_id );

        if ( $userdata->id && $subscription ) {
            $user_sub             = Subscription::get_user_pack( $userdata->id );
            $post_expiration_time = wpuf_date2mysql( $user_sub['expire'] );

            $table_data = [
                'user_id'             => $userdata->id,
                'name'                => $userdata->user->data->display_name,
                'subscribtion_id'     => $pack_id,
                'subscribtion_status' => 'Free',
                'gateway'             => 'Free',
                'transaction_id'      => 'Free',
                'starts_from'         => gmdate( 'd-m-Y' ),
                'expire'              => empty( $post_expiration_time ) ? 'recurring' : $post_expiration_time,
            ];

            Stores::subscribers()->insert( $table_data );
        }
    }
}
