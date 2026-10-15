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
        'wpuf_general'          => apply_filters(
            'wpuf_options_others', [
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
                    'desc'    => __(
                        'Allow you to block specific user role to Ajax request and Media upload.',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Users see the edit link in post if s/he is capable to edit the post/page. Selecting <strong>Yes</strong> will override the default WordPress edit post link in frontend',
                        'wp-user-frontend'
                    ),
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
                    'desc'  => __(
                        '<a target="_blank" href="https://www.google.com/recaptcha/">Register here</a> to get reCaptcha Site and Secret keys.',
                        'wp-user-frontend'
                    ),
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
                    'desc'  => __(
                        'If you want to add your custom CSS code, it will be added on page header wrapped with style tag',
                        'wp-user-frontend'
                    ),
                    'type'  => 'textarea',
                ],
            ]
        ),
        'wpuf_frontend_posting' => apply_filters(
            'wpuf_options_frontend_posting', [
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
                    'desc'    => __(
                        'If guest post is enabled and user details are OFF, the posts are assigned to this user',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Default image size of "<strong>Insert Photo</strong>" button in post content area',
                        'wp-user-frontend'
                    ),
                    'type'    => 'select',
                    'options' => wpuf_get_image_sizes(),
                    'default' => 'thumbnail',
                ],
                [
                    'name'    => 'insert_photo_type',
                    'label'   => __( 'Insert Photo image type', 'wp-user-frontend' ),
                    'desc'    => __(
                        'Default image type of "<strong>Insert Photo</strong>" button in post content area',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Allow users to update image/video title, caption and description',
                        'wp-user-frontend'
                    ),
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
            ]
        ),
        'wpuf_dashboard'        => apply_filters(
            'wpuf_options_dashboard', [
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
            ]
        ),
        'wpuf_my_account'       => apply_filters(
            'wpuf_options_wpuf_my_account', [
                [
                    'name'    => 'account_page',
                    'label'   => __( 'Account Page', 'wp-user-frontend' ),
                    'desc'    => __(
                        'Select the page which contains <code>[wpuf_account]</code> shortcode',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Which tab should be set as active by default when opening the account page',
                        'wp-user-frontend'
                    ),
                    'type'    => 'select',
                    'options' => wpuf_get_account_sections_list(),
                ],
                [
                    'name'    => 'show_subscriptions',
                    'label'   => __( 'Show Subscriptions', 'wp-user-frontend' ),
                    'desc'    => __(
                        'Show Subscriptions tab in "my account" page where <code>[wpuf_account]</code> is located',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Enable if you want to allow users to submit post from the account page.',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'Select a post form that will use to submit post by the users from their account page.',
                        'wp-user-frontend'
                    ),
                    'type'    => 'select',
                    'options' => wpuf_get_post_forms(),
                ],
            ]
        ),
        'wpuf_profile'          => apply_filters(
            'wpuf_options_profile', [
                [
                    'name'    => 'autologin_after_registration',
                    'label'   => __( 'Auto Login After Registration', 'wp-user-frontend' ),
                    'desc'    => __(
                        'If enabled, users after registration will be logged in to the system',
                        'wp-user-frontend'
                    ),
                    'type'    => 'checkbox',
                    'default' => 'on',
                ],
                [
                    'name'    => 'register_link_override',
                    'label'   => __( 'Login/Registration override', 'wp-user-frontend' ),
                    'desc'    => __(
                        'If enabled, default login and registration forms will be overridden by WPUF with pages below',
                        'wp-user-frontend'
                    ),
                    'type'    => 'checkbox',
                    'default' => 'on',
                ],
                [
                    'name'    => 'reg_override_page',
                    'label'   => __( 'Registration Page', 'wp-user-frontend' ),
                    'desc'    => __(
                        'Select the page you want to use as registration page override <em>(should have shortcode)</em>',
                        'wp-user-frontend'
                    ),
                    'type'    => 'select',
                    'options' => $pages,
                ],
                [
                    'name'    => 'login_page',
                    'label'   => __( 'Login Page', 'wp-user-frontend' ),
                    'desc'    => __(
                        'Select the page which contains <code>[wpuf-login]</code> shortcode',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'If enabled, users who login using WordPress default login form will be redirected to the selected page.',
                        'wp-user-frontend'
                    ),
                    'type'    => 'checkbox',
                    'default' => 'off',
                ],
                [
                    'name'    => 'login_form_recaptcha',
                    'label'   => __( 'reCAPTCHA in Login Form', 'wp-user-frontend' ),
                    'desc'    => __(
                        'If enabled, users have to verify reCAPTCHA in login page. Also, make sure that reCAPTCHA is configured properly from <b>General Options</b>',
                        'wp-user-frontend'
                    ),
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
                [
                    'name'     => 'profile_form_roles',
                    'label'    => __( 'Profile Forms for User Roles', 'wp-user-frontend' ),
                    'type'     => 'html',
                    'callback' => 'wpuf_settings_field_profile',
                ],
                [
                    'name'     => 'login_form_settings_section',
                    'label'    => '',
                    'type'     => 'html',
                    'callback' => 'wpuf_render_login_settings_section_header',
                    // Clean heading for the React screen (the callback emits legacy
                    // table markup that only works in the classic screen). Legacy
                    // ignores `desc` for callback fields, so this is React-only.
                    'desc'     => '<h3 class="wpuf-settings-section-title" style="margin:24px 0 4px;font-size:1.125rem;font-weight:600;color:#111827;">'
                        . esc_html__( 'Login Form Customization', 'wp-user-frontend' )
                        . '</h3><p style="margin:0 0 8px;color:#6b7280;">'
                        . esc_html__( 'Customize the appearance and text of your login forms.', 'wp-user-frontend' )
                        . '</p>',
                ],
                [
                    'name'           => 'wpuf_login_form_layout',
                    'label'          => __( 'Login Form Layout', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'desc'           => __( 'Choose a layout style for your login forms.', 'wp-user-frontend' ),
                    'type'           => 'radio',
                    'options'        => wpuf_get_login_layout_options(),
                    'default'        => 'layout1',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                    'callback'       => 'wpuf_render_login_layout_field',
                ],
                [
                    'name'           => 'wpuf_login_form_bg_color',
                    'label'          => __( 'Form BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => 'transparent',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_form_border_color',
                    'label'          => __( 'Form Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => 'transparent',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_field_border_color',
                    'label'          => __( 'Field Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#D1D5DB',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_field_bg_color',
                    'label'          => __( 'Field BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => 'transparent',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_label_text_color',
                    'label'          => __( 'Label Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#333333',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_placeholder_color',
                    'label'          => __( 'Placeholder Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#9CA3AF',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_input_text_color',
                    'label'          => __( 'Input Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#111827',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_help_text_color',
                    'label'          => __( 'Help Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#6B7280',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_button_bg_color',
                    'label'          => __( 'Button BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#3B82F6',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_button_border_color',
                    'label'          => __( 'Button Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_button_text_color',
                    'label'          => __( 'Button Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'color',
                    'default'        => '#ffffff',
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_form_title',
                    'label'          => __( 'Form Header Title', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Login Form', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_form_subtitle',
                    'label'          => __( 'Form Description', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Please complete all information below', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_username_label',
                    'label'          => __( 'Username Label', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Username or Email', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_username_placeholder',
                    'label'          => __( 'Username Placeholder', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Enter Username or Email', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_username_help',
                    'label'          => __( 'Username Help Text', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Please enter your username or email address', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_password_label',
                    'label'          => __( 'Password Label', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Password', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_password_placeholder',
                    'label'          => __( 'Password Placeholder', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Enter Password', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_password_help',
                    'label'          => __( 'Password Help Text', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Please enter your password', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_remember_me_text',
                    'label'          => __( 'Remember Me Text', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Remember Me', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_lost_password_text',
                    'label'          => __( 'Lost Password Text', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Lost Password', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'wpuf_login_button_text',
                    'label'          => __( 'Login Button Text', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'type'           => 'text',
                    'default'        => __( 'Log In', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'pending_user_message',
                    'label'          => __( 'Pending User Message', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'desc'           => __( 'Pending user will see this message when try to log in.', 'wp-user-frontend' ),
                    'type'           => 'textarea',
                    'default'        => '<strong>' . __( 'ERROR:', 'wp-user-frontend' ) . '</strong> ' . __( 'Your account has to be approved by an administrator before you can login.', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
                [
                    'name'           => 'denied_user_message',
                    'label'          => __( 'Denied User Message', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '"></span>',
                    'desc'           => __( 'Denied user will see this message when try to log in.', 'wp-user-frontend' ),
                    'type'           => 'textarea',
                    'default'        => '<strong>' . __( 'ERROR:', 'wp-user-frontend' ) . '</strong> ' . __( 'Your account has been denied by an administrator, please contact admin to approve your account.', 'wp-user-frontend' ),
                    'is_pro_preview' => ! wpuf_is_pro_active(),
                ],
            ]
        ),
        'wpuf_payment'          => apply_filters(
            'wpuf_options_payment', [
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
                        'left'        => sprintf(
                            '%1$s (%2$s99.99)', __( 'Left', 'wp-user-frontend' ),
                            $default_currency_symbol
                        ),
                        'right'       => sprintf(
                            '%1$s (99.99%2$s)', __( 'Right', 'wp-user-frontend' ),
                            $default_currency_symbol
                        ),
                        'left_space'  => sprintf(
                            '%1$s (%2$s 99.99)', __( 'Left with space', 'wp-user-frontend' ),
                            $default_currency_symbol
                        ),
                        'right_space' => sprintf(
                            '%1$s (99.99 %2$s)', __( 'Right with space', 'wp-user-frontend' ),
                            $default_currency_symbol
                        ),
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
                    'desc'              => __(
                        'This sets the number of decimal points shown in displayed prices.',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        'When sandbox mode is active, all payment gateway will be used in demo mode',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __( 'Select the payment methods available at checkout', 'wp-user-frontend' ),
                    'type'    => 'gateway_selector',
                    'options' => wpuf_get_gateways( 'gateway_selector' ),
                ],
                [
                    'name'              => 'failed_retry',
                    'label'             => __( 'Retry Failed Payment', 'wp-user-frontend' ),
                    'desc'              => __(
                        'How many times should retry for failed payment max is 4',
                        'wp-user-frontend'
                    ),
                    'default'           => '2',
                    'type'              => 'number',
                    'custom_attributes' => [
                        'min'  => 1,
                        'max'  => 4,
                        'step' => 1,
                    ],
                ],
            ]
        ),
        'wpuf_mails'            => apply_filters(
            'wpuf_mail_options', [
                [
                    'name'  => 'guest_email_setting',
                    'label' => __(
                        '<span class="dashicons dashicons-universal-access-alt"></span> Guest Email',
                        'wp-user-frontend'
                    ),
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
                    'desc'    => __(
                        "This sets the body of the emails sent to guest users. Please DON'T edit the <code>{activation_link}</code> part, you can use {sitename} too.",
                        'wp-user-frontend'
                    ),
                    'default' => 'Hey There,

                We just received your guest post and now we want you to confirm your email so that we can verify the content and move on to the publishing process.

                Please click the link below to verify:
                {activation_link}

                Regards,
                {sitename}',
                    'type'    => 'wysiwyg',
                    'class'   => 'guest-email-setting-option',
                ],
            ]
        ),
        'wpuf_privacy'          => apply_filters(
            'wpuf_privacy_options', [
                [
                    'name'     => 'export_post_types',
                    'label'    => __( 'Post Types', 'wp-user-frontend' ),
                    'desc'     => __( 'Select the post types you will allow users to export.', 'wp-user-frontend' ),
                    'callback' => 'wpuf_settings_multiselect',
                    'options'  => $post_types,
                ],
            ]
        ),
        'wpuf_ai'               => apply_filters(
            'wpuf_ai_options', [
                [
                    'name'    => 'ai_provider',
                    'label'   => __( 'AI Provider', 'wp-user-frontend' ),
                    'desc'    => __( 'Select the AI service provider you want to use.', 'wp-user-frontend' ),
                    'type'    => 'radio_inline',
                    'options' => \WeDevs\Wpuf\AI\Config::get_provider_options(),
                    'default' => 'openai',
                    'class'   => 'wpuf-ai-provider-radio',
                ],
                [
                    'name'     => 'api_key_current',
                    'label'    => __( 'API Key', 'wp-user-frontend' ),
                    'desc'     => sprintf(
                        // translators: %1$s is the OpenAI URL, %2$s is the Anthropic URL, %3$s is the Google URL
                        __(
                            'Enter your AI service API key. Need help finding your <a href="%1$s" target="_blank" class="wpuf-api-key-link" data-openai="%1$s" data-anthropic="%2$s" data-google="%3$s" style="text-decoration: underline;">API Key?</a>',
                            'wp-user-frontend'
                        ),
                        esc_url( 'https://platform.openai.com/api-keys' ),
                        esc_url( 'https://console.anthropic.com/settings/keys' ),
                        esc_url( 'https://aistudio.google.com/app/apikey' )
                    ),
                    'type'     => 'callback',
                    'callback' => 'wpuf_ai_api_key_field',
                ],
                [
                    'name'    => 'ai_model',
                    'label'   => __( 'AI Model', 'wp-user-frontend' ),
                    'desc'    => __( 'Select the AI model to use for content generation.', 'wp-user-frontend' ),
                    'type'    => 'select',
                    'options' => apply_filters( 'wpuf_ai_model_options', \WeDevs\Wpuf\AI\Config::get_model_options() ),
                    'default' => 'gpt-3.5-turbo',
                    'class'   => 'ai-model-select',
                ],
                [
                    'name'     => 'temperature',
                    'label'    => __( 'Temperature', 'wp-user-frontend' ),
                    'desc'     => __( 'Controls randomness in responses. Lower values (0.1-0.3) are more focused and deterministic. Higher values (0.7-1.0) are more creative and varied.', 'wp-user-frontend' ),
                    'type'     => 'callback',
                    'callback' => 'wpuf_ai_temperature_field',
                    'default'  => '0.7',
                ],
            ]
        ),
    ];

    return apply_filters( 'wpuf_settings_fields', $settings_fields );
}
