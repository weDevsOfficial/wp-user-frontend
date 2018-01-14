<?php
global $post;

$form_settings = wpuf_get_form_settings( $post->ID );

$guest_post             = !empty( $form_settings['guest_post'] ) ? $form_settings['guest_post'] : 'false';
$role_base              = !empty( $form_settings['role_base'] ) ? $form_settings['role_base'] : 'false';
$roles                  = !empty( $form_settings['roles'] ) ? $form_settings['roles'] : array( 'administrator' );
$guest_details          = !empty( $form_settings['guest_details'] ) ? $form_settings['guest_details'] : 'true';
$guest_email_verify     = !empty( $form_settings['guest_email_verify'] ) ? $form_settings['guest_email_verify'] : 'false';
$name_label             = !empty( $form_settings['name_label'] ) ? $form_settings['name_label'] : __( 'Name', 'wpuf' );
$email_label            = !empty( $form_settings['email_label'] ) ? $form_settings['email_label'] : __( 'Email', 'wpuf' );
$message_restrict       = !empty( $form_settings['message_restrict'] ) ? $form_settings['message_restrict'] : $restrict_message;

$schedule_form          = !empty( $form_settings['schedule_form'] ) ? $form_settings['schedule_form'] : 'false';
$schedule_start         = !empty( $form_settings['schedule_start'] ) ? $form_settings['schedule_start'] : '';
$schedule_end           = !empty( $form_settings['schedule_end'] ) ? $form_settings['schedule_end'] : '';
$form_pending_message   = !empty( $form_settings['form_pending_message'] ) ? $form_settings['form_pending_message'] : 'Form submission not started.';
$form_expired_message   = !empty( $form_settings['form_expired_message'] ) ? $form_settings['form_expired_message'] : 'Submission date expired.';

$limit_entries   = !empty( $form_settings['limit_entries'] ) ? $form_settings['limit_entries'] : 'false';
$limit_number   = !empty( $form_settings['limit_number'] ) ? $form_settings['limit_number'] : 100;
$limit_message   = !empty( $form_settings['limit_message'] ) ? $form_settings['limit_message'] : 'Form submission limit exceeded';
?>
    <table class="form-table">

        <!-- Added Submission Restriction Settings -->
        <tr>
            <th><?php _e( 'Guest Post', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[guest_post]" value="false">
                    <input type="checkbox" name="wpuf_settings[guest_post]" value="true"<?php checked( $guest_post, 'true' ); ?> />
                    <?php _e( 'Enable Guest Post', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Unregistered users will be able to submit posts', 'wpuf' ); ?>. <a href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/"><?php _e( 'Learn more about guest posting.', 'wpuf' ); ?></a></p>
            </td>
        </tr>

        <tr class="show-if-guest">
            <th>&mdash; <?php _e( 'User Details', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[guest_details]" value="false">
                    <input type="checkbox" name="wpuf_settings[guest_details]" value="true"<?php checked( $guest_details, 'true' ); ?> />
                    <?php _e( 'Require Name and Email address', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'If requires, users will be automatically registered to the site using the name and email address', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-guest show-if-details">
            <th>&mdash; &mdash; <?php _e( 'Name Label', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[name_label]" value="<?php echo esc_attr( $name_label ); ?>" />
                </label>
                <p class="description"><?php _e( 'Label text for name field', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-guest show-if-details">
            <th>&mdash; &mdash; <?php _e( 'E-Mail Label', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[email_label]" value="<?php echo esc_attr( $email_label ); ?>" />
                </label>
                <p class="description"><?php _e( 'Label text for email field', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-guest">
            <th>&mdash; <?php _e( 'Email Verification', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wpuf_settings[guest_email_verify]" value="true"<?php checked( $guest_email_verify, 'true' ); ?> />
                    <?php _e( 'Require Email Verification for Guests', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'If requires, users will be required to verify their email adress.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-not-guest">
            <th>&mdash; <?php _e( 'Role Base', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[role_base]" value="false">
                    <input type="checkbox" name="wpuf_settings[role_base]" value="true"<?php checked( $role_base, 'true' ); ?> />
                    <?php _e( 'Enable role base post', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'You can choose which role can submit posts by this form.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-not-guest show-if-roles">
            <th>&mdash; &mdash; <?php _e( 'Roles', 'wpuf' ); ?></th>
            <td>
                <?php
                foreach ( wpuf_get_user_roles() as $key => $role ) { ?>
                    <label>
                        <input type="checkbox" name="wpuf_settings[roles][]" value="<?php echo $key; ?>"
                        <?php echo in_array($key, $roles) || 'administrator' == $key ? 'checked="checked"' : '';
                        echo 'administrator' == $key ? 'disabled' : '';
                        ?> />
                        <?php echo $role; ?>
                    </label><br>
                <?php } ?>
                <input type="hidden" name="wpuf_settings[roles][]" value="administrator">
                <p class="description"><?php _e( 'Choose which roles can submit posts.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-not-guest">
            <th>&mdash; <?php _e( 'Unauthorized Message', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[message_restrict]"><?php echo esc_textarea( $message_restrict ); ?></textarea>
                <p class="description"><?php _e( 'Not logged in users will see this message', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr>
            <th><?php _e( 'Schedule form', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[schedule_form]" value="false">
                    <input type="checkbox" name="wpuf_settings[schedule_form]" value="true"<?php checked( $schedule_form, 'true' ); ?> />
                    <?php _e( 'Schedule form for a period', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Schedule for a time period the form is active.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-schedule">
            <th>&mdash; <?php _e( 'Schedule Period', 'wpuf' ); ?></th>
            <td>

                <?php _e( 'From', 'wpuf' ); ?>
                <input type="text" name="wpuf_settings[schedule_start]" id="schedule_start" value="<?php echo $schedule_start; ?>" class="datepicker">
                <!-- <datepicker name="wpuf_settings[schedule_start]"></datepicker> -->

                <?php _e( 'To', 'wpuf' ); ?>
                <input type="text" name="wpuf_settings[schedule_end]" id="schedule_end" value="<?php echo $schedule_end; ?>" class="datepicker">
                <!-- <datepicker name="wpuf_settings[schedule_end]"></datepicker> -->
            </td>
        </tr>

        <tr class="show-if-schedule">
            <th>&mdash; <?php _e( 'Form Pending Message', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[form_pending_message]"><?php echo esc_textarea( $form_pending_message ); ?></textarea>
            </td>
        </tr>

        <tr class="show-if-schedule">
            <th>&mdash; <?php _e( 'Form Expired Message', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[form_expired_message]"><?php echo esc_textarea( $form_expired_message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-limit-entries">
            <th><?php _e( 'Limit Entries', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[limit_entries]" value="false">
                    <input type="checkbox" name="wpuf_settings[limit_entries]" value="true"<?php checked( $limit_entries, 'true' ); ?> />
                    <?php _e( 'Enable form entry limit', 'wpuf' ); ?>
                </label>

                <p class="description">
                    <?php _e( 'Limit the number of entries allowed for this form', 'wpuf' ) ?>
                </p>
            </td>
        </tr>

        <tr class="show-if-limit-entries">
            <th>&mdash; <?php _e( 'Number of Entries', 'wpuf' ); ?></th>
            <td>
                <input type="number" value="<?php echo esc_attr( $limit_number ); ?>" name="wpuf_settings[limit_number]">
            </td>
        </tr>

        <tr class="show-if-limit-entries">
            <th>&mdash; <?php _e( 'Limit Reached Message', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[limit_message]"><?php echo esc_textarea( $limit_message ); ?></textarea>
            </td>
        </tr>
        <?php do_action( 'wpuf_form_submission_restriction', $form_settings, $post ); ?>
    </table>
