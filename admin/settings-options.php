<?php

/**
 * Settings Sections
 *
 * @since 1.0
 * @return array
 */
function wpuf_settings_sections() {
    $sections = array(
        array(
            'id'    => 'wpuf_general',
            'title' => __( 'General Options', 'wpuf' )
        ),
        array(
            'id'    => 'wpuf_dashboard',
            'title' => __( 'Dashboard', 'wpuf' )
        ),
        array(
            'id'    => 'wpuf_profile',
            'title' => __( 'Login / Registration', 'wpuf' )
        ),
        array(
            'id'    => 'wpuf_payment',
            'title' => __( 'Payments', 'wpuf' )
        ),
    );

    return apply_filters( 'wpuf_settings_sections', $sections );
}

function wpuf_settings_fields() {
    $pages = wpuf_get_pages();
    $users = wpuf_list_users();

    $all_currencies = wpuf_get_currencies();

    $currencies = array();
    foreach ( $all_currencies as $currency ) {
        $currencies[ $currency['currency'] ] = $currency['label'] . ' (' . $currency['symbol'] . ')';
    }

    $default_currency_symbol = wpuf_get_currency( 'symbol' );

    $settings_fields = array(
        'wpuf_general' => apply_filters( 'wpuf_options_others', array(
            array(
                'name'    => 'edit_page_id',
                'label'   => __( 'Edit Page', 'wpuf' ),
                'desc'    => __( 'Select the page where [wpuf_edit] is located', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
            array(
                'name'    => 'default_post_owner',
                'label'   => __( 'Default Post Owner', 'wpuf' ),
                'desc'    => __( 'If guest post is enabled and user details are OFF, the posts are assigned to this user', 'wpuf' ),
                'type'    => 'select',
                'options' => $users,
                'default' => '1'
            ),
            array(
                'name'    => 'admin_access',
                'label'   => __( 'Admin area access', 'wpuf' ),
                'desc'    => __( 'Allow you to block specific user role to WordPress admin area.', 'wpuf' ),
                'type'    => 'select',
                'default' => 'read',
                'options' => array(
                    'manage_options'    => __( 'Admin Only', 'wpuf' ),
                    'edit_others_posts' => __( 'Admins, Editors', 'wpuf' ),
                    'publish_posts'     => __( 'Admins, Editors, Authors', 'wpuf' ),
                    'edit_posts'        => __( 'Admins, Editors, Authors, Contributors', 'wpuf' ),
                    'read'              => __( 'Default', 'wpuf' )
                )
            ),
            array(
                'name'    => 'override_editlink',
                'label'   => __( 'Override the post edit link', 'wpuf' ),
                'desc'    => __( 'Users see the edit link in post if s/he is capable to edit the post/page. Selecting <strong>Yes</strong> will override the default WordPress edit post link in frontend', 'wpuf' ),
                'type'    => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no'  => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name'    => 'cf_show_front',
                'label'   => __( 'Custom Fields in post', 'wpuf' ),
                'desc'    => __( 'Show custom fields on post content area', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
            array(
                'name'    => 'load_script',
                'label'   => __( 'Load Scripts', 'wpuf' ),
                'desc'    => __( 'Load scripts/styles in all pages', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name'    => 'insert_photo_size',
                'label'   => __( 'Insert Photo image size', 'wpuf' ),
                'desc'    => __( 'Default image size of "<strong>Insert Photo</strong>" button in post content area', 'wpuf' ),
                'type'    => 'select',
                'options' => wpuf_get_image_sizes(),
                'default' => 'thumbnail'
            ),
            array(
                'name'  => 'insert_photo_type',
                'label' => __( 'Insert Photo image type', 'wpuf' ),
                'desc'  => __( 'Default image type of "<strong>Insert Photo</strong>" button in post content area', 'wpuf' ),
                'type'  => 'select',
                'options' => array(
                    'image' => __( 'Image only', 'wpuf' ),
                    'link'  => __( 'Image with link', 'wpuf' )
                ),
                'default' => 'link'
            ),
            array(
                'name'    => 'image_caption',
                'label'   => __( 'Enable Image Caption', 'wpuf' ),
                'desc'    => __( 'Allow users to update image/video title, caption and description', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
            array(
                'name'    => 'default_post_form',
                'label'   => __( 'Default Post Form', 'wpuf' ),
                'desc'    => __( 'Fallback form for post editing if no associated form found', 'wpuf' ),
                'type'    => 'select',
                'options' => wpuf_get_pages( 'wpuf_forms' )
            ),
            array(
                'name'  => 'recaptcha_public',
                'label' => __( 'reCAPTCHA Site Key', 'wpuf' ),
            ),
            array(
                'name'  => 'recaptcha_private',
                'label' => __( 'reCAPTCHA Secret Key', 'wpuf' ),
                'desc'  => __( '<a target="_blank" href="https://www.google.com/recaptcha/">Register here</a> to get reCaptcha Site and Secret keys.', 'wpuf' ),
            ),
            array(
                'name'  => 'custom_css',
                'label' => __( 'Custom CSS codes', 'wpuf' ),
                'desc'  => __( 'If you want to add your custom CSS code, it will be added on page header wrapped with style tag', 'wpuf' ),
                'type'  => 'textarea'
            ),
        ) ),
        'wpuf_dashboard' => apply_filters( 'wpuf_options_dashboard', array(
            array(
                'name'    => 'enable_post_edit',
                'label'   => __( 'Users can edit post?', 'wpuf' ),
                'desc'    => __( 'Users will be able to edit their own posts', 'wpuf' ),
                'type'    => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no'  => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name'    => 'enable_post_del',
                'label'   => __( 'User can delete post?', 'wpuf' ),
                'desc'    => __( 'Users will be able to delete their own posts', 'wpuf' ),
                'type'    => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no'  => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name'    => 'disable_pending_edit',
                'label'   => __( 'Pending Post Edit', 'wpuf' ),
                'desc'    => __( 'Disable post editing while post in "pending" status', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name'    => 'per_page',
                'label'   => __( 'Posts per page', 'wpuf' ),
                'desc'    => __( 'How many posts will be listed in a page', 'wpuf' ),
                'type'    => 'text',
                'default' => '10'
            ),
            array(
                'name'    => 'show_user_bio',
                'label'   => __( 'Show user bio', 'wpuf' ),
                'desc'    => __( 'Users biographical info will be shown', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name'    => 'show_post_count',
                'label'   => __( 'Show post count', 'wpuf' ),
                'desc'    => __( 'Show how many posts are created by the user', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name'  => 'show_ft_image',
                'label' => __( 'Show Featured Image', 'wpuf' ),
                'desc'  => __( 'Show featured image of the post', 'wpuf' ),
                'type'  => 'checkbox'
            ),
            array(
                'name'    => 'ft_img_size',
                'label'   => __( 'Featured Image size', 'wpuf' ),
                'type'    => 'select',
                'options' => wpuf_get_image_sizes()
            ),
             array(
                'name'  => 'un_auth_msg',
                'label' => __( 'Unauthorized Message', 'wpuf' ),
                'desc'  => __( 'Not logged in users will see this message', 'wpuf' ),
                'type'  => 'textarea'
            ),
        ) ),
        'wpuf_profile' => array(
            array(
                'name'    => 'autologin_after_registration',
                'label'   => __( 'Auto Login After Registration', 'wpuf' ),
                'desc'    => __( 'If enabled, users after registration will be logged in to the system', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
            array(
                'name'    => 'register_link_override',
                'label'   => __( 'Login/Registration override', 'wpuf' ),
                'desc'    => __( 'If enabled, default login and registration forms will be overridden by WPUF with pages below', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'off'
            ),
            array(
                'name'    => 'reg_override_page',
                'label'   => __( 'Registration Page', 'wpuf' ),
                'desc'    => __( 'Select the page you want to use as registration page override <em>(should have shortcode)</em>', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
            array(
                'name'    => 'login_page',
                'label'   => __( 'Login Page', 'wpuf' ),
                'desc'    => __( 'Select the page which contains <code>[wpuf-login]</code> shortcode', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
        ),
        'wpuf_payment' => apply_filters( 'wpuf_options_payment', array(
            array(
                'name'    => 'charge_posting',
                'label'   => __( 'Charge for posting', 'wpuf' ),
                'desc'    => __( 'Charge user for submitting a post', 'wpuf' ),
                'type'    => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no'  => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name'    => 'force_pack',
                'label'   => __( 'Force pack purchase', 'wpuf' ),
                'desc'    => __( 'When active, users must have to buy a pack for posting', 'wpuf' ),
                'type'    => 'select',
                'default' => 'no',
                'options' => array(
                    'no'  => __( 'Disable', 'wpuf' ),
                    'yes' => __( 'Enable', 'wpuf' )
                )
            ),
            array(
                'name'    => 'subscription_page',
                'label'   => __( 'Subscription Pack Page', 'wpuf' ),
                'desc'    => __( 'Select the page where <code>[wpuf_sub_pack]</code> located.', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
            array(
                'name'  => 'register_subscription',
                'label' => __( 'Subscription at registration', 'wpuf' ),
                'desc'  => __( 'Registration time redirect to subscription page', 'wpuf' ),
                'type'  => 'checkbox',
            ),
            array(
                'name'    => 'currency',
                'label'   => __( 'Currency', 'wpuf' ),
                'type'    => 'select',
                'default' => 'USD',
                'options' => $currencies
            ),
            array(
                'name'    => 'currency_position',
                'label'   => __( 'Currency Position', 'wpuf' ),
                'type'    => 'select',
                'default' => 'left',
                'options' => array(
                    'left'        => sprintf( '%1$s (%2$s99.99)', __( 'Left', 'wpuf' ), $default_currency_symbol ),
                    'right'       => sprintf( '%1$s (99.99%2$s)', __( 'Right', 'wpuf' ), $default_currency_symbol ),
                    'left_space'  => sprintf( '%1$s (%2$s 99.99)', __( 'Left with space', 'wpuf' ), $default_currency_symbol ),
                    'right_space' => sprintf( '%1$s (99.99 %2$s)', __( 'Right with space', 'wpuf' ), $default_currency_symbol ),
                )
            ),
            array(
                'name'       => 'wpuf_price_thousand_sep',
                'label'    => __( 'Thousand Separator', 'wpuf' ),
                'desc'     => __( 'This sets the thousand separator of displayed prices.', 'wpuf' ),
                'css'      => 'width:50px;',
                'default'  => ',',
                'type'     => 'text',
                'desc_tip' =>  true,
            ),
            array(
                'name'       => 'wpuf_price_decimal_sep',
                'label'    => __( 'Decimal Separator', 'wpuf' ),
                'desc'     => __( 'This sets the decimal separator of displayed prices.', 'wpuf' ),
                'default'  => '.',
                'type'     => 'text',
            ),

            array(
                'name'       => 'wpuf_price_num_decimals',
                'label'    => __( 'Number of Decimals', 'wpuf' ),
                'desc'     => __( 'This sets the number of decimal points shown in displayed prices.', 'wpuf' ),
                'default'  => '2',
                'type'     => 'number',
                'custom_attributes' => array(
                    'min'  => 0,
                    'step' => 1
                )
            ),
            array(
                'name'    => 'cost_per_post',
                'label'   => __( 'Cost', 'wpuf' ),
                'desc'    => __( 'Cost per post in - <span>' . wpuf_get_currency( 'code' ) . ' ( ' . wpuf_get_currency( 'symbol' ) . '</span> )', 'wpuf' ),
                'type'    => 'text',
                'default' => '2'
            ),
            array(
                'name'    => 'sandbox_mode',
                'label'   => __( 'Enable demo/sandbox mode', 'wpuf' ),
                'desc'    => __( 'When sandbox mode is active, all payment gateway will be used in demo mode', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name'    => 'payment_page',
                'label'   => __( 'Payment Page', 'wpuf' ),
                'desc'    => __( 'This page will be used to process payment options', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
            array(
                'name'    => 'payment_success',
                'label'   => __( 'Payment Success Page', 'wpuf' ),
                'desc'    => __( 'After payment users will be redirected here', 'wpuf' ),
                'type'    => 'select',
                'options' => $pages
            ),
            array(
                'name'    => 'active_gateways',
                'label'   => __( 'Payment Gateways', 'wpuf' ),
                'desc'    => __( 'Active payment gateways', 'wpuf' ),
                'type'    => 'multicheck',
                'options' => wpuf_get_gateways()
            )
        ) ),
    );

    return apply_filters( 'wpuf_settings_fields', $settings_fields );
}

function wpuf_settings_field_profile( $form ) {
    $user_roles = wpuf_get_user_roles();
    $forms = get_posts( array(
        'numberposts' => -1,
        'post_type'   => 'wpuf_profile'
    ) );

    $val = get_option( 'wpuf_profile', array() );
    ?>

    <p style="padding-left: 10px; font-style: italic; font-size: 13px;">
        <strong><?php _e( 'Select profile/registration forms for user roles. These forms will be used to populate extra edit profile fields in backend.', 'wpuf' ); ?></strong>
    </p>
    <table class="form-table">
        <?php
        foreach ($user_roles as $role => $name) {
            $current = isset( $val['roles'][$role] ) ? $val['roles'][$role] : '';
            ?>
            <tr valign="top">
                <th scrope="row"><?php echo $name; ?></th>
                <td>
                    <select name="wpuf_profile[roles][<?php echo $role; ?>]">
                        <option value=""><?php _e( ' - select - ', 'wpuf' ); ?></option>
                        <?php foreach ($forms as $form) { ?>
                            <option value="<?php echo $form->ID; ?>"<?php selected( $current, $form->ID ); ?>><?php echo $form->post_title; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php } ?>
    </table>
    <?php
}

add_action( 'wsa_form_bottom_wpuf_profile', 'wpuf_settings_field_profile' );
