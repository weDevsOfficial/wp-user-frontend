<?php

global $current_user;
ob_start();

$eye_icon_src = file_exists( WPUF_ROOT . '/assets/images/eye.svg' ) ? WPUF_ASSET_URI . '/images/eye.svg' : '';
wp_enqueue_script( 'zxcvbn' );
wp_enqueue_script( 'password-strength-meter' );
?>

<div class="wpuf-edit-profile-container">
    <!-- Page Title -->
    <div class="wpuf-page-header">
        <h2 class="wpuf-page-title"><?php esc_html_e( 'Account details', 'wp-user-frontend' ); ?></h2>
        <p class="wpuf-page-subtitle"><?php esc_html_e( 'The following addresses will be used on the checkout page by default.', 'wp-user-frontend' ); ?></p>
    </div>

    <form class="wpuf-edit-profile-form" action="" method="post">

        <div style="display: none;" class="wpuf-success wpuf-bg-green-50 wpuf-border wpuf-border-green-200 wpuf-text-green-800 wpuf-rounded-lg wpuf-p-4"><?php esc_html_e( 'Profile updated successfully!', 'wp-user-frontend' ); ?></div>
        <div style="display: none;" class="wpuf-error wpuf-bg-red-50 wpuf-border wpuf-border-red-200 wpuf-text-red-800 wpuf-rounded-lg wpuf-p-4"><?php esc_html_e( 'Something went wrong!', 'wp-user-frontend' ); ?></div>

        <!-- First Name -->
        <div class="wpuf-form-group">
            <label for="first_name" class="wpuf-form-label">
                <?php esc_html_e( 'First Name', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="first_name"
                id="first_name"
                placeholder="<?php esc_attr_e( 'First Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( $current_user->first_name ); ?>"
                class="wpuf-form-input"
                required
            >
        </div>

        <!-- Last Name -->
        <div class="wpuf-form-group">
            <label for="last_name" class="wpuf-form-label">
                <?php esc_html_e( 'Last Name', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="last_name"
                id="last_name"
                placeholder="<?php esc_attr_e( 'Last Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( $current_user->last_name ); ?>"
                class="wpuf-form-input"
                required
            >
        </div>

        <!-- Display Name -->
        <div class="wpuf-form-group">
            <label for="display_name" class="wpuf-form-label">
                <?php esc_html_e( 'Display Name', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="display_name"
                id="display_name"
                placeholder="<?php esc_attr_e( 'Display Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( $current_user->display_name ); ?>"
                class="wpuf-form-input"
            >
        </div>

        <!-- Current Password -->
        <div class="wpuf-form-group">
            <label for="current_password" class="wpuf-form-label">
                <?php esc_html_e( 'Current Password', 'wp-user-frontend' ); ?>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    placeholder="<?php esc_attr_e( 'Current Password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="off"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
        </div>

        <!-- New Password -->
        <div class="wpuf-form-group">
            <label for="pass1" class="wpuf-form-label">
                <?php esc_html_e( 'New Password', 'wp-user-frontend' ); ?>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="pass1"
                    id="pass1"
                    placeholder="<?php esc_attr_e( 'New Password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="off"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
        </div>

        <!-- Confirm New Password -->
        <div class="wpuf-form-group">
            <label for="pass2" class="wpuf-form-label">
                <?php esc_html_e( 'Confirm New Password', 'wp-user-frontend' ); ?>
            </label>
            <div class="wpuf-password-field">
                <input
                    type="password"
                    name="pass2"
                    id="pass2"
                    placeholder="<?php esc_attr_e( 'Confirm New Password', 'wp-user-frontend' ); ?>"
                    class="wpuf-form-input"
                    autocomplete="off"
                />
                <?php if ( $eye_icon_src ) : ?>
                    <img class="wpuf-eye wpuf-password-toggle" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
                <?php endif; ?>
            </div>
            <span class="wpuf-password-strength" id="pass-strength-result"><?php esc_html_e( 'Strength indicator', 'wp-user-frontend' ); ?></span>
        </div>

        <!-- Social Fields -->
        <div class="wpuf-form-group">
            <label for="facebook" class="wpuf-form-label">
                <?php esc_html_e( 'Facebook', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="facebook"
                id="facebook"
                placeholder="<?php esc_attr_e( 'Facebook User Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'facebook', true ) ); ?>"
                class="wpuf-form-input"
            >
        </div>

        <div class="wpuf-form-group">
            <label for="twitter" class="wpuf-form-label">
                <?php esc_html_e( 'X', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="twitter"
                id="twitter"
                placeholder="<?php esc_attr_e( 'X Handle', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'twitter', true ) ); ?>"
                class="wpuf-form-input"
            >
        </div>

        <div class="wpuf-form-group">
            <label for="linkedin" class="wpuf-form-label">
                <?php esc_html_e( 'LinkedIn', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="linkedin"
                id="linkedin"
                placeholder="<?php esc_attr_e( 'LinkedIn User Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'linkedin', true ) ); ?>"
                class="wpuf-form-input"
            >
        </div>

        <div class="wpuf-form-group">
            <label for="instagram" class="wpuf-form-label">
                <?php esc_html_e( 'Instagram', 'wp-user-frontend' ); ?>
            </label>
            <input
                type="text"
                name="instagram"
                id="instagram"
                placeholder="<?php esc_attr_e( 'Instagram User Name', 'wp-user-frontend' ); ?>"
                value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'instagram', true ) ); ?>"
                class="wpuf-form-input"
            >
        </div>

        <!-- Submit Buttons -->
        <div class="wpuf-form-actions">
            <?php wp_nonce_field( 'wpuf-account-update-profile' ); ?>
            <input type="hidden" name="action" value="wpuf_account_update_profile">
            <button
                type="submit"
                name="update_profile"
                id="wpuf-account-update-profile"
                class="wpuf-btn wpuf-btn-primary"
            >
                <?php esc_html_e( 'Update', 'wp-user-frontend' ); ?>
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
                function check_pass_strength() {
                    var pass1 = $("#pass1").val(),
                        pass2 = $("#pass2").val(),
                        strength;

                    if ( typeof pass2 === undefined ) {
                        pass2 = pass1;
                    }

                    var pwsL10n = {
                        empty: "Strength indicator",
                        short: "Very weak",
                        bad: "Weak",
                        good: "Medium",
                        strong: "Strong",
                        mismatch: "Mismatch"
                    };

                    $("#pass-strength-result").removeClass('short bad good strong');
                    if (!pass1) {
                        $("#pass-strength-result").html(pwsL10n.empty);
                        return;
                    }

                    strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputBlacklist(), pass2);

                    switch (strength) {
                        case 2:
                            $("#pass-strength-result").addClass('bad').html(pwsL10n.bad);
                            break;
                        case 3:
                            $("#pass-strength-result").addClass('good').html(pwsL10n.good);
                            break;
                        case 4:
                            $("#pass-strength-result").addClass('strong').html(pwsL10n.strong);
                            break;
                        case 5:
                            $("#pass-strength-result").addClass('short').html(pwsL10n.mismatch);
                            break;
                        default:
                            $("#pass-strength-result").addClass('short').html(pwsL10n['short']);
                    }
                }

                $("#pass1").val('').keyup(check_pass_strength);
                $("#pass2").val('').keyup(check_pass_strength);
                $("#pass-strength-result").show();
            });
        </script>

    </form>
</div>

<?php
    $output = apply_filters( 'wpuf_account_edit_profile_content', ob_get_clean() );
    echo $output; // phpcs:ignore
?>
