<?php
/**
 * Account → Security tab
 *
 * Locals:
 * @var array  $sections        All registered tabs.
 * @var string $current_section Active tab slug.
 * @var bool   $is_enrolled     Whether the current user has TOTP enrolled.
 * @var int|null $enrolled_at   Unix timestamp of enrollment, if enrolled.
 * @var string $totp_label      Human label for the Authenticator method.
 * @var string $nonce           Nonce for AJAX enrollment endpoints.
 * @var string $ajax_url        admin-ajax.php URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wpuf-space-y-6" id="wpuf-2fa-security"
    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
    data-nonce="<?php echo esc_attr( $nonce ); ?>">

    <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6">
        <h3 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-2">
            <?php esc_html_e( 'Two-Factor Authentication', 'wp-user-frontend' ); ?>
        </h3>
        <p class="wpuf-text-gray-600 wpuf-mb-0">
            <?php esc_html_e( 'Add a second verification step to your account. After entering your password you will need to enter a code from your authenticator app.', 'wp-user-frontend' ); ?>
        </p>
        <p class="wpuf-text-gray-500 wpuf-text-sm wpuf-mt-3 wpuf-mb-0">
            <?php esc_html_e( 'Two-factor authentication protects logins through this site\'s login form. Administrators logging in via /wp-admin/ are not challenged.', 'wp-user-frontend' ); ?>
        </p>
    </div>

    <?php if ( ! $is_enrolled ) : ?>

        <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6"
            data-2fa-state="not-enrolled">
            <div class="wpuf-flex wpuf-items-start wpuf-justify-between wpuf-gap-4">
                <div>
                    <h4 class="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-1">
                        <?php echo esc_html( $totp_label ); ?>
                    </h4>
                    <p class="wpuf-text-gray-600 wpuf-mb-0 wpuf-text-sm">
                        <?php esc_html_e( 'Use Google Authenticator, 1Password, Authy, Microsoft Authenticator, or any RFC 6238 app.', 'wp-user-frontend' ); ?>
                    </p>
                </div>
                <button type="button"
                        class="wpuf-2fa-totp-start wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-bg-emerald-600 wpuf-border wpuf-border-transparent wpuf-rounded-md hover:wpuf-bg-emerald-700 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-emerald-500 focus:wpuf-ring-offset-2 wpuf-shrink-0">
                    <?php esc_html_e( 'Set up', 'wp-user-frontend' ); ?>
                </button>
            </div>

            <div class="wpuf-2fa-totp-enrollment wpuf-hidden wpuf-mt-6 wpuf-pt-6 wpuf-border-t wpuf-border-gray-200">
                <p class="wpuf-text-gray-700 wpuf-text-sm wpuf-mb-4">
                    <?php esc_html_e( 'Scan this QR code with your authenticator app, or type the key manually.', 'wp-user-frontend' ); ?>
                </p>

                <div class="wpuf-flex wpuf-flex-wrap wpuf-gap-6 wpuf-items-start wpuf-mb-5">
                    <div class="wpuf-2fa-qr-target"></div>
                    <div class="wpuf-flex-1 wpuf-min-w-0">
                        <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">
                            <?php esc_html_e( 'Manual entry key', 'wp-user-frontend' ); ?>
                        </label>
                        <input type="text" readonly
                                class="wpuf-2fa-secret-display wpuf-font-mono wpuf-text-sm wpuf-w-full wpuf-max-w-xs wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-gray-50 wpuf-text-gray-700 wpuf-px-3 wpuf-py-2"
                                value="" />
                    </div>
                </div>

                <label class="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-1">
                    <?php esc_html_e( 'Verification code', 'wp-user-frontend' ); ?>
                </label>
                <input type="text"
                        class="wpuf-2fa-totp-code wpuf-w-40 wpuf-font-mono wpuf-text-base wpuf-tracking-widest wpuf-text-center wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-white wpuf-text-gray-900 wpuf-px-3 wpuf-py-2 focus:wpuf-border-emerald-500 focus:wpuf-ring-1 focus:wpuf-ring-emerald-500"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        pattern="[0-9]{6}" />

                <div class="wpuf-mt-5">
                    <button type="button"
                            class="wpuf-2fa-totp-confirm wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-white wpuf-bg-emerald-600 wpuf-border wpuf-border-transparent wpuf-rounded-md hover:wpuf-bg-emerald-700 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-emerald-500 focus:wpuf-ring-offset-2">
                        <?php esc_html_e( 'Verify and enable', 'wp-user-frontend' ); ?>
                    </button>
                </div>

                <p class="wpuf-2fa-message wpuf-mt-3 wpuf-mb-0 wpuf-text-sm"></p>
            </div>
        </div>

    <?php else : ?>

        <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6"
            data-2fa-state="enrolled">
            <div class="wpuf-flex wpuf-items-center wpuf-flex-wrap wpuf-gap-3 wpuf-mb-2">
                <h4 class="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-mt-0 wpuf-mb-0">
                    <?php echo esc_html( $totp_label ); ?>
                </h4>
                <span class="wpuf-inline-flex wpuf-items-center wpuf-bg-green-50 wpuf-text-green-700 wpuf-text-xs wpuf-font-medium wpuf-px-2 wpuf-py-0.5 wpuf-rounded-full">
                    <?php esc_html_e( 'Active', 'wp-user-frontend' ); ?>
                </span>
            </div>
            <p class="wpuf-text-gray-500 wpuf-text-sm wpuf-mt-0 wpuf-mb-5">
                <?php
                printf(
                    /* translators: %s: human-readable enrollment date */
                    esc_html__( 'Enrolled on %s.', 'wp-user-frontend' ),
                    esc_html( wp_date( get_option( 'date_format' ), $enrolled_at ) )
                );
                ?>
            </p>

            <div class="wpuf-2fa-disable-form wpuf-pt-5 wpuf-border-t wpuf-border-gray-200">
                <p class="wpuf-text-gray-600 wpuf-text-sm wpuf-mb-4">
                    <?php esc_html_e( 'To disable two-factor authentication, confirm with your password and a code from your authenticator app.', 'wp-user-frontend' ); ?>
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
                        <input type="text"
                                class="wpuf-2fa-disable-code wpuf-w-full wpuf-font-mono wpuf-tracking-widest wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-bg-white wpuf-text-gray-900 wpuf-px-3 wpuf-py-2 focus:wpuf-border-emerald-500 focus:wpuf-ring-1 focus:wpuf-ring-emerald-500"
                                maxlength="6"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                pattern="[0-9]{6}" />
                    </div>
                </div>

                <button type="button"
                        class="wpuf-2fa-disable-submit wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-red-700 wpuf-bg-white wpuf-border wpuf-border-red-200 wpuf-rounded-md hover:wpuf-bg-red-50 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-red-500 focus:wpuf-ring-offset-2">
                    <?php esc_html_e( 'Disable 2FA', 'wp-user-frontend' ); ?>
                </button>

                <p class="wpuf-2fa-message wpuf-mt-3 wpuf-mb-0 wpuf-text-sm"></p>
            </div>
        </div>

    <?php endif; ?>
</div>
