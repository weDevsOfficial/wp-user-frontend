<?php

ob_start();

$eye_icon_src = file_exists( WPUF_ROOT . '/assets/images/eye.svg' ) ? WPUF_ASSET_URI . '/images/eye.svg' : '';
wp_enqueue_script( 'zxcvbn' );
wp_enqueue_script( 'password-strength-meter' );
?>

<div class="wpuf-edit-profile-container">
    <!-- Page Title -->
    <div class="wpuf-bg-transparent wpuf-rounded-t-lg wpuf-mb-3">
        <h2 class="wpuf-text-gray-700 wpuf-font-bold wpuf-text-[32px] wpuf-leading-[48px] wpuf-tracking-[0.13px] wpuf-m-0">
            <?php esc_html_e( 'Change Password', 'wp-user-frontend' ); ?>
        </h2>
    </div>

    <div class="wpuf-bg-transparent wpuf-mb-[48px]">
        <p class="wpuf-text-gray-400 wpuf-font-normal wpuf-text-[18px] wpuf-leading-[24px] wpuf-tracking-[0.13px] wpuf-m-0">
            <?php esc_html_e( 'Update your account password. Choose a strong, unique password to keep your account secure.', 'wp-user-frontend' ); ?>
        </p>
    </div>

    <!-- Form Wrapper -->
    <div style="border: 1px solid #E1E5E8; border-radius: 24px; padding: 48px;">
    <form class="wpuf-form wpuf-change-password-form" action="" method="post">

        <div style="display: none;" class="wpuf-success wpuf-bg-green-50 wpuf-border wpuf-border-green-200 wpuf-text-green-800 wpuf-rounded-lg wpuf-p-4"></div>
        <div style="display: none;" class="wpuf-error wpuf-bg-red-50 wpuf-border wpuf-border-red-200 wpuf-text-red-800 wpuf-rounded-lg wpuf-p-4"></div>

        <!-- Current Password -->
        <div class="wpuf-form-group">
            <label for="wpuf_current_password" class="wpuf-form-label">
                <?php esc_html_e( 'Current Password', 'wp-user-frontend' ); ?>
                <span class="wpuf-help-tip" title="<?php esc_attr_e( 'Required to verify your identity before changing the password.', 'wp-user-frontend' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-left:4px;cursor:help;color:#9CA3AF;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </span>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="current_password"
                    id="wpuf_current_password"
                    placeholder="<?php esc_attr_e( 'Enter current password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="current-password"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
        </div>

        <!-- New Password -->
        <div class="wpuf-form-group">
            <label for="wpuf_pass1" class="wpuf-form-label">
                <?php esc_html_e( 'New Password', 'wp-user-frontend' ); ?>
                <span class="wpuf-help-tip" title="<?php esc_attr_e( 'Must be at least 8 characters. Use a mix of letters, numbers, and symbols for a stronger password.', 'wp-user-frontend' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-left:4px;cursor:help;color:#9CA3AF;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </span>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="pass1"
                    id="wpuf_pass1"
                    placeholder="<?php esc_attr_e( 'Enter new password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="new-password"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
            <span class="wpuf-password-strength" id="wpuf-cp-pass-strength-result"><?php esc_html_e( 'Strength indicator', 'wp-user-frontend' ); ?></span>
        </div>

        <!-- Confirm New Password -->
        <div class="wpuf-form-group">
            <label for="wpuf_pass2" class="wpuf-form-label">
                <?php esc_html_e( 'Confirm New Password', 'wp-user-frontend' ); ?>
                <span class="wpuf-help-tip" title="<?php esc_attr_e( 'Must match the new password entered above.', 'wp-user-frontend' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-left:4px;cursor:help;color:#9CA3AF;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </span>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="pass2"
                    id="wpuf_pass2"
                    placeholder="<?php esc_attr_e( 'Re-enter new password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="new-password"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="wpuf-form-actions">
            <?php wp_nonce_field( 'wpuf-account-change-password' ); ?>
            <input type="hidden" name="action" value="wpuf_account_change_password">
            <button
                type="submit"
                id="wpuf-change-password-submit"
                class="wpuf-btn wpuf-btn-primary"
            >
                <?php esc_html_e( 'Update Password', 'wp-user-frontend' ); ?>
            </button>
            <button
                type="button"
                class="wpuf-btn wpuf-btn-secondary"
                onclick="window.location.reload();"
            >
                <?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>
            </button>
        </div>

        <script type="text/javascript">
            jQuery(function($) {
                // Password strength meter
                function wpuf_cp_check_pass_strength() {
                    var pass1 = $('#wpuf_pass1').val(),
                        pass2 = $('#wpuf_pass2').val(),
                        strength;

                    var pwsL10n = {
                        empty:    '<?php echo esc_js( __( 'Strength indicator', 'wp-user-frontend' ) ); ?>',
                        short:    '<?php echo esc_js( __( 'Very weak', 'wp-user-frontend' ) ); ?>',
                        bad:      '<?php echo esc_js( __( 'Weak', 'wp-user-frontend' ) ); ?>',
                        good:     '<?php echo esc_js( __( 'Medium', 'wp-user-frontend' ) ); ?>',
                        strong:   '<?php echo esc_js( __( 'Strong', 'wp-user-frontend' ) ); ?>',
                        mismatch: '<?php echo esc_js( __( 'Mismatch', 'wp-user-frontend' ) ); ?>'
                    };

                    $('#wpuf-cp-pass-strength-result').removeClass('short bad good strong');

                    if ( ! pass1 ) {
                        $('#wpuf-cp-pass-strength-result').html(pwsL10n.empty);
                        return;
                    }

                    strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputBlacklist(), pass2);

                    switch (strength) {
                        case 2:
                            $('#wpuf-cp-pass-strength-result').addClass('bad').html(pwsL10n.bad);
                            break;
                        case 3:
                            $('#wpuf-cp-pass-strength-result').addClass('good').html(pwsL10n.good);
                            break;
                        case 4:
                            $('#wpuf-cp-pass-strength-result').addClass('strong').html(pwsL10n.strong);
                            break;
                        case 5:
                            $('#wpuf-cp-pass-strength-result').addClass('short').html(pwsL10n.mismatch);
                            break;
                        default:
                            $('#wpuf-cp-pass-strength-result').addClass('short').html(pwsL10n.short);
                    }
                }

                $('#wpuf_pass1').val('').on('keyup', wpuf_cp_check_pass_strength);
                $('#wpuf_pass2').val('').on('keyup', wpuf_cp_check_pass_strength);
                $('#wpuf-cp-pass-strength-result').show();

                // AJAX form submit
                $('.wpuf-change-password-form').on('submit', function(e) {
                    e.preventDefault();

                    var $form   = $(this);
                    var $btn    = $form.find('#wpuf-change-password-submit');
                    var $success = $form.find('.wpuf-success');
                    var $error   = $form.find('.wpuf-error');

                    $success.hide();
                    $error.hide();
                    $btn.prop('disabled', true);

                    $.post(
                        wpuf_frontend.ajaxurl,
                        $form.serialize(),
                        function(response) {
                            $btn.prop('disabled', false);
                            if ( response.success ) {
                                $success.text(response.data).show();
                                $form.find('input[type="password"]').val('');
                                $('#wpuf-cp-pass-strength-result').html('<?php echo esc_js( __( 'Strength indicator', 'wp-user-frontend' ) ); ?>').removeClass('short bad good strong');
                            } else {
                                $error.text(response.data).show();
                            }
                        }
                    );
                });
            });
        </script>

    </form>
    </div><!-- Close form wrapper -->
</div>

<?php
    $output = ob_get_clean();
    echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
