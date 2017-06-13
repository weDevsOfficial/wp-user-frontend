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
        add_action( 'wp_ajax_wpuf_account_update_profile', array( $this, 'update_profile' ) );
    }

    /**
     * Handle's user account functionality
     *
     * Insert shortcode [wpuf_account] in a page to
     * show the user account
     *
     * @since 2.4.2
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

            wpuf_load_template( 'account.php', array( 'sections' => $sections, 'current_section' => $current_section ) );
        } else {
            $message = wpuf_get_option( 'un_auth_msg', 'wpuf_dashboard' );
            wpuf_load_template( 'unauthorized.php', array( 'message' => $message ) );
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
     * @since  2.4.2
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
     * @since  2.4.2
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
     * @since  2.4.2
     *
     * @return void
     */
    public function subscription_section( $sections, $current_section ) {

        if ( wpuf_get_option( 'charge_posting', 'wpuf_payment' ) != 'yes' || ! is_user_logged_in() ) {
            return;
        }

        global $userdata;

        $userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

        $user_sub = WPUF_Subscription::get_user_pack( $userdata->ID );
        if ( ! isset( $user_sub['pack_id'] ) ) {
            die( __( "<p>You've not subscribed any package yet.</p>", 'wpuf' ) );
        } else {
            _e( "<p>You've subscribed to the following package.</p>", 'wpuf' );
        }

        $pack = WPUF_Subscription::get_subscription( $user_sub['pack_id'] );

        $details_meta['payment_page'] = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
        $details_meta['onclick']      = '';
        $details_meta['symbol']       = wpuf_get_currency( 'symbol' );

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
     * @since  2.4.2
     *
     * @return void
     */
    public function edit_profile_section( $sections, $current_section ) {
        wpuf_load_template(
            "dashboard/edit-profile.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
        );
    }

    /**
     * Update profile via Ajax
     *
     * @since  2.4.2
     *
     * @return json
     */
    public function update_profile() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-account-update-profile' ) ) {
            wp_send_json_error( __( 'Nonce failure', 'wpuf' ) );
        }

        global $current_user;

        $first_name       = ! empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name        = ! empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
        $email            = ! empty( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
        $current_password = ! empty( $_POST['current_password'] ) ? $_POST['current_password'] : '';
        $pass1            = ! empty( $_POST['pass1'] ) ? $_POST['pass1'] : '';
        $pass2            = ! empty( $_POST['pass2'] ) ? $_POST['pass2'] : '';
        $save_pass        = true;

        if ( empty( $first_name ) ) {
            wp_send_json_error( __( 'First Name is a required field.', 'wpuf' ) );
        }

        if ( empty( $last_name ) ) {
            wp_send_json_error( __( 'Last Name is a required field.', 'wpuf' ) );
        }

        if ( empty( $email ) ) {
            wp_send_json_error( __( 'Email is a required field.', 'wpuf' ) );
        }

        $user             = new stdClass();
        $user->ID         = $current_user->ID;
        $user->first_name = $first_name;
        $user->last_name  = $last_name;

        if ( $email ) {
            $email = sanitize_email( $email );
            if ( ! is_email( $email ) ) {
                wp_send_json_error( __( 'Please provide a valid email address.', 'wpuf' ) );
            } elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
                wp_send_json_error( __( 'This email address is already registered.', 'wpuf' ) );
            }
            $user->user_email = $email;
        }

        if ( ! empty( $current_password ) && empty( $pass1 ) && empty( $pass2 ) ) {
            wp_send_json_error( __( 'Please fill out all password fields.', 'wpuf' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && empty( $current_password ) ) {
            wp_send_json_error( __( 'Please enter your current password.', 'wpuf' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
            wp_send_json_error( __( 'Please re-enter your password.', 'wpuf' ) );
            $save_pass = false;
        } elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
            wp_send_json_error( __( 'New passwords do not match.', 'wpuf' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
            wp_send_json_error( __( 'Your current password is incorrect.', 'wpuf' ) );
            $save_pass = false;
        }

        if ( $pass1 && $save_pass ) {
            $user->user_pass = $pass1;
        }

        $result = wp_update_user( $user );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( __( 'Your current password is incorrect.', 'wpuf' ) );
        }

        wp_send_json_success();
    }

}
