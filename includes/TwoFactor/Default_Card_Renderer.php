<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Default consumer of `wpuf_2fa_security_card_html`
 *
 * Ships generic per-method card markup that works for every method —
 * label, description, state badge, and the enroll / disable forms. The
 * markup is intentionally *not* TOTP-specific; method-specific dynamic
 * UI (QR code, masked-email field, etc.) is rendered client-side by
 * `wpuf-2fa-account.js` from the props returned by
 * `Method_Interface::start_enrollment()`.
 *
 * Pro and third parties hook the same filter at a later priority and
 * return their own markup for specific `method_id` values.
 *
 * Escaping: this class returns a complete HTML string; the template
 * (`templates/dashboard/security.php`) passes it through `wp_kses_post()`
 * before output. We don't escape inside `<style>`/`<script>` because
 * `wp_kses_post()` strips them entirely — keep card markup HTML-only.
 *
 * @since WPUF_SINCE
 */
class Default_Card_Renderer {

    public function __construct() {
        // Priority 20 so consumers at the default priority 10 see the
        // empty default string first and have a chance to return their
        // own HTML. Default_Card_Renderer fills in only when nothing
        // else has.
        add_filter( 'wpuf_2fa_security_card_html', [ $this, 'render' ], 20, 3 );
    }

    /**
     * @param string $html      Default empty. We render only when no
     *                          higher-priority consumer has already
     *                          supplied markup.
     * @param array  $card_data Structured scalars from the template.
     * @param int    $user_id   Required by filter signature; consumed
     *                          by other consumers, not by us.
     */
    // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    public function render( $html, $card_data, $user_id ) {
        if ( $html !== '' ) {
            return $html;
        }

        if ( ! is_array( $card_data ) ) {
            return '';
        }

        $is_enrolled = ! empty( $card_data['is_enrolled'] );

        return $is_enrolled
            ? $this->render_enrolled( $card_data )
            : $this->render_unenrolled( $card_data );
    }

    private function render_unenrolled( array $card_data ): string {
        ob_start();
        ?>
        <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6 wpuf-2fa-method-card"
            data-method-id="<?php echo esc_attr( $card_data['method_id'] ); ?>"
            data-2fa-state="not-enrolled">
            <div class="wpuf-flex wpuf-items-start wpuf-justify-between wpuf-gap-4">
                <div>
                    <h4 class="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-1">
                        <?php echo esc_html( $card_data['label'] ); ?>
                    </h4>
                    <p class="wpuf-text-gray-600 wpuf-mb-0 wpuf-text-sm">
                        <?php echo esc_html( $card_data['description'] ); ?>
                    </p>
                </div>
                <button type="button"
                        class="wpuf-2fa-method-start wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-bg-emerald-600 wpuf-border wpuf-border-transparent wpuf-rounded-md hover:wpuf-bg-emerald-700 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-emerald-500 focus:wpuf-ring-offset-2 wpuf-shrink-0">
                    <?php esc_html_e( 'Set up', 'wp-user-frontend' ); ?>
                </button>
            </div>

            <div class="wpuf-2fa-method-enrollment wpuf-hidden wpuf-mt-6 wpuf-pt-6 wpuf-border-t wpuf-border-gray-200">
                <p class="wpuf-2fa-enrollment-instructions wpuf-text-gray-700 wpuf-text-sm wpuf-mb-4"></p>

                <div class="wpuf-2fa-enrollment-extras wpuf-mb-5"></div>

                <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">
                    <?php esc_html_e( 'Verification code', 'wp-user-frontend' ); ?>
                </label>
                <input type="text"
                        class="wpuf-2fa-method-code wpuf-w-40 wpuf-font-mono wpuf-text-base wpuf-tracking-widest wpuf-text-center wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-white wpuf-text-gray-900 wpuf-px-3 wpuf-py-2 focus:wpuf-border-emerald-500 focus:wpuf-ring-1 focus:wpuf-ring-emerald-500"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        pattern="[0-9]{6}" />

                <div class="wpuf-mt-5">
                    <button type="button"
                            class="wpuf-2fa-method-confirm wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-bg-emerald-600 wpuf-border wpuf-border-transparent wpuf-rounded-md hover:wpuf-bg-emerald-700 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-emerald-500 focus:wpuf-ring-offset-2">
                        <?php esc_html_e( 'Verify and enable', 'wp-user-frontend' ); ?>
                    </button>
                </div>

                <p class="wpuf-2fa-message wpuf-mt-3 wpuf-mb-0 wpuf-text-sm"></p>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function render_enrolled( array $card_data ): string {
        $enrolled_human    = $card_data['enrolled_at_human'];
        $destination_label = $card_data['destination_label'];

        ob_start();
        ?>
        <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6 wpuf-2fa-method-card"
            data-method-id="<?php echo esc_attr( $card_data['method_id'] ); ?>"
            data-2fa-state="enrolled">
            <div class="wpuf-flex wpuf-items-center wpuf-flex-wrap wpuf-gap-3 wpuf-mb-2">
                <h4 class="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-0">
                    <?php echo esc_html( $card_data['label'] ); ?>
                </h4>
                <span class="wpuf-inline-flex wpuf-items-center wpuf-bg-green-50 wpuf-text-green-700 wpuf-text-xs wpuf-font-medium wpuf-px-2 wpuf-py-0.5 wpuf-rounded-full">
                    <?php esc_html_e( 'Active', 'wp-user-frontend' ); ?>
                </span>
            </div>
            <?php if ( $destination_label ) : ?>
                <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-mt-0 wpuf-mb-1">
                    <?php echo esc_html( $destination_label ); ?>
                </p>
            <?php endif; ?>
            <?php if ( $enrolled_human ) : ?>
                <p class="wpuf-text-gray-500 wpuf-text-sm wpuf-mt-0 wpuf-mb-5">
                    <?php
                    printf(
                        /* translators: %s: human-readable enrollment date */
                        esc_html__( 'Enrolled on %s.', 'wp-user-frontend' ),
                        esc_html( $enrolled_human )
                    );
                    ?>
                </p>
            <?php endif; ?>

            <div class="wpuf-2fa-disable-form wpuf-pt-5 wpuf-border-t wpuf-border-gray-200">
                <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-mb-4">
                    <?php esc_html_e( 'To disable two-factor authentication, confirm with your password and a verification code.', 'wp-user-frontend' ); ?>
                </p>

                <div class="wpuf-grid wpuf-grid-cols-1 md:wpuf-grid-cols-2 wpuf-gap-4 wpuf-mb-4">
                    <div>
                        <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">
                            <?php esc_html_e( 'Current password', 'wp-user-frontend' ); ?>
                        </label>
                        <input type="password"
                                class="wpuf-2fa-disable-password wpuf-w-full wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-white wpuf-text-gray-900 wpuf-px-3 wpuf-py-2 focus:wpuf-border-emerald-500 focus:wpuf-ring-1 focus:wpuf-ring-emerald-500"
                                autocomplete="current-password" />
                    </div>
                    <div>
                        <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">
                            <?php esc_html_e( 'Verification code', 'wp-user-frontend' ); ?>
                        </label>
                        <div class="wpuf-flex wpuf-gap-2">
                            <input type="text"
                                    class="wpuf-2fa-disable-code wpuf-flex-1 wpuf-font-mono wpuf-tracking-widest wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-white wpuf-text-gray-900 wpuf-px-3 wpuf-py-2 focus:wpuf-border-emerald-500 focus:wpuf-ring-1 focus:wpuf-ring-emerald-500"
                                    maxlength="6"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    pattern="[0-9]{6}" />
                            <button type="button"
                                    class="wpuf-2fa-issue-disable-code wpuf-inline-flex wpuf-items-center wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700 wpuf-bg-white wpuf-border wpuf-border-gray-300 wpuf-rounded-md hover:wpuf-bg-gray-50 wpuf-hidden">
                                <?php esc_html_e( 'Send code', 'wp-user-frontend' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button"
                        class="wpuf-2fa-method-disable wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-red-700 wpuf-bg-white wpuf-border wpuf-border-red-200 wpuf-rounded-md hover:wpuf-bg-red-50 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-red-500 focus:wpuf-ring-offset-2">
                    <?php esc_html_e( 'Disable 2FA', 'wp-user-frontend' ); ?>
                </button>

                <p class="wpuf-2fa-message wpuf-mt-3 wpuf-mb-0 wpuf-text-sm"></p>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
