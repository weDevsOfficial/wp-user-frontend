<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Generic AJAX surface for enrollment, challenge issuance, and self-disable
 *
 * Four endpoints, all gated on `is_user_logged_in()` and a single nonce
 * scoped to `wpuf_2fa_enrollment`. Every endpoint takes a `method_id`
 * and dispatches to the matching `Method_Interface` instance — no
 * method-specific actions.
 *
 *   - `wpuf_2fa_method_start`     → Method_Interface::start_enrollment()
 *   - `wpuf_2fa_method_confirm`   → Method_Interface::confirm_enrollment()
 *   - `wpuf_2fa_method_issue`     → Method_Interface::issue_challenge()
 *                                   (used by methods that need a fresh
 *                                   code before self-disable, e.g. Email OTP)
 *   - `wpuf_2fa_method_disable`   → Method_Interface::reset() after
 *                                   verifying password + current code
 *
 * @since WPUF_SINCE
 */
class Enrollment_Controller {

    public const NONCE_ACTION = 'wpuf_2fa_enrollment';

    /** @var Method_Registry */
    private $registry;

    /** @var User_Storage */
    private $storage;

    public function __construct( Method_Registry $registry, User_Storage $storage ) {
        $this->registry = $registry;
        $this->storage  = $storage;

        add_action( 'wp_ajax_wpuf_2fa_method_start', [ $this, 'ajax_start' ] );
        add_action( 'wp_ajax_wpuf_2fa_method_confirm', [ $this, 'ajax_confirm' ] );
        add_action( 'wp_ajax_wpuf_2fa_method_issue', [ $this, 'ajax_issue' ] );
        add_action( 'wp_ajax_wpuf_2fa_method_disable', [ $this, 'ajax_disable' ] );
    }

    /**
     * Begin enrollment for a method. Returns whatever props the method's
     * UI needs to render the next step (QR + secret for TOTP, masked
     * destination for Email OTP, etc.).
     */
    public function ajax_start() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        $method = $this->resolve_active_method();
        $input  = $this->collect_input();

        $result = $method->start_enrollment( $user_id, $input );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $this->format_error( $result ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Complete enrollment. On success the pending state is promoted to
     * active and `wpuf_2fa_method_enrolled` fires.
     */
    public function ajax_confirm() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        $method = $this->resolve_active_method();
        $input  = $this->collect_input();

        $result = $method->confirm_enrollment( $user_id, $input );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $this->format_error( $result ) );
        }

        $this->storage->append_audit( $user_id, $method->get_id(), 'enrolled', $user_id );

        do_action( 'wpuf_2fa_method_enrolled', $user_id, $method->get_id() );

        wp_send_json_success(
            [
                'message' => __( 'Two-factor authentication is now active on your account.', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Issue a challenge code for a method. Used by issued-code methods
     * (Email OTP, SMS OTP) when the user is about to self-disable —
     * they need a fresh code before they can submit the disable form.
     * TOTP doesn't need this endpoint, but calling it is harmless
     * (issue_challenge() is a no-op).
     *
     * Only allowed when the user is enrolled in the method.
     */
    public function ajax_issue() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        $method = $this->resolve_active_method();

        if ( ! $method->is_enrolled( $user_id ) ) {
            wp_send_json_error(
                [ 'message' => __( 'You are not enrolled in this method.', 'wp-user-frontend' ) ],
                400
            );
        }

        $issued = $method->issue_challenge( $user_id );

        if ( is_wp_error( $issued ) ) {
            $this->storage->append_audit(
                $user_id,
                $method->get_id(),
                'challenge_issue_failed',
                $user_id,
                [ 'error' => $issued->get_error_message() ]
            );

            wp_send_json_error(
                [ 'message' => __( "Couldn't send your code, try again.", 'wp-user-frontend' ) ]
            );
        }

        do_action( 'wpuf_2fa_issue_challenge', $user_id, $method->get_id() );

        wp_send_json_success(
            [
                'message'           => __( 'Code sent.', 'wp-user-frontend' ),
                'destination_label' => $method->get_destination_label( $user_id ),
            ]
        );
    }

    /**
     * Self-disable. Requires current password + a current valid code
     * for the method. Verifies both before resetting; either failure
     * leaves the enrollment intact.
     */
    public function ajax_disable() {
        $user_id = $this->require_logged_in_user();
        $this->verify_nonce();

        $method = $this->resolve_active_method();

        if ( ! $method->is_enrolled( $user_id ) ) {
            wp_send_json_error(
                [ 'message' => __( 'You are not enrolled in this method.', 'wp-user-frontend' ) ],
                400
            );
        }

        // Passwords must not be sanitized — they're verified against a
        // hash and can contain any byte.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

        $user = get_userdata( $user_id );

        if ( ! $password || ! wp_check_password( $password, $user->user_pass, $user_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Your password is incorrect.', 'wp-user-frontend' ) ] );
        }

        $verified = $method->verify( $user_id, $code );

        if ( is_wp_error( $verified ) ) {
            wp_send_json_error( $this->format_error( $verified ) );
        }

        $method->reset( $user_id );

        $this->storage->append_audit( $user_id, $method->get_id(), 'self_disable', $user_id );

        do_action( 'wpuf_2fa_method_disabled', $user_id, $method->get_id(), $user_id, 'self_disable' );

        wp_send_json_success(
            [
                'message' => __( 'Two-factor authentication has been disabled.', 'wp-user-frontend' ),
            ]
        );
    }

    /* ------------------------------------------------------------------
     * Internals
     * ------------------------------------------------------------------ */

    private function require_logged_in_user(): int {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'wp-user-frontend' ) ], 403 );
        }

        return get_current_user_id();
    }

    private function verify_nonce(): void {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            wp_send_json_error(
                [ 'message' => __( 'Security check failed. Please refresh the page and try again.', 'wp-user-frontend' ) ],
                403
            );
        }
    }

    /**
     * Look up the method by id from the request, ensure it's active in
     * Global Settings, and return it. Aborts the request on any failure.
     */
    private function resolve_active_method(): Method_Interface {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $method_id = isset( $_POST['method_id'] ) ? sanitize_key( wp_unslash( $_POST['method_id'] ) ) : '';

        if ( ! $method_id ) {
            wp_send_json_error( [ 'message' => __( 'Method not specified.', 'wp-user-frontend' ) ], 400 );
        }

        $active = $this->registry->active();

        if ( ! isset( $active[ $method_id ] ) ) {
            wp_send_json_error( [ 'message' => __( 'That method is not enabled.', 'wp-user-frontend' ) ], 400 );
        }

        return $active[ $method_id ];
    }

    /**
     * Pluck non-reserved POST keys to pass to the method as input. The
     * controller doesn't know what shape each method needs — it forwards
     * whatever the form submitted, sanitized as text fields.
     *
     * Reserved keys (`action`, `_wpnonce`, `method_id`) are stripped.
     * Methods that need raw input (e.g. passwords) read $_POST directly
     * inside their own handlers — we don't have one of those today.
     */
    private function collect_input(): array {
        $reserved = [ 'action', '_wpnonce', 'method_id' ];
        $input    = [];

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        foreach ( $_POST as $key => $value ) {
            if ( in_array( $key, $reserved, true ) ) {
                continue;
            }

            if ( is_scalar( $value ) ) {
                $input[ $key ] = sanitize_text_field( wp_unslash( (string) $value ) );
            }
        }

        return $input;
    }

    /**
     * Format a WP_Error for wp_send_json_error(). Surfaces the message
     * and any UI-hint data the method attached (e.g. ['expired' => true]).
     */
    private function format_error( \WP_Error $error ): array {
        $payload = [ 'message' => $error->get_error_message() ];

        $data = $error->get_error_data();

        if ( is_array( $data ) ) {
            $payload = array_merge( $payload, $data );
        }

        return $payload;
    }
}
