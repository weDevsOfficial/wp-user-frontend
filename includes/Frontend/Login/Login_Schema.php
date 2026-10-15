<?php
/**
 * Boot data of the React `[wpuf-login]` screens
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Login;

use WeDevs\Wpuf\Free\Simple_Login;

/**
 * What `templates/login-form.php`, `lost-pass-form.php` and
 * `reset-pass-form.php` print, as the boot data of the React `login` app
 * (src/frontend/login): one method per screen, `view` picks the screen in
 * the app. The React forms post the same fields to the same URLs, so
 * Simple_Login processes them exactly as before; what the templates print
 * through filters and hooks (`login_message`, the notices, the captchas,
 * `wpuf_login_form_bottom`, `lostpassword_form`, `resetpassword_form`, the
 * action links) is captured here and shown in place.
 *
 * @since WPUF_SINCE
 */
class Login_Schema {

    /**
     * The login handler (notices, action links).
     *
     * @var Simple_Login
     */
    private $login;

    /**
     * @since WPUF_SINCE
     *
     * @param Simple_Login $login The login handler
     */
    public function __construct( Simple_Login $login ) {
        $this->login = $login;
    }

    /**
     * The login form (templates/login-form.php).
     *
     * @since WPUF_SINCE
     *
     * @param array $args action_url, redirect_to
     *
     * @return array
     */
    public function login( array $args ) {
        $data = [
            'id'            => 0,
            'view'          => 'login',
            'action_url'    => esc_url_raw( $args['action_url'] ),
            'redirect_to'   => (string) $args['redirect_to'],
            'nonce'         => wp_create_nonce( 'wpuf_login_action' ),
            'lost_password' => esc_url_raw( wp_lostpassword_url() ),
            'message'       => wp_kses_post( apply_filters( 'login_message', '' ) ),
            'notices'       => $this->notices(),
            'captcha'       => $this->captcha(),
            'bottom'        => $this->capture(
                function () {
                    do_action( 'wpuf_login_form_bottom' );
                }
            ),
            'links'         => wp_kses_post( $this->login->get_action_links( [ 'login' => false, 'lostpassword' => false ] ) ),
            'texts'         => [
                'usernameLabel' => __( 'Username or Email', 'wp-user-frontend' ),
                'passwordLabel' => __( 'Password', 'wp-user-frontend' ),
                'rememberMe'    => __( 'Remember Me', 'wp-user-frontend' ),
                'lostPassword'  => __( 'Lost Password', 'wp-user-frontend' ),
                'buttonText'    => __( 'Log In', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filters the boot data of the React login form (texts, links, the
         * captured hook output).
         *
         * @since WPUF_SINCE
         *
         * @param array $data Boot data
         * @param array $args action_url, redirect_to
         */
        return apply_filters( 'wpuf_login_form_data', $data, $args );
    }

    /**
     * The lost password form (templates/lost-pass-form.php, `?action=lostpassword`).
     *
     * @since WPUF_SINCE
     *
     * @param array $args action_url, redirect_to
     *
     * @return array
     */
    public function lost_password( array $args ) {
        $data = [
            'id'          => 0,
            'view'        => 'lostpassword',
            'redirect_to' => Simple_Login::get_posted_value( 'redirect_to' ),
            'nonce'       => wp_create_nonce( 'wpuf_lost_pass' ),
            'notices'     => $this->notices(),
            'hook'        => $this->capture(
                function () {
                    do_action( 'lostpassword_form' );
                }
            ),
            'links'       => wp_kses_post( $this->login->get_action_links( [ 'lostpassword' => false ] ) ),
            'texts'       => [
                'usernameLabel' => __( 'Username or E-mail:', 'wp-user-frontend' ),
                'buttonText'    => __( 'Get New Password', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filters the boot data of the React lost password form.
         *
         * @since WPUF_SINCE
         *
         * @param array $data Boot data
         * @param array $args action_url, redirect_to
         */
        return apply_filters( 'wpuf_lost_password_form_data', $data, $args );
    }

    /**
     * The reset password form (templates/reset-pass-form.php, `?action=rp`).
     *
     * @since WPUF_SINCE
     *
     * @param array $args action_url, redirect_to
     *
     * @return array
     */
    public function reset_password( array $args ) {
        $data = [
            'id'      => 0,
            'view'    => 'resetpass',
            'key'     => Simple_Login::get_posted_value( 'key' ),
            'login'   => Simple_Login::get_posted_value( 'login' ),
            'nonce'   => wp_create_nonce( 'wpuf_reset_pass' ),
            'notices' => $this->notices(),
            'hook'    => $this->capture(
                function () {
                    do_action( 'resetpassword_form' );
                }
            ),
            'texts'   => [
                'pass1Label' => __( 'New password', 'wp-user-frontend' ),
                'pass2Label' => __( 'Confirm new password', 'wp-user-frontend' ),
                'buttonText' => __( 'Reset Password', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filters the boot data of the React reset password form.
         *
         * @since WPUF_SINCE
         *
         * @param array $data Boot data
         * @param array $args action_url, redirect_to
         */
        return apply_filters( 'wpuf_reset_password_form_data', $data, $args );
    }

    /**
     * The reCAPTCHA / Turnstile markup the login template prints.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function captcha() {
        $recaptcha = 'on' === wpuf_get_option( 'login_form_recaptcha', 'wpuf_profile', 'off' );
        $turnstile = 'on' === wpuf_get_option( 'login_form_turnstile', 'wpuf_profile', 'off' );
        $captcha   = '';

        if ( $recaptcha && function_exists( 'recaptcha_get_html' ) ) {
            $captcha .= '<div class="wpuf-fields">' . recaptcha_get_html( wpuf_get_option( 'recaptcha_public', 'wpuf_general' ), true, null, is_ssl() ) . '</div>';
        }

        if ( $turnstile ) {
            $site_key = wpuf_get_option( 'turnstile_site_key', 'wpuf_general', '' );

            wp_enqueue_script( 'wpuf-turnstile' );

            $captcha .= sprintf(
                '<div id="wpuf-turnstile" class="wpuf-turnstile" data-sitekey="%1$s" data-callback="javascriptCallback"></div><script>window.onloadTurnstileCallback = function () { turnstile.render("#wpuf-turnstile", { sitekey: "%2$s" }); };</script>',
                esc_attr( $site_key ),
                esc_js( $site_key )
            );
        }

        return $captcha;
    }

    /**
     * The errors and messages the templates print above the form.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function notices() {
        return $this->capture(
            function () {
                $this->login->show_errors();
                $this->login->show_messages();
            }
        );
    }

    /**
     * What a callable prints, as a string.
     *
     * @since WPUF_SINCE
     *
     * @param callable $print Prints
     *
     * @return string
     */
    private function capture( callable $print ) {
        ob_start();
        $print();

        return (string) ob_get_clean();
    }
}
