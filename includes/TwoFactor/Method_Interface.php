<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Contract every 2FA method implements
 *
 * TOTP is the only built-in method in the free MVP. Email OTP and SMS OTP
 * (deferred) plug in here without touching login or enrollment controllers.
 *
 * @since WPUF_SINCE
 */
interface Method_Interface {

    /**
     * Stable method identifier (e.g. "totp", "email_otp", "sms_otp").
     */
    public function get_id();

    /**
     * Human-readable label for the enrollment card.
     */
    public function get_label();

    /**
     * Whether this user has completed enrollment for this method.
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function is_enrolled( $user_id );

    /**
     * Verify a code submitted by the user against the stored credential.
     *
     * Implementations are responsible for replay protection.
     *
     * @param int    $user_id
     * @param string $code
     *
     * @return bool
     */
    public function verify( $user_id, $code );

    /**
     * Tear down enrollment for this user (admin reset or self-disable).
     *
     * @param int $user_id
     *
     * @return void
     */
    public function reset( $user_id );
}
