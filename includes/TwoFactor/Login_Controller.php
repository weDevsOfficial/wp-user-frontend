<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Inline single-page TOTP challenge for the WPUF login form
 *
 * Two-stage flow on the same `[wpuf-login]` page:
 *
 * Stage 1 — username + password submitted.
 *   We hook the WP `authenticate` filter at priority 40 (after WPUF's
 *   own activation check at 30). If the user has TOTP enrolled, we mint
 *   a 5-minute transient keyed by a one-time token, then return a
 *   `WP_Error` to abort `wp_signon()` cleanly. The auth cookie never
 *   gets set. WPUF re-renders the login form with our error in the
 *   message area.
 *
 * Stage 2 — token + code submitted (no password round-trips).
 *   A small `init`-priority handler catches the second submission
 *   before `Simple_Login::process_login` runs. It looks up the
 *   transient, verifies the code against the user's stored secret,
 *   then calls `wp_set_auth_cookie()` directly and redirects.
 *
 * The form field for the code is injected via `wpuf_login_form_bottom`
 * only when an instance flag set by stage 1 is present, so the field is
 * invisible until stage 1 succeeds.
 *
 * Limitation (documented): wp-login.php is not intercepted. Admins
 * logging in via /wp-admin/ bypass this challenge entirely.
 *
 * @since WPUF_SINCE
 */
class Login_Controller {

    public const NONCE_ACTION  = 'wpuf_2fa_challenge';
    public const TOKEN_TTL     = 300; // 5 minutes to complete the challenge.
    public const TRANSIENT_KEY = 'wpuf_2fa_challenge_';

    /** @var Method_Registry */
    private $registry;

    /** @var TOTP_Method */
    private $totp;

    /**
     * Set during stage 1 (or on stage-2 failure) so `wpuf_login_form_bottom`
     * knows to render the 2FA code field. Per-request state.
     *
     * @var string
     */
    private $pending_token = '';

    public function __construct( Method_Registry $registry, TOTP_Method $totp ) {
        $this->registry = $registry;
        $this->totp     = $totp;

        add_action( 'init', [ $this, 'maybe_handle_stage_two' ], 9 );
        add_filter( 'authenticate', [ $this, 'maybe_require_totp_challenge' ], 40, 3 );
        add_action( 'wpuf_login_form_bottom', [ $this, 'render_2fa_field' ] );
    }

    /**
     * Stage 2: catches the submission with token + code. Runs at init
     * priority 9, just before Simple_Login::process_login (priority 10).
     *
     * Stage-2 POSTs only carry the code + token — `log` and `pwd` are
     * empty (the form's hidden credential fields submit blank) and the
     * Turnstile widget isn't re-rendered for the challenge view.
     * `Simple_Login::process_login` short-circuits when it sees
     * `$_POST['wpuf_2fa_token']`, so it won't pollute the error pipeline
     * with "Username is required" / "Cloudflare Turnstile failed".
     */
    public function maybe_handle_stage_two() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['wpuf_2fa_token'] ) || empty( $_POST['wpuf_login'] ) ) {
            return;
        }

        $nonce = isset( $_POST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_POST['wpuf-login-nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
            // Fail closed with our own message. Simple_Login is gated by
            // the wpuf_2fa_token presence guard, so it won't surface its
            // own "Nonce is invalid" message on top of this one.
            $this->pending_token = '';
            $this->push_login_error( __( 'Security check failed. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $token = sanitize_text_field( wp_unslash( $_POST['wpuf_2fa_token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $code = isset( $_POST['wpuf_2fa_code'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_2fa_code'] ) ) : '';

        $challenge = $this->load_challenge( $token );

        if ( ! $challenge ) {
            // Expired. The user must re-enter credentials to mint a new
            // token, so clear the pending flag and let the form re-render
            // its username/password fields.
            $this->pending_token = '';
            $this->push_login_error( __( 'Your sign-in session expired. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        $user_id = (int) $challenge['user_id'];

        if ( ! $this->totp->verify( $user_id, $code ) ) {
            // Wrong code — keep the same token so the user can retry
            // without re-entering credentials.
            $this->pending_token = $token;
            $this->push_login_error( __( "That code didn't match. Try again.", 'wp-user-frontend' ) );

            return;
        }

        // Success: consume token, set the auth cookie, redirect.
        delete_transient( self::TRANSIENT_KEY . $token );

        $remember = ! empty( $challenge['remember'] );
        wp_set_auth_cookie( $user_id, $remember );

        $redirect = ! empty( $challenge['final_redirect'] )
            ? $challenge['final_redirect']
            : home_url( '/' );

        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Stage 1: hook into the standard `authenticate` chain. If the user
     * has TOTP enrolled, mint a token and return a WP_Error to abort
     * the signon. The user has been authenticated correctly up to this
     * point — the error is purely a "we need one more step" signal.
     *
     * @param \WP_User|\WP_Error|null $user
     * @param string                  $username
     * @param string                  $password
     *
     * @return \WP_User|\WP_Error|null
     */
    // $username and $password are required by the `authenticate` filter
    // signature even though we read credentials from the validated user.
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function maybe_require_totp_challenge( $user, $username, $password ) {
        // Scope strictly to WPUF login form submissions.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['wpuf_login'] ) ) {
            return $user;
        }

        // If a token is present we're in stage 2, which is handled by
        // maybe_handle_stage_two() at init priority 9; by the time
        // wp_signon runs (later) the redirect/exit has already happened.
        // This branch is defense-in-depth only.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['wpuf_2fa_token'] ) ) {
            return $user;
        }

        // Pass-through if WP core or another plugin already failed auth.
        if ( is_wp_error( $user ) || ! is_a( $user, '\WP_User' ) ) {
            return $user;
        }

        if ( ! $this->is_2fa_enabled_globally() ) {
            return $user;
        }

        if ( ! $this->totp->is_enrolled( $user->ID ) ) {
            return $user;
        }

        // Credentials are valid; mint a challenge token and require code.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $remember = isset( $_POST['rememberme'] ) ? (bool) $_POST['rememberme'] : false;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $final_redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );

        $token = wp_generate_password( 32, false, false );

        set_transient(
            self::TRANSIENT_KEY . $token,
            [
                'user_id'        => $user->ID,
                'remember'       => $remember,
                'final_redirect' => $final_redirect,
            ],
            self::TOKEN_TTL
        );

        $this->pending_token = $token;

        return new \WP_Error(
            'wpuf_2fa_required',
            __( 'Enter the 6-digit code from your authenticator app to finish signing in.', 'wp-user-frontend' )
        );
    }

    /**
     * Render the 2FA code input + token inside the WPUF login form.
     * Only renders when a challenge is pending for this request — kept
     * invisible until stage 1 succeeds.
     */
    public function render_2fa_field() {
        if ( ! $this->pending_token ) {
            return;
        }
        ?>
        <div class="wpuf-2fa-challenge-fields">
            <p>
                <label for="wpuf-2fa-code"><?php esc_html_e( 'Verification code', 'wp-user-frontend' ); ?></label>
                <input type="text"
                        name="wpuf_2fa_code"
                        id="wpuf-2fa-code"
                        class="input wpuf-2fa-code"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        pattern="[0-9]{6}"
                        autofocus
                        required />
            </p>
            <input type="hidden" name="wpuf_2fa_token" value="<?php echo esc_attr( $this->pending_token ); ?>" />
        </div>

        <style>
            /* Hide the username / password fields once a challenge is in flight.
             * The form still posts to itself; stage 2 reads the hidden token. */
            #loginform .wpuf-login-form > p:has(#wpuf-user_login),
            #loginform .wpuf-login-form > p:has(#wpuf-user_pass),
            #loginform > p:has(#wpuf-user_login),
            #loginform > p:has(#wpuf-user_pass),
            .wpuf-login-form .forgetmenot {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Push a message into Simple_Login's error pipeline. Uses the
     * `wpuf_login_errors` filter so we don't reach into private state.
     *
     * @param string $message
     */
    private function push_login_error( $message ) {
        add_filter(
            'wpuf_login_errors',
            static function ( $errors ) use ( $message ) {
                $errors   = is_array( $errors ) ? $errors : [];
                $errors[] = $message;

                return $errors;
            }
        );
    }

    private function load_challenge( $token ) {
        if ( ! $token ) {
            return null;
        }

        $data = get_transient( self::TRANSIENT_KEY . $token );

        return is_array( $data ) && ! empty( $data['user_id'] ) ? $data : null;
    }

    private function is_2fa_enabled_globally() {
        if ( wpuf_get_option( 'enable_2fa', 'wpuf_2fa', 'off' ) !== 'on' ) {
            return false;
        }

        $active = wpuf_get_option( 'active_2fa_methods', 'wpuf_2fa', [] );

        return is_array( $active ) && in_array( TOTP_Method::ID, $active, true );
    }
}
