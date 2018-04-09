<?php

/**
 * Login and forgot password handler class
 *
 * @since 2.2
 * @author Tareq Hasan <tareq@wedevs.com>
 */
class WPUF_Simple_Login {

    private $login_errors = array();
    private $messages = array();

    private static $_instance;

    function __construct() {
        add_shortcode( 'wpuf-login', array($this, 'login_form') );

        add_action( 'init', array($this, 'process_login') );
        add_action( 'init', array($this, 'process_logout') );
        add_action( 'init', array($this, 'process_reset_password') );

        add_action( 'init', array($this, 'wp_login_page_redirect') );
        add_action( 'init', array($this, 'activation_user_registration') );

        // URL filters
        add_filter( 'login_url', array($this, 'filter_login_url'), 10, 2 );
        add_filter( 'logout_url', array($this, 'filter_logout_url'), 10, 2 );
        add_filter( 'lostpassword_url', array($this, 'filter_lostpassword_url'), 10, 2 );
        add_filter( 'register_url', array($this, 'get_registration_url') );

        add_filter( 'login_redirect', array( $this, 'default_login_redirect' ) );

        add_filter( 'authenticate', array($this, 'successfully_authenticate'), 30, 3 );
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
    function get_action_url( $action = 'login', $redirect_to = '' ) {
        $root_url = $this->get_login_url();

        switch ($action) {
            case 'resetpass':
                return add_query_arg( array('action' => 'resetpass'), $root_url );
                break;

            case 'lostpassword':
                return add_query_arg( array('action' => 'lostpassword'), $root_url );
                break;

            case 'register':
                return $this->get_registration_url();
                break;

            case 'logout':
                return wp_nonce_url( add_query_arg( array('action' => 'logout'), $root_url ), 'log-out' );
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
     * Get login page url
     *
     * @return boolean|string
     */
    function get_login_url() {
        $page_id = wpuf_get_option( 'login_page', 'wpuf_profile', false );

        if ( !$page_id ) {
            return false;
        }

        $url = get_permalink( $page_id );

        return apply_filters( 'wpuf_login_url', $url, $page_id );
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
     * Filter the login url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_login_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'login', $redirect );
    }


    /**
     * Filter the logout url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_logout_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'logout', $redirect );
    }


    /**
     * Filter the lost password url with ours
     *
     * @param string $url
     * @param string $redirect
     * @return string
     */
    function filter_lostpassword_url( $url, $redirect ) {

        if ( !$this->is_override_enabled() ) {
            return $url;
        }

        return $this->get_action_url( 'lostpassword', $redirect );
    }


    /**
     * Get actions links for displaying in forms
     *
     * @param array $args
     * @return string
     */
    function get_action_links( $args = array() ) {
        $defaults = array(
            'login'        => true,
            'register'     => true,
            'lostpassword' => true
        );

        $args = wp_parse_args( $args, $defaults );
        $links = array();

        if ( $args['login'] ) {
            $links[] = sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'login' ), __( 'Log In', 'wpuf' ) );
        }

        if ( $args['register'] ) {
            $links[] = sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'register' ), __( 'Register', 'wpuf' ) );
        }

        if ( $args['lostpassword'] ) {
            $links[] = sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'lostpassword' ), __( 'Lost Password', 'wpuf' ) );
        }

        return implode( ' | ', $links );
    }

    /**
     * Shows the login form
     *
     * @return string
     */
    function login_form() {

        $login_page = $this->get_login_url();

        if ( false === $login_page ) {
            return;
        }

        ob_start();

        if ( is_user_logged_in() ) {

            wpuf_load_template( 'logged-in.php', array(
                'user' => wp_get_current_user()
            ) );

        } else {

            $action = isset( $_GET['action'] ) ? $_GET['action'] : 'login';

            $args = array(
                'action_url' => $login_page,
            );

            switch ($action) {
                case 'lostpassword':

                    $this->messages[] = __( 'Please enter your username or email address. You will receive a link to create a new password via email.', 'wpuf' );

                    wpuf_load_template( 'lost-pass-form.php', $args );
                    break;

                case 'rp':
                case 'resetpass':

                    if ( isset( $_GET['reset'] ) && $_GET['reset'] == 'true' ) {

                        printf( '<div class="wpuf-message">' . __( 'Your password has been reset. %s', 'wpuf' ) . '</div>', sprintf( '<a href="%s">%s</a>', $this->get_action_url( 'login' ), __( 'Log In', 'wpuf' ) ) );
                        return;
                    } else {

                        $this->messages[] = __( 'Enter your new password below..', 'wpuf' );

                        wpuf_load_template( 'reset-pass-form.php', $args );
                    }

                    break;

                default:

                    if ( isset( $_GET['checkemail'] ) && $_GET['checkemail'] == 'confirm' ) {
                        $this->messages[] = __( 'Check your e-mail for the confirmation link.', 'wpuf' );
                    }

                    if ( isset( $_GET['loggedout'] ) && $_GET['loggedout'] == 'true' ) {
                        $this->messages[] = __( 'You are now logged out.', 'wpuf' );
                    }

                    wpuf_load_template( 'login-form.php', $args );

                    break;
            }
        }

        return ob_get_clean();
    }

    /**
     * Process login form
     *
     * @return void
     */
    function process_login() {
        if ( !empty( $_POST['wpuf_login'] ) && !empty( $_POST['_wpnonce'] ) ) {
            $creds = array();

            if ( isset( $_POST['_wpnonce'] ) ) {
                wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_login_action' );
            }

            $validation_error = new WP_Error();
            $validation_error = apply_filters( 'wpuf_process_login_errors', $validation_error, $_POST['log'], $_POST['pwd'] );

            if ( $validation_error->get_error_code() ) {
                $this->login_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . $validation_error->get_error_message();
                return;
            }

            if ( empty( $_POST['log'] ) ) {
                $this->login_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Username is required.', 'wpuf' );
                return;
            }

            if ( empty( $_POST['pwd'] ) ) {
                $this->login_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'Password is required.', 'wpuf' );
                return;
            }

            if ( is_email( $_POST['log'] ) && apply_filters( 'wpuf_get_username_from_email', true ) ) {
                $user = get_user_by( 'email', $_POST['log'] );

                if ( isset( $user->user_login ) ) {
                    $creds['user_login'] = $user->user_login;
                } else {
                    $this->login_errors[] = '<strong>' . __( 'Error', 'wpuf' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'wpuf' );
                    return;
                }
            } else {
                $creds['user_login'] = $_POST['log'];
            }

            $creds['user_password'] = $_POST['pwd'];
            $creds['remember'] = isset( $_POST['rememberme'] );
            $secure_cookie = is_ssl() ? true : false;
            $user = wp_signon( apply_filters( 'wpuf_login_credentials', $creds ), $secure_cookie );

            if ( is_wp_error( $user ) ) {
                $this->login_errors[] = $user->get_error_message();
                return;
            } else {
                $redirect = $this->login_redirect();
                wp_redirect( apply_filters( 'wpuf_login_redirect', $redirect, $user ) );
                exit;
            }
        }
    }

    /**
     * Redirect user to a specific page after login
     *
     * @return  string $url
     */
    function login_redirect() {

        $redirect_to = wpuf_get_option( 'redirect_after_login_page', 'wpuf_profile', false );

        if ( 'previous_page' == $redirect_to && !empty( $_POST['redirect_to'] ) ) {
            return esc_url( $_POST['redirect_to'] );
        }

        $redirect = get_permalink( $redirect_to );

        if ( !empty( $redirect ) ) {
            return $redirect;
        }

        return home_url();
    }

    /**
     * Redirect user to a specific page after login using default WordPress login form
     *
     * @return  string $url
     */
    function default_login_redirect( $redirect ) {
        $override    = wpuf_get_option( 'wp_default_login_redirect', 'wpuf_profile', false );
        $redirect_to = wpuf_get_option( 'redirect_after_login_page', 'wpuf_profile', false );

        $link = get_permalink( $redirect_to );
        if ( $override != 'on' || 'previous_page' == $redirect_to || empty( $link ) ) {
            return $redirect;
        }

        return $this->login_redirect();
    }

    /**
     * Logout the user
     *
     * @return void
     */
    function process_logout() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'logout' ) {

            if ( !$this->is_override_enabled() ) {
                return;
            }

            check_admin_referer('log-out');
            wp_logout();

            $redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : add_query_arg( array( 'loggedout' => 'true' ), $this->get_login_url() ) ;
            wp_safe_redirect( $redirect_to );
            exit();
        }
    }


    /**
     * Handle reset password form
     *
     * @return void
     */
    public function process_reset_password() {

        if ( ! isset( $_POST['wpuf_reset_password'] ) ) {
            return;
        }

        // process lost password form
        if ( isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) ) {
            wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_lost_pass' );

            if ( $this->retrieve_password() ) {
                $url = add_query_arg( array( 'checkemail' => 'confirm' ), $this->get_login_url() );
                wp_redirect( $url );
                exit;
            }
        }

        // process reset password form
        if ( isset( $_POST['pass1'] ) && isset( $_POST['pass2'] ) && isset( $_POST['key'] ) && isset( $_POST['login'] ) && isset( $_POST['_wpnonce'] ) ) {

            // verify reset key again
            $user = $this->check_password_reset_key( $_POST['key'], $_POST['login'] );

            if ( is_object( $user ) ) {

                // save these values into the form again in case of errors
                $args['key']   = $_POST['key'];
                $args['login'] = $_POST['login'];

                wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_reset_pass' );

                if ( empty( $_POST['pass1'] ) || empty( $_POST['pass2'] ) ) {
                    $this->login_errors[] = __( 'Please enter your password.', 'wpuf' );
                    return;
                }

                if ( $_POST[ 'pass1' ] !== $_POST[ 'pass2' ] ) {
                    $this->login_errors[] = __( 'Passwords do not match.', 'wpuf' );
                    return;
                }

                $errors = new WP_Error();

                do_action( 'validate_password_reset', $errors, $user );

                if ( $errors->get_error_messages() ) {
                    foreach ( $errors->get_error_messages() as $error ) {
                        $this->login_errors[] = $error;
                    }

                    return;
                }

                if ( ! $this->login_errors ) {

                    $this->reset_password( $user, $_POST['pass1'] );

                    do_action( 'wpuf_customer_reset_password', $user );

                    wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );
                    exit;
                }
            }

        }
    }


    /**
     * Handles sending password retrieval email to customer.
     *
     * @access public
     * @uses $wpdb WordPress Database object
     * @return bool True: when finish. False: on error
     */
    function retrieve_password() {
        global $wpdb, $wp_hasher;

        if ( empty( $_POST['user_login'] ) ) {

            $this->login_errors[] = __( 'Enter a username or e-mail address.', 'wpuf' );
            return;

        } elseif ( strpos( $_POST['user_login'], '@' ) && apply_filters( 'wpuf_get_username_from_email', true ) ) {

            $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );

            if ( empty( $user_data ) ) {
                $this->login_errors[] = __( 'There is no user registered with that email address.', 'wpuf' );
                return;
            }

        } else {

            $login = trim( $_POST['user_login'] );

            $user_data = get_user_by( 'login', $login );
        }

        do_action('lostpassword_post');

        if ( $this->login_errors ) {
            return false;
        }

        if ( ! $user_data ) {
            $this->login_errors[] = __( 'Invalid username or e-mail.', 'wpuf' );
            return false;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        do_action('retrieve_password', $user_login);

        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if ( ! $allow ) {

            $this->login_errors[] = __( 'Password reset is not allowed for this user', 'wpuf' );
            return false;

        } elseif ( is_wp_error( $allow ) ) {

            $this->login_errors[] = $allow->get_error_message();
            return false;
        }

        $key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

        if ( empty( $key ) ) {

            // Generate something random for a key...
            $key = wp_generate_password( 20, false );

            if ( empty( $wp_hasher ) ) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
                $wp_hasher = new PasswordHash( 8, true );
            }

            $key = time() . ':' . $wp_hasher->HashPassword( $key );

            do_action( 'retrieve_password_key', $user_login, $user_email, $key );

            // Now insert the new hash key into the db
            $wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
        }

        // Send email notification
        $this->email_reset_pass( $user_login, $user_email, $key );

        return true;
    }

    /**
     * Retrieves a user row based on password reset key and login
     *
     * @uses $wpdb WordPress Database object
     *
     * @access public
     * @param string $key Hash to validate sending user's password
     * @param string $login The user login
     * @return object|bool User's database row on success, false for invalid keys
     */
    function check_password_reset_key( $key, $login ) {
        global $wpdb;

        //keeping backward compatible
        if ( strlen( $key ) == 20 ) {
            $key = preg_replace( '/[^a-z0-9]/i', '', $key );
        }

        if ( empty( $key ) || ! is_string( $key ) ) {
            $this->login_errors[] = __( 'Invalid key', 'wpuf' );
            return false;
        }

        if ( empty( $login ) || ! is_string( $login ) ) {
            $this->login_errors[] = __( 'Invalid Login', 'wpuf' );
            return false;
        }

        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

        if ( empty( $user ) ) {
            $this->login_errors[] = __( 'Invalid key', 'wpuf' );
            return false;
        }

        return $user;
    }

    /**
     * Successfull authenticate when enable email verfication in registration
     *
     * @param  object $user
     * @param  string $username
     * @param  string $password
     * @return object
     */
    function successfully_authenticate( $user, $username, $password ) {

        if ( !is_wp_error( $user ) ) {

            if ( $user->ID ) {

                $error = new WP_Error();
                if ( get_user_meta( $user->ID, '_wpuf_user_active', true ) == '0' ) {
                    $error->add( 'acitve_user', sprintf( __( '<strong>Your account is not active.</strong><br>Please check your email for activation link.', 'wpuf' ) ) );
                    return $error;
                }
            }
        }

        return $user;
    }

    /**
     * Check in activation of user registration
     *
     * @since 2.2
     */
    function activation_user_registration() {

        if ( !isset( $_GET['wpuf_registration_activation'] ) && empty( $_GET['wpuf_registration_activation'] ) ) {
            return;
        }

        if ( !isset( $_GET['id'] ) && empty( $_GET['id'] ) ) {
            return;
        }

        $user_id = intval( $_GET['id'] );
        $activation_key = $_GET['wpuf_registration_activation'];

        if ( get_user_meta( $user_id, '_wpuf_activation_key', true ) != $activation_key ) {
            return;
        }

        delete_user_meta( $user_id, '_wpuf_user_active' );
        delete_user_meta( $user_id, '_wpuf_activation_key' );

        // show activation message
        add_filter( 'wp_login_errors', array($this, 'user_activation_message') );
        wp_send_new_user_notifications( $user_id );

        do_action( 'wpuf_user_activated', $user_id );
    }

    /**
     * Shows activation message on success to wp-login.php
     *
     * @since 2.2
     * @return \WP_Error
     */
    function user_activation_message() {
        return new WP_Error( 'user-activated', __( 'Your account has been activated', 'wpuf' ), 'message' );
    }

    function wp_login_page_redirect() {
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
     * Handles resetting the user's password.
     *
     * @access public
     * @param object $user The user
     * @param string $new_pass New password for the user in plaintext
     * @return void
     */
    public function reset_password( $user, $new_pass ) {
        do_action( 'password_reset', $user, $new_pass );

        wp_set_password( $new_pass, $user->ID );

        wp_password_change_notification( $user );
    }

    /**
     * Email reset password link
     *
     * @param string $user_login
     * @param string $user_email
     * @param string $key
     */
    function email_reset_pass( $user_login, $user_email, $key ) {
        $reset_url = add_query_arg( array( 'action' => 'rp', 'key' => $key, 'login' => urlencode( $user_login ) ), $this->get_login_url() );

        $message = __('Someone requested that the password be reset for the following account:', 'wpuf') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'wpuf' ), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'wpuf') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:', 'wpuf') . "\r\n\r\n";
        $message .= ' ' . $reset_url . " \r\n";

        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        if ( is_multisite() ) {
            $blogname = $GLOBALS['current_site']->site_name;
        }

        $title   = sprintf( __('[%s] Password Reset', 'wpuf' ), $blogname );
        $title   = apply_filters( 'retrieve_password_title', $title );

        $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login );

        if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
            wp_die( __('The e-mail could not be sent.', 'wpuf') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', 'wpuf') );
        }
    }

    /**
     * Show erros on the form
     *
     * @return void
     */
    function show_errors() {
        if ( $this->login_errors ) {
            foreach ($this->login_errors as $error) {
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
