<?php

/**
 * Page installer
 *
 * @since 2.3
 */
class WPUF_Admin_Installer {

    function __construct() {
        add_action( 'admin_notices', array($this, 'admin_notice') );
        add_action( 'admin_init', array($this, 'handle_request') );
    }

    /**
     * Print admin notices
     *
     * @return void
     */
    function admin_notice() {
        $page_created = get_option( '_wpuf_page_created' );

        if ( $page_created != '1' ) {
            ?>
            <div class="updated error">
                <p>
                    <?php _e( 'If you have not created <strong>WP User Frontend</strong> pages yet, you can do this by one click.', 'wpuf' ); ?>
                </p>
                <p class="submit">
                    <a class="button button-primary" href="<?php echo add_query_arg( array( 'install_wpuf_pages' => true ), admin_url( 'admin.php?page=wpuf-settings' ) ); ?>"><?php _e( 'Install WPUF Pages', 'wpuf' ); ?></a>
                    or
                    <a class="button" href="<?php echo add_query_arg( array( 'wpuf_hide_page_nag' => true ) ); ?>"><?php _e( 'Skip Setup', 'wpuf' ); ?></a>
                </p>
            </div>
            <?php
        }

        if ( isset( $_GET['wpuf_page_installed'] ) && $_GET['wpuf_page_installed'] == '1' ) {
            ?>
            <div class="updated">
                <p>
                    <strong><?php _e( 'Congratulations!', 'wpuf' ); ?></strong> <?php _e( 'Pages for <strong>WP User Frontend</strong> has been successfully installed and saved!', 'wpuf' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Handle the page creation button requests
     *
     * @return void
     */
    function handle_request() {
        if ( isset( $_GET['install_wpuf_pages'] ) && $_GET['install_wpuf_pages'] == '1' ) {
            $this->init_pages();
        }

        if ( isset( $_GET['wpuf_hide_page_nag'] ) && $_GET['wpuf_hide_page_nag'] == '1' ) {
            update_option( '_wpuf_page_created', '1' );
        }
    }

    /**
     * Initialize the plugin with some default page/settings
     *
     * @since 2.2
     * @return void
     */
    function init_pages() {

        // create a dashboard page
        $dashboard_page = $this->create_page( __( 'Dashboard', 'wpuf' ), '[wpuf_dashboard]' );
        $account_page   = $this->create_page( __( 'Account', 'wpuf' ), '[wpuf_account]' );
        $edit_page      = $this->create_page( __( 'Edit', 'wpuf' ), '[wpuf_edit]' );

        // login page
        $login_page     = $this->create_page( __( 'Login', 'wpuf' ), '[wpuf-login]' );

        $post_form      = $this->create_form();

        // payment page
        $subscr_page    = $this->create_page( __( 'Subscription', 'wpuf' ), __( '[wpuf_sub_pack]') );
        $payment_page   = $this->create_page( __( 'Payment', 'wpuf' ), __( 'Please select a gateway for payment') );
        $thank_page     = $this->create_page( __( 'Thank You', 'wpuf' ), __( '<h1>Payment is complete</h1><p>Congratulations, your payment has been completed!</p>') );
        $bank_page      = $this->create_page( __( 'Order Received', 'wpuf' ), __( 'Hi, we have received your order. We will validate the order and will take necessary steps to move forward.') );

        // save the settings
        if ( $edit_page ) {
            update_option( 'wpuf_general', array(
                'edit_page_id'      => $edit_page,
                'default_post_form' => $post_form
            ) );
        }

        // profile pages
        $profile_options = array();
        $reg_page = false;

        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }

        $data = apply_filters( 'wpuf_pro_page_install', $profile_options );

        if ( is_array( $data ) ) {

            if ( isset ( $data['profile_options'] ) ) {
                $profile_options = $data['profile_options'];
            }
            if ( isset ( $data['reg_page'] ) ) {
                $reg_page = $data['reg_page'];
            }
        }

        if ( $login_page && $reg_page ) {
            $profile_options['register_link_override'] = 'on';
        }

        update_option( 'wpuf_profile', $profile_options );

        // payment pages
        update_option( 'wpuf_payment', array(
            'subscription_page' => $subscr_page,
            'payment_page'      => $payment_page,
            'payment_success'   => $thank_page,
            'bank_success'      => $bank_page
        ) );

        update_option( '_wpuf_page_created', '1' );

        wp_redirect( admin_url( 'admin.php?page=wpuf-settings&wpuf_page_installed=1' ) );
        exit;
    }

    /**
     * Create a page with title and content
     *
     * @param  string $page_title
     * @param  string $post_content
     * @return false|int
     */
    function create_page( $page_title, $post_content = '', $post_type = 'page' ) {
        $page_id = wp_insert_post( array(
            'post_title'     => $page_title,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'post_content'   => $post_content
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            return $page_id;
        }

        return false;
    }

    /**
     * Create a basic registration form by default
     *
     * @return int|boolean
     */
    function create_reg_form() {
        return wpuf_create_sample_form( __( 'Registration', 'wpuf' ), 'wpuf_profile' );
    }

    /**
     * Create a post form
     *
     * @return void
     */
    function create_form() {
        return wpuf_create_sample_form( __( 'Sample Form', 'wpuf' ), 'wpuf_forms' );
    }

}
