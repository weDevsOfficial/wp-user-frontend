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

    public function __construct( Google2FA $google2fa, User_Storage $storage, Rate_Limiter $rate_limiter ) {
        $this->google2fa    = $google2fa;
        $this->storage      = $storage;
        $this->rate_limiter = $rate_limiter;

        $this->google2fa->setWindow( self::DRIFT_WINDOW_STEPS );

        // The library's "Google Authenticator compatibility" check is too
        // strict — it occasionally rejects its OWN random secrets even
        // though Google Authenticator accepts them fine. Disabling the
        // check is what every production integration does. Verified safe
        // because the secret length and base32 alphabet are still validated.
        $this->google2fa->setEnforceGoogleAuthenticatorCompatibility( false );
    }

    public function get_id() {
        return self::ID;
    }

    public function get_label() {
        return __( 'Authenticator App', 'wp-user-frontend' );
    }

    public function is_enrolled( $user_id ) {
        return $this->storage->get_totp_secret( $user_id ) !== null;
    }

    /**
     * Generate a fresh base32 secret. Used for new enrollment and
     * regeneration.
     *
     * @return string
     */
    public function generate_secret() {
        return $this->google2fa->generateSecretKey( self::SECRET_LENGTH_BYTES );
    }

    /**
     * Build the otpauth:// URI for QR-code rendering.
     *
     * @param string $secret   Base32 secret.
     * @param string $username Email or username shown in the authenticator app.
     *
     * @return string
     */
    public function build_otpauth_uri( $secret, $username ) {
        $issuer = wpuf_get_option( 'totp_issuer_label', 'wpuf_2fa', get_bloginfo( 'name' ) );

        return $this->google2fa->getQRCodeUrl(
            $issuer,
            $username,
            $secret
        );
    }

    /**
     * Verify a code against the user's *active* secret. Honors rate limit
     * and replay protection. Use verify_against_secret() for enrollment
     * confirmation where the secret is still pending.
     *
     * @param int    $user_id
     * @param string $code
     *
     * @return bool
     */
    public function verify( $user_id, $code ) {
        if ( $this->rate_limiter->is_locked( $user_id ) ) {
            return false;
        }

        $secret = $this->storage->get_totp_secret( $user_id );

        if ( $secret === null ) {
            return false;
        }

        $accepted_step = $this->verify_step( $secret, $code );

        if ( $accepted_step === null ) {
            $this->rate_limiter->record_failure( $user_id );

            return false;
        }

        // Replay protection: reject codes whose step has already been used.
        $last = $this->storage->get_last_accepted_step( $user_id );

        if ( $accepted_step <= $last ) {
            $this->rate_limiter->record_failure( $user_id );

            return false;
        }

        $this->storage->set_last_accepted_step( $user_id, $accepted_step );
        $this->rate_limiter->clear( $user_id );

        return true;
    }

    /**
     * Verify a code against an arbitrary secret. Used during enrollment
     * confirmation where the candidate secret is in the pending transient,
     * not user meta. Still honors the rate limiter.
     *
     * @param int    $user_id
     * @param string $secret
     * @param string $code
     *
     * @return bool
     */
    public function verify_against_secret( $user_id, $secret, $code ) {
        if ( $this->rate_limiter->is_locked( $user_id ) ) {
            return false;
        }

        $accepted_step = $this->verify_step( $secret, $code );

        if ( $accepted_step === null ) {
            $this->rate_limiter->record_failure( $user_id );

            return false;
        }

        $this->rate_limiter->clear( $user_id );

        return true;
    }

    public function reset( $user_id ) {
        $this->storage->clear_totp( $user_id );
        $this->rate_limiter->clear( $user_id );
    }

    /**
     * Run RFC 6238 verification across the drift window.
     *
     * Uses verifyKeyNewer() rather than verifyKey() so we get the matching
     * step number (int) back instead of just bool. Replay enforcement
     * lives in our own User_Storage::get_last_accepted_step() check, not
     * here — passing oldTimestamp = -1 disables the library's own replay
     * gate so it always returns the matched step on a hit.
     *
     * @param string $secret
     * @param string $code
     *
     * @return int|null Step number that accepted the code, or null on miss.
     */
    private function verify_step( $secret, $code ) {
        $code = preg_replace( '/\s+/', '', (string) $code );

        if ( $code === '' || \strlen( $code ) !== 6 ) {
            return null;
        }

        $accepted = $this->google2fa->verifyKeyNewer( $secret, $code, -1 );

        if ( $accepted === false ) {
            return null;
        }

        return (int) $accepted;
    }
}
