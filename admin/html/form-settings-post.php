<?php
global $post;

$form_settings = wpuf_get_form_settings( $post->ID );

$post_status_selected  = isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish';
$restrict_message      = __( "This page is restricted. Please Log in / Register to view this page.", 'wpuf' );

$post_type_selected    = isset( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';

$post_format_selected  = isset( $form_settings['post_format'] ) ? $form_settings['post_format'] : 0;
$default_cat           = isset( $form_settings['default_cat'] ) ? $form_settings['default_cat'] : -1;

$guest_post            = isset( $form_settings['guest_post'] ) ? $form_settings['guest_post'] : 'false';
$guest_details         = isset( $form_settings['guest_details'] ) ? $form_settings['guest_details'] : 'true';
$name_label            = isset( $form_settings['name_label'] ) ? $form_settings['name_label'] : __( 'Name' );
$email_label           = isset( $form_settings['email_label'] ) ? $form_settings['email_label'] : __( 'Email' );
$message_restrict      = isset( $form_settings['message_restrict'] ) ? $form_settings['message_restrict'] : $restrict_message;

$redirect_to           = isset( $form_settings['redirect_to'] ) ? $form_settings['redirect_to'] : 'post';
$message               = isset( $form_settings['message'] ) ? $form_settings['message'] : __( 'Post saved', 'wpuf' );
$update_message        = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Post updated successfully', 'wpuf' );
$page_id               = isset( $form_settings['page_id'] ) ? $form_settings['page_id'] : 0;
$url                   = isset( $form_settings['url'] ) ? $form_settings['url'] : '';
$comment_status        = isset( $form_settings['comment_status'] ) ? $form_settings['comment_status'] : 'open';

$submit_text           = isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Submit', 'wpuf' );
$draft_text            = isset( $form_settings['draft_text'] ) ? $form_settings['draft_text'] : __( 'Save Draft', 'wpuf' );
$preview_text          = isset( $form_settings['preview_text'] ) ? $form_settings['preview_text'] : __( 'Preview', 'wpuf' );
$draft_post            = isset( $form_settings['draft_post'] ) ? $form_settings['draft_post'] : 'false';
$subscription_disabled = isset( $form_settings['subscription_disabled'] ) ? $form_settings['subscription_disabled'] : '';

?>
    <table class="form-table">

        <tr class="">
            <th><?php _e( 'Disable Subscription', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wpuf_settings[subscription_disabled]" value="yes" <?php checked( $subscription_disabled, 'yes' ); ?> />
                    <?php _e( 'Disable Subscription', 'wpuf' ); ?>
                </label>

                <p class="description"><?php echo __( 'If checked, any subscription and pay-per-post will be disabled on the form and will take no effect.', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="wpuf-post-type">
            <th><?php _e( 'Post Type', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[post_type]">
                    <?php
                    $post_types = get_post_types();
                    unset($post_types['attachment']);
                    unset($post_types['revision']);
                    unset($post_types['nav_menu_item']);
                    unset($post_types['wpuf_forms']);
                    unset($post_types['wpuf_profile']);
                    unset($post_types['wpuf_input']);
                    unset($post_types['wpuf_subscription']);
                    unset($post_types['custom_css']);
                    unset($post_types['customize_changeset']);
                    unset($post_types['wpuf_coupon']);

                    foreach ($post_types as $post_type) {
                        printf('<option value="%s"%s>%s</option>', $post_type, selected( $post_type_selected, $post_type, false ), $post_type );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-post-status">
            <th><?php _e( 'Post Status', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[post_status]">
                    <?php
                    $statuses = get_post_statuses();
                    foreach ($statuses as $status => $label) {
                        printf('<option value="%s"%s>%s</option>', $status, selected( $post_status_selected, $status, false ), $label );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-post-fromat">
            <th><?php _e( 'Post Format', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[post_format]">
                    <option value="0"><?php _e( '- None -', 'wpuf' ); ?></option>
                    <?php
                    $post_formats = get_theme_support( 'post-formats' );

                    if ( isset($post_formats[0]) && is_array( $post_formats[0] ) ) {
                        foreach ($post_formats[0] as $format) {
                            printf('<option value="%s"%s>%s</option>', $format, selected( $post_format_selected, $format, false ), $format );
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-default-cat">
            <th><?php _e( 'Default Post Category', 'wpuf' ); ?></th>
            <td>
                <?php
                wp_dropdown_categories( array(
                    'hide_empty'       => false,
                    'hierarchical'     => true,
                    'selected'         => $default_cat,
                    'name'             => 'wpuf_settings[default_cat]',
                    'show_option_none' => __( '- None -', 'wpuf' ),
                    'taxonomy'         => ( $post_type_selected == 'product' ) ? 'product_cat' : 'category'
                ) );
                ?>
                <p class="description"><?php echo __( 'If users are not allowed to choose any category, this category will be used instead (if post type supports)', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr>
            <th><?php _e( 'Guest Post', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[guest_post]" value="false">
                    <input type="checkbox" name="wpuf_settings[guest_post]" value="true"<?php checked( $guest_post, 'true' ); ?> />
                    <?php _e( 'Enable Guest Post', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'Unregistered users will be able to submit posts', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-guest">
            <th><?php _e( 'User Details', 'wpuf' ); ?></th>
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
            <th><?php _e( 'Name Label', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[name_label]" value="<?php echo esc_attr( $name_label ); ?>" />
                </label>
                <p class="description"><?php _e( 'Label text for name field', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-guest show-if-details">
            <th><?php _e( 'E-Mail Label', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[email_label]" value="<?php echo esc_attr( $email_label ); ?>" />
                </label>
                <p class="description"><?php _e( 'Label text for email field', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="show-if-not-guest">
            <th><?php _e( 'Unauthorized Message', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[message_restrict]"><?php echo esc_textarea( $message_restrict ); ?></textarea>
                <p class="description"><?php _e( 'Not logged in users will see this message', 'wpuf' ); ?></p>
            </td>
        </tr>

        <tr class="wpuf-redirect-to">
            <th><?php _e( 'Redirect To', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[redirect_to]">
                    <?php
                    $redirect_options = array(
                        'post' => __( 'Newly created post', 'wpuf' ),
                        'same' => __( 'Same Page', 'wpuf' ),
                        'page' => __( 'To a page', 'wpuf' ),
                        'url' => __( 'To a custom URL', 'wpuf' )
                    );

                    foreach ($redirect_options as $to => $label) {
                        printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                    }
                    ?>
                </select>
                <p class="description">
                    <?php _e( 'After successfull submit, where the page will redirect to', $domain = 'default' ) ?>
                </p>
            </td>
        </tr>

        <tr class="wpuf-same-page">
            <th><?php _e( 'Message to show', 'wpuf' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[message]"><?php echo esc_textarea( $message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-page-id">
            <th><?php _e( 'Page', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[page_id]">
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
                <input type="url" name="wpuf_settings[url]" value="<?php echo esc_attr( $url ); ?>">
            </td>
        </tr>

        <tr class="wpuf-comment">
            <th><?php _e( 'Comment Status', 'wpuf' ); ?></th>
            <td>
                <select name="wpuf_settings[comment_status]">
                    <option value="open" <?php selected( $comment_status, 'open'); ?>><?php _e('Open'); ?></option>
                    <option value="closed" <?php selected( $comment_status, 'closed'); ?>><?php _e('Closed'); ?></option>
                </select>
            </td>
        </tr>

        <tr class="wpuf-submit-text">
            <th><?php _e( 'Submit Post Button text', 'wpuf' ); ?></th>
            <td>
                <input type="text" name="wpuf_settings[submit_text]" value="<?php echo esc_attr( $submit_text ); ?>">
            </td>
        </tr>

        <tr>
            <th><?php _e( 'Post Draft', 'wpuf' ); ?></th>
            <td>
                <label>
                    <input type="hidden" name="wpuf_settings[draft_post]" value="false">
                    <input type="checkbox" name="wpuf_settings[draft_post]" value="true"<?php checked( $draft_post, 'true' ); ?> />
                    <?php _e( 'Enable Saving as draft', 'wpuf' ) ?>
                </label>
                <p class="description"><?php _e( 'It will show a button to save as draft', 'wpuf' ); ?></p>
            </td>
        </tr>

        <?php do_action( 'wpuf_form_setting', $form_settings, $post ); ?>
    </table>