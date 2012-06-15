<?php

/**
 * WPUF subscription manager
 *
 * @since 0.2
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class WPUF_Subscription {

    function __construct() {
        add_filter( 'wpuf_add_post_args', array($this, 'set_pending'), 10, 1 );
        add_filter( 'wpuf_after_post_redirect', array($this, 'post_redirect'), 10, 2 );

        add_action( 'wpuf_add_post_after_insert', array($this, 'monitor_new_post'), 10, 1 );
        add_action( 'wpuf_payment_received', array($this, 'payment_received') );

        add_shortcode( 'wpuf_sub_info', array($this, 'subscription_info') );
        add_shortcode( 'wpuf_sub_pack', array($this, 'subscription_packs') );
    }

    /**
     * Get a subscription row from database
     *
     * @global object $wpdb
     * @param int $sub_id subscription pack id
     * @return object|bool
     */
    public function get_subscription( $sub_id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}wpuf_subscription WHERE id=$sub_id";
        $row = $wpdb->get_row( $sql );

        return $row;
    }

    /**
     * Get all the subscription package
     *
     * @global object $wpdb
     * @return object|bool
     */
    public function get_subscription_packs() {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}wpuf_subscription ORDER BY created DESC";
        $result = $wpdb->get_results( $sql );

        return $result;
    }

    /**
     * Checks against the user, if he is valid for posting new post
     *
     * @global object $userdata
     * @param int $post_id
     * @return bool
     */
    function has_post_error( $post_id = 0 ) {
        global $userdata;

        if ( wpuf_get_option( 'charge_posting' ) == 'no' ) {
            return false;
        }

        //get duration and count
        $duration = ( $userdata->wpuf_sub_validity ) ? $userdata->wpuf_sub_validity : 0;
        $count = ( $userdata->wpuf_sub_pcount ) ? $userdata->wpuf_sub_pcount : 0;

        $error = false;

        //validate duration and count
        if ( !$duration || !$count ) {
            //if someone is zero
            return true;
        }

        //if duration is expired
        if ( $duration != 'unlimited' ) {
            $diff = strtotime( $duration ) - time();
            if ( $diff < 0 ) {
                return true;
            }
        }

        //no balance
        if ( $count != 'unlimited' && $count <= 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Set the new post status if charging is active
     *
     * @param string $postdata
     * @return string
     */
    function set_pending( $postdata ) {

        if ( wpuf_get_option( 'charge_posting' ) == 'yes' ) {
            $postdata['post_status'] = 'pending';
        }

        return $postdata;
    }

    /**
     * Checks the posting validity after a new post
     *
     * @global object $userdata
     * @global object $wpdb
     * @param int $post_id
     */
    function monitor_new_post( $post_id ) {
        global $wpdb;

        $userdata = get_userdata( get_current_user_id() );

        if ( $this->has_post_error( $post_id ) ) {
            //there is some error and it needs payment
            //add a uniqid to track the post easily
            $order_id = uniqid( rand( 10, 1000 ), false );
            add_post_meta( $post_id, 'wpuf_order_id', $order_id, true );
        } else {
            $count = ( $userdata->wpuf_sub_pcount ) ? $userdata->wpuf_sub_pcount : 0;

            //decrease the post count, if not umlimited
            if ( $count != 'unlimited' ) {
                $count = intval( $count );
                update_usermeta( $userdata->ID, 'wpuf_sub_pcount', $count - 1 );

                //set the post status to publish
                wp_update_post( array('ID' => $post_id, 'post_status' => 'publish') );
            }
        }
    }

    /**
     * Redirect to payment page after new post
     *
     * @param string $str
     * @param type $post_id
     * @return string
     */
    function post_redirect( $post_url, $post_id ) {

        if ( $this->has_post_error( $post_id ) ) {
            $redirect = get_permalink( wpuf_get_option( 'payment_page' ) ) . '?action=wpuf_pay&type=post&post_id=' . $post_id;

            return $redirect;
        }

        return $post_url;
    }

    /**
     * Perform actions when a new payment is made
     *
     * @param array $info payment info
     */
    function payment_received( $info ) {

        if ( $info['post_id'] ) {
            $this->handle_post_publish( $info['post_id'] );
        } else if ( $info['pack_id'] ) {
            $this->new_subscription( $info['user_id'], $info['pack_id'] );
        }
    }

    /**
     * Store new subscription info on user profile
     *
     * if data = 0, means 'unlimited'
     *
     * @param int $user_id
     * @param int $pack_id subscription pack id
     */
    public function new_subscription( $user_id, $pack_id ) {
        $subscription = $this->get_subscription( $pack_id );

        if ( $user_id && $subscription ) {

            //store the duration
            if ( $subscription->duration == 0 ) {
                update_user_meta( $user_id, 'wpuf_sub_validity', 'unlimited' );
            } else {
                //store that future date in usermeta
                $duration = date( 'Y-m-d G:i:s', strtotime( date( 'Y-m-d G:i:s', time() ) . " +{$subscription->duration} day" ) );
                update_user_meta( $user_id, 'wpuf_sub_validity', $duration );
            }

            //store post count
            if ( $subscription->count == 0 ) {
                update_user_meta( $user_id, 'wpuf_sub_pcount', 'unlimited' );
            } else {
                update_user_meta( $user_id, 'wpuf_sub_pcount', $subscription->count );
            }

            //store pack id
            update_user_meta( $user_id, 'wpuf_sub_pack', $subscription->id );
        }
    }

    /**
     * Publish the post if payment is made
     *
     * @param int $post_id
     */
    function handle_post_publish( $post_id ) {

        $post = get_post( $post_id );

        if ( $post && $post->post_status != 'publish' ) {
            $update_post = array(
                'ID' => $post_id,
                'post_status' => 'publish'
            );

            wp_update_post( $update_post );
        }
    }

    /**
     * Generate users subscription info with a shortcode
     *
     * @global type $userdata
     */
    function subscription_info() {
        global $userdata;

        ob_start();

        $userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

        if ( wpuf_get_option( 'charge_posting' ) == 'yes' && is_user_logged_in() ) {
            $duration = ( $userdata->wpuf_sub_validity ) ? $userdata->wpuf_sub_validity : 0;
            $count = ( $userdata->wpuf_sub_pcount ) ? $userdata->wpuf_sub_pcount : 0;

            $diff = strtotime( $duration ) - time();

            //var_dump( $duration, $count, $diff );
            //var_dump( $userdata );
            $d_str = '';
            $c_str = '';

            if ( $duration == 0 ) {
                $d_str = 0;
            } elseif ( $duration == 'unlimited' ) {
                $d_str = __( 'Unlimited duration', 'wpuf' );
            } elseif ( $diff <= 0 ) {
                $d_str = __( 'Expired', 'wpuf' );
            } elseif ( $diff > 0 ) {
                $d_str = 'Till ' . date_i18n( 'd M, Y H:i', strtotime( $duration ) );
            }

            if ( $count == 0 ) {
                $c_str = 0;
            } elseif ( $count == 'unlimited' ) {
                $c_str = 'unlimited post';
            } else {
                $c_str = $count;
            }
            ?>
            <div class="wpuf_sub_info">
                <h3><?php _e( 'Subscription Details', 'wpuf' ); ?></h3>
                <div class="text">
                    <strong><?php _e( 'Validity:', 'wpuf' ); ?></strong> <?php echo $d_str; ?>,
                    <strong><?php _e( 'Post Left:', 'wpuf' ); ?></strong> <?php echo $c_str; ?>
                </div>
            </div>

            <?php
        }

        return ob_get_clean();
    }

    /**
     * Show the subscription packs that are built
     * from admin Panel
     */
    function subscription_packs() {
        $packs = $this->get_subscription_packs();

        ob_start();

        if ( $packs ) {
            echo '<ul class="wpuf_packs">';
            foreach ($packs as $pack) {
                $duration = ( $pack->duration == 0 ) ? 'unlimited' : $pack->duration;
                $count = ( $pack->count == 0 ) ? 'unlimited' : $pack->count;
                ?>
                <li>
                    <h3><?php echo $pack->name; ?> - <?php echo $pack->description; ?></h3>
                    <p><?php echo $count; ?> posts for <?php echo $duration; ?> days.
                        <span class="cost"><?php echo wpuf_get_option( 'currency_symbol' ) . $pack->cost; ?></span>
                    </p>
                    <p><a href="<?php echo get_permalink( wpuf_get_option( 'payment_page' ) ); ?>?action=wpuf_pay&type=pack&pack_id=<?php echo $pack->id; ?>"><?php _e( 'Buy Now', 'wpuf' ); ?></a></p>
                </li>
                <?php
            }
            echo '</ul>';
        }

        return ob_get_clean();
    }

    /**
     * Show a info message when posting if payment is enabled
     */
    function add_post_info() {
        if ( $this->has_post_error() ) {
            ?>
            <div class="info">
                <?php printf( __( 'This will cost you <strong>%s</strong>. to add a new post. You may buy some bulk package too. ', 'wpuf' ), wpuf_get_option( 'currency_symbol' ) . wpuf_get_option( 'cost_per_post' ) ); ?>
            </div>
            <?php
        }
    }

}

$wpuf_subscription = new WPUF_Subscription();