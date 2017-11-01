<?php

/**
 * User Subscription Class
 *
 * @since 2.6.0
 */
class WPUF_User_Subscription {

    /**
     * The user object
     *
     * @var \WPUF_User
     */
    private $user;

    /**
     * The current subscription package
     *
     * @var array
     */
    private $pack;

    /**
     * Constructor
     *
     * @param \WPUF_User $user
     */
    function __construct( $user ) {
        $this->user = $user;

        $this->populate_data();
    }

    /**
     * Populate the subscription info into the class
     *
     * @return void
     */
    public function populate_data() {

        if ( ! $this->pack ) {
            $this->pack = get_user_meta( $this->user->id, '_wpuf_subscription_pack', true );
        }
    }

    /**
     * Get the current pack of the user
     *
     * @return array|WP_Error
     */
    public function current_pack() {
        $pack = $this->pack;

        if ( ! isset( $this->pack['pack_id'] ) ) {
            return new WP_Error( 'no-pack', __( 'The user doesn\'t have an active subscription.') );
        }

        // seems like the user has a pack, now check expiration
        if ( $this->expired() ) {
            return new WP_Error( 'expired', __( 'The subscription has been expired.' ) );
        }

        return $pack;
    }

    /**
     * Check if the current pack is expired
     *
     * @return boolean
     */
    public function expired() {

        // if no data found, take it as expired
        if ( ! isset( $this->pack['expire'] ) ) {
            return true;
        }

        $expire_date = isset( $this->pack['expire'] ) ? $this->pack['expire'] : 0;
        $expired     = true;

        if ( strtolower( $expire_date ) == 'unlimited' || empty( $expire_date ) ) {
            $expired = false;
        } else if ( strtotime( date( 'Y-m-d', strtotime( $expire_date ) ) ) >= strtotime( date( 'Y-m-d', time() ) ) ) {
            $expired = false;
        } else {
            $expired = true;
        }

        return $expired;
    }

    /**
     * Check if a pack is recurring
     *
     * @return boolean
     */
    public function recurring() {
        $current_pack = $this->current_pack();

        if ( is_wp_error( $current_pack ) ) {
            return false;
        }

        return 'yes' == $current_pack['recurring'];
    }

    /**
     * Check if the user has posts left on a post type
     *
     * @param  string  $post_type
     *
     * @return boolean
     */
    public function has_post_count( $post_type ) {
        if ( isset( $this->pack['posts'] ) && isset( $this->pack['posts'][ $post_type ] ) ) {
            $count = (int) $this->pack['posts'][ $post_type ];

            if ( $count > 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has a subscription pack
     *
     * @param  integer  $pack_id
     *
     * @return boolean
     */
    public function has_pack( $pack_id ) {
        return $pack_id == $this->current_pack_id();
    }

    /**
     * Returns the current pack ID used by the user
     *
     * @return integer|false
     */
    public function current_pack_id() {
        $pack = get_user_meta( $this->user->id, '_wpuf_subscription_pack', true );

        if ( isset( $pack['pack_id'] ) ) {
            return (int) $pack['pack_id'];
        }

        return false;
    }

    /**
     * Determine if the user has used a free pack before
     *
     * @param integer $pack_id
     *
     * @return boolean
     */
    public function used_free_pack( $pack_id ) {
        $has_used = get_user_meta( $this->user->id, 'wpuf_fp_used', true );

        if ( $has_used == '' ) {
            return false;
        }

        if ( is_array( $has_used ) && isset( $has_used[ $pack_id ] ) ) {
            return true;
        }

        return false;
    }

    /**
     * Add a free used pack to the user account
     *
     * @param int $pack_id
     */
    public function add_free_pack( $user_id, $pack_id ) {
        $has_used = get_user_meta( $this->user->id, 'wpuf_fp_used', true );
        $has_used = is_array( $has_used ) ? $has_used : array();

        $has_used[$pack_id] = $pack_id;
        update_user_meta( $user_id, 'wpuf_fp_used', $has_used );
    }
}
