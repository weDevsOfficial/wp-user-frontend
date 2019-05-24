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
        add_action( 'wpuf_account_content_billing-address', array( $this, 'billing_address_section' ), 10, 2 );
        add_action( 'wp_ajax_wpuf_account_update_profile', array( $this, 'update_profile' ) );
        add_filter( 'wpuf_options_wpuf_my_account', array( $this, 'add_settings_options' ) );
        add_filter( 'wpuf_account_sections', array( $this, 'add_account_sections' ) );
        add_action( 'wpuf_account_content_submit-post', array( $this, 'submit_post_section' ), 10, 2 );
    }

    /**
     * Add new settings options
     *
     * @return array $options
     *
     * @since 2.9.0
     */
    public function add_settings_options( $options ) {
        $options[] = array(
            'name'  => 'allow_post_submission',
            'label' => __( 'Post Submission', 'wp-user-frontend' ),
            'desc'  => __( 'Enable if you want to allow users to submit post from the account page.', 'wp-user-frontend' ),
            'type'  => 'checkbox',
            'default' => 'on',
        );

        $options[] = array(
            'name'  => 'post_submission_label',
            'label' => __( 'Submission Menu Label', 'wp-user-frontend' ),
            'desc'  => __( 'Label for post submission menu', 'wp-user-frontend' ),
            'type'  => 'text',
            'default' => __( 'Submit Post', 'wp-user-frontend' ),
        );

        $options[] = array(
            'name'    => 'post_submission_form',
            'label'   => __( 'Submission Form', 'wp-user-frontend' ),
            'desc'    => __( 'Select a post form that will use to submit post by the users from their account page.', 'wp-user-frontend' ),
            'type'    => 'select',
            'options' => $this->get_post_forms()
        );

        return $options;
    }

    /**
     * Get post forms created by WPUF
     *
     * @return array $forms
     *
     * @since 2.9.0
     */
    public function get_post_forms() {
        $args = array(
            'post_type' => 'wpuf_forms',
            'post_status' => 'any',
            'orderby'     => 'DESC',
            'order'       => 'ID'
        );

        $query = new WP_Query( $args );

        $forms = array();

        if ( $query->have_posts() ) {

            $i = 0;

            while ( $query->have_posts() ) {
                $query->the_post();

                $form = $query->posts[ $i ];

                $settings = get_post_meta( get_the_ID(), 'wpuf_form_settings', true );

                $forms[ $form->ID ] = $form->post_title;

                $i++;
            }
        }

        return $forms;
    }

    /**
     * Show/Hide frontend post submission menu depending on option
     *
     * @return array $sections
     *
     * @since 2.9.0
     */
    public function add_account_sections( $sections ) {
        $allow_post_submission = wpuf_get_option( 'allow_post_submission', 'wpuf_my_account', 'on' );
        $submission_label      = wpuf_get_option( 'post_submission_label', 'wpuf_my_account', __( 'Submit Post', 'wp-user-frontend' ) );

        if ( $allow_post_submission == 'on' ) {
            $sections = array_merge( $sections, array( array( 'slug' => 'submit-post', 'label' => $submission_label ) ) );
        }

        return $sections;
    }

    /**
     * Display the submit post section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @return void
     *
     * @since 2.9.0
     */
    public function submit_post_section( $sections, $current_section ) {
        $allow_post_submission = wpuf_get_option( 'allow_post_submission', 'wpuf_my_account', 'on' );

        if ( $allow_post_submission != 'on' ) {
            return;
        }

        wpuf_load_template(
            "submit-post.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
        );
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

        $wpuf_user  = wpuf_get_user();
        $sub_id     = $wpuf_user->subscription()->current_pack_id();

        if ( !$sub_id ) {
            _e( "<p>You are not subscribed to any package yet.</p>", 'wp-user-frontend' );
            return;
        }
        $user_subscription = new WPUF_User_Subscription( $wpuf_user );
        $user_sub = $user_subscription->current_pack();
        $pack     = WPUF_Subscription::get_subscription( $sub_id );

        $details_meta['payment_page'] = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
        $details_meta['onclick']      = '';
        $details_meta['symbol']       = wpuf_get_currency( 'symbol' );

        $recurring_des = '';

        $billing_amount = ( intval( $pack->meta_value['billing_amount'] ) > 0 ) ? $details_meta['symbol'] . $pack->meta_value['billing_amount'] : __( 'Free', 'wp-user-frontend' );
        if ( $pack->meta_value['recurring_pay'] == 'yes' ) {
            $recurring_des = sprintf( __( 'For each %s %s', 'wp-user-frontend' ), $pack->meta_value['billing_cycle_number'], $pack->meta_value['cycle_period'], $pack->meta_value['trial_duration_type'] );
            $recurring_des .= !empty( $pack->meta_value['billing_limit'] ) ? sprintf( __( ', for %s installments', 'wp-user-frontend' ), $pack->meta_value['billing_limit'] ) : '';
        }

        wpuf_load_template(
            "dashboard/subscription.php",
            array(
                'sections'        => $sections,
                'current_section' => $current_section,
                'userdata'        => $wpuf_user->user,
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
     * Display the billing address section
     *
     * @param  array  $sections
     * @param  string $current_section
     *
     * @return void
     */
    public function billing_address_section( $sections, $current_section ) {
        wpuf_load_template(
            "dashboard/billing-address.php",
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
            wp_send_json_error( __( 'Nonce failure', 'wp-user-frontend' ) );
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
            wp_send_json_error( __( 'First Name is a required field.', 'wp-user-frontend' ) );
        }

        if ( empty( $last_name ) ) {
            wp_send_json_error( __( 'Last Name is a required field.', 'wp-user-frontend' ) );
        }

        if ( empty( $email ) ) {
            wp_send_json_error( __( 'Email is a required field.', 'wp-user-frontend' ) );
        }

        $user             = new stdClass();
        $user->ID         = $current_user->ID;
        $user->first_name = $first_name;
        $user->last_name  = $last_name;

        if ( $email ) {
            $email = sanitize_email( $email );
            if ( ! is_email( $email ) ) {
                wp_send_json_error( __( 'Please provide a valid email address.', 'wp-user-frontend' ) );
            } elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
                wp_send_json_error( __( 'This email address is already registered.', 'wp-user-frontend' ) );
            }
            $user->user_email = $email;
        }

        if ( ! empty( $current_password ) && empty( $pass1 ) && empty( $pass2 ) ) {
            wp_send_json_error( __( 'Please fill out all password fields.', 'wp-user-frontend' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && empty( $current_password ) ) {
            wp_send_json_error( __( 'Please enter your current password.', 'wp-user-frontend' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
            wp_send_json_error( __( 'Please re-enter your password.', 'wp-user-frontend' ) );
            $save_pass = false;
        } elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
            wp_send_json_error( __( 'New passwords do not match.', 'wp-user-frontend' ) );
            $save_pass = false;
        } elseif ( ! empty( $pass1 ) && ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
            wp_send_json_error( __( 'Your current password is incorrect.', 'wp-user-frontend' ) );
            $save_pass = false;
        }

        if ( $pass1 && $save_pass ) {
            $user->user_pass = $pass1;
        }

        $result = wp_update_user( $user );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( __( 'Your current password is incorrect.', 'wp-user-frontend' ) );
        }

        wp_send_json_success();
    }

}
