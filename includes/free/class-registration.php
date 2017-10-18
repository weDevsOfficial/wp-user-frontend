<?php

/**
 * Registration handler class
 *
 * @since 2.5.8
 */
class WPUF_Registration {

    private $registration_errors = array();
    private $messages = array();

    private static $_instance;
    public $atts = array();
    public $userrole = '';

    function __construct() {

        add_shortcode( 'wpuf-registration', array($this, 'registration_form') );

        add_action( 'init', array($this, 'process_registration') );
        add_action( 'init', array($this, 'wp_registration_page_redirect') );

        add_filter( 'register_url', array($this, 'get_registration_url') );
    }

    /**
     * Singleton object
     *
     * @return self
     */
    public static function init() {

        if ( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Is override enabled
     *
     * @return boolean
     */
    function is_override_enabled() {

        $override = wpuf_get_option( 'register_link_override', 'wpuf_profile', 'off' );

        if ( $override !== 'on' ) {
            return false;
        }

        return true;
    }

    /**
     * Get action url based on action type
     *
     * @param string $action
     * @param string $redirect_to url to redirect to
     * @return string
     */
    function get_action_url( $action = 'registration', $redirect_to = '' ) {

        $root_url = $this->get_registration_url();

        switch ($action) {
            case 'register':
                return $this->get_registration_url();
                break;

            default:
                if ( empty( $redirect_to ) ) {
                    return $root_url;
                }

                return add_query_arg( array('redirect_to' => urlencode( $redirect_to )), $root_url );
                break;
        }
    }


    /**
     * Get registration page url
     *
     * @return boolean|string
     */
    function get_registration_url( $register_url = null ) {

        $register_link_override = wpuf_get_option('register_link_override','wpuf_profile',false);
        $page_id = wpuf_get_option( 'reg_override_page', 'wpuf_profile', false );

        if ( $register_link_override == 'off' ) {
            return $register_url;
        }

        if ( !$page_id ) {
            return false;
        }

        $url = get_permalink( $page_id );

        return apply_filters( 'wpuf_register_url', $url, $page_id );
    }


    /**
     * Get actions links for displaying in forms
     *
     * @param array $args
     * @return string
     */
    function get_action_links( $args = array() ) {

        $defaults = array(
            'register'     => true
        );

        $args = wp_parse_args( $args, $defaults );
        $links = array();

        if ( $args['register'] ) {
            $links[] = sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'register' ), __( 'Register', 'wpuf' ) );
        }

        return implode( ' | ', $links );
    }

    /**
     * Shows the registration form
     *
     * @return string
     */
    function registration_form( $atts ) {
        $atts = shortcode_atts(
        array(
                'role' => '',
            ), $atts
        );
        $userrole = $atts['role'];

        $roleencoded = wpuf_encryption( $userrole );

        $reg_page = $this->get_registration_url();

        if ( false === $reg_page ) {
            return;
        }

        ob_start();

        if ( is_user_logged_in() ) {

            wpuf_load_template( 'logged-in.php', array(
                'user' => wp_get_current_user()
            ) );

        } else {

            $action = isset( $_GET['action'] ) ? $_GET['action'] : 'register';

            $args = array(
                'action_url' => $reg_page,
                'userrole'   => $roleencoded
            );

            wpuf_load_template( 'registration-form.php', $args );

        }

        return ob_get_clean();
    }

    /**
     * Process registration form
     *
     * @return void
     */
    function process_registration() {
        if ( !empty( $_POST['wpuf_registration'] ) && !empty( $_POST['_wpnonce'] ) ) {
            $userdata = array();

            if ( isset( $_POST['_wpnonce'] ) ) {
                wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_registration_action' );
            }

            $validation_error = new WP_Error();
            $validation_error = apply_filters( 'wpuf_process_registration_errors', $validation_error, $_POST['reg_fname'], $_POST['reg_lname'], $_POST['reg_email'],  $_POST['log'], $_POST['pwd1'], $_POST['pwd2'] );

            if ( $validation_error->get_error_code() ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . $validation_error->get_error_message();
                return;
            }

            if ( empty( $_POST['reg_fname'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'First name is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['reg_lname'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Last name is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['reg_email'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Email is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['log'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Username is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['pwd1'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Password is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['pwd2'] ) ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Confirm Password is required.', 'wpuf' );
                return;
            }

            if ( $_POST['pwd1'] != $_POST['pwd2'] ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Passwords are not same.', 'wpuf' );
                return;
            }

            if ( get_user_by( 'login', $_POST['log'] ) === $_POST['log'] ) {
                $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'A user with same username already exists.', 'wpuf' );
                return;
            }

            if ( is_email( $_POST['log'] ) && apply_filters( 'wpuf_get_username_from_email', true ) ) {
                $user = get_user_by( 'email', $_POST['log'] );

                if ( isset( $user->user_login ) ) {
                    $userdata['user_login']  = $user->user_login;
                } else {
                    $this->registration_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'wpuf' );
                    return;
                }
            } else {
                $userdata['user_login']      = $_POST['log'];
            }

            $dec_role = wpuf_decryption( $_POST['urhidden'] );

            $userdata['first_name'] = $_POST['reg_fname'];
            $userdata['last_name']  = $_POST['reg_lname'];
            $userdata['user_email'] = $_POST['reg_email'];
            $userdata['user_pass']  = $_POST['pwd1'];

            if ( get_role( $dec_role ) ) {
                $userdata['role'] = $dec_role;
            }

            $user = wp_insert_user( $userdata );

            if ( is_wp_error( $user ) ) {
                $this->registration_errors[] = $user->get_error_message();
                return;
            } else {

                if ( !empty( $_POST['redirect_to'] ) ) {
                    $redirect = esc_url( $_POST['redirect_to'] );
                } elseif ( wp_get_referer() ) {
                    $redirect = esc_url( wp_get_referer() );
                } else {
                    $redirect = $this->get_registration_url() . '?success=yes';
                }

                wp_redirect( apply_filters( 'wpuf_registration_redirect', $redirect, $user ) );
                exit;
            }
        }
    }

    /**
     * Redirect to registration page
     *
     * @return void
     */
    function wp_registration_page_redirect() {
        global $pagenow;

        if ( ! is_admin() && $pagenow == 'wp-login.php' && isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {

            if ( wpuf_get_option( 'register_link_override', 'wpuf_profile' ) != 'on' ) {
                return;
            }

            $reg_page = get_permalink( wpuf_get_option( 'reg_override_page', 'wpuf_profile' ) );
            wp_redirect( $reg_page );
            exit;
        }
    }

    /**
     * Show errors on the form
     *
     * @return void
     */
    function show_errors() {
        if ( $this->registration_errors ) {
            foreach ($this->registration_errors as $error) {
                echo '<div class="wpuf-error">';
                _e( $error,'wpuf' );
                echo '</div>';
            }
        }
    }

    /**
     * Show messages on the form
     *
     * @return void
     */
    function show_messages() {
        if ( $this->messages ) {
            foreach ($this->messages as $message) {
                printf( '<div class="wpuf-message">%s</div>', $message );
            }
        }
    }

    /**
     * Get a posted value for showing in the form field
     *
     * @param string $key
     * @return string
     */
    public static function get_posted_value( $key ) {
        if ( isset( $_REQUEST[$key] ) ) {
            return esc_attr( $_REQUEST[$key] );
        }

        return '';
    }

}
