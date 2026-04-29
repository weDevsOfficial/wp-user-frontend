<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Handles user-initiated enrollment, regeneration, and self-disable
 *
 * Three AJAX endpoints, all gated on `is_user_logged_in()` and a nonce
 * scoped to the action.
 *
 * @since WPUF_SINCE
 */
class Enrollment_Controller {

    public const NONCE_ACTION = 'wpuf_2fa_enrollment';

    /** @var TOTP_Method */
    private $totp;

    /** @var User_Storage */
    private $storage;

    /** @var QR_Renderer */
    private $qr;

    public function __construct( TOTP_Method $totp, User_Storage $storage, QR_Renderer $qr ) {
        $this->totp    = $totp;
        $this->storage = $storage;
        $this->qr      = $qr;

        add_action( 'wp_ajax_wpuf_2fa_totp_start', [ $this, 'ajax_start' ] );
        add_action( 'wp_ajax_wpuf_2fa_totp_confirm', [ $this, 'ajax_confirm' ] );
        add_action( 'wp_ajax_wpuf_2fa_totp_disable', [ $this, 'ajax_disable' ] );
    }

    /**
     * Start (or restart) enrollment. Generates a fresh secret, parks it in
     * the pending transient, returns secret + QR for the UI.
     */
    public function ajax_start() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        if ( ! $this->is_method_active() ) {
            wp_send_json_error( [ 'message' => __( 'Authenticator app method is not enabled.', 'wp-user-frontend' ) ] );
        }

        $secret = $this->totp->generate_secret();
        $this->storage->set_pending_totp( $user_id, $secret );

        $user = get_userdata( $user_id );
        $uri  = $this->totp->build_otpauth_uri( $secret, $user->user_email );

        wp_send_json_success(
            [
                'secret'      => $secret,
                'otpauth_uri' => $uri,
                'qr_svg'      => $this->qr->render_svg( $uri ),
            ]
        );
    }

    /**
     * Confirm enrollment with a code. On success, the pending secret
     * becomes active in a single user-meta write.
     */
    public function ajax_confirm() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        // Nonce verified above in verify_nonce(); phpcs cannot trace it.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

        $pending = $this->storage->get_pending_totp( $user_id );

        if ( $pending === null ) {
            wp_send_json_error(
                [
                    'message' => __( 'Your setup session has expired. Please start again.', 'wp-user-frontend' ),
                    'expired' => true,
                ]
            );
        }

        if ( ! $this->totp->verify_against_secret( $user_id, $pending, $code ) ) {
            wp_send_json_error( [ 'message' => __( "That code didn't match. Try again.", 'wp-user-frontend' ) ] );
        }

        // Atomic swap: pending becomes active, transient cleared.
        $this->storage->set_totp_secret( $user_id, $pending );
        $this->storage->clear_pending_totp( $user_id );
        $this->storage->append_audit( $user_id, 'enrolled', $user_id );

        do_action( 'wpuf_2fa_totp_enrolled', $user_id );

        wp_send_json_success(
            [
				'message' => __( 'Two-factor authentication is now active on your account.', 'wp-user-frontend' ),
			]
        );
    }

    /**
     * Self-disable. Requires current password + current valid TOTP code.
     */
    public function ajax_disable() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        // Passwords must not be sanitized — they're verified against a
        // hash and can contain any byte. Nonce verified above.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

        $user = get_userdata( $user_id );

        if ( ! $password || ! wp_check_password( $password, $user->user_pass, $user_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Your password is incorrect.', 'wp-user-frontend' ) ] );
        }

        if ( ! $this->totp->verify( $user_id, $code ) ) {
            wp_send_json_error( [ 'message' => __( "That code didn't match. Try again.", 'wp-user-frontend' ) ] );
        }

        $this->totp->reset( $user_id );
        $this->storage->append_audit( $user_id, 'self_disable', $user_id );

        do_action( 'wpuf_2fa_totp_disabled', $user_id, 'self' );

        wp_send_json_success(
            [
				'message' => __( 'Two-factor authentication has been disabled.', 'wp-user-frontend' ),
			]
        );
    }

    private function require_logged_in_user() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'wp-user-frontend' ) ], 403 );
        }

        return get_current_user_id();
    }

    private function verify_nonce() {
        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed. Please refresh the page and try again.', 'wp-user-frontend' ) ], 403 );
        }
    }

    private function is_method_active() {
        if ( wpuf_get_option( 'enable_2fa', 'wpuf_2fa', 'off' ) !== 'on' ) {
            return false;
        }

        $active = wpuf_get_option( 'active_2fa_methods', 'wpuf_2fa', [] );

        return is_array( $active ) && in_array( TOTP_Method::ID, $active, true );
    }
}
