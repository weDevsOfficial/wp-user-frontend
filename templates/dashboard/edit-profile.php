<?php global $current_user; ?>

<form class="wpuf-form" action="" method="post">

    <p class="form-row form-row-first">
        <label for="first_name"><?php _e( 'First Name ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo $current_user->first_name; ?>">
    </p>
    <p class="form-row form-row-last">
        <label for="last_name"><?php _e('Last Name ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo $current_user->last_name; ?>">
    </p>
    <div class="clear"></div>

    <p class="form-row form-row-wide">
        <label for="email"><?php _e( 'Email Address ', 'wpuf' ); ?><span class="required">*</span></label>
        <input type="email" class="input-text" name="email" id="email" value="<?php echo $current_user->user_email; ?>">
    </p>

    <fieldset>
        <legend><?php _e( 'Password Change', 'wpuf' ); ?></legend>

        <p class="desc"><?php _e( 'If you  want to unchanged the password then just leave this section.', 'wpuf' ); ?></p>

        <p class="form-row form-row-wide">
            <label for="current_password"><?php _e( 'Current Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="current_password" id="current_password">
        </p>
        <p class="form-row form-row-wide">
            <label for="password_1"><?php _e( 'New Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="password_1" id="password_1">
        </p>
        <p class="orm-row form-row-wide">
            <label for="password_2"><?php _e( 'Confirm New Password', 'wpuf' ); ?></label>
            <input type="password" class="input-text" name="password_2" id="password_2">
        </p>
    </fieldset>
    <div class="clear"></div>

    <p>
        <?php wp_nonce_field( 'wpuf-dashboard-update-profile' ); ?>
        <input type="submit" class="button" name="update_profile" value="Update Profile">
    </p>

</form>