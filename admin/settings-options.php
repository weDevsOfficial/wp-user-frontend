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
        array(
            'id'    => 'wpuf_support',
            'title' => __( 'Support', 'wpuf' )
        ),
    );

    return apply_filters( 'wpuf_settings_sections', $sections );
}

function wpuf_settings_fields() {
    $pages = wpuf_get_pages();
    $users = wpuf_list_users();

    $settings_fields = array(
        'wpuf_general' => apply_filters( 'wpuf_options_others', array(
            array(
                'name'    => 'fixed_form_element',
                'label'   => __( 'Fixed Form Elements ', 'wpuf' ),
                'desc'    => __( 'Show fixed form elements sidebar in form editor', 'wpuf' ),
                'type'    => 'checkbox',
                'default' => 'on'
            ),
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
                'default' => 'yes',
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
                'label' => __( 'reCAPTCHA Public Key', 'wpuf' ),
            ),
            array(
                'name'  => 'recaptcha_private',
                'label' => __( 'reCAPTCHA Private Key', 'wpuf' ),
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
                'options' => array(
                    'AUD' => 'Australian Dollar',
                    'CAD' => 'Canadian Dollar',
                    'EUR' => 'Euro',
                    'GBP' => 'British Pound',
                    'JPY' => 'Japanese Yen',
                    'USD' => 'U.S. Dollar',
                    'NZD' => 'New Zealand Dollar',
                    'CHF' => 'Swiss Franc',
                    'HKD' => 'Hong Kong Dollar',
                    'SGD' => 'Singapore Dollar',
                    'SEK' => 'Swedish Krona',
                    'DKK' => 'Danish Krone',
                    'PLN' => 'Polish Zloty',
                    'NOK' => 'Norwegian Krone',
                    'HUF' => 'Hungarian Forint',
                    'CZK' => 'Czech Koruna',
                    'ILS' => 'Israeli New Shekel',
                    'MXN' => 'Mexican Peso',
                    'BRL' => 'Brazilian Real',
                    'MYR' => 'Malaysian Ringgit',
                    'PHP' => 'Philippine Peso',
                    'TWD' => 'New Taiwan Dollar',
                    'THB' => 'Thai Baht',
                    'TRY' => 'Turkish Lira'
                )
            ),
            array(
                'name'    => 'currency_symbol',
                'label'   => __( 'Currency Symbol', 'wpuf' ),
                'type'    => 'text',
                'default' => '$'
            ),
            array(
                'name'    => 'cost_per_post',
                'label'   => __( 'Cost', 'wpuf' ),
                'desc'    => __( 'Cost per post', 'wpuf' ),
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
        'wpuf_support' => apply_filters( 'wpuf_options_support', array(
            array(
                'name'  => 'support',
                'label' => __( 'Need Help?', 'wpuf' ),
                'type'  => 'html',
                'desc'  => '
                        <ol>
                            <li>
                                <strong>Check the FAQ and the documentation</strong>
                                <p>First of all, check the <strong><a target="_blank" href="http://docs.wedevs.com/wp-user-frontend-pro/">Documentation</a></strong> before contacting! Most of the questions you might need answers to have already been asked and the answers are in the FAQ. Checking the FAQ is the easiest and quickest way to solve your problem.</p>
                            </li>
                            <li>
                                <strong>Use the Support Forum</strong>
                                <p>If you were unable to find the answer to your question on the documentation page, you should check the <strong><a href="http://wedevs.com/support/forum/plugin-support/wp-user-frontend/wp-user-frontend-pro/">support forum on wedevs.com</a></strong>. If you can’t locate any topics that pertain to your particular issue, post a new topic for it.</p>
                            </li>
                        </ol>'
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