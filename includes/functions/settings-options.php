<?php

/**
 * Settings Sections
 *
 * @since 1.0
 *
 * @return array
 */
function wpuf_settings_sections() {
    $sections = [
        [
            'id'    => 'wpuf_general',
            'title' => __( 'General Options', 'wp-user-frontend' ),
            'icon'  => 'dashicons-admin-generic',
        ],
        [
            'id'    => 'wpuf_frontend_posting',
            'title' => __( 'Frontend Posting', 'wp-user-frontend' ),
            'icon'  => 'dashicons-welcome-write-blog',
        ],
        [
            'id'    => 'wpuf_dashboard',
            'title' => __( 'Dashboard', 'wp-user-frontend' ),
            'icon'  => 'dashicons-dashboard',
        ],
        [
            'id'    => 'wpuf_my_account',
            'title' => __( 'My Account', 'wp-user-frontend' ),
            'icon'  => 'dashicons-id',
        ],
        [
            'id'    => 'wpuf_profile',
            'title' => __( 'Login / Registration', 'wp-user-frontend' ),
            'icon'  => 'dashicons-admin-users',
        ],
        [
            'id'    => 'wpuf_payment',
            'title' => __( 'Payments', 'wp-user-frontend' ),
            'icon'  => 'dashicons-money',
        ],
        [
            'id'    => 'wpuf_mails',
            'title' => __( 'E-Mails', 'wp-user-frontend' ),
            'icon'  => 'dashicons-email-alt',
        ],
        [
            'id'    => 'wpuf_privacy',
            'title' => __( 'Privacy Options', 'wp-user-frontend' ),
            'icon'  => 'dashicons-shield-alt',
        ],
        [
            'id'    => 'wpuf_ai',
            'title' => __( 'AI Settings', 'wp-user-frontend' ),
            'icon'  => 'dashicons-admin-network',
        ],
    ];

    return apply_filters( 'wpuf_settings_sections', $sections );
}

function wpuf_settings_fields() {
    $pages      = wpuf_get_pages();
    $users      = wpuf_list_users();
    $post_types = get_post_types();
    unset( $post_types['attachment'], $post_types['revision'], $post_types['nav_menu_item'], $post_types['wpuf_forms'], $post_types['wpuf_profile'], $post_types['wpuf_input'], $post_types['wpuf_subscription'], $post_types['custom_css'], $post_types['customize_changeset'], $post_types['wpuf_coupon'], $post_types['oembed_cache'] );

    $login_redirect_pages = [
        'previous_page' => __( 'Previous Page', 'wp-user-frontend' ),
    ] + $pages;

    $all_currencies = wpuf_get_currencies();

    $currencies = [];

    foreach ( $all_currencies as $currency ) {
        $currencies[ $currency['currency'] ] = $currency['label'] . ' (' . $currency['symbol'] . ')';
    }

    $default_currency_symbol = wpuf_get_currency( 'symbol' );

    $user_roles = [];
    $all_roles  = get_editable_roles();

    foreach ( $all_roles as $key => $value ) {
        $user_roles[ $key ] = $value['name'];
    }

    $settings_fields = [
        'wpuf_general'          => apply_filters( 'wpuf_options_others', [
            [
                'name'     => 'show_admin_bar',
                'label'    => __( 'Show Admin Bar', 'wp-user-frontend' ),
                'desc'     => __( 'Select user by roles, who can view admin bar in frontend.', 'wp-user-frontend' ),
                'callback' => 'wpuf_settings_multiselect',
                'options'  => $user_roles,
                'default'  => [
                    'administrator',
                    'editor',
                    'author',
                    'contributor',
                ],
            ],
            [
                'name'    => 'admin_access',
                'label'   => __( 'Admin area access', 'wp-user-frontend' ),
                'desc'    => __( 'Allow you to block specific user role to Ajax request and Media upload.',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'read',
                'options' => [
                    'manage_options'    => __( 'Admin Only', 'wp-user-frontend' ),
                    'edit_others_posts' => __( 'Admins, Editors', 'wp-user-frontend' ),
                    'publish_posts'     => __( 'Admins, Editors, Authors', 'wp-user-frontend' ),
                    'edit_posts'        => __( 'Admins, Editors, Authors, Contributors', 'wp-user-frontend' ),
                    'read'              => __( 'Default', 'wp-user-frontend' ),
                ],
            ],
            [
                'name'    => 'override_editlink',
                'label'   => __( 'Override the post edit link', 'wp-user-frontend' ),
                'desc'    => __( 'Users see the edit link in post if s/he is capable to edit the post/page. Selecting <strong>Yes</strong> will override the default WordPress edit post link in frontend',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'no',
                'options' => [
                    'yes' => __( 'Yes', 'wp-user-frontend' ),
                    'no'  => __( 'No', 'wp-user-frontend' ),
                ],
            ],
            [
                'name'    => 'wpuf_compatibility_acf',
                'label'   => __( 'ACF Compatibility', 'wp-user-frontend' ),
                'desc' => sprintf(
                    // translators: %1$s and %2$s are strong tags
                    __(
                        'Select %1$sYes%2$s if you want to make compatible WPUF custom fields data with advanced custom fields.',
                        'wp-user-frontend'
                    ),
                    '<strong>',
                    '</strong>'
                ),
                'type'    => 'select',
                'default' => 'no',
                'options' => [
                    'yes' => __( 'Yes', 'wp-user-frontend' ),
                    'no'  => __( 'No', 'wp-user-frontend' ),
                ],
            ],
            [
                'name'    => 'load_script',
                'label'   => __( 'Load Scripts', 'wp-user-frontend' ),
                'desc'    => __( 'Load scripts/styles in all pages', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'  => 'recaptcha_public',
                'label' => __( 'reCAPTCHA Site Key', 'wp-user-frontend' ),
            ],
            [
                'name'  => 'recaptcha_private',
                'label' => __( 'reCAPTCHA Secret Key', 'wp-user-frontend' ),
                'desc'  => __( '<a target="_blank" href="https://www.google.com/recaptcha/">Register here</a> to get reCaptcha Site and Secret keys.',
                               'wp-user-frontend' ),
            ],
            [
                'name'    => 'enable_turnstile',
                'label'   => __( 'Enable Turnstile', 'wp-user-frontend' ),
                'type'    => 'toggle',
                'default' => 'off',
            ],
            [
                'name'       => 'turnstile_site_key',
                'label'      => __( 'Turnstile Site Key', 'wp-user-frontend' ),
                'depends_on' => 'enable_turnstile',
            ],
            [
                'name'       => 'turnstile_secret_key',
                'label'      => __( 'Turnstile Secret Key', 'wp-user-frontend' ),
                'depends_on' => 'enable_turnstile',
                'desc'       => sprintf(
                    // translators: %s is a link
                    __(
                        '<a target="_blank" href="%1$s">Register here</a> to get Turnstile Site and Secret keys.',
                        'wp-user-frontend'
                    ), esc_url( 'https://developers.cloudflare.com/turnstile/' )
                ),
            ],
            [
                'name'  => 'custom_css',
                'label' => __( 'Custom CSS codes', 'wp-user-frontend' ),
                'desc'  => __( 'If you want to add your custom CSS code, it will be added on page header wrapped with style tag',
                               'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
        ] ),
        'wpuf_frontend_posting' => apply_filters( 'wpuf_options_frontend_posting', [
            [
                'name'    => 'edit_page_id',
                'label'   => __( 'Edit Page', 'wp-user-frontend' ),
                'desc'    => __( 'Select the page where <code>[wpuf_edit]</code> is located', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'    => 'default_post_owner',
                'label'   => __( 'Default Post Owner', 'wp-user-frontend' ),
                'desc'    => __( 'If guest post is enabled and user details are OFF, the posts are assigned to this user',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $users,
                'default' => '1',
            ],
            [
                'name'    => 'cf_show_front',
                'label'   => __( 'Custom Fields in post', 'wp-user-frontend' ),
                'desc'    => __( 'Show custom fields on post content area', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'off',
            ],
            [
                'name'    => 'insert_photo_size',
                'label'   => __( 'Insert Photo image size', 'wp-user-frontend' ),
                'desc'    => __( 'Default image size of "<strong>Insert Photo</strong>" button in post content area',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => wpuf_get_image_sizes(),
                'default' => 'thumbnail',
            ],
            [
                'name'    => 'insert_photo_type',
                'label'   => __( 'Insert Photo image type', 'wp-user-frontend' ),
                'desc'    => __( 'Default image type of "<strong>Insert Photo</strong>" button in post content area',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => [
                    'image' => __( 'Image only', 'wp-user-frontend' ),
                    'link'  => __( 'Image with link', 'wp-user-frontend' ),
                ],
                'default' => 'link',
            ],
            [
                'name'    => 'image_caption',
                'label'   => __( 'Enable Image Caption', 'wp-user-frontend' ),
                'desc'    => __( 'Allow users to update image/video title, caption and description',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'off',
            ],
            [
                'name'    => 'default_post_form',
                'label'   => __( 'Default Post Form', 'wp-user-frontend' ),
                'desc'    => __( 'Fallback form for post editing if no associated form found', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => wpuf_get_pages( 'wpuf_forms' ),
            ],
        ] ),
        'wpuf_dashboard'        => apply_filters( 'wpuf_options_dashboard', [
            [
                'name'    => 'enable_post_edit',
                'label'   => __( 'Users can edit post?', 'wp-user-frontend' ),
                'desc'    => __( 'Users will be able to edit their own posts', 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'yes',
                'options' => [
                    'yes' => __( 'Yes', 'wp-user-frontend' ),
                    'no'  => __( 'No', 'wp-user-frontend' ),
                ],
            ],
            [
                'name'    => 'enable_post_del',
                'label'   => __( 'User can delete post?', 'wp-user-frontend' ),
                'desc'    => __( 'Users will be able to delete their own posts', 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'yes',
                'options' => [
                    'yes' => __( 'Yes', 'wp-user-frontend' ),
                    'no'  => __( 'No', 'wp-user-frontend' ),
                ],
            ],
            [
                'name'    => 'disable_pending_edit',
                'label'   => __( 'Pending Post Edit', 'wp-user-frontend' ),
                'desc'    => __( 'Disable post editing while post in "pending" status', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'disable_publish_edit',
                'label'   => __( 'Editing Published Post', 'wp-user-frontend' ),
                'desc'    => __( 'Disable post editing while post in "publish" status', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'off',
            ],
            [
                'name'    => 'per_page',
                'label'   => __( 'Posts per page', 'wp-user-frontend' ),
                'desc'    => __( 'How many posts will be listed in a page', 'wp-user-frontend' ),
                'type'    => 'text',
                'default' => '10',
            ],
            [
                'name'    => 'show_user_bio',
                'label'   => __( 'Show user bio', 'wp-user-frontend' ),
                'desc'    => __( 'Users biographical info will be shown', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'show_post_count',
                'label'   => __( 'Show post count', 'wp-user-frontend' ),
                'desc'    => __( 'Show how many posts are created by the user', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'  => 'show_ft_image',
                'label' => __( 'Show Featured Image', 'wp-user-frontend' ),
                'desc'  => __( 'Show featured image of the post (Overridden by Shortcode)', 'wp-user-frontend' ),
                'type'  => 'checkbox',
            ],
            [
                'name'    => 'show_payment_column',
                'label'   => __( 'Show Payment Column', 'wp-user-frontend' ),
                'desc'    => __( 'Enable if you want show payment column on posts table', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'ft_img_size',
                'label'   => __( 'Featured Image size', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => wpuf_get_image_sizes(),
            ],
            [
                'name'  => 'un_auth_msg',
                'label' => __( 'Unauthorized Message', 'wp-user-frontend' ),
                'desc'  => __( 'Not logged in users will see this message', 'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
        ] ),
        'wpuf_my_account'       => apply_filters( 'wpuf_options_wpuf_my_account', [
            [
                'name'    => 'account_page',
                'label'   => __( 'Account Page', 'wp-user-frontend' ),
                'desc'    => __( 'Select the page which contains <code>[wpuf_account]</code> shortcode',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'     => 'cp_on_acc_page',
                'label'    => __( 'Select Custom Post For Account Page', 'wp-user-frontend' ),
                'desc'     => __( 'Select the post types you want to show on user dashboard.', 'wp-user-frontend' ),
                'callback' => 'wpuf_settings_multiselect',
                'options'  => $post_types,
            ],
            [
                'name'    => 'account_page_active_tab',
                'label'   => __( 'Active Tab', 'wp-user-frontend' ),
                'desc'    => __( 'Which tab should be set as active by default when opening the account page',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => wpuf_get_account_sections_list(),
            ],
            [
                'name'    => 'show_subscriptions',
                'label'   => __( 'Show Subscriptions', 'wp-user-frontend' ),
                'desc'    => __( 'Show Subscriptions tab in "my account" page where <code>[wpuf_account]</code> is located',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'show_billing_address',
                'label'   => __( 'Show Billing Address', 'wp-user-frontend' ),
                'desc'    => __( 'Show billing address in account page.', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'allow_post_submission',
                'label'   => __( 'Post Submission', 'wp-user-frontend' ),
                'desc'    => __( 'Enable if you want to allow users to submit post from the account page.',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'post_submission_label',
                'label'   => __( 'Submission Menu Label', 'wp-user-frontend' ),
                'desc'    => __( 'Label for post submission menu', 'wp-user-frontend' ),
                'type'    => 'text',
                'default' => __( 'Submit Post', 'wp-user-frontend' ),
            ],
            [
                'name'    => 'post_submission_form',
                'label'   => __( 'Submission Form', 'wp-user-frontend' ),
                'desc'    => __( 'Select a post form that will use to submit post by the users from their account page.',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => wpuf_get_post_forms(),
            ],
        ] ),
        'wpuf_profile'          => apply_filters( 'wpuf_options_profile', [
            [
                'name'    => 'autologin_after_registration',
                'label'   => __( 'Auto Login After Registration', 'wp-user-frontend' ),
                'desc'    => __( 'If enabled, users after registration will be logged in to the system',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'register_link_override',
                'label'   => __( 'Login/Registration override', 'wp-user-frontend' ),
                'desc'    => __( 'If enabled, default login and registration forms will be overridden by WPUF with pages below',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'reg_override_page',
                'label'   => __( 'Registration Page', 'wp-user-frontend' ),
                'desc'    => __( 'Select the page you want to use as registration page override <em>(should have shortcode)</em>',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'    => 'login_page',
                'label'   => __( 'Login Page', 'wp-user-frontend' ),
                'desc'    => __( 'Select the page which contains <code>[wpuf-login]</code> shortcode',
                                 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'    => 'redirect_after_login_page',
                'label'   => __( 'Redirect After Login', 'wp-user-frontend' ),
                'desc'    => __( 'After successfull login, where the page will redirect to', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $login_redirect_pages,
            ],
            [
                'name'    => 'wp_default_login_redirect',
                'label'   => __( 'Default Login Redirect', 'wp-user-frontend' ),
                'desc'    => __( 'If enabled, users who login using WordPress default login form will be redirected to the selected page.',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'off',
            ],
            [
                'name'    => 'login_form_recaptcha',
                'label'   => __( 'reCAPTCHA in Login Form', 'wp-user-frontend' ),
                'desc'    => __( 'If enabled, users have to verify reCAPTCHA in login page. Also, make sure that reCAPTCHA is configured properly from <b>General Options</b>',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'off',
            ],
            [
                'name'       => 'login_form_turnstile',
                'label'      => __( 'Turnstile in Login Form', 'wp-user-frontend' ),
                'desc'       => __(
                    'If enabled, users have to verify Cloudflare Turnstile in login page. Also, make sure that Turnstile is configured properly from <b>General Options</b>',
                    'wp-user-frontend'
                ),
                'type'       => 'toggle',
                'default'    => 'off',
                'depends_on' => 'enable_turnstile',
            ],
        ] ),
        'wpuf_payment'          => apply_filters( 'wpuf_options_payment', [
            [
                'name'    => 'enable_payment',
                'label'   => __( 'Enable Payments', 'wp-user-frontend' ),
                'desc'    => __( 'Enable payments on your site.', 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'subscription_page',
                'label'   => __( 'Subscription Pack Page', 'wp-user-frontend' ),
                'desc'    => __( 'Select the page where <code>[wpuf_sub_pack]</code> located.', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'  => 'register_subscription',
                'label' => __( 'Subscription at registration', 'wp-user-frontend' ),
                'desc'  => __( 'Registration time redirect to subscription page', 'wp-user-frontend' ),
                'type'  => 'checkbox',
            ],
            [
                'name'    => 'currency',
                'label'   => __( 'Currency', 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'USD',
                'options' => $currencies,
            ],
            [
                'name'    => 'currency_position',
                'label'   => __( 'Currency Position', 'wp-user-frontend' ),
                'type'    => 'select',
                'default' => 'left',
                'options' => [
                    'left'        => sprintf( '%1$s (%2$s99.99)', __( 'Left', 'wp-user-frontend' ),
                                              $default_currency_symbol ),
                    'right'       => sprintf( '%1$s (99.99%2$s)', __( 'Right', 'wp-user-frontend' ),
                                              $default_currency_symbol ),
                    'left_space'  => sprintf( '%1$s (%2$s 99.99)', __( 'Left with space', 'wp-user-frontend' ),
                                              $default_currency_symbol ),
                    'right_space' => sprintf( '%1$s (99.99 %2$s)', __( 'Right with space', 'wp-user-frontend' ),
                                              $default_currency_symbol ),
                ],
            ],
            [
                'name'     => 'wpuf_price_thousand_sep',
                'label'    => __( 'Thousand Separator', 'wp-user-frontend' ),
                'desc'     => __( 'This sets the thousand separator of displayed prices.', 'wp-user-frontend' ),
                'css'      => 'width:50px;',
                'default'  => ',',
                'type'     => 'text',
                'desc_tip' => true,
            ],
            [
                'name'    => 'wpuf_price_decimal_sep',
                'label'   => __( 'Decimal Separator', 'wp-user-frontend' ),
                'desc'    => __( 'This sets the decimal separator of displayed prices.', 'wp-user-frontend' ),
                'default' => '.',
                'type'    => 'text',
            ],
            [
                'name'              => 'wpuf_price_num_decimals',
                'label'             => __( 'Number of Decimals', 'wp-user-frontend' ),
                'desc'              => __( 'This sets the number of decimal points shown in displayed prices.',
                                           'wp-user-frontend' ),
                'default'           => '2',
                'type'              => 'number',
                'custom_attributes' => [
                    'min'  => 0,
                    'step' => 1,
                ],
            ],
            [
                'name'    => 'sandbox_mode',
                'label'   => __( 'Enable demo/sandbox mode', 'wp-user-frontend' ),
                'desc'    => __( 'When sandbox mode is active, all payment gateway will be used in demo mode',
                                 'wp-user-frontend' ),
                'type'    => 'checkbox',
                'default' => 'on',
            ],
            [
                'name'    => 'payment_page',
                'label'   => __( 'Payment Page', 'wp-user-frontend' ),
                'desc'    => __( 'This page will be used to process payment options', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'    => 'payment_success',
                'label'   => __( 'Payment Success Page', 'wp-user-frontend' ),
                'desc'    => __( 'After payment users will be redirected here', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
            ],
            [
                'name'    => 'active_gateways',
                'label'   => __( 'Payment Gateways', 'wp-user-frontend' ),
                'desc'    => __( 'Active payment gateways', 'wp-user-frontend' ),
                'type'    => 'multicheck',
                'options' => wpuf_get_gateways(),
            ],
            [
                'name'              => 'failed_retry',
                'label'             => __( 'Retry Failed Payment', 'wp-user-frontend' ),
                'desc'              => __( 'How many times should retry for failed payment max is 4',
                                           'wp-user-frontend' ),
                'default'           => '2',
                'type'              => 'number',
                'custom_attributes' => [
                    'min'  => 1,
                    'max'  => 4,
                    'step' => 1,
                ],
            ],
        ] ),
        'wpuf_mails'            => apply_filters( 'wpuf_mail_options', [
            [
                'name'  => 'guest_email_setting',
                'label' => __( '<span class="dashicons dashicons-universal-access-alt"></span> Guest Email',
                               'wp-user-frontend' ),
                'type'  => 'html',
                'class' => 'guest-email-setting',
            ],
            [
                'name'    => 'enable_guest_email_notification',
                'class'   => 'guest-email-setting-option',
                'label'   => __( 'Guest Email Notification', 'wp-user-frontend' ),
                'desc'    => __( 'Enable Guest Email Notification .', 'wp-user-frontend' ),
                'default' => 'on',
                'type'    => 'checkbox',
            ],
            [
                'name'    => 'guest_email_subject',
                'label'   => __( 'Guest mail subject', 'wp-user-frontend' ),
                'desc'    => __( 'This sets the subject of the emails sent to guest users', 'wp-user-frontend' ),
                'default' => 'Please Confirm Your Email to Get the Post Published!',
                'type'    => 'text',
                'class'   => 'guest-email-setting-option',
            ],
            [
                'name'    => 'guest_email_body',
                'label'   => __( 'Guest mail body', 'wp-user-frontend' ),
                'desc'    => __( "This sets the body of the emails sent to guest users. Please DON'T edit the <code>{activation_link}</code> part, you can use {sitename} too.",
                                 'wp-user-frontend' ),
                'default' => 'Hey There,

                We just received your guest post and now we want you to confirm your email so that we can verify the content and move on to the publishing process.

                Please click the link below to verify:
                {activation_link}

                Regards,
                {sitename}',
                'type'    => 'wysiwyg',
                'class'   => 'guest-email-setting-option',
            ],
        ] ),
        'wpuf_privacy'          => apply_filters( 'wpuf_privacy_options', [
            [
                'name'     => 'export_post_types',
                'label'    => __( 'Post Types', 'wp-user-frontend' ),
                'desc'     => __( 'Select the post types you will allow users to export.', 'wp-user-frontend' ),
                'callback' => 'wpuf_settings_multiselect',
                'options'  => $post_types,
            ],
        ] ),
        'wpuf_ai'               => apply_filters( 'wpuf_ai_options', [
            [
                'name'    => 'ai_provider',
                'label'   => __( 'AI Provider', 'wp-user-frontend' ),
                'desc'    => __( 'Select the AI service provider you want to use.', 'wp-user-frontend' ),
                'type'    => 'radio_inline',
                'options' => [
                    'openai'    => 'OpenAI',
                    'anthropic' => 'Anthropic',
                    'google'    => 'Google',
                ],
                'default' => 'openai',
                'class'   => 'wpuf-ai-provider-radio',
            ],
            [
                'name'    => 'ai_model',
                'label'   => __( 'AI Model', 'wp-user-frontend' ),
                'desc'    => __( 'Select the AI model to use for content generation.', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => apply_filters('wpuf_ai_model_options', [
                    // OpenAI GPT-4.1 Series (Latest - December 2024)
                    'gpt-4.1' => 'GPT-4.1 - Latest Flagship (OpenAI)',
                    'gpt-4.1-mini' => 'GPT-4.1 Mini - Fast & Smart (OpenAI)',
                    'gpt-4.1-nano' => 'GPT-4.1 Nano - Fastest & Cheapest (OpenAI)',

                    // OpenAI O1 Series (Reasoning Models)
                    'o1' => 'O1 - Full Reasoning Model (OpenAI)',
                    'o1-mini' => 'O1 Mini - Cost-Effective Reasoning (OpenAI)',
                    'o1-preview' => 'O1 Preview - Limited Access (OpenAI)',

                    // OpenAI GPT-4o Series (Multimodal)
                    'gpt-4o' => 'GPT-4o - Multimodal (OpenAI)',
                    'gpt-4o-mini' => 'GPT-4o Mini - Efficient Multimodal (OpenAI)',
                    'gpt-4o-2024-08-06' => 'GPT-4o Latest Snapshot (OpenAI)',

                    // OpenAI GPT-4 Turbo & Legacy
                    'gpt-4-turbo' => 'GPT-4 Turbo (OpenAI)',
                    'gpt-4-turbo-2024-04-09' => 'GPT-4 Turbo Latest (OpenAI)',
                    'gpt-4' => 'GPT-4 (OpenAI)',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenAI)',
                    'gpt-3.5-turbo-0125' => 'GPT-3.5 Turbo Latest (OpenAI)',

                    // Anthropic Claude 4 Series (Latest Generation)
                    'claude-4-opus' => 'Claude 4 Opus - Best Coding Model (Anthropic)',
                    'claude-4-sonnet' => 'Claude 4 Sonnet - Advanced Reasoning (Anthropic)',
                    'claude-4.1-opus' => 'Claude 4.1 Opus - Most Capable (Anthropic)',

                    // Anthropic Claude 3.7 Series
                    'claude-3.7-sonnet' => 'Claude 3.7 Sonnet - Hybrid Reasoning (Anthropic)',

                    // Anthropic Claude 3.5 Series (Current Available)
                    'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet Latest (Anthropic)',
                    'claude-3-5-sonnet-20240620' => 'Claude 3.5 Sonnet (Anthropic)',
                    'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (Anthropic)',

                    // Anthropic Claude 3 Legacy
                    'claude-3-opus-20240229' => 'Claude 3 Opus (Anthropic)',
                    'claude-3-sonnet-20240229' => 'Claude 3 Sonnet (Anthropic)',
                    'claude-3-haiku-20240307' => 'Claude 3 Haiku (Anthropic)',

                    // Google Gemini (Current Models)
                    'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash Experimental - Latest (Google)',
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash - Fast & Free (Google)',
                    'gemini-1.5-flash-8b' => 'Gemini 1.5 Flash 8B - Fast & Free (Google)',
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro - Most Capable (Google)',
                    'gemini-1.0-pro' => 'Gemini 1.0 Pro - Stable (Google)',

                ]),
                'default' => 'gpt-3.5-turbo',
                'class'   => 'ai-model-select',
            ],
            [
                'name'    => 'api_key_current',
                'label'   => __( 'API Key', 'wp-user-frontend' ),
                'desc'    => __( 'Enter your AI service API key. Need help finding your <a href="https://platform.openai.com/api-keys" target="_blank" class="wpuf-api-key-link" data-openai="https://platform.openai.com/api-keys" data-anthropic="https://console.anthropic.com/settings/keys" data-google="https://aistudio.google.com/app/apikey" style="text-decoration: underline;">API Key?</a>', 'wp-user-frontend' ),
                'type'    => 'callback',
                'callback' => 'wpuf_ai_api_key_field',
            ],
        ] ),
    ];

    return apply_filters( 'wpuf_settings_fields', $settings_fields );
}

function wpuf_settings_field_profile( $form ) {
    $user_roles = apply_filters( 'wpuf_settings_user_roles', wpuf_get_user_roles() );
    $forms      = get_posts(
        [
            'numberposts' => -1,
            'post_type'   => 'wpuf_profile',
        ]
    );
    $crown_icon = '';
    $class      = '';
    $disabled   = '';

    $val = get_option( 'wpuf_profile', [] );

    if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
        $crown_icon = sprintf( '<span class="pro-icon"> %s</span>', file_get_contents( WPUF_ROOT . '/assets/images/crown.svg' ) );
        $class      = 'class="pro-preview"';
        $disabled   = 'disabled';
    }

    if ( ! empty( $user_roles ) ) {
        ?>

    <p style="padding-left: 10px; font-style: italic; font-size: 13px;">
        <strong><?php esc_html_e( 'Select profile/registration forms for user roles. These forms will be used to populate extra edit profile fields in backend.', 'wp-user-frontend' ); ?></strong>
    </p>
    <table class="form-table">
        <?php
        foreach ( $user_roles as $role => $name ) {
            $current = isset( $val['roles'][ $role ] ) ? $val['roles'][ $role ] : '';
            ?>
            <tr valign="top" <?php echo esc_attr( $class ); ?>>
                <th scrope="row"><?php echo esc_attr( $name ) . wp_kses( $crown_icon, array('svg' => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewBox' => true, 'fill' => true ], 'path' => [ 'd' => true, 'fill' => true ], 'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ], ) ); ?></th>
                <td>
                    <select name="wpuf_profile[roles][<?php echo esc_attr( $role ); ?>]" class="regular" style="min-width: 300px;" <?php echo esc_attr( $disabled ); ?>>
                        <option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'wp-user-frontend' ); ?></option>
                        <?php
                        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
                            foreach ( $forms as $form ) {
                                ?>
                                <option value="<?php echo esc_attr( $form->ID ); ?>"<?php selected( $current, $form->ID ); ?>><?php echo esc_html( $form->post_title ); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <?php
                    if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
                        echo wp_kses_post( wpuf_get_pro_preview_html() );
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
        <?php
    }
}

add_action( 'wsa_form_bottom_wpuf_profile', 'wpuf_settings_field_profile' );

/**
 * Render dynamic API key field based on selected provider
 */
function wpuf_ai_api_key_field( $args ) {
    $settings = get_option( 'wpuf_ai', [] );

    // Get current provider
    $current_provider = $settings['ai_provider'] ?? 'openai';

    // Get all API keys
    $openai_key = $settings['openai_api_key'] ?? '';
    $anthropic_key = $settings['anthropic_api_key'] ?? '';
    $google_key = $settings['google_api_key'] ?? '';

    ?>
    <input type="password"
           id="wpuf_ai_api_key_field"
           name="wpuf_ai[<?php echo esc_attr( $current_provider ); ?>_api_key]"
           class="regular-text wpuf-ai-api-key-dynamic"
           value="<?php echo esc_attr( $settings[ $current_provider . '_api_key' ] ?? '' ); ?>"
           placeholder="<?php esc_attr_e( 'Enter your API key', 'wp-user-frontend' ); ?>"
           autocomplete="off">

    <!-- Store API keys for each provider -->
    <input type="hidden" name="wpuf_ai[openai_api_key]" id="wpuf_ai_openai_key" value="<?php echo esc_attr($openai_key); ?>">
    <input type="hidden" name="wpuf_ai[anthropic_api_key]" id="wpuf_ai_anthropic_key" value="<?php echo esc_attr($anthropic_key); ?>">
    <input type="hidden" name="wpuf_ai[google_api_key]" id="wpuf_ai_google_key" value="<?php echo esc_attr($google_key); ?>">

    <?php
    // Determine the correct API key link based on current provider
    $api_key_links = [
        'openai' => 'https://platform.openai.com/api-keys',
        'anthropic' => 'https://console.anthropic.com/settings/keys',
        'google' => 'https://aistudio.google.com/app/apikey'
    ];
    $current_link = $api_key_links[$current_provider] ?? $api_key_links['openai'];
    ?>

    <p class="description">
        Enter your AI service API key. Need help finding your
        <a href="<?php echo esc_url($current_link); ?>" class="wpuf-api-key-link"
           data-openai="https://platform.openai.com/api-keys"
           data-anthropic="https://console.anthropic.com/settings/keys"
           data-google="https://aistudio.google.com/app/apikey"
           target="_blank"
           style="text-decoration: underline;">API Key?</a>
    </p>

    <script>
    jQuery(document).ready(function($) {
        // Function to update API key link
        function updateApiKeyLink(provider) {
            var apiKeyLink = $('.wpuf-api-key-link');
            if (apiKeyLink.length > 0) {
                var providerLinks = {
                    'openai': 'https://platform.openai.com/api-keys',
                    'anthropic': 'https://console.anthropic.com/settings/keys',
                    'google': 'https://aistudio.google.com/app/apikey'
                };

                var newLink = providerLinks[provider] || providerLinks['openai'];
                apiKeyLink.prop('href', newLink);
                apiKeyLink.attr('href', newLink);
                console.log('[Settings] Updated API key link for', provider, 'to:', newLink);
            }
        }

        // Update API key field and link when provider changes
        $('input[name="wpuf_ai[ai_provider]"]').on('change', function() {
            var provider = $(this).val();
            var apiKey = $('#wpuf_ai_' + provider + '_key').val();
            $('#wpuf_ai_api_key_field').val(apiKey);

            // Update the API key link
            updateApiKeyLink(provider);
        });

        // Save API key to hidden field when typing
        $('#wpuf_ai_api_key_field').on('input', function() {
            var provider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
            $('#wpuf_ai_' + provider + '_key').val($(this).val());
        });
    });
    </script>
    <?php
}
