<?php

class WPUF_form_element extends WPUF_Pro_Prompt {

    public static function add_form_custom_buttons() {
        $title = esc_attr( __( 'Click to add to the editor', 'wpuf' ) );
        ?>
        <button class="button" data-name="custom_image" data-type="image" title="<?php echo $title; ?>"><?php _e( 'Image Upload', 'wpuf' ); ?></button>

        <?php self::get_pro_prompt(); ?>
        <button class="button" disabled data-type="repeat" title="<?php echo $title; ?>"><?php _e( 'Repeat Field', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="date" title="<?php echo $title; ?>"><?php _e( 'Date', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="file" title="<?php echo $title; ?>"><?php _e( 'File Upload', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="map" title="<?php echo $title; ?>"><?php _e( 'Google Maps', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="select" title="<?php echo $title; ?>"><?php _e( 'Country List', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="text" title="<?php echo $title; ?>"><?php _e( 'Numeric Field', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="text" title="<?php echo $title; ?>"><?php _e( 'Address Field', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="text" title="<?php echo $title; ?>"><?php _e( 'Step Start', 'wpuf' ); ?></button>
    <?php
    }

    /**
     * add formbuilder's button in Others section
     */
    public static function add_form_other_buttons() {
        $title = esc_attr( __( 'Click to add to the editor', 'wpuf' ) );

        self::get_pro_prompt();
        ?>
        <button class="button" disabled data-type="captcha" title="<?php echo $title; ?>"><?php _e( 'reCaptcha', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="rscaptcha" title="<?php echo $title; ?>"><?php _e( 'Really Simple Captcha', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="action" title="<?php echo $title; ?>"><?php _e( 'Action Hook', 'wpuf' ); ?></button>
        <button class="button" disabled data-type="action" title="<?php echo $title; ?>"><?php _e( 'Term &amp; Conditions', 'wpuf' ); ?></button>
    <?php
    }

    /**
     * Render form expiration tab
     */
    public static function render_form_expiration_tab() {
        global $post;

        $is_post_exp_selected         = 'checked';
        $time_value                   = 1;
        $time_type                    = 'day';
        $expired_post_status          = 'draft';
        $is_enable_mail_after_expired = 'checked';
        $post_expiration_message      = '';

        self::get_pro_prompt();
        ?>
        <div id="wpuf-pro-content">
            <table class="form-table">
                <tr>
                    <th><?php _e( 'Post Expiration', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input disabled type="checkbox" id="wpuf-enable_post_expiration" name="" value="on" <?php echo $is_post_exp_selected;?> />
                            <?php _e( 'Enable Post Expiration', 'wpuf' ); ?>
                        </label>
                    </td>
                </tr>
                <tr class="wpuf_expiration_field">
                    <th><?php _e( 'Post Expiration Time', 'wpuf' ); ?></th>
                    <td>
                        <?php
                        $timeType_array = array(
                            'year'  => 100,
                            'month' => 12,
                            'day'   => 30
                        );
                        ?>
                        <select disabled name="" id="wpuf-expiration_time_value">
                            <?php
                            for( $i = 1; $i <= $timeType_array[$time_type]; $i++ ){
                                ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $time_value?'selected':''; ?> ><?php echo $i;?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <select disabled name="" id="wpuf-expiration_time_type">
                            <?php
                            foreach( $timeType_array as $each_time_type=>$each_time_type_val ){
                                ?>
                                <option value="<?php echo $each_time_type;?>" <?php echo $each_time_type==$time_type?'selected':''; ?> ><?php echo ucfirst( $each_time_type ); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf_expiration_field">
                    <th>
                        Post Status :
                    </th>
                    <td>
                        <?php $post_statuses = get_post_statuses();
                        ?>
                        <select disabled name="" id="wpuf-expired_post_status">
                            <?php
                            foreach( $post_statuses as $post_status => $text ){
                                ?>
                                <option value="<?php echo $post_status ?>" <?php echo ( $expired_post_status == $post_status )?'selected':''; ?> ><?php echo $text;?></option>
                            <?php
                            }
                            ?>
                        </select>
                        <p class="description"><?php echo _( 'Status of post after post expiration time is over ' ); ?></p>

                    </td>
                </tr>
                <tr class="wpuf_expiration_field">
                    <th>
                        Send Mail :
                    </th>
                    <td>
                        <label>
                            <input disabled type="checkbox" name="" value="on" <?php echo $is_enable_mail_after_expired;?> />
                            <?php echo _( 'Send Email to Author After Exceeding Post Expiration Time' );?>
                        </label>
                    </td>
                </tr>
                <tr class="wpuf_expiration_field">
                    <th>Post Expiration Message</th>
                    <td>
                        <textarea disabled name="" id="wpuf-post_expiration_message" cols="50" rows="5"><?php echo $post_expiration_message; ?></textarea>
                    </td>
                </tr>
            </table>
        </div>

    <?php
    }

    /**
     * Add form settings content
     */
    public static function add_form_settings_content( $form_settings, $post ) {
        ?>
        <tr>
            <td colspan="2"><?php self::get_pro_prompt(); ?></td>
        </tr>
        <tr class="wpuf_enable_multistep_section wpuf-pro-content">
            <th><?php _e( 'Enable Multistep', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input disabled type="checkbox" name="" value="yes" checked />
                    <?php _e( 'Enable Multistep', 'wpuf' ); ?>
                </label>

                <p class="description"><?php echo __( 'If checked, form will be displayed in frontend in multiple steps', 'wpuf' ); ?></p>
            </td>
        </tr>
        <tr class="wpuf_multistep_progress_type wpuf-pro-content">
            <th><?php _e( 'Multistep Progressbar Type', 'wpuf' ); ?></th>
            <td>
                <label>
                    <select disabled name="">
                        <option value="progressive" selected >Progressive</option>
                        <option value="step_by_step" >Step by Step</option>
                    </select>
                </label>


                <p class="description"><?php echo __( 'Choose how you want the progressbar', 'wpuf' ); ?></p>
            </td>
        </tr>
    <?php
    }

    /**
     * Add content to post notification section
     */
    public static function add_post_notification_content() {

        global $post;

        $new_mail_body  = "Hi Admin,\r\n";
        $new_mail_body  .= "A new post has been created in your site %sitename% (%siteurl%).\r\n\r\n";

        $edit_mail_body = "Hi Admin,\r\n";
        $edit_mail_body .= "The post \"%post_title%\" has been updated.\r\n\r\n";

        $mail_body      = "Here is the details:\r\n";
        $mail_body      .= "Post Title: %post_title%\r\n";
        $mail_body      .= "Content: %post_content%\r\n";
        $mail_body      .= "Author: %author%\r\n";
        $mail_body      .= "Post URL: %permalink%\r\n";
        $mail_body      .= "Edit URL: %editlink%";

        $form_settings    = wpuf_get_form_settings( $post->ID );

        $new_notificaton  = 'on';
        $new_to           = get_option( 'admin_email' );
        $new_subject      = __( 'New post created', 'wpuf' );
        $new_body         = $new_mail_body . $mail_body;

        $edit_notificaton = 'off';
        $edit_to          = get_option( 'admin_email' );
        $edit_subject     = __( 'A post has been edited', 'wpuf' );
        $edit_body        = $edit_mail_body . $mail_body;
        ?>

        <?php self::get_pro_prompt(); ?>
        <div id="wpuf-pro-content">
            <h3><?php _e( 'New Post Notificatoin', 'wpuf' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e( 'Notification', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input disabled type="checkbox" name="" value="on"<?php checked( $new_notificaton, 'on' ); ?>>
                            <input type="hidden" name="wpuf_settings[notification][new]" value="on">
                            <?php _e( 'Enable post notification', 'wpuf' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'To', 'wpuf' ); ?></th>
                    <td>
                        <input disabled type="text" name="" class="regular-text" value="<?php echo esc_attr( $new_to ) ?>">
                        <input type="hidden" name="wpuf_settings[notification][new_to]" value="<?php echo esc_attr( $new_to ) ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Subject', 'wpuf' ); ?></th>
                    <td><input disabled type="text" name="" class="regular-text" value="<?php echo esc_attr( $new_subject ) ?>"></td>
                    <input type="hidden" name="wpuf_settings[notification][new_subject]" value="<?php echo esc_attr( $new_subject ) ?>">
                </tr>

                <tr>
                    <th><?php _e( 'Message', 'wpuf' ); ?></th>
                    <td>
                        <textarea disabled rows="6" cols="60" name=""><?php echo esc_textarea( $new_body ) ?></textarea>
                        <input type="hidden" name="wpuf_settings[notification][new_body]" value="<?php echo esc_attr( $new_body ) ?>">
                    </td>
                </tr>
            </table>

            <h3><?php _e( 'Update Post Notificatoin', 'wpuf' ); ?></h3>

            <table class="form-table">
                <tr>
                    <th><?php _e( 'Notification', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input disabled type="checkbox" name="" value="on"<?php checked( $edit_notificaton, 'on' ); ?>>
                            <input type="hidden" name="wpuf_settings[notification][edit]" value="off">
                            <?php _e( 'Enable post notification', 'wpuf' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'To', 'wpuf' ); ?></th>
                    <td><input disabled type="text" name="" class="regular-text" value="<?php echo esc_attr( $edit_to ) ?>"></td>
                </tr>

                <tr>
                    <th><?php _e( 'Subject', 'wpuf' ); ?></th>
                    <td><input disabled type="text" name="" class="regular-text" value="<?php echo esc_attr( $edit_subject ) ?>"></td>
                </tr>

                <tr>
                    <th><?php _e( 'Message', 'wpuf' ); ?></th>
                    <td>
                        <textarea disabled rows="6" cols="60" name=""><?php echo esc_textarea( $edit_body ) ?></textarea>
                    </td>
                </tr>
            </table>

            <h3><?php _e( 'You may use in message:', 'wpuf' ); ?></h3>
            <p>
                <code>%post_title%</code>, <code>%post_content%</code>, <code>%post_excerpt%</code>, <code>%tags%</code>, <code>%category%</code>,
                <code>%author%</code>, <code>%author_email%</code>, <code>%author_bio%</code>, <code>%sitename%</code>, <code>%siteurl%</code>, <code>%permalink%</code>, <code>%editlink%</code>
                <br><code>%custom_{NAME_OF_CUSTOM_FIELD}%</code> e.g: <code>%custom_website_url%</code> for <code>website_url</code> meta field
            </p>
        </div>

    <?php

    }

    /**
     * Render registration form
     */
    public static function render_registration_form() {

        global $post, $pagenow, $form_inputs;

        $form_inputs = wpuf_get_form_fields( $post->ID );

        self::get_pro_prompt();

        ?>
        <div style="margin-bottom: 10px">
            <button class="button wpuf-collapse"><?php _e( 'Toggle All', 'wpuf' ); ?></button>
        </div>

        <div class="wpuf-updated">
            <p><?php _e( 'Click on a form element to add to the editor', 'wpuf' ); ?></p>
        </div>

        <ul id="wpuf-form-editor" class="wpuf-form-editor unstyled">

            <?php

            if ($form_inputs) {
                $count = 0;
                foreach ($form_inputs as $order => $input_field) {
                    $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                    WPUF_Admin_Template_Profile::$input_field['template']( $count, $name, $input_field );

                    $count++;
                }
            }
            ?>
        </ul>
        <?php

    }

    /**
     * Render registration settings
     */
    public static function render_registration_settings() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $email_verification = 'no';
        $role_selected      = 'subscriber';
        $redirect_to        = 'post';
        $message            = __( 'Registration successful', 'wpuf' );
        $update_message     = __( 'Profile updated successfully', 'wpuf' );
        $page_id            = 0;
        $url                = '';
        $submit_text        = __( 'Register', 'wpuf' );
        $update_text        = __( 'Update Profile', 'wpuf' );
        ?>
        <tr>
            <td colspan="2">
                <?php self::get_pro_prompt(); ?>
            </td>
        </tr>
        <tr class="wpuf-post-type">
            <th><?php _e( 'Enable Email Verfication', 'wpuf' ); ?></th>
            <td>
                <input type="hidden" name="" value="no">
                <input disabled type="checkbox" id="wpuf-enable_email_verification" name="" value="yes" <?php checked( $email_verification, 'yes' ); ?> > <label for="wpuf-enable_email_verification">Enable Email Verification</label>
            </td>
        </tr>

        <tr class="wpuf-post-type">
            <th><?php _e( 'New User Role', 'wpuf' ); ?></th>
            <td>
                <select disabled name="">
                    <?php
                    $user_roles = wpuf_get_user_roles();
                    foreach ( $user_roles as $role => $label ) {
                        printf('<option value="%s"%s>%s</option>', $role, selected( $role_selected, $role, false ), $label );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-redirect-to">
            <th><?php _e( 'Redirect To', 'wpuf' ); ?></th>
            <td>
                <select disabled name="">
                    <?php
                    $redirect_options = array(
                        'same' => __( 'Same Page', 'wpuf' ),
                        'page' => __( 'To a page', 'wpuf' ),
                        'url' => __( 'To a custom URL', 'wpuf' )
                    );

                    foreach ( $redirect_options as $to => $label ) {
                        printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                    }
                    ?>
                </select>
                <div class="description">
                    <?php _e( 'After successfull submit, where the page will redirect to', 'wpuf' ) ?>
                </div>
            </td>
        </tr>

        <tr class="wpuf-same-page">
            <th><?php _e( 'Registration success message', 'wpuf' ); ?></th>
            <td>
                <textarea disabled rows="3" cols="40" name=""><?php echo esc_textarea( $message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-same-page">
            <th><?php _e( 'Update profile message', 'wpuf' ); ?></th>
            <td>
                <textarea disabled rows="3" cols="40" name=""><?php echo esc_textarea( $update_message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-page-id">
            <th><?php _e( 'Page', 'wpuf' ); ?></th>
            <td>
                <select disabled name="">
                    <?php
                    $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                    foreach ($pages as $page) {
                        printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-url">
            <th><?php _e( 'Custom URL', 'wpuf' ); ?></th>
            <td>
                <input disabled type="url" name="" value="<?php echo esc_attr( $url ); ?>">
            </td>
        </tr>

        <tr class="wpuf-submit-text">
            <th><?php _e( 'Submit Button text', 'wpuf' ); ?></th>
            <td>
                <input disabled type="text" name="" value="<?php echo esc_attr( $submit_text ); ?>">
            </td>
        </tr>

        <tr class="wpuf-update-text">
            <th><?php _e( 'Update Button text', 'wpuf' ); ?></th>
            <td>
                <input disabled type="text" name="" value="<?php echo esc_attr( $update_text ); ?>">
            </td>
        </tr>
    <?php
    }

    /**
     * Checks what the post type is
     */
    public static function check_post_type( $post, $update ) {

        if( get_post_type( $post->ID ) == 'wpuf_profile' && $update ){

            wp_redirect($_SERVER["HTTP_REFERER"]);
            die();
        }
    }

    /**
     * Render custom taxonomies
     */
    public static function render_custom_taxonomies_element() {
        self::get_pro_prompt();
    }

    /**
     * Render conditional logic
     */
    public static function render_conditional_field( $field_id, $con_fields, $obj ) {
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Conditional Logic', 'wpuf' ); ?></label>

            <div class="wpuf-form-sub-fields">
                <label><input type="radio" name="" disabled class="wpuf-conditional-enable" value="yes"> <?php _e( 'Yes', 'wpuf' ); ?></label>
                <label><input type="radio" name="" disabled class="wpuf-conditional-enable" value="no" checked> <?php _e( 'No', 'wpuf' ); ?></label>

                <label class="wpuf-pro-text-alert"> (<?php echo self::get_pro_prompt_text(); ?>)</label>
            </div>
        </div> <!-- .wpuf-form-rows -->
    <?php

    }

}