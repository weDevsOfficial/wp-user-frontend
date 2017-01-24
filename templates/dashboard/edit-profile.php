<?php global $current_user; ?>

<form class="wpuf-form wpuf-update-profile-form" action="" method="post">

    <div style="display: none;" class="wpuf-success"><?php _e( 'Profile updated successfully!', 'wpuf' ); ?></div>
    <div style="display: none;" class="wpuf-error"><?php _e( 'Something went wrong!', 'wpuf' ); ?></div>

    <p class="form-row form-row-first">
        <label for="first_name"><?php _e( 'First Name ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo $current_user->first_name; ?>" required>
    </p>
    <p class="form-row form-row-last">
        <label for="last_name"><?php _e('Last Name ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo $current_user->last_name; ?>" required>
    </p>
    <div class="clear"></div>

    <p class="form-row">
        <label for="email"><?php _e( 'Email Address ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="email" class="input-text" name="email" id="email" value="<?php echo $current_user->user_email; ?>" required>
    </p>

    <fieldset>
        <legend><?php _e( 'Password Change', 'wpuf' ); ?></legend>

        <p class="desc"><?php _e( 'If you  want to unchanged the password then just leave this section.', 'wpuf' ); ?></p>

        <p class="form-row">
            <label for="current_password"><?php _e( 'Current Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="current_password" id="current_password">
        </p>
        <p class="form-row">
            <label for="pass1"><?php _e( 'New Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="pass1" id="pass1" size="16" value="" autocomplete="off" />

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
        </p>
        <p class="form-row">
            <label for="pass2"><?php _e( 'Confirm New Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="pass2" id="pass2" size="16" value="" autocomplete="off" />
        </p>
    </fieldset>
    <div class="clear"></div>

    <p>
        <?php wp_nonce_field( 'wpuf-account-update-profile' ); ?>
        <input type="hidden" name="action" value="wpuf_account_update_profile">
        <button class="button" name="update_profile" id="wpuf-account-update-profile"><?php _e( 'Update Profile', 'wpuf' ); ?></button>
    </p>

</form>
