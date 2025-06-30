<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
WPUF will always look in your theme's directory first, before using this default template.
*/
use WeDevs\Wpuf\Free\Simple_Login;
?>
<div class="login" id="wpuf-login-form">

    <?php
        wpuf()->frontend->simple_login->show_errors();
        wpuf()->frontend->simple_login->show_messages();
  
        $eye_icon_src = file_exists( WPUF_ROOT . '/assets/images/eye.svg' ) ? WPUF_ASSET_URI . '/images/eye.svg' : '';
    ?>

    <form name="resetpasswordform" id="resetpasswordform" action="" method="post">
        <p>
            <label for="wpuf-pass1"><?php esc_html_e( 'New password', 'wp-user-frontend' ); ?></label>
            <div class="wpuf-fields-inline" style="position: relative; width: fit-content">
                <input name="pass1" id="wpuf-pass1" class="input" size="20" value="" type="password" autocomplete="off" />
                <img class="wpuf-eye" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
            </div>
        </p>

        <p>
            <label for="wpuf-pass2"><?php esc_html_e( 'Confirm new password', 'wp-user-frontend' ); ?></label>
            <div class="wpuf-fields-inline" style="position: relative; width: fit-content">
                <input name="pass2" id="wpuf-pass2" class="input" size="20" value="" type="password" autocomplete="off" />
                <img class="wpuf-eye" src="<?php echo esc_url( $eye_icon_src ); ?>" alt="">
            </div>
        </p>

        <?php do_action( 'resetpassword_form' ); ?>

        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Reset Password', 'wp-user-frontend' ); ?>" />
            <input type="hidden" name="key" value="<?php echo esc_attr( Simple_Login::get_posted_value( 'key' ) ); ?>" />
            <input type="hidden" name="login" id="user_login" value="<?php echo esc_attr( Simple_Login::get_posted_value( 'login' ) ); ?>" />
            <input type="hidden" name="wpuf_reset_password" value="true" />
        </p>

        <?php wp_nonce_field( 'wpuf_reset_pass' ); ?>
    </form>
</div>
