<?php global $current_user; ?>

<form class="wpuf-form wpuf-form-add wpuf-update-profile-form" action="" method="post">

    <div style="display: none;" class="wpuf-success"><?php _e( 'Profile updated successfully!', 'wpuf' ); ?></div>
    <div style="display: none;" class="wpuf-error"><?php _e( 'Something went wrong!', 'wpuf' ); ?></div>
    <ul class="wpuf-form form-label-above">
        <li class="wpuf-el form-row form-row-first">
            <div class="wpuf-label" >
                <label for="first_name"><?php _e( 'First Name ', 'wpuf' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo $current_user->first_name; ?>" required>
            </div>
        </li>
        <li class="wpuf-el form-row-last">
            <div class="wpuf-label" >
                <label for="last_name"><?php _e('Last Name ', 'wpuf' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo $current_user->last_name; ?>" required>
            </div>
        </li>
        <div class="clear"></div>

        <li class="wpuf-el form-row">
            <div class="wpuf-label" >
                <label for="email"><?php _e( 'Email Address ', 'wpuf' ); ?><span class="required">*</span></label>
            </div>
            <div class="wpuf-fields" >
                <input type="email" class="input-text" name="email" id="email" value="<?php echo $current_user->user_email; ?>" required>
            </div>
        </li>

        <li class="wpuf-el">
            <div class="wpuf-label" >
                <label for="current_password"><?php _e( 'Current Password', 'wpuf' ); ?></label>
            </div>
            <div class="wpuf-fields" >
                <input type="password" class="input-text" name="pass1" id="pass1" size="16" value="" autocomplete="off" />
            </div>
            <span class="wpuf-help"><?php _e( 'If you  want to unchanged the password then just leave this section.', 'wpuf' ); ?></span>
        </li>

            <span style="width: 100%;" id="pass-strength-result"><?php _e( 'Strength indicator', 'wpuf' ); ?></span>
            <script src="<?php echo site_url(); ?>/wp-includes/js/zxcvbn.min.js"></script>
            <script src="<?php echo admin_url(); ?>/js/password-strength-meter.js"></script>
            <script type="text/javascript">
                var pwsL10n = {
                    empty: "Strength indicator",
                    short: "Very weak",
                    bad: "Weak",
                    good: "Medium",
                    strong: "Strong",
                    mismatch: "Mismatch"
                };
                try {
                    convertEntities(pwsL10n);
                } catch (e) {};
            </script>
        <li class="wpuf-el">
            <div class="wpuf-label" >
                <label for="pass2"><?php _e( 'Confirm New Password', 'wpuf' ); ?></label>
            </div>
            <div class="wpuf-fields" >
                <input type="password" class="input-text" name="pass2" id="pass2" size="16" value="" autocomplete="off" />
            </div>
        </li>
        <div class="clear"></div>

        <li class="wpuf-submit">
            <?php wp_nonce_field( 'wpuf-account-update-profile' ); ?>
            <input type="hidden" name="action" value="wpuf_account_update_profile">
            <input type="submit" name="update_profile"  id="wpuf-account-update-profile" value="Update Profile">
        </li>
    </ul>

</form>
