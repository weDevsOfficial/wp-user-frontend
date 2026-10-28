<?php

namespace WeDevs\Wpuf;

use Invisible_Recaptcha;
use WeDevs\Wpuf\Traits\FieldableTrait;
use WPUF_ReCaptcha;

/**
 * The classic form helpers still in use: the captcha checks the login form
 * and the field trait run, `send_error()` and `guess_username()`. Pro's
 * Profile_Form extends it. The classic renderer methods were removed (the
 * React forms render through Frontend\Forms\Form_Schema).
 */
class Render_Form {
    use FieldableTrait;

    public static $meta_key = 'wpuf_form';

    public static $separator = ' | ';

    public static $config_id = '_wpuf_form_id';

    private static $_instance;

    public $multiform_start = 0;

    public static function init() {
        if ( ! self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Send json error message
     *
     * @param string $error
     */
    public function send_error( $error ) {
        echo wp_json_encode(
            [
				'success' => false,
				'error'   => $error,
			]
        );

        die();
    }

    /**
     * Really simple captcha validation
     *
     * @return void
     */
    public function validate_rs_captcha() {
        $nonce = isset( $_REQUEST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf-login-nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
            return;
        }

        $rs_captcha_input = isset( $_POST['rs_captcha'] ) ? sanitize_text_field( wp_unslash( $_POST['rs_captcha'] ) ) : '';
        $rs_captcha_file  = isset( $_POST['rs_captcha_val'] ) ? sanitize_text_field( wp_unslash( $_POST['rs_captcha_val'] ) ) : '';

        if ( class_exists( 'ReallySimpleCaptcha' ) ) {
            $captcha_instance = new ReallySimpleCaptcha();

            if ( ! $captcha_instance->check( $rs_captcha_file, $rs_captcha_input ) ) {
                $this->send_error( __( 'Really Simple Captcha validation failed', 'wp-user-frontend' ) );
            } else {
                // validation success, remove the files
                $captcha_instance->remove( $rs_captcha_file );
            }
        }
    }

    /**
     * ReCaptcha validation
     *
     * @return void
     */
    public function validate_re_captcha( $no_captcha = '', $invisible = '' ) {
        $nonce = isset( $_REQUEST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['wpuf-login-nonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
            return;
        }
        // need to check if invisible reCaptcha need library or we can do it here.
        // ref: https://shareurcodes.com/blog/google%20invisible%20recaptcha%20integration%20with%20php
        $site_key             = wpuf_get_option( 'recaptcha_public', 'wpuf_general' );
        $private_key          = wpuf_get_option( 'recaptcha_private', 'wpuf_general' );
        $rremote_addr         = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $g_recaptcha_response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

        if ( $no_captcha == 1 && 0 == $invisible ) {
            if ( ! class_exists( 'WPUF_ReCaptcha' ) ) {
                require_once WPUF_ROOT . '/Lib/recaptchalib_noCaptcha.php';
            }

            $response  = null;
            $re_captcha = new WPUF_ReCaptcha( $private_key );

            $resp = $re_captcha->verifyResponse(
                $rremote_addr,
                $g_recaptcha_response
            );

            if ( ! $resp->success ) {
                $this->send_error( __( 'noCaptcha reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        } elseif ( $no_captcha == 0 && 0 == $invisible ) {
            $recap_challenge = isset( $_POST['recaptcha_challenge_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_challenge_field'] ) ) : '';
            $recap_response  = isset( $_POST['recaptcha_response_field'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response_field'] ) ) : '';

            $resp = recaptcha_check_answer( $private_key, $rremote_addr, $recap_challenge, $recap_response );

            if ( ! $resp->is_valid ) {
                $this->send_error( __( 'reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        } elseif ( $no_captcha == 0 && 1 == $invisible ) {
            $response  = null;
            $recaptcha = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
            $object    = new Invisible_Recaptcha( $site_key, $private_key );

            $response = $object->verifyResponse( $recaptcha );

            if ( isset( $response['success'] ) && ! $response['success'] ) {
                $this->send_error( __( 'Invisible reCAPTCHA validation failed', 'wp-user-frontend' ) );
            }
        }
    }

    /**
     * Guess a suitable username for registration based on email address
     *
     * @param string $email email address
     *
     * @return string username
     */
    public function guess_username( $email ) {
        // username from email address
        $username = sanitize_user( substr( $email, 0, strpos( $email, '@' ) ) );

        if ( ! username_exists( $username ) ) {
            return $username;
        }

        // try to add some random number in username
        // and may be we got our username
        $username .= wp_rand( 1, 199 );

        if ( ! username_exists( $username ) ) {
            return $username;
        }
    }
}
