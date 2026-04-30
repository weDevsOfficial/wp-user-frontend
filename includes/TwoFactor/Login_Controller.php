<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Multi-method 2FA challenge for the WPUF login form
 *
 * Two- or three-stage flow on the same `[wpuf-login]` page:
 *
 * Stage 1 — username + password submitted.
 *   We hook the WP `authenticate` filter at priority 40 (after WPUF's
 *   own activation check at 30). If the user has exactly one active
 *   enrolled method, we issue the challenge and mint a 5-minute
 *   challenge transient. If the user has 2+ enrolled methods, we mint
 *   a 5-minute *selector* transient instead and skip issuance — Email
 *   OTP costs a real send, so we don't burn one until the user picks.
 *   Either way we return a `WP_Error` to abort `wp_signon()` cleanly.
 *
 * Stage 1.5 — method choice submitted (only when 2+ methods enrolled).
 *   `maybe_handle_method_choice()` runs on `init` priority 8. It
 *   validates the selector token, issues the challenge for the chosen
 *   method, and mints the challenge transient. From here the flow
 *   merges with the single-method path.
 *
 * Stage 2 — challenge token + code submitted.
 *   A small `init` priority-9 handler catches the submission before
 *   `Simple_Login::process_login` runs. It looks up the transient,
 *   finds the method by id, calls `$method->verify()`, then calls
 *   `wp_set_auth_cookie()` directly and redirects.
 *
 * The picker / code field is injected via `wpuf_login_form_bottom`
 * only when an instance flag set by an earlier stage is present, so
 * those fields are invisible until the prior stage succeeds.
 *
 * Limitation (documented): wp-login.php is not intercepted. Admins
 * logging in via /wp-admin/ bypass this challenge entirely.
 *
 * @since WPUF_SINCE
 */
class Login_Controller {

    public const NONCE_ACTION       = 'wpuf_2fa_challenge';
    public const TOKEN_TTL          = 300; // 5 minutes to complete the challenge.
    public const TRANSIENT_KEY      = 'wpuf_2fa_challenge_';
    public const SELECTOR_KEY       = 'wpuf_2fa_selector_';
    public const SELECTOR_TOKEN_TTL = 300;

    /** @var Method_Registry */
    private $registry;

    /**
     * Set during stage 1 (or on stage-2 failure) so `wpuf_login_form_bottom`
     * knows to render the 2FA code field. Per-request state.
     *
     * @var string
     */
    private $pending_token = '';

    /**
     * Label for the destination of the in-flight challenge (e.g.
     * "Authenticator app", "user@exa•••.com"). Per-request state, used
     * by the rendered code-entry screen.
     *
     * @var string
     */
    private $pending_destination_label = '';

    /**
     * Set when a user has 2+ enrolled methods and we need them to pick
     * one before issuing a challenge. Per-request state.
     *
     * @var string
     */
    private $pending_selector_token = '';

    /**
     * Method choices to render in the picker. Shape: [ id => label ].
     * Set alongside `$pending_selector_token`.
     *
     * @var array
     */
    private $pending_method_choices = [];

    public function __construct( Method_Registry $registry ) {
        $this->registry = $registry;

        add_action( 'init', [ $this, 'maybe_handle_method_choice' ], 8 );
        add_action( 'init', [ $this, 'maybe_handle_stage_two' ], 9 );
        add_filter( 'authenticate', [ $this, 'maybe_require_2fa_challenge' ], 40, 3 );
        add_action( 'wpuf_login_form_bottom', [ $this, 'render_2fa_field' ] );
    }

    /**
     * Stage 2: catches the submission with token + code. Runs at init
     * priority 9, just before Simple_Login::process_login (priority 10).
     */
    public function maybe_handle_stage_two() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['wpuf_2fa_token'] ) || empty( $_POST['wpuf_login'] ) ) {
            return;
        }

        $nonce = isset( $_POST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_POST['wpuf-login-nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
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
            // Expired or never existed. The user must re-enter credentials
            // to mint a new token.
            $this->pending_token = '';
            $this->push_login_error( __( 'Your sign-in session expired. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        $user_id   = (int) $challenge['user_id'];
        $method_id = (string) $challenge['method_id'];
        $method    = $this->registry->get( $method_id );

        if ( ! $method ) {
            // Method went missing between stage 1 and stage 2 — admin
            // disabled it, plugin deactivated, etc. Fail closed.
            delete_transient( self::TRANSIENT_KEY . $token );
            $this->pending_token = '';
            $this->push_login_error( __( 'Two-factor authentication is unavailable. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        $result = $method->verify( $user_id, $code );

        if ( is_wp_error( $result ) ) {
            // Wrong code (or replay/lockout). Keep the same token so the
            // user can retry without re-entering credentials.
            $this->pending_token             = $token;
            $this->pending_destination_label = $method->get_destination_label( $user_id );
            $this->push_login_error( $result->get_error_message() );

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
     * has any active 2FA method enrolled, mint a token and return a
     * WP_Error to abort the signon.
     *
     * @param \WP_User|\WP_Error|null $user
     * @param string                  $username
     * @param string                  $password
     *
     * @return \WP_User|\WP_Error|null
     */
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function maybe_require_2fa_challenge( $user, $username, $password ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['wpuf_login'] ) ) {
            return $user;
        }

        // Stage 2 / method-choice are handled separately at init priority
        // 8 and 9; defense-in-depth.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['wpuf_2fa_token'] ) || ! empty( $_POST['wpuf_2fa_selector_token'] ) ) {
            return $user;
        }

        // Simple_Login::process_login() calls wp_signon() twice in some
        // paths (legacy email-as-username fallback at line ~616). Without
        // this guard we'd mint a second selector/challenge token on the
        // retry, orphaning the first transient for its full TTL and —
        // for OTP methods — sending a duplicate code. If we've already
        // staged either flow this request, the answer is the same.
        if ( $this->pending_selector_token || $this->pending_token ) {
            return new \WP_Error(
                'wpuf_2fa_required',
                __( 'Two-factor verification required.', 'wp-user-frontend' )
            );
        }

        if ( is_wp_error( $user ) || ! is_a( $user, '\WP_User' ) ) {
            return $user;
        }

        if ( ! $this->is_2fa_enabled_globally() ) {
            return $user;
        }

        $enrolled = $this->intersect_active_and_enrolled( $user->ID );

        if ( empty( $enrolled ) ) {
            return $user;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $remember = isset( $_POST['rememberme'] ) ? (bool) $_POST['rememberme'] : false;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $final_redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );

        // Multiple methods enrolled — let the user pick, defer the
        // challenge until after they choose. Email OTP sends a real
        // message and consumes a slot, so we don't want to issue one
        // for a method the user is about to switch away from.
        if ( count( $enrolled ) >= 2 ) {
            $this->begin_method_selection( $user->ID, $enrolled, $remember, $final_redirect );

            return new \WP_Error(
                'wpuf_2fa_method_required',
                __( 'Choose how you want to verify your sign-in.', 'wp-user-frontend' )
            );
        }

        $method = $this->resolve_challenge_method( $user->ID, $enrolled );

        if ( ! $method ) {
            return $user;
        }

        return $this->issue_and_mint_challenge( $user->ID, $method, $remember, $final_redirect );
    }

    /**
     * Stage 1.5: user picked a method from the selector. Validate the
     * selector token, then issue the challenge and mint a real challenge
     * token. Runs at init priority 8, before stage 2 at 9.
     */
    public function maybe_handle_method_choice() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['wpuf_2fa_selector_token'] ) || empty( $_POST['wpuf_login'] ) ) {
            return;
        }

        $nonce = isset( $_POST['wpuf-login-nonce'] ) ? sanitize_key( wp_unslash( $_POST['wpuf-login-nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wpuf_login_action' ) ) {
            $this->push_login_error( __( 'Security check failed. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $token = sanitize_text_field( wp_unslash( $_POST['wpuf_2fa_selector_token'] ) );
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $chosen_id = isset( $_POST['wpuf_2fa_method'] ) ? sanitize_key( wp_unslash( $_POST['wpuf_2fa_method'] ) ) : '';

        $selection = $this->load_selector( $token );

        if ( ! $selection ) {
            $this->push_login_error( __( 'Your sign-in session expired. Please log in again.', 'wp-user-frontend' ) );

            return;
        }

        $user_id = (int) $selection['user_id'];

        // The chosen method must be one the user is actually enrolled in
        // and that the admin has enabled. Re-derive at choice time so a
        // disable/unenroll between stage 1 and now fails closed.
        $enrolled = $this->intersect_active_and_enrolled( $user_id );

        if ( ! $chosen_id || ! isset( $enrolled[ $chosen_id ] ) ) {
            // Re-render the picker so the user can try again.
            $this->pending_selector_token = $token;
            $this->pending_method_choices = $this->build_method_choices( $enrolled, $user_id );
            $this->push_login_error( __( 'Please choose a verification method to continue.', 'wp-user-frontend' ) );

            return;
        }

        $method = $enrolled[ $chosen_id ];

        // Selector token is single-use — consume it now even if issuance
        // fails below, so a failed send can't be replayed silently.
        delete_transient( self::SELECTOR_KEY . $token );

        $result = $this->issue_and_mint_challenge(
            $user_id,
            $method,
            ! empty( $selection['remember'] ),
            ! empty( $selection['final_redirect'] ) ? $selection['final_redirect'] : home_url( '/' )
        );

        if ( is_wp_error( $result ) ) {
            $this->push_login_error( $result->get_error_message() );
        }
    }

    /**
     * Issue a challenge for the chosen method and mint the challenge
     * token. Returns a WP_Error on issuance failure (stage 1 surfaces it
     * via the authenticate filter; stage 1.5 pushes it to login errors).
     *
     * @return \WP_Error
     */
    private function issue_and_mint_challenge( int $user_id, Method_Interface $method, bool $remember, string $final_redirect ) {
        // Side-effect time. Email/SMS OTP send the code here. TOTP no-ops.
        $issued = $method->issue_challenge( $user_id );

        if ( is_wp_error( $issued ) ) {
            wpuf()->two_factor->storage->append_audit(
                $user_id,
                $method->get_id(),
                'challenge_issue_failed',
                $user_id,
                [ 'error' => $issued->get_error_message() ]
            );

            // Generic UI message — never surface send-channel internals,
            // they help attackers map infrastructure.
            return new \WP_Error(
                'wpuf_2fa_issue_failed',
                __( "Couldn't send your code, try again.", 'wp-user-frontend' )
            );
        }

        do_action( 'wpuf_2fa_issue_challenge', $user_id, $method->get_id() );

        $token = wp_generate_password( 32, false, false );

        set_transient(
            self::TRANSIENT_KEY . $token,
            [
                'user_id'        => $user_id,
                'method_id'      => $method->get_id(),
                'remember'       => $remember,
                'final_redirect' => $final_redirect,
            ],
            self::TOKEN_TTL
        );

        $this->pending_token             = $token;
        $this->pending_destination_label = $method->get_destination_label( $user_id );

        return new \WP_Error(
            'wpuf_2fa_required',
            $this->challenge_prompt_message( $method, $user_id )
        );
    }

    /**
     * Park selector state for a user with 2+ enrolled methods. The
     * actual challenge isn't issued until they pick — see
     * `maybe_handle_method_choice()`.
     */
    private function begin_method_selection( int $user_id, array $enrolled, bool $remember, string $final_redirect ): void {
        $token = wp_generate_password( 32, false, false );

        set_transient(
            self::SELECTOR_KEY . $token,
            [
                'user_id'        => $user_id,
                'remember'       => $remember,
                'final_redirect' => $final_redirect,
            ],
            self::SELECTOR_TOKEN_TTL
        );

        $this->pending_selector_token = $token;
        $this->pending_method_choices = $this->build_method_choices( $enrolled, $user_id );
    }

    /**
     * Shape the enrolled methods into the [ id => [ label, hint ] ]
     * array the picker template consumes.
     */
    private function build_method_choices( array $enrolled, int $user_id ): array {
        $choices = [];

        foreach ( $enrolled as $id => $method ) {
            $choices[ $id ] = [
                'label' => $method->get_label(),
                'hint'  => $method->get_destination_label( $user_id ),
            ];
        }

        return $choices;
    }

    private function load_selector( $token ) {
        if ( ! $token ) {
            return null;
        }

        $data = get_transient( self::SELECTOR_KEY . $token );

        return is_array( $data ) && ! empty( $data['user_id'] ) ? $data : null;
    }

    /**
     * Pick the method to challenge with for this user. Default: first
     * enrolled method in registry order. Filterable so Pro can honor a
     * per-user "preferred method" setting.
     *
     * Only used when the user has exactly one enrolled method, or when
     * a filter wants to force-skip the picker. Multi-method users go
     * through `begin_method_selection()` instead.
     *
     * @param Method_Interface[]|null $enrolled Pre-computed enrolled list,
     *                                          or null to derive it.
     *
     * @return Method_Interface|null
     */
    private function resolve_challenge_method( int $user_id, $enrolled = null ) {
        if ( ! is_array( $enrolled ) ) {
            $enrolled = $this->intersect_active_and_enrolled( $user_id );
        }

        if ( empty( $enrolled ) ) {
            return null;
        }

        $default = reset( $enrolled );

        $resolved = apply_filters( 'wpuf_2fa_login_challenge_method', $default, $user_id, $enrolled );

        return $resolved instanceof Method_Interface ? $resolved : $default;
    }

    /**
     * Active methods (admin enabled) intersected with methods this user
     * is enrolled in. A user can be enrolled in TOTP from before an
     * admin disabled TOTP — we shouldn't challenge with a method the
     * admin has turned off.
     *
     * @return Method_Interface[]
     */
    private function intersect_active_and_enrolled( int $user_id ): array {
        $active   = $this->registry->active();
        $enrolled = [];

        foreach ( $active as $id => $method ) {
            if ( $method->is_enrolled( $user_id ) ) {
                $enrolled[ $id ] = $method;
            }
        }

        return $enrolled;
    }

    /**
     * Render either the method picker or the 2FA code input + token,
     * inside the WPUF login form. Only renders when a selector or a
     * challenge is pending for this request.
     */
    public function render_2fa_field() {
        if ( $this->pending_selector_token ) {
            $this->render_method_picker();

            return;
        }

        if ( ! $this->pending_token ) {
            return;
        }

        // Shared challenge-stage CSS: hides credential row + remember-me
        // + submit while a challenge is in flight. See assets/css/wpuf-2fa-login.css.
        wp_enqueue_style( 'wpuf-2fa-login' );

        $destination = $this->pending_destination_label;
        // See render_method_picker() for why we close + re-open the <p>.
        ?>
        </p>
        <div class="wpuf-2fa-challenge-fields">
            <?php if ( $destination ) : ?>
                <div class="wpuf-2fa-challenge-destination">
                    <?php
                    printf(
                        /* translators: %s: human-readable destination, e.g. "Authenticator app" or "user@exa•••.com" */
                        esc_html__( 'Code sent to: %s', 'wp-user-frontend' ),
                        esc_html( $destination )
                    );
                    ?>
                </div>
            <?php endif; ?>
            <div class="wpuf-2fa-challenge-code">
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
            </div>
            <div class="wpuf-2fa-challenge-actions">
                <button type="submit" class="button wpuf-2fa-challenge-submit">
                    <?php esc_html_e( 'Verify', 'wp-user-frontend' ); ?>
                </button>
            </div>
            <input type="hidden" name="wpuf_2fa_token" value="<?php echo esc_attr( $this->pending_token ); ?>" />
        </div>
        <p style="display:none;"><?php // Re-open the <p> the template will try to close. ?>
        <?php
    }

    /**
     * Render the multi-method picker. Auto-submits on radio change so
     * a single click advances to the code-entry screen; the Continue
     * button is the no-JS fallback.
     */
    private function render_method_picker() {
        // Shared challenge-stage CSS + picker-specific auto-advance JS.
        // See assets/css/wpuf-2fa-login.css and assets/js/wpuf-2fa-login.js.
        wp_enqueue_style( 'wpuf-2fa-login' );
        wp_enqueue_script( 'wpuf-2fa-login' );

        // The login template wraps `wpuf_login_form_bottom` in a <p>;
        // closing it here keeps our block-level <div> from being
        // auto-flushed by the parser, which produced stray markup and
        // duplicated text in the rendered form.
        ?>
        </p>
        <div class="wpuf-2fa-method-picker">
            <?php
            $first = true;
            foreach ( $this->pending_method_choices as $id => $choice ) :
                $input_id = 'wpuf-2fa-method-' . sanitize_html_class( $id );
                ?>
                <div class="wpuf-2fa-method-picker__option">
                    <label for="<?php echo esc_attr( $input_id ); ?>">
                        <input type="radio"
                                name="wpuf_2fa_method"
                                id="<?php echo esc_attr( $input_id ); ?>"
                                value="<?php echo esc_attr( $id ); ?>"
                                <?php checked( $first, true ); ?>
                                class="wpuf-2fa-method-picker__radio" />
                        <span class="wpuf-2fa-method-picker__label">
                            <?php echo esc_html( $choice['label'] ); ?>
                        </span>
                        <?php if ( ! empty( $choice['hint'] ) && $choice['hint'] !== $choice['label'] ) : ?>
                            <span class="wpuf-2fa-method-picker__hint">
                                <?php echo esc_html( $choice['hint'] ); ?>
                            </span>
                        <?php endif; ?>
                    </label>
                </div>
                <?php
                $first = false;
            endforeach;
            ?>

            <div class="wpuf-2fa-method-picker__actions">
                <button type="submit" class="button wpuf-2fa-method-picker__submit">
                    <?php esc_html_e( 'Continue', 'wp-user-frontend' ); ?>
                </button>
            </div>

            <input type="hidden" name="wpuf_2fa_selector_token" value="<?php echo esc_attr( $this->pending_selector_token ); ?>" />
        </div>
        <p style="display:none;"><?php // Re-open the <p> the template will try to close. ?>
        <?php
    }

    private function challenge_prompt_message( Method_Interface $method, int $user_id ): string {
        if ( $method->get_id() === TOTP_Method::ID ) {
            return __( 'Enter the 6-digit code from your authenticator app to finish signing in.', 'wp-user-frontend' );
        }

        return sprintf(
            /* translators: %s: destination label, e.g. "user@exa•••.com" */
            __( 'Enter the 6-digit code we sent to %s.', 'wp-user-frontend' ),
            $method->get_destination_label( $user_id )
        );
    }

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

        return is_array( $data ) && ! empty( $data['user_id'] ) && ! empty( $data['method_id'] ) ? $data : null;
    }

    private function is_2fa_enabled_globally(): bool {
        if ( wpuf_get_option( 'enable_2fa', 'wpuf_2fa', 'off' ) !== 'on' ) {
            return false;
        }

        $active = wpuf_get_option( 'active_2fa_methods', 'wpuf_2fa', [] );

        return is_array( $active ) && ! empty( $active );
    }
}
