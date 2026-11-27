<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Contract every 2FA method implements
 *
 * The framework treats every method through this single surface — TOTP,
 * Email OTP, SMS OTP, and any future method all flow through the same
 * controllers, storage layer, and UI rendering. Adding a method is a
 * "register through `wpuf_2fa_register_methods` and implement these"
 * change, never a controller patch.
 *
 * See `docs/2fa-framework-generalization.md` for the design rationale
 * and `docs/2fa-extending.md` for a worked example.
 *
 * @since WPUF_SINCE
 */
interface Method_Interface {

    /**
     * Stable, lowercase, snake_case identifier (e.g. 'totp', 'email_otp',
     * 'sms_otp'). Used as the meta-key discriminator and in hook arg
     * shapes. Never change after release — third-party data depends on it.
     */
    public function get_id(): string;

    /**
     * Human-readable label for the method as a whole (e.g. "Authenticator
     * App", "Email", "SMS"). Translatable.
     */
    public function get_label(): string;

    /**
     * One-line description shown on the Security tab when the method is
     * unenrolled. Translatable.
     */
    public function get_description(): string;

    /**
     * Per-user destination label, shown on the enrolled-state card and on
     * the login challenge screen.
     *
     *   TOTP      → "Authenticator app"
     *   Email OTP → "user@exa•••.com"
     *   SMS OTP   → "+1 ••• ••• 1234"
     *
     * Lets the Security card and the challenge screen read from one
     * source — no per-method branches in the controllers.
     */
    public function get_destination_label( int $user_id ): string;

    /**
     * Begin enrollment. Two-step because every real method needs a round
     * trip — TOTP shows a QR for the user to scan, Email OTP sends a code
     * to verify the destination, SMS OTP does the same.
     *
     * Returns the props the next UI step needs. Opaque to the framework;
     * shaped by the method.
     *
     *   TOTP      → [ 'otpauth_uri' => '…', 'qr_svg' => '<svg…>', 'secret' => '…' ]
     *   Email OTP → [ 'masked_destination' => 'user@exa•••.com', 'resend_in' => 60 ]
     *   SMS OTP   → [ 'masked_destination' => '+1 ••• ••• 1234' ]
     *
     * @param int   $user_id
     * @param array $input  Whatever the "start setup" form submitted —
     *                      destination email for Email OTP, phone for SMS,
     *                      empty array for TOTP.
     *
     * @return array|\WP_Error Setup-screen props on success, WP_Error on
     *                         validation failure (bad email, send failure,
     *                         method already enrolled, etc.).
     */
    public function start_enrollment( int $user_id, array $input );

    /**
     * Complete enrollment. Receives the verification code (or whatever
     * proves the user controls the destination). On success the method
     * atomically promotes pending state to active and marks the user
     * enrolled — `is_enrolled()` returns true immediately after.
     *
     * Implementations are responsible for clearing pending state on
     * success.
     *
     * @param int   $user_id
     * @param array $input  At minimum a 'code' key; methods may require more.
     *
     * @return true|\WP_Error
     */
    public function confirm_enrollment( int $user_id, array $input );

    /**
     * Whether this user has completed enrollment for this method. Cheap —
     * single meta read. Called by `Method_Registry::enrolled_for()` and
     * the Security tab on every render.
     */
    public function is_enrolled( int $user_id ): bool;

    /**
     * Called at login-challenge time, before the code-entry screen renders.
     *
     * TOTP returns true immediately (the user already has the code in
     * their app). Email OTP generates a code, hashes it via
     * `User_Storage::set_issued_code()`, and sends the email. SMS OTP
     * does the equivalent.
     *
     * Returns true on success or `WP_Error` to abort the challenge (send
     * failure, rate-limit hit). On `WP_Error` the controller shows a
     * generic "Couldn't send your code, try again." and writes the
     * `WP_Error` message to the audit log only — never to the UI.
     *
     * @return true|\WP_Error
     */
    public function issue_challenge( int $user_id );

    /**
     * Verify a submitted code against this user's enrolled state.
     * Implementations are responsible for replay protection (TOTP step
     * tracking) or one-time-use enforcement (issued-code clear).
     *
     * @return true|\WP_Error
     */
    public function verify( int $user_id, string $code );

    /**
     * Tear down enrollment for this user. Called by self-disable and
     * admin reset. Implementations call `User_Storage::clear_method()`
     * for the standard meta keys, then handle anything method-specific.
     */
    public function reset( int $user_id ): void;
}
