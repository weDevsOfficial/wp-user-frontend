<?php

/**
 * Dashboard class
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class WPUF_Frontend_Account {

    /**
     * Class constructor
     */
    public function __construct() {
        add_shortcode( 'wpuf_account', array( $this, 'shortcode' ) );
        add_action( 'wpuf_account_content_dashboard', array( $this, 'dashboard_section' ), 10, 2 );
        add_action( 'wpuf_account_content_posts', array( $this, 'posts_section' ), 10, 2 );
        add_action( 'wpuf_account_content_subscription', array( $this, 'subscription_section' ), 10, 2 );
        add_action( 'wpuf_account_content_edit-profile', array( $this, 'edit_profile_section' ), 10, 2 );
        add_action( 'template_redirect', array( $this, 'update_profile' ) );
    }

    /**
     * Handle's user account functionality
     *
     * Insert shortcode [wpuf_account] in a page to
     * show the user account
     *
     * @since 2.5
     */
    function shortcode( $atts ) {

        extract( shortcode_atts( array(), $atts ) );

        ob_start();

        if ( is_user_logged_in() ) {
            $section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : 'dashboard';

            $sections        = wpuf_get_account_sections();
            $current_section = array();

            foreach ( $sections as $account_section ) {
                if ( $section == $account_section['slug'] ) {
                    $current_section = $account_section;
                    break;
                }
            }

            $content = wpuf_load_template( 'account.php', array( 'sections' => $sections, 'current_section' => $current_section ) );
        } else {
            $message = wpuf_get_option( 'un_auth_msg', 'wpuf_dashboard' );

            if ( empty( $message ) ) {
                $msg = '<div class="wpuf-message">' . sprintf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( get_permalink(), false ) ) . '</div>';
                echo apply_filters( 'wpuf_dashboard_unauth', $msg, $post_type );
            } else {
                echo $message;
            }
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Display the dashboard section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @since  2.5
     *
     * @return void
     */
    public function dashboard_section( $sections, $current_section ) {
        wpuf_load_template(
            "dashboard/dashboard.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
        );
    }

    /**
     * Display the posts section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @since  2.5
     *
     * @return void
     */
    public function posts_section( $sections, $current_section ) {
        wpuf_load_template(
            "dashboard/posts.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
        );
    }

    /**
     * Display the subscription section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @since  2.5
     *
     * @return void
     */
    public function subscription_section( $sections, $current_section ) {

        if ( wpuf_get_option( 'charge_posting', 'wpuf_payment' ) != 'yes' || !is_user_logged_in() ) {
            return;
        }

        global $userdata;

        $userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

        $user_sub = WPUF_Subscription::get_user_pack( $userdata->ID );
        if ( !isset( $user_sub['pack_id'] ) ) {
            return;
        }

        $pack = WPUF_Subscription::get_subscription( $user_sub['pack_id'] );

        $details_meta['payment_page'] = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
        $details_meta['onclick'] = '';
        $details_meta['symbol'] = wpuf_get_option( 'currency_symbol', 'wpuf_payment' );

        $billing_amount = ( intval( $pack->meta_value['billing_amount'] ) > 0 ) ? $details_meta['symbol'] . $pack->meta_value['billing_amount'] : __( 'Free', 'wpuf' );
        if ( $pack->meta_value['recurring_pay'] == 'yes' ) {
            $recurring_des = sprintf( 'For each %s %s', $pack->meta_value['billing_cycle_number'], $pack->meta_value['cycle_period'], $pack->meta_value['trial_duration_type'] );
            $recurring_des .= !empty( $pack->meta_value['billing_limit'] ) ? sprintf( ', for %s installments', $pack->meta_value['billing_limit'] ) : '';
            $recurring_des = $recurring_des;
        } else {
            $recurring_des = '';
        }

        wpuf_load_template(
            "dashboard/subscription.php",
            array(
                'sections'        => $sections,
                'current_section' => $current_section,
                'userdata'        => $userdata,
                'user_sub'        => $user_sub,
                'pack'            => $pack,
                'billing_amount'  => $billing_amount,
                'recurring_des'   => $recurring_des,
            )
        );
    }

    /**
     * Display the edit profile section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @since  2.5
     *
     * @return void
     */
    public function edit_profile_section( $sections, $current_section ) {
        wpuf_load_template(
            "dashboard/edit-profile.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
        );
    }

    public function update_profile() {
        if ( isset( $_POST['action'] ) && $_POST['action'] == 'wpuf_account_update_profile' ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-account-update-profile' ) ) {
                wp_die( __( 'Nonce failure', 'wpuf' ) );
            }

            var_dump( $_POST ); exit;
        }
    }

}