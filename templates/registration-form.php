<?php
/*
  If you would like to edit this file, copy it to your current theme's directory and edit it there.
  WPUF will always look in your theme's directory first, before using this default template.
 */
?>
<div class="registration" id="wpuf-registration-form">

    <?php
    $message = apply_filters( 'registration_message', '' );
    if ( ! empty( $message ) ) {
        echo $message . "\n";
    }
    ?>

    <?php wpuf()->registration->show_errors(); ?>
    <?php wpuf()->registration->show_messages(); ?>

    <form name="registrationform" class="wpuf-registration-form" id="registrationform" action="<?php echo $action_url; ?>" method="post">
        <p size="40">
            <label style="display: inline-block; width: 172px;" size="20" for="wpuf-user_reg_fname"><?php _e( 'First Name', 'wpuf' ); ?></label>
            <label style="display: inline-block; width: 172px;" size="20" for="wpuf-user_reg_lname"><?php _e( 'Last Name', 'wpuf' ); ?></label>
            <br>
            <input type="text" name="reg_fname" id="wpuf-user_fname" class="input" value="" size="16" />
            <input type="text" name="reg_lname" id="wpuf-user_lname" class="input" value="" size="16" />
        </p>
        <p>
            <label for="wpuf-user_reg_email">Email <strong>*</strong></label>
            <br>
            <input type="text" name="reg_email" id="wpuf-user_email" class="input" value="" size="40">
        </p>
        <p>
            <label for="wpuf-user_login"><?php _e( 'Username', 'wpuf' ); ?></label>
            <br>
            <input type="text" name="log" id="wpuf-user_login" class="input" value="" size="40" />
        </p>
        <p>
            <label for="wpuf-user_pass"><?php _e( 'Password', 'wpuf' ); ?></label>
            <br>
            <input type="password" name="pwd1" id="wpuf-user_pass1" class="input" value="" size="40" />
        </p>
        <p>
            <label for="wpuf-user_pass"><?php _e( 'Confirm Password', 'wpuf' ); ?></label>
            <br>
            <input type="password" name="pwd2" id="wpuf-user_pass2" class="input" value="" size="40" />
        </p>

        <?php do_action( 'registration_form' ); ?>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Register', 'wpuf' ); ?>" />
            <input type="hidden" name="urhidden" value=" <?php echo $userrole; ?>" />
            <input type="hidden" name="redirect_to" value="<?php echo wpuf()->registration->get_posted_value( 'redirect_to' ); ?>" />
            <input type="hidden" name="wpuf_registration" value="true" />
            <input type="hidden" name="action" value="registration" />

            <?php wp_nonce_field( 'wpuf_registration_action' ); ?>
        </p>
    </form>
</div>
