<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Read/write helpers for per-user 2FA state
 *
 * Centralizes user-meta keys so the rest of the code never deals with raw
 * meta strings. Audit trail lives here too — capped at 10 entries.
 *
 * @since WPUF_SINCE
 */
class User_Storage {

    const META_TOTP_SECRET    = '_wpuf_2fa_totp_secret';
    const META_TOTP_ENROLLED  = '_wpuf_2fa_totp_enrolled_at';
    const META_TOTP_LAST_STEP = '_wpuf_2fa_totp_last_step';
    const META_AUDIT_LOG      = '_wpuf_2fa_audit_log';
    const TRANSIENT_PENDING   = 'wpuf_2fa_pending_totp_';

    const PENDING_TTL  = 30 * MINUTE_IN_SECONDS;
    const AUDIT_LIMIT  = 10;

    /** @var Crypto */
    private $crypto;

    public function __construct( Crypto $crypto ) {
        $this->crypto = $crypto;
    }

    public function set_totp_secret( $user_id, $plaintext_secret ) {
        update_user_meta( $user_id, self::META_TOTP_SECRET, $this->crypto->encrypt( $plaintext_secret ) );
        update_user_meta( $user_id, self::META_TOTP_ENROLLED, time() );
    }

    /**
     * @return string|null Plaintext base32 secret, or null if not enrolled
     *                     (or AUTH_KEY rotated and the ciphertext is now garbage).
     */
    public function get_totp_secret( $user_id ) {
        $payload = get_user_meta( $user_id, self::META_TOTP_SECRET, true );

        if ( ! $payload ) {
            return null;
        }

        return $this->crypto->decrypt( $payload );
    }

    public function clear_totp( $user_id ) {
        delete_user_meta( $user_id, self::META_TOTP_SECRET );
        delete_user_meta( $user_id, self::META_TOTP_ENROLLED );
        delete_user_meta( $user_id, self::META_TOTP_LAST_STEP );
        $this->clear_pending_totp( $user_id );
    }

    public function get_totp_enrolled_at( $user_id ) {
        $ts = (int) get_user_meta( $user_id, self::META_TOTP_ENROLLED, true );

        return $ts ? $ts : null;
    }

    /**
     * Last accepted RFC 6238 step. Replay protection: any submitted code
     * whose step is <= this value is rejected.
     */
    public function get_last_accepted_step( $user_id ) {
        return (int) get_user_meta( $user_id, self::META_TOTP_LAST_STEP, true );
    }

    public function set_last_accepted_step( $user_id, $step ) {
        update_user_meta( $user_id, self::META_TOTP_LAST_STEP, (int) $step );
    }

    /**
     * Pending secret lives in a transient, keyed by user ID.
     * The active secret remains valid until the pending one is confirmed.
     */
    public function set_pending_totp( $user_id, $plaintext_secret ) {
        set_transient(
            self::TRANSIENT_PENDING . $user_id,
            $this->crypto->encrypt( $plaintext_secret ),
            self::PENDING_TTL
        );
    }

    public function get_pending_totp( $user_id ) {
        $payload = get_transient( self::TRANSIENT_PENDING . $user_id );

        if ( ! $payload ) {
            return null;
        }

        return $this->crypto->decrypt( $payload );
    }

    public function clear_pending_totp( $user_id ) {
        delete_transient( self::TRANSIENT_PENDING . $user_id );
    }

    /**
     * Append an audit entry. Rolling list capped at AUDIT_LIMIT entries.
     *
     * @param int    $user_id     Subject of the action.
     * @param string $action      e.g. 'admin_reset', 'self_disable', 'enrolled'.
     * @param int    $actor_id    User ID that performed the action.
     */
    public function append_audit( $user_id, $action, $actor_id ) {
        $log   = $this->get_audit_log( $user_id );
        $log[] = [
            'action'    => $action,
            'actor_id'  => (int) $actor_id,
            'timestamp' => time(),
        ];

        if ( count( $log ) > self::AUDIT_LIMIT ) {
            $log = array_slice( $log, -self::AUDIT_LIMIT );
        }

        update_user_meta( $user_id, self::META_AUDIT_LOG, $log );
    }

    /**
     * @return array<int, array{action:string, actor_id:int, timestamp:int}>
     */
    public function get_audit_log( $user_id ) {
        $log = get_user_meta( $user_id, self::META_AUDIT_LOG, true );

        return is_array( $log ) ? $log : [];
    }
}
