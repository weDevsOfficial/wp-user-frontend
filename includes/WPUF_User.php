<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_User;

/**
 * The User Class
 *
 * @since 2.6.0
 */
class WPUF_User {

    /**
     * User ID
     *
     * @var int
     */
    public $id;

    /**
     * User Object
     *
     * @var WP_User
     */
    public $user;

    /**
     * The constructor
     *
     * @param int|WP_User $user
     */
    public function __construct( $user ) {
        if ( is_numeric( $user ) ) {
            $the_user = get_user_by( 'id', $user );

            if ( $the_user ) {
                $this->id   = $the_user->ID;
                $this->user = $the_user;
            }
        } elseif ( is_a( $user, 'WP_User' ) ) {
            $this->id   = $user->ID;
            $this->user = $user;
        }
    }

    /**
     * Check if a user's posting capability is locked
     *
     * @return bool
     */
    public function post_locked() {
        return 'yes' == get_user_meta( $this->id, 'wpuf_postlock', true );
    }

    /**
     * Get the post lock reason
     *
     * @return string
     */
    public function lock_reason() {
        return get_user_meta( $this->id, 'wpuf_lock_cause', true );
    }

    /**
     * Check if a user's post edit capability is locked
     *
     * @return bool
     */
    public function edit_post_locked() {
        return 'yes' == get_user_meta( $this->id, 'wpuf_edit_postlock', true );
    }

    /**
     * Get the edit post lock reason
     *
     * @return string
     */
    public function edit_post_lock_reason() {
        return get_user_meta( $this->id, 'wpuf_edit_post_lock_cause', true );
    }

    /**
     * Lock or unlock the user's frontend posting, with the reason shown to them.
     *
     * @since WPUF_SINCE
     *
     * @param string $locked 'yes' / 'no'
     * @param string $reason Reason text
     *
     * @return void
     */
    public function set_post_lock( $locked, $reason ) {
        update_user_meta( $this->id, 'wpuf_postlock', $locked );
        update_user_meta( $this->id, 'wpuf_lock_cause', $reason );
    }

    /**
     * Lock or unlock the user's frontend editing, with the reason shown to them.
     *
     * @since WPUF_SINCE
     *
     * @param string $locked 'yes' / 'no'
     * @param string $reason Reason text
     *
     * @return void
     */
    public function set_edit_post_lock( $locked, $reason ) {
        update_user_meta( $this->id, 'wpuf_edit_postlock', $locked );
        update_user_meta( $this->id, 'wpuf_edit_post_lock_cause', $reason );
    }

    /**
     * Handles user subscription
     *
     * @return User_Subscription
     */
    public function subscription() {
        return new User_Subscription( $this );
    }

    /**
     * Check if user is verified
     *
     * @since 2.8.8
     *
     * @return bool
     */
    public function is_verified() {
        if ( ! metadata_exists( 'user', $this->id, '_wpuf_user_active' ) ) {
            return true;
        }

        if ( intval( get_user_meta( $this->id, '_wpuf_user_active', true ) ) == 1 ) {
            return true;
        }

        return false;
    }

    /**
     * Mark user as verified
     *
     * @since 2.8.8
     *
     * @return void
     */
    public function mark_verified() {
        update_user_meta( $this->id, '_wpuf_user_active', 1 );
    }

    /**
     * Mark user as unverified
     *
     * @since 2.8.8
     *
     * @return void
     */
    public function mark_unverified() {
        update_user_meta( $this->id, '_wpuf_user_active', 0 );
    }

    /**
     * Set user activation key
     *
     * @since 2.8.8
     *
     * @return void
     */
    public function set_activation_key( $key ) {
        update_user_meta( $this->id, '_wpuf_activation_key', $key );
    }

    /**
     * Get user activation key
     *
     * @since 2.8.8
     *
     * @return string
     */
    public function get_activation_key() {
        return get_user_meta( $this->id, '_wpuf_activation_key', true );
    }

    /**
     * Remove user activation key
     *
     * @since 2.8.8
     *
     * @return void
     */
    public function remove_activation_key() {
        delete_user_meta( $this->id, '_wpuf_activation_key' );
    }

    /**
     * @since 2.8.9
     *
     * @param bool $array
     *
     * @return mixed|string
     */
    public function get_billing_address( $array = false ) {
        $address = get_user_meta( $this->id, 'wpuf_address_fields', true );

        if ( $array ) {
            return $address;
        }

        if ( ! empty( $address ) ) {
            return implode( ' ,', $address );
        }

        return '';
    }

    /**
     * @since 2.8.9
     *
     * @param bool $array
     *
     * @return mixed|string
     */
    public function get_transaction_data( $array = false ) {
        $txn_data = Stores::transactions()->rows_for_user( $this->id, [], 0, 0, true );

        return apply_filters( 'wpuf_privacy_transaction_export_data', $txn_data );
    }
}
