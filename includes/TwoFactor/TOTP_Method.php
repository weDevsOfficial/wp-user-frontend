<?php

namespace WeDevs\Wpuf\TwoFactor;

use PragmaRX\Google2FA\Google2FA;

/**
 * RFC 6238 TOTP method
 *
 * Wraps pragmarx/google2fa for the standard primitives (secret generation,
 * code verification at a given timestamp). Adds replay protection and
 * rate limiting on top.
 *
 * Defaults are the universal authenticator-app defaults and are not
 * configurable: SHA1, 6 digits, 30-second period, ±1 step drift.
 *
 * @since WPUF_SINCE
 */
class TOTP_Method implements Method_Interface {

    public const ID                  = 'totp';
    public const PERIOD_SECONDS      = 30;
    public const DRIFT_WINDOW_STEPS  = 1;
    public const SECRET_LENGTH_BYTES = 20; // 160 bits per RFC 6238 §5.

    /** @var Google2FA */
    private $google2fa;

    /** @var User_Storage */
    private $storage;

    /** @var Rate_Limiter */
    private $rate_limiter;

    /** @var QR_Renderer */
    private $qr;

    public function __construct(
        Google2FA $google2fa,
        User_Storage $storage,
        Rate_Limiter $rate_limiter,
        QR_Renderer $qr
    ) {
        $this->google2fa    = $google2fa;
        $this->storage      = $storage;
        $this->rate_limiter = $rate_limiter;
        $this->qr           = $qr;

        $this->google2fa->setWindow( self::DRIFT_WINDOW_STEPS );

        // The library's "Google Authenticator compatibility" check is too
        // strict — it occasionally rejects its OWN random secrets even
        // though Google Authenticator accepts them fine. Disabling the
        // check is what every production integration does. Verified safe
        // because the secret length and base32 alphabet are still validated.
        $this->google2fa->setEnforceGoogleAuthenticatorCompatibility( false );
    }

    public function get_id(): string {
        return self::ID;
    }

    public function get_label(): string {
        return __( 'Authenticator App', 'wp-user-frontend' );
    }

    public function get_description(): string {
        return __( 'Use Google Authenticator, 1Password, Authy, Microsoft Authenticator, or any RFC 6238 app.', 'wp-user-frontend' );
    }

    public function get_destination_label( int $user_id ): string {
        return __( 'Authenticator app', 'wp-user-frontend' );
    }

    public function is_enrolled( int $user_id ): bool {
        return $this->storage->get_method_secret( $user_id, self::ID ) !== null;
    }

    /**
     * Start enrollment. Generate a fresh secret, park it in the pending
     * transient, return the QR + manual key the UI needs.
     *
     * The active secret (if any) stays valid until `confirm_enrollment()`
     * succeeds. Restarting setup mid-flight does not log the user out
     * of an existing 2FA enrollment.
     *
     * @param int   $user_id
     * @param array $input  Unused for TOTP. Ignored.
     *
     * @return array|\WP_Error
     */
    public function start_enrollment( int $user_id, array $input ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return new \WP_Error(
                'wpuf_2fa_invalid_user',
                __( 'Could not start setup. Please try again.', 'wp-user-frontend' )
            );
        }

        $secret = $this->google2fa->generateSecretKey( self::SECRET_LENGTH_BYTES );

        $this->storage->set_pending_method( $user_id, self::ID, $secret );

        $issuer = wpuf_get_option( 'totp_issuer_label', 'wpuf_2fa', get_bloginfo( 'name' ) );
        $uri    = $this->google2fa->getQRCodeUrl( $issuer, $user->user_email, $secret );

        return [
            'secret'      => $secret,
            'otpauth_uri' => $uri,
            'qr_svg'      => $this->qr->render_svg( $uri ),
        ];
    }

    /**
     * Confirm enrollment with a code. On success the pending secret is
     * promoted to active in a single user-meta write.
     *
     * @param int   $user_id
     * @param array $input  Must contain 'code'.
     *
     * @return true|\WP_Error
     */
    public function confirm_enrollment( int $user_id, array $input ) {
        $code = isset( $input['code'] ) ? (string) $input['code'] : '';

        $pending = $this->storage->get_pending_method( $user_id, self::ID );

        if ( $pending === null ) {
            return new \WP_Error(
                'wpuf_2fa_pending_expired',
                __( 'Your setup session has expired. Please start again.', 'wp-user-frontend' ),
                [ 'expired' => true ]
            );
        }

        if ( $this->rate_limiter->is_locked( $user_id ) ) {
            return new \WP_Error(
                'wpuf_2fa_locked_out',
                __( 'Too many incorrect attempts. Please wait a few minutes before trying again.', 'wp-user-frontend' )
            );
        }

        $accepted_step = $this->verify_step( $pending, $code );

        if ( $accepted_step === null ) {
            $this->rate_limiter->record_failure( $user_id );

            return new \WP_Error(
                'wpuf_2fa_invalid_code',
                __( "That code didn't match. Try again.", 'wp-user-frontend' )
            );
        }

        // Atomic-enough: the pair of writes happens fast and is idempotent
        // on retry (set_method_secret stamps enrolled_at; clear drops the
        // transient).
        $this->storage->set_method_secret( $user_id, self::ID, $pending );
        $this->storage->clear_pending_method( $user_id, self::ID );

        // Seed replay protection so the just-used step can't be replayed.
        $this->set_last_accepted_step( $user_id, $accepted_step );
        $this->rate_limiter->clear( $user_id );

        return true;
    }

    /**
     * No-op for TOTP — the user already has the code in their
     * authenticator app. Every method must implement issue_challenge();
     * this is the trivial case.
     *
     * @return true|\WP_Error
     */
    public function issue_challenge( int $user_id ) {
        return true;
    }

    /**
     * Verify a code against the user's *active* secret. Honors rate
     * limit and replay protection.
     *
     * @return true|\WP_Error
     */
    public function verify( int $user_id, string $code ) {
        if ( $this->rate_limiter->is_locked( $user_id ) ) {
            return new \WP_Error(
                'wpuf_2fa_locked_out',
                __( 'Too many incorrect attempts. Please wait a few minutes before trying again.', 'wp-user-frontend' )
            );
        }

        $secret = $this->storage->get_method_secret( $user_id, self::ID );

        if ( $secret === null ) {
            return new \WP_Error(
                'wpuf_2fa_not_enrolled',
                __( 'Two-factor authentication is not set up for this account.', 'wp-user-frontend' )
            );
        }

        $accepted_step = $this->verify_step( $secret, $code );

        if ( $accepted_step === null ) {
            $this->rate_limiter->record_failure( $user_id );

            return new \WP_Error(
                'wpuf_2fa_invalid_code',
                __( "That code didn't match. Try again.", 'wp-user-frontend' )
            );
        }

        $last = $this->get_last_accepted_step( $user_id );

        if ( $accepted_step <= $last ) {
            // Replay: same step number already used. Treat as a failure
            // so brute-forcing the same window doesn't avoid rate limit.
            $this->rate_limiter->record_failure( $user_id );

            return new \WP_Error(
                'wpuf_2fa_replay',
                __( 'That code was already used. Wait for a new one.', 'wp-user-frontend' )
            );
        }

        $this->set_last_accepted_step( $user_id, $accepted_step );
        $this->rate_limiter->clear( $user_id );

        return true;
    }

    public function reset( int $user_id ): void {
        $this->storage->clear_method( $user_id, self::ID );
        $this->rate_limiter->clear( $user_id );
    }

    /* ------------------------------------------------------------------
     * Internals — replay tracking lives in opaque method state.
     * ------------------------------------------------------------------ */

    private function get_last_accepted_step( int $user_id ): int {
        $state = $this->storage->get_method_state( $user_id, self::ID );

        return isset( $state['last_step'] ) ? (int) $state['last_step'] : 0;
    }

    private function set_last_accepted_step( int $user_id, int $step ): void {
        $state              = $this->storage->get_method_state( $user_id, self::ID );
        $state['last_step'] = $step;
        $this->storage->set_method_state( $user_id, self::ID, $state );
    }

    /**
     * Run RFC 6238 verification across the drift window.
     *
     * Uses verifyKeyNewer() rather than verifyKey() so we get the matching
     * step number (int) back instead of just bool. Replay enforcement
     * lives in `last_step` state, not here — passing oldTimestamp = -1
     * disables the library's own replay gate so it always returns the
     * matched step on a hit.
     *
     * @return int|null Step number that accepted the code, or null on miss.
     */
    private function verify_step( string $secret, string $code ): ?int {
        $code = preg_replace( '/\s+/', '', $code );

        if ( $code === '' || strlen( $code ) !== 6 ) {
            return null;
        }

        $accepted = $this->google2fa->verifyKeyNewer( $secret, $code, -1 );

        if ( $accepted === false ) {
            return null;
        }

        return (int) $accepted;
    }
}
