<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Soft lockout after repeated 2FA failures
 *
 * 5 consecutive failures lock further attempts for 15 minutes. The user is
 * not logged out of any existing session; this only blocks new TOTP
 * verifications. Counter resets on success or admin reset.
 *
 * @since WPUF_SINCE
 */
class Rate_Limiter {

    const MAX_FAILURES   = 5;
    const LOCKOUT_WINDOW = 900; // 15 minutes in seconds.

    const META_FAILURES = '_wpuf_2fa_failures';
    const META_LOCKED   = '_wpuf_2fa_locked_until';

    /**
     * @param int $user_id
     *
     * @return bool True if the user is currently locked out.
     */
    public function is_locked( $user_id ) {
        $until = (int) get_user_meta( $user_id, self::META_LOCKED, true );

        if ( $until && $until > time() ) {
            return true;
        }

        if ( $until && $until <= time() ) {
            // Lockout expired — clear so the next attempt starts fresh.
            $this->clear( $user_id );
        }

        return false;
    }

    /**
     * Record a failed attempt. Engages the lockout when MAX_FAILURES is hit.
     *
     * @param int $user_id
     *
     * @return void
     */
    public function record_failure( $user_id ) {
        $count = (int) get_user_meta( $user_id, self::META_FAILURES, true );
        ++$count;

        update_user_meta( $user_id, self::META_FAILURES, $count );

        if ( $count >= self::MAX_FAILURES ) {
            update_user_meta( $user_id, self::META_LOCKED, time() + self::LOCKOUT_WINDOW );
        }
    }

    /**
     * Clear failure count and any active lockout. Called on success or
     * admin reset.
     *
     * @param int $user_id
     *
     * @return void
     */
    public function clear( $user_id ) {
        delete_user_meta( $user_id, self::META_FAILURES );
        delete_user_meta( $user_id, self::META_LOCKED );
    }
}
