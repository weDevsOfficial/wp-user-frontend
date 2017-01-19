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
        wpuf_load_template(
            "dashboard/subscription.php",
            array( 'sections' => $sections, 'current_section' => $current_section )
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

}