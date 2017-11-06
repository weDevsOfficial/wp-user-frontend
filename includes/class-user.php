<?php

/**
 * The User Class
 *
 * @since 2.6.0
 */
class WPUF_User {

    /**
     * User ID
     *
     * @var integer
     */
    public $id;

    /**
     * User Object
     *
     * @var \WP_User
     */
    public $user;

    /**
     * The constructor
     *
     * @param integer|WP_User $user
     */
    function __construct( $user ) {

        if ( is_numeric( $user ) ) {

            $the_user = get_user_by( 'id', $user );

            if ( $the_user ) {
                $this->id   = $the_user->ID;
                $this->user = $the_user;
            }

        } elseif ( is_a( $user, 'WP_User') ) {
            $this->id   = $user->ID;
            $this->user = $user;
        }
    }

    /**
     * Check if a user's posting capability is locked
     *
     * @return boolean
     */
    public function post_locked() {
        return 'yes' ==  get_user_meta( $this->id, 'wpuf_postlock', true );
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
     * Handles user subscription
     *
     * @return \WPUF_User_Subscription
     */
    public function subscription() {
        return new WPUF_User_Subscription( $this );
    }
}
