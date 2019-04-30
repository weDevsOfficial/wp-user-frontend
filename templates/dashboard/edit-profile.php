<?php

global $current_user;
ob_start();

?>

<form class="wpuf-form wpuf-update-profile-form" action="" method="post">

    <div style="display: none;" class="wpuf-success"><?php _e( 'Profile updated successfully!', 'wp-user-frontend' ); ?></div>
    <div style="display: none;" class="wpuf-error"><?php _e( 'Something went wrong!', 'wp-user-frontend' ); ?></div>
    <ul class="wpuf-form form-label-above">
        <li class="wpuf-el form-row form-row-first">
            <div class="wpuf-label" >
                <label for="first_name"><?php _e( 'First Name ', 'wp-user-frontend' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo $current_user->first_name; ?>" required>
            </div>
        </li>
        <li class="wpuf-el form-row-last">
            <div class="wpuf-label" >
                <label for="last_name"><?php _e('Last Name ', 'wp-user-frontend' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo $current_user->last_name; ?>" required>
            </div>
        </li>
        <div class="clear"></div>

        <li class="wpuf-el form-row">
            <div class="wpuf-label" >
                <label for="email"><?php _e( 'Email Address ', 'wp-user-frontend' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="email" class="input-text" name="email" id="email" value="<?php echo $current_user->user_email; ?>" required>
            </div>
        </li>
        <div class="clear"></div>

        <li class="wpuf-el">
            <div class="wpuf-label" >
                <label for="current_password"><?php _e( 'Current Password', 'wp-user-frontend' ); ?></label>
            </div>
            <div class="wpuf-fields" >
                <input type="password" class="input-text" name="current_password" id="current_password" size="16" value="" autocomplete="off" />
            </div>
            <span class="wpuf-help"><?php _e( 'Leave this field empty to keep your password unchanged.', 'wp-user-frontend' ); ?></span>
        </li>
        <div class="clear"></div>

        <li class="wpuf-el">
            <div class="wpuf-label" >
                <label for="pass1"><?php _e( 'New Password', 'wp-user-frontend' ); ?></label>
            </div>
            <div class="wpuf-fields" >
                <input type="password" class="input-text" name="pass1" id="pass1" size="16" value="" autocomplete="off" />
            </div>
        </li>
        <div class="clear"></div>

        <li class="wpuf-el">
            <div class="wpuf-label" >
                <label for="pass2"><?php _e( 'Confirm New Password', 'wp-user-frontend' ); ?></label>
            </div>
            <div class="wpuf-fields" >
                <input type="password" class="input-text" name="pass2" id="pass2" size="16" value="" autocomplete="off" />
            </div>

            <span style="display: block; margin-top:20px" class="pass-strength-result" id="pass-strength-result"><?php _e( 'Strength indicator', 'wp-user-frontend' ); ?></span>
            <script src="<?php echo includes_url(); ?>/js/zxcvbn.min.js"></script>
            <script src="<?php echo admin_url(); ?>/js/password-strength-meter.js"></script>
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
        </li>
        <div class="clear"></div>

        <li class="wpuf-submit">
            <?php wp_nonce_field( 'wpuf-account-update-profile' ); ?>
            <input type="hidden" name="action" value="wpuf_account_update_profile">
            <button type="submit" name="update_profile" id="wpuf-account-update-profile"><?php _e( 'Update Profile', 'wp-user-frontend' ); ?></button>
        </li>
    </ul>

</form>

<?php
    $output = ob_get_clean();

    echo apply_filters( 'wpuf_account_edit_profile_content', $output );
?>
