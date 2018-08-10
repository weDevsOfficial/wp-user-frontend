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

    if ( isset($_GET['success']) && "yes" == $_GET['success'] ) {
        echo "<div class='wpuf-success' style='text-align:center'>" . __( 'Registration has been successful!', 'wp-user-frontend' ) ."</div>";
    }
    ?>

    <?php wpuf()->registration->show_errors(); ?>
    <?php wpuf()->registration->show_messages(); ?>

    <form name="registrationform" class="wpuf-registration-form" id="registrationform" action="<?php echo $action_url; ?>" method="post">

        <ul class="wpuf-form form-label-above">
            <li>
                <div class="wpuf-label"><?php _e( 'Name', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <div class="wpuf-name-field-wrap format-first-last">
                        <div class="wpuf-name-field-first-name">
                            <input type="text" name="reg_fname" id="wpuf-user_fname" class="input" value="<?php echo wpuf()->registration->get_posted_value( 'reg_fname' ); ?>" size="" />
                            <label class="wpuf-form-sub-label"><?php _e( 'First', 'wp-user-frontend' ); ?></label>
                        </div>

                        <div class="wpuf-name-field-last-name">
                            <input type="text" name="reg_lname" id="wpuf-user_lname" class="input" value="<?php echo wpuf()->registration->get_posted_value( 'reg_lname' ); ?>" size="16" />
                            <label class="wpuf-form-sub-label"><?php _e( 'Last', 'wp-user-frontend' ); ?></label>
                        </div>
                    </div>
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'Email', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" name="reg_email" id="wpuf-user_email" class="input" value="<?php echo wpuf()->registration->get_posted_value( 'reg_email' ); ?>" size="40">
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'Username', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="text" name="log" id="wpuf-user_login" class="input" value="<?php echo wpuf()->registration->get_posted_value( 'log' ); ?>" size="40" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'Password', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="password" name="pwd1" id="wpuf-user_pass1" class="input" value="" size="40" />
                </div>
            </li>

            <li>
                <div class="wpuf-label"><?php _e( 'Confirm Password', 'wp-user-frontend' ); ?> <span class="required">*</span></div>
                <div class="wpuf-fields">
                    <input type="password" name="pwd2" id="wpuf-user_pass2" class="input" value="" size="40" />
                </div>
            </li>

            <li class="wpuf-submit">
                <input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Register', 'wp-user-frontend' ); ?>" />
                <input type="hidden" name="urhidden" value=" <?php echo $userrole; ?>" />
                <input type="hidden" name="redirect_to" value="<?php echo wpuf()->registration->get_posted_value( 'redirect_to' ); ?>" />
                <input type="hidden" name="wpuf_registration" value="true" />
                <input type="hidden" name="action" value="registration" />

                <?php wp_nonce_field( 'wpuf_registration_action' ); ?>
            </li>

            <li>
                <?php echo wpuf()->login->get_action_links( array( 'register' => false ) ); ?>
            </li>

            <?php do_action( 'wpuf_reg_form_bottom' ); ?>

        </ul>
    </form>
</div>
