<?php

/**
 * WPUF paid subscription manager
 *
 * @since v0.2
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class WPUF_Subscription {

    public function __construct() {
        $this->actions();
        $this->filters();
    }

    /**
     * Binds all the actions with WP
     *
     * @package WPUF Subscriptoin
     */
    public function actions() {
        //add_action( 'init', array( $this, 'debug' ) );
        add_action( 'wpuf_add_post_after_insert', array($this, 'check_new_post'), 10, 1 );
        add_action( 'init', array($this, 'paypal_success') );
        add_action( 'wpuf_add_post_form_top', array($this, 'add_post_info') );
        add_action( 'wpuf_payment_received', array($this, 'payment_notify_mail') );

        add_shortcode( 'wpuf_sub_info', array($this, 'subscription_info') );
        add_shortcode( 'wpuf_sub_pack', array($this, 'subscription_packs') );
    }

    /**
     * Bind all the filters with WP
     *
     * @package WPUF Subscriptoin
     */
    public function filters() {
        add_filter( 'wpuf_add_post_args', array($this, 'check_new_post_date'), 10, 1 );
        add_filter( 'the_content', array($this, 'show_payment_form'), 10, 1 );
        add_filter( 'wpuf_after_post_redirect', array($this, 'post_redirect'), 10, 2 );
    }

    /**
     * Debug helper method
     *
     * @global type $userdata
     */
    public function debug() {
        global $userdata;

        //update_usermeta( $userdata->ID, 'wpuf_sub_pcount', 1 );
        //var_dump( $userdata->wpuf_sub_validity, $userdata->wpuf_sub_pcount );
        //var_dump( $duration, $count );
    }

    /**
     * Get a subscription row from database
     *
     * @global type $wpdb
     * @param type $sub_id pack id
     * @return object|bool
     */
    public function get_subscription( $sub_id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}wpuf_subscription WHERE id=$sub_id";
        $row = $wpdb->get_row( $sql );

        if ( $row ) {
            return $row;
        }

        return false;
    }

    /**
     * Get all the subscription package
     *
     * @global type $wpdb
     * @return object|bool
     */
    public function get_subscription_packs() {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}wpuf_subscription ORDER BY created DESC";
        $result = $wpdb->get_results( $sql );

        if ( $result ) {
            return $result;
        }

        return false;
    }

    /**
     * Store new subscription info on user profile
     *
     * if data = 0, means 'unlimited'
     *
     * @param type $user_id
     * @param type $sub_id subscription pack id
     */
    public function new_subscription( $user_id, $sub_id ) {
        $subscription = $this->get_subscription( $sub_id );

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
     * Checks against the user, if he is valid for posting new post
     *
     * @global type $userdata
     * @param type $post_id
     * @return type
     */
    function has_post_error( $post_id = 0 ) {
        global $userdata;

        if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' ) {
            //get duration and count
            $duration = ( $userdata->wpuf_sub_validity ) ? $userdata->wpuf_sub_validity : 0;
            $count = ( $userdata->wpuf_sub_pcount ) ? $userdata->wpuf_sub_pcount : 0;

            $error = false;

            //validate duration and count
            if ( !$duration || !$count ) { //if someone is zero
                $error = true;
            }

            //if duration is expired
            if ( $duration != 'unlimited' ) {
                $diff = strtotime( $duration ) - time();
                if ( $diff < 0 ) {
                    $error = true;
                }
            }

            //no balance
            if ( $count != 'unlimited' && $count <= 0 ) {
                $error = true;
            }

            if ( $error ) {
                //some error found, charge for this post
                //error means no balance or no validity
                return true;
            } else {
                //user has balance, decrement the post count
                return false;
            }
        }
    }

    /**
     * Checks the posting validity after a new post
     *
     * @global type $userdata
     * @global type $wpdb
     * @param type $post_id
     */
    function check_new_post( $post_id ) {
        global $userdata, $wpdb;

        $userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

        if ( $this->has_post_error( $post_id ) ) {
            //there is some error and it needs payment
            //add a uniqid to track the post easily
            $order_id = uniqid( rand( 10, 1000 ), false );
            add_post_meta( $post_id, 'wpuf_order_id', $order_id, true );

            $this->paypal_form( 'post', $post_id );
        } else {
            $count = ( $userdata->wpuf_sub_pcount ) ? $userdata->wpuf_sub_pcount : 0;
            if ( $count != 'unlimited' ) {
                $count = intval( $count );
                //decrease the post count, if not umlimited
                update_usermeta( $userdata->ID, 'wpuf_sub_pcount', $count - 1 );

                //set the post status to publish
                wp_update_post( array('ID' => $post_id, 'post_status' => 'publish') );
            }
        }
    }

    /**
     * Set the new post status if charging is active
     *
     * @param string $postdata
     * @return string
     */
    function check_new_post_date( $postdata ) {
        //if post chargin is enabled, make post as pending
        if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' ) {
            $postdata['post_status'] = 'pending';
        }

        //var_dump( $postdata ); die();
        return $postdata;
    }

    /**
     * Shows the paypal button after new post
     *
     * @param type $type
     * @param type $post_id
     * @param type $pack_id
     * @param type $display
     * @return type
     */
    public function paypal_form( $type = 'post', $post_id = 0, $pack_id = 0, $display = false ) {
        // Include the paypal library
        include_once dirname( __FILE__ ) . '/lib/payment/Paypal.php';

        //var_dump( $type, $post_id, $pack_id ); exit;
        $email = get_option( 'wpuf_sub_paypal_mail' );
        $curreny = get_option( 'wpuf_sub_currency' );
        $amount = 0;

        if ( $type == 'post' ) {
            $post = get_post( $post_id );
            $amount = get_option( 'wpuf_sub_amount' );
            $item_name = $post->post_title;
            $item_number = get_post_meta( $post_id, 'wpuf_order_id', true );
            $custom = 'post';
            $cbt = sprintf( __( 'Click here to complete the pack on %s', 'wpuf' ), get_bloginfo( 'name' ) );
        }

        if ( $type == 'pack' ) {
            $pack = $this->get_subscription( $pack_id );
            if ( $pack ) {
                $amount = $pack->cost;
                $item_name = $pack->name;
                $item_number = $pack->id;
                $custom = 'pack';
                $cbt = sprintf( __( 'Click here to complete the pack on %s', 'wpuf' ), get_bloginfo( 'name' ) );
            }
        }


        // Create an instance of the paypal library
        $myPaypal = new Paypal();

        // Specify your paypal email
        $myPaypal->addField( 'business', $email );

        // Specify the currency
        $myPaypal->addField( 'currency_code', $curreny );

        // Specify the url where paypal will send the user on success/failure
        $myPaypal->addField( 'return', get_bloginfo( 'home' ) . '/?action=wpuf_pay_success' );
        $myPaypal->addField( 'cancel_return', get_bloginfo( 'home' ) );

        // Specify the url where paypal will send the IPN
        $myPaypal->addField( 'notify_url', get_bloginfo( 'home' ) . '/?action=wpuf_pay_success' );

        // Specify the product information
        $myPaypal->addField( 'item_name', $item_name );
        $myPaypal->addField( 'amount', $amount );
        $myPaypal->addField( 'item_number', $item_number );

        // Specify any custom value
        $myPaypal->addField( 'custom', $custom );

        $myPaypal->addField( 'cbt', $cbt );

        // Enable test mode if needed
        if ( get_option( 'wpuf_sub_paypal_sandbox' ) == 'yes' ) {
            $myPaypal->enableTestMode();
        }

        // Let's start the train!
        $form = $myPaypal->submitPayment();

        return $form;
    }

    /**
     * Handles the paypal return events
     *
     * @global type $userdata
     */
    function paypal_success() {
        global $userdata;

        //just returned from paypal after payment
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpuf_pay_success' ) {
            if ( $_POST['custom'] == 'post' ) {
                $this->handle_post_publish();
            } else if ( $_POST['custom'] == 'pack' ) {
                $this->new_subscription( $userdata->ID, intval( $_POST['item_number'] ) );
                $this->insert_payment( 0, intval( $_POST['item_number'] ) );
            }
        }

        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpuf_pay' && isset( $_REQUEST['post_id'] ) ) {
            //$this->paypal_form( 'post', intval( $_REQUEST['post_id'] ), 0, true );
        }
    }

    /**
     * Publish the post if payment is made
     *
     * @global type $wpdb
     * @global type $userdata
     */
    function handle_post_publish() {
        global $wpdb, $userdata;

        $order_id = $_POST['item_number'];
        $sql = $wpdb->prepare( "SELECT p.ID, p.post_status
            FROM $wpdb->posts p, $wpdb->postmeta m
            WHERE p.ID = m.post_id AND p.post_status <> 'publish' AND m.meta_key = 'wpuf_order_id' AND m.meta_value = '$order_id'" );

        $post = $wpdb->get_row( $sql );

        if ( $post && $post->post_status != 'publish' ) {
            $update_post = array();
            $update_post['ID'] = $post->ID;
            $update_post['post_status'] = 'publish';
            wp_update_post( $update_post );
        }

        $this->insert_payment( $post->ID );
    }

    /**
     * Insert the payment details in databse
     *
     * @global type $wpdb
     * @global type $userdata
     * @param type $post_id
     * @param type $pack_id
     */
    function insert_payment( $post_id = 0, $pack_id = 0 ) {
        global $wpdb, $userdata;

        // check and make sure this transaction hasn't already been added
        $sql = "SELECT transaction_id
                FROM " . $wpdb->prefix . "wpuf_transaction
                WHERE txn_id = '" . $wpdb->escape( wpuf_clean_tags( $_POST['txn_id'] ) ) . "' LIMIT 1";

        $results = $wpdb->get_row( $sql );

        if ( !$results ) {
            $data = array(
                'user_id' => $userdata->ID,
                'status' => 'completed',
                'cost' => $_POST['mc_gross'],
                'post_id' => $post_id,
                'pack_id' => $pack_id,
                'payer_first_name' => $_POST['first_name'],
                'payer_last_name' => $_POST['last_name'],
                'payer_email' => $_POST['payer_email'],
                'payment_type' => 'Paypal',
                'payer_address' => $_POST['address_country_code'],
                'transaction_id' => $_POST['txn_id'],
                'created' => current_time( 'mysql' )
            );

            $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', $data );
            do_action( 'wpuf_payment_received', $data );
        }

        wp_redirect( home_url(), 301 );
        exit;
    }

    /**
     * Redirect to payment page after new post
     *
     * @param string $str
     * @param type $post_id
     * @return string
     */
    function post_redirect( $str, $post_id ) {
        if ( $this->has_post_error( $post_id ) ) {
            $str = get_permalink( get_option( 'wpuf_sub_pay_page' ) ) . '?action=wpuf_pay&type=post&post_id=' . $post_id;
        }

        return $str;
    }

    /**
     * Show the paypal form with hidden fields
     *
     * @global type $post
     * @param type $content
     * @return type
     */
    function show_payment_form( $content ) {
        global $post;

        $pay_page = intval( get_option( 'wpuf_sub_pay_page' ) );
        if ( $post->ID == $pay_page && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpuf_pay' ) {
            $type = ( $_REQUEST['type'] == 'post' ) ? 'post' : 'pack';
            $post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
            $pack_id = isset( $_REQUEST['pack_id'] ) ? intval( $_REQUEST['pack_id'] ) : 0;

            $content .= $this->paypal_form( $type, $post_id, $pack_id );
        }

        return $content;
    }

    /**
     * Generate users subscription info with a shortcode
     *
     * @global type $userdata
     */
    function subscription_info() {
        global $userdata;

        $userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

        if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' && is_user_logged_in() ) {
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
    }

    /**
     * Show the subscription packs that are built
     * from admin Panel
     */
    function subscription_packs() {
        $packs = $this->get_subscription_packs();

        if ( $packs ) {
            echo '<ul class="wpuf_packs">';
            foreach ($packs as $pack) {
                $duration = ( $pack->duration == 0 ) ? 'unlimited' : $pack->duration;
                $count = ( $pack->count == 0 ) ? 'unlimited' : $pack->count;
                ?>
                <li>
                    <h3><?php echo $pack->name; ?> - <?php echo $pack->description; ?></h3>
                    <p><?php echo $count; ?> posts for <?php echo $duration; ?> days.
                        <span class="cost"><?php echo get_option( 'wpuf_sub_currency_sym' ) . $pack->cost; ?></span>
                    </p>
                    <p><a href="<?php echo get_permalink( get_option( 'wpuf_sub_pay_page' ) ); ?>?action=wpuf_pay&type=pack&pack_id=<?php echo $pack->id; ?>"><?php _e( 'Buy Now', 'wpuf' ); ?></a></p>
                </li>
                <?php
            }
            echo '</ul>';
        } else {
            //no pack found
        }
    }

    /**
     * Show a info message when posting if payment is enabled
     */
    function add_post_info() {
        if ( $this->has_post_error() ) {
            ?>
            <div class="info">
                <?php printf( __( 'This will cost you <strong>%s</strong>. to add a new post. You may buy some bulk package too. ', 'wpuf' ), get_option( 'wpuf_sub_currency_sym' ) . get_option( 'wpuf_sub_amount' ) ); ?>
            </div>
            <?php
        }
    }

    /**
     * Send payment received mail
     */
    function payment_notify_mail() {
        $headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ) . ">" . "\r\n\\";
        $subject = sprintf( __( '[%s] Payment Received', 'wpuf' ), get_bloginfo( 'name' ) );
        $msg = sprintf( __( 'New payment received at %s', 'wpuf' ), get_bloginfo( 'name' ) );

        $receiver = get_bloginfo( 'admin_email' );
        wp_mail( $receiver, $subject, $msg, $headers );
    }

}

global $wpuf_subscription;
$wpuf_subscription = new WPUF_Subscription();
