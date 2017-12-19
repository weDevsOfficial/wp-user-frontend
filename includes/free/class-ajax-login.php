<?php

/**
 * Ajax Login and Forgot password handler class
 *
 * @since 2.8
 * @author Tareq Hasan <tareq@wedevs.com>
 */

class WPUF_Ajax_Login extends WP_Widget {

    function __construct() {

        parent::__construct(
            'WPUF_Ajax_Login', 
            __('WPUF Ajax Login', 'wpuf'), 
            array( 'description' => __( 'Ajax Login widget for WP User Frontend', 'wpuf' ), ) 
        );

        add_action( 'widgets_init', array( $this, 'wpuf_ajax_login_widget' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_nopriv_wpuf_ajax_login', array( $this, 'wpuf_ajax_login' ) );
        add_action( 'wp_ajax_nopriv_wpuf_ajax_reset_pass', array( $this, 'wpuf_ajax_reset_pass' ) );
        add_action( 'wp_ajax_wpuf_ajax_logout', array( $this, 'wpuf_ajax_logout' ) );
    }

    public function enqueue_scripts() {

        wp_enqueue_script( 'bootstrap', WPUF_ASSET_URI . '/js/bootstrap.js', array( 'jquery' ), '3.3.4', true );
        wp_enqueue_script( 'wp_ajax_login', WPUF_ASSET_URI . '/js/wpuf-ajax-login.js', array( 'jquery' ), false, true );
        
        wp_localize_script( 'wp_ajax_login', 'wpuf_ajax', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ));
    }

    /**
     * Ajax Login function
     *
     * @return void
     */
    public function wpuf_ajax_login(){

        $user_login     = $_POST['wpuf_ajax_user_login'];  
        $user_pass      = $_POST['wpuf_ajax_user_pass'];

        // Check CSRF token
        if ( !check_ajax_referer( 'ajax-login-nonce', 'login-security', false) ) {
            echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">'.__('Session token has expired, please reload the page and try again', 'wpuf').'</div>'));
        } elseif ( empty($user_login) || empty($user_pass) ) {
            echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">'.__('Please fill all form fields', 'wpuf').'</div>'));
        } else {
            $user = wp_signon( array('user_login' => $user_login, 'user_password' => $user_pass), false );

            if ( is_wp_error($user) ) {
                echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">'.$user->get_error_message().'</div>'));
            } else {
                echo json_encode(array('error' => false, 'message'=> '<div class="alert alert-success">'.__('Login successful!', 'wpuf').'</div>'));
            }
        }

        die();
    }

    /**
     * Ajax Logout function
     *
     * @return void
     */
    public function wpuf_ajax_logout() {
        wp_logout();
        echo json_encode(array('error' => false, 'message'=> '<div class="alert alert-success">'.__('Logout successful!', 'wpuf').'</div>') );
        die();
    }

    /**
     * Ajax password reset function
     *
     * @return void
     */
    function wpuf_ajax_reset_pass() {

        $username_or_email = $_POST['wpuf_ajax_user_or_email'];

        // Check CSRF token
        if ( !check_ajax_referer( 'ajax-login-nonce', 'password-security', false) ) {
            echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">'.__('Session token has expired, please reload the page and try again', 'wpuf').'</div>'));
        }       

        // Check if input variables are empty
        elseif ( empty($username_or_email) ) {
            echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">'.__( 'Please fill all form fields', 'wpuf').'</div>') );
        } else {
            $username = is_email($username_or_email) ? sanitize_email($username_or_email) : sanitize_user($username_or_email);

            $user_forgotten = $this->wpuf_ajax_lostPassword_retrieve($username);
            
            if ( is_wp_error($user_forgotten) ) {
                $lostpass_error_messages = $user_forgotten->errors;

                $display_errors = '<div class="alert alert-warning">';
                foreach ( $lostpass_error_messages as $error ) {
                    $display_errors .= '<p>'.$error[0].'</p>';
                }
                $display_errors .= '</div>';
                
                echo json_encode(array('error' => true, 'message' => $display_errors));
            } else {
                echo json_encode(array('error' => false, 'message' => '<p class="alert alert-success">'.__('Password has been reset. Please check your email.', 'wpuf').'</p>'));
            }
        }

        die();
    }

    /**
     * Password retrieve function
     *
     * @return mixed
     */
    private function wpuf_ajax_lostPassword_retrieve( $user_input ) {
        
        global $wpdb, $wp_hasher;

        $errors = new WP_Error();

        if ( empty( $user_input ) ) {
            $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.', 'wpuf'));
        } elseif ( strpos( $user_input, '@' ) ) {
            $user_data = get_user_by( 'email', trim( $user_input ) );
            if ( empty( $user_data ) )
                $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'wpuf'));
        } else {
            $login = trim($user_input);
            $user_data = get_user_by('login', $login);
        }

        /**
         * Fires before errors are returned from a password reset request.
         */
        do_action( 'lostpassword_post', $errors );

        if ( $errors->get_error_code() )
            return $errors;

        if ( !$user_data ) {
            $errors->add( 'invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.', 'wpuf') );
            return $errors;
        }

        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key( $user_data );

        if ( is_wp_error( $key ) ) {
            return $key;
        }

        $message = __('Someone has requested a password reset for the following account:', 'wpuf') . "\r\n\r\n";
        $message .= network_home_url( '/' ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s', 'wpuf'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'wpuf') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:', 'wpuf') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
        
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf( __('[%s] Password Reset', 'wpuf'), $blogname );

        $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

        $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

        if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) )
            $errors->add('mailfailed', __('<strong>ERROR</strong>: The email could not be sent.Possible reason: your host may have disabled the mail() function.', 'wpuf'));

        return true;
    }

    /**
     * Display Ajax Login widget
     *
     * @return void
     */
    public function widget( $args, $instance ) {
        $title      = apply_filters( 'widget_title', $instance['title'] );
        $logintext  = apply_filters( 'widget_text', $instance['logintext'] );
        $lostpwtext = apply_filters( 'widget_text', $instance['lostpwtext'] );
 
        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
 
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            echo get_avatar( $user_id, 24 );
        }

        if ( ! is_user_logged_in() ) {
            $linktext =  __('Log in', 'wpuf');
        }
        else {
            $linktext =  __('Log out', 'wpuf'); 
        }
        ?>
        <div class='login-container'>
            <a href='#wpuf-ajax-login'><?php echo $linktext ?></a>
            <div class="modal dropdown wpuf-ajax-user-modal" id="wpuf-ajax-user-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" data-active-tab="">
                <div class="modal-content">
                <?php 
                    if( ! is_user_logged_in() ){ // only show the registration/login form to non-logged-in members ?>   
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <!-- Login form -->
                            <div class="wpuf-ajax-login">
                                <?php echo get_option( 'WPUF_Ajax_Login' ); ?>

                                <p><?php echo $logintext; ?></p>
                         
                                <form id="wpuf-ajax-login-form" action="<?php echo home_url( '/' ); ?>" method="POST">

                                    <div class="form-field">
                                        <span class="screen-reader-text"><?php _e('Username', 'wpuf') ?></span>
                                        <input class="form-control input-lg required" name="wpuf_ajax_user_login" type="text" placeholder="<?php _e('Username', 'wpuf') ?>" />
                                    </div>
                                    <div class="form-field">
                                        <span class="screen-reader-text"><?php _e('Password', 'wpuf')?></span>
                                        <input class="form-control input-lg required" name="wpuf_ajax_user_pass" id="wpuf_ajax_user_pass" type="password"/ placeholder="<?php _e('Password', 'wpuf')?>">
                                    </div>
                                    <div class="form-field">
                                        <input type="hidden" name="action" value="wpuf_ajax_login"/>
                                        <button class="btn btn-theme btn-lg" data-loading-text="<?php _e('Loading...', 'wpuf') ?>" type="submit"><?php _e('Login', 'wpuf'); ?></button> 
                                    </div>
                                    <?php wp_nonce_field( 'ajax-login-nonce', 'login-security' ); ?>
                                </form>
                                <div class="wpuf-ajax-errors"></div>
                            </div>

                            <!-- Lost Password form -->
                            <div class="wpuf-ajax-reset-password">
                         
                                <p><?php echo $lostpwtext ?></p>
                         
                                <form id="wpuf_ajax_reset_pass_form" action="<?php echo home_url( '/' ); ?>" method="POST">
                                    <div class="form-field">
                                        <span class="screen-reader-text"><?php _e('Username or E-mail', 'wpuf') ?></span>
                                        <input class="form-control input-lg required" name="wpuf_ajax_user_or_email" id="wpuf_ajax_user_or_email" type="text" placeholder="<?php _e('Username or E-mail', 'wpuf') ?>" />
                                    </div>
                                    <div class="form-field">
                                        <input type="hidden" name="action" value="wpuf_ajax_reset_pass"/>
                                        <button class="btn btn-theme btn-lg" data-loading-text="<?php _e('Loading...', 'wpuf') ?>" type="submit"><?php _e('Get new password', 'wpuf'); ?></button>
                                    </div>
                                    <?php wp_nonce_field( 'ajax-login-nonce', 'password-security' ); ?>
                                </form>
                                <div class="wpuf-ajax-errors"></div>
                            </div>

                            <div class="wpuf-ajax-loading">
                                <p><i class="fa fa-refresh fa-spin"></i><br><?php _e('Loading...', 'wpuf') ?></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                                <span class="wpuf-ajax-register-footer"><?php _e('Lost Password?', 'wpuf'); ?> <a class="alignright" href="#wpuf-ajax-reset-password"><?php _e('Reset Password', 'wp-ajax-login') ?></a></span>
                                <span class="wpuf-ajax-login-footer"><?php _e('Already have an account?', 'wpuf'); ?> <a href="#wpuf-ajax-login"><?php _e('Login', 'wpuf'); ?></a></span>
                        </div>
                    <?php } else { ?>
                        <div class="modal-body">
                            <div class="wpuf-ajax-logout">                         
                                <div class="alert alert-info"><?php $current_user = wp_get_current_user(); printf( __( 'You have already logged in as %1$s. <a href="#logout">Logout?</a>', 'wpuf' ), $current_user->user_login );?></div>
                                <div class="wpuf-ajax-errors"></div>
                            </div>
                        </div>
                    <?php } ?>      
                    </div>
                </div>
            </div>
        </div>

        <?php
        echo $args['after_widget'];
    }
         
    /**
     * Ajax Login widget backend
     *
     * @return void
     */ 
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        } else {
            $title = __( 'WPUF Login', 'wpuf' );
        }
        if ( isset( $instance[ 'logintext' ] ) ) {
            $logintext = $instance[ 'logintext' ];
        } else {
            $logintext = __( 'Enter your username and password to login.', 'wpuf' );
        }
        if ( isset( $instance['lostpwtext'] ) ) {
            $lostpwtext = $instance['lostpwtext'];
        } else {
            $lostpwtext = __( 'Enter the username or e-mail you used in your profile. A password reset link will be sent to you by email.', 'wpuf' );
        }
        // Ajax Login Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wpuf' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'logintext' ); ?>"><?php _e( 'Login Text:', 'wpuf' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'logintext' ); ?>" name="<?php echo $this->get_field_name( 'logintext' ); ?>" type="text" value="<?php echo esc_attr( $logintext ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'lostpwtext' ); ?>"><?php _e( 'Lost password text:', 'wpuf' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'lostpwtext' ); ?>" name="<?php echo $this->get_field_name( 'lostpwtext' ); ?>" type="text" value="<?php echo esc_attr( $lostpwtext ); ?>" />
        </p>
        <?php 
    }

    /**
     * Updating widget replacing old instances with new
     *
     * @return $instance
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['logintext'] = ( ! empty( $new_instance['logintext'] ) ) ? strip_tags( $new_instance['logintext'] ) : '';
        $instance['lostpwtext'] = ( ! empty( $new_instance['lostpwtext'] ) ) ? strip_tags( $new_instance['lostpwtext'] ) : '';
        return $instance;
    }

    /**
     * Register Ajax Login widget
     *
     */
    function wpuf_ajax_login_widget() {
        register_widget( 'WPUF_Ajax_Login' );
    }

}
