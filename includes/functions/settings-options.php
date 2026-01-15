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
            ],
            [
                'name'           => 'wpuf_login_form_layout',
                'label'          => __( 'Login Form Layout', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'desc'           => __( 'Choose a layout style for your login forms.', 'wp-user-frontend' ),
                'type'           => 'radio',
                'options'        => wpuf_get_login_layout_options(),
                'default'        => 'layout1',
                'is_pro_preview' => ! wpuf_is_pro_active(),
                'callback'       => 'wpuf_render_login_layout_field',
            ],
            [
                'name'           => 'wpuf_login_form_bg_color',
                'label'          => __( 'Form BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => 'transparent',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_form_border_color',
                'label'          => __( 'Form Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => 'transparent',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_field_border_color',
                'label'          => __( 'Field Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#D1D5DB',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_field_bg_color',
                'label'          => __( 'Field BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => 'transparent',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_label_text_color',
                'label'          => __( 'Label Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#333333',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_placeholder_color',
                'label'          => __( 'Placeholder Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#9CA3AF',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_input_text_color',
                'label'          => __( 'Input Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#111827',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_help_text_color',
                'label'          => __( 'Help Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#6B7280',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_button_bg_color',
                'label'          => __( 'Button BG Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#3B82F6',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_button_border_color',
                'label'          => __( 'Button Border Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_button_text_color',
                'label'          => __( 'Button Text Color', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'color',
                'default'        => '#ffffff',
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_form_title',
                'label'          => __( 'Form Header Title', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Login Form', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_form_subtitle',
                'label'          => __( 'Form Description', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Please complete all information below', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_username_label',
                'label'          => __( 'Username Label', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Username or Email', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_username_placeholder',
                'label'          => __( 'Username Placeholder', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Enter Username or Email', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_username_help',
                'label'          => __( 'Username Help Text', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Please enter your username or email address', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_password_label',
                'label'          => __( 'Password Label', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Password', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_password_placeholder',
                'label'          => __( 'Password Placeholder', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Enter Password', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_password_help',
                'label'          => __( 'Password Help Text', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Please enter your password', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_remember_me_text',
                'label'          => __( 'Remember Me Text', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Remember Me', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_lost_password_text',
                'label'          => __( 'Lost Password Text', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Lost Password', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'wpuf_login_button_text',
                'label'          => __( 'Login Button Text', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'type'           => 'text',
                'default'        => __( 'Log In', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'pending_user_message',
                'label'          => __( 'Pending User Message', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'desc'           => __( 'Pending user will see this message when try to log in.', 'wp-user-frontend' ),
                'type'           => 'textarea',
                'default'        => '<strong>' . __( 'ERROR:', 'wp-user-frontend' ) . '</strong> ' . __( 'Your account has to be approved by an administrator before you can login.', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
            ],
            [
                'name'           => 'denied_user_message',
                'label'          => __( 'Denied User Message', 'wp-user-frontend' ) . '<span class="pro-icon"> ' . '<img src="' . esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ) . '" alt="' . esc_attr__( 'PRO', 'wp-user-frontend' ) . '">' . '</span>',
                'desc'           => __( 'Denied user will see this message when try to log in.', 'wp-user-frontend' ),
                'type'           => 'textarea',
                'default'        => '<strong>' . __( 'ERROR:', 'wp-user-frontend' ) . '</strong> ' . __( 'Your account has been denied by an administrator, please contact admin to approve your account.', 'wp-user-frontend' ),
                'is_pro_preview' => ! wpuf_is_pro_active(),
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
                'options' => \WeDevs\Wpuf\AI\Config::get_provider_options(),
                'default' => 'openai',
                'class'   => 'wpuf-ai-provider-radio',
            ],
            [
                'name'    => 'ai_model',
                'label'   => __( 'AI Model', 'wp-user-frontend' ),
                'desc'    => __( 'Select the AI model to use for content generation.', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => apply_filters('wpuf_ai_model_options', \WeDevs\Wpuf\AI\Config::get_model_options()),
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

function wpuf_settings_field_profile( $args = [] ) {
    $user_roles    = apply_filters( 'wpuf_settings_user_roles', wpuf_get_user_roles() );
    $profile_forms = get_posts(
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
        $crown_icon = sprintf( '<span class="pro-icon"><img src="%s" alt="PRO"></span>', WPUF_ASSET_URI . '/images/pro-badge.svg' );
        $class      = 'class="pro-preview"';
        $disabled   = 'disabled';
    }

    if ( ! empty( $user_roles ) ) {
        ?>

    <p style="padding-left: 0; font-style: italic; font-size: 13px;">
        <strong><?php esc_html_e( 'Select profile/registration forms for user roles. These forms will be used to populate extra edit profile fields in backend.', 'wp-user-frontend' ); ?></strong>
    </p>
    <table class="form-table" style="margin-top: 0;">
        <?php
        foreach ( $user_roles as $role => $name ) {
            $current = isset( $val['roles'][ $role ] ) ? $val['roles'][ $role ] : '';
            ?>
            <tr valign="top" <?php echo esc_attr( $class ); ?>>
                <th scrope="row"><?php echo esc_attr( $name ) . wp_kses( $crown_icon, [ 'svg' => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewBox' => true, 'fill' => true ], 'path' => [ 'd' => true, 'fill' => true ], 'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ] ] ); ?></th>
                <td>
                    <select name="wpuf_profile[roles][<?php echo esc_attr( $role ); ?>]" class="regular" style="min-width: 300px;" <?php echo esc_attr( $disabled ); ?>>
                        <option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'wp-user-frontend' ); ?></option>
                        <?php
                        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
                            foreach ( $profile_forms as $profile_form ) {
                                ?>
                                <option value="<?php echo esc_attr( $profile_form->ID ); ?>"<?php selected( $current, $profile_form->ID ); ?>><?php echo esc_html( $profile_form->post_title ); ?></option>
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

/**
 * Render login form settings section header
 *
 * @since 4.2.7
 *
 * @param array $args Settings field args.
 *
 * @return void
 */
function wpuf_render_login_settings_section_header( $args = [] ) {
    ?>
    </td></tr></tbody></table>
    <h2 class="wpuf-settings-section-title" style="margin: 30px 0 10px 0; padding-bottom: 10px; border-bottom: 1px solid #c3c4c7; font-size: 1.3em;">
        <?php esc_html_e( 'Login Form Customization', 'wp-user-frontend' ); ?>
    </h2>
    <p class="description" style="margin-bottom: 15px;">
        <?php esc_html_e( 'Customize the appearance and text of your login forms.', 'wp-user-frontend' ); ?>
    </p>

    <div class="wpuf-login-settings-tabs" style="margin: 20px 0;">
        <nav class="wpuf-login-tabs-nav" style="display: flex; gap: 0; border-bottom: 1px solid #c3c4c7;">
            <a href="#" class="wpuf-login-tab-link active" data-tab="appearance" style="padding: 10px 20px; text-decoration: none; color: #2271b1; font-weight: 500; border-bottom: 2px solid #2271b1; margin-bottom: -1px; background: transparent;">
                <?php esc_html_e( 'Appearance', 'wp-user-frontend' ); ?>
            </a>
            <a href="#" class="wpuf-login-tab-link" data-tab="fields" style="padding: 10px 20px; text-decoration: none; color: #646970; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -1px; background: transparent;">
                <?php esc_html_e( 'Fields', 'wp-user-frontend' ); ?>
            </a>
        </nav>
    </div>

    <style>
        .wpuf-login-tab-link:hover {
            color: #2271b1 !important;
        }
        .wpuf-login-tab-link.active {
            color: #2271b1 !important;
            border-bottom-color: #2271b1 !important;
        }
        .wpuf-login-settings-row.hidden {
            display: none !important;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var appearanceFields = [
            'wpuf_login_form_layout',
            'wpuf_login_form_bg_color',
            'wpuf_login_form_border_color',
            'wpuf_login_field_border_color',
            'wpuf_login_field_bg_color',
            'wpuf_login_label_text_color',
            'wpuf_login_placeholder_color',
            'wpuf_login_input_text_color',
            'wpuf_login_help_text_color',
            'wpuf_login_button_bg_color',
            'wpuf_login_button_border_color',
            'wpuf_login_button_text_color'
        ];

        var fieldsFields = [
            'wpuf_login_form_title',
            'wpuf_login_form_subtitle',
            'wpuf_login_username_label',
            'wpuf_login_username_placeholder',
            'wpuf_login_username_help',
            'wpuf_login_password_label',
            'wpuf_login_password_placeholder',
            'wpuf_login_password_help',
            'wpuf_login_remember_me_text',
            'wpuf_login_lost_password_text',
            'wpuf_login_button_text',
            'pending_user_message',
            'denied_user_message'
        ];

        function markRows() {
            appearanceFields.forEach(function(field) {
                $('tr').has('[name*="' + field + '"]').addClass('wpuf-login-settings-row wpuf-tab-appearance');
            });
            fieldsFields.forEach(function(field) {
                $('tr').has('[name*="' + field + '"]').addClass('wpuf-login-settings-row wpuf-tab-fields');
            });
        }

        function showTab(tab) {
            if (tab === 'appearance') {
                $('.wpuf-tab-appearance').removeClass('hidden');
                $('.wpuf-tab-fields').addClass('hidden');
            } else {
                $('.wpuf-tab-fields').removeClass('hidden');
                $('.wpuf-tab-appearance').addClass('hidden');
            }
        }

        markRows();
        showTab('appearance');

        $('.wpuf-login-tab-link').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');

            $('.wpuf-login-tab-link').removeClass('active').css({
                'color': '#646970',
                'border-bottom-color': 'transparent'
            });
            $(this).addClass('active').css({
                'color': '#2271b1',
                'border-bottom-color': '#2271b1'
            });

            showTab(tab);
        });
    });
    </script>

    <table class="form-table" role="presentation"><tbody><tr style="display:none;"><td>
    <?php
}

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
            }
        }

        // Function to update visible input's name attribute
        function updateVisibleInputName(provider) {
            var $visibleInput = $('#wpuf_ai_api_key_field');
            $visibleInput.attr('name', 'wpuf_ai[' + provider + '_api_key]');
        }

        // Set the initial name on page load
        var initialProvider = $('input[name="wpuf_ai[ai_provider]"]:checked').val() || 'openai';
        updateVisibleInputName(initialProvider);

        // Update API key field and link when provider changes
        $('input[name="wpuf_ai[ai_provider]"]').on('change', function() {
            var provider = $(this).val();
            var apiKey = $('#wpuf_ai_' + provider + '_key').val();
            $('#wpuf_ai_api_key_field').val(apiKey);

            // Update the visible input's name attribute to match the new provider
            updateVisibleInputName(provider);

            // Update the API key link
            updateApiKeyLink(provider);

            // Filter model list by provider
            filterModelsByProvider(provider);

            // Refresh model list if API key exists
            if (apiKey) {
                refreshAllModels();
            }
        });

        // Save API key to hidden field when typing
        $('#wpuf_ai_api_key_field').on('input', function() {
            var provider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
            $('#wpuf_ai_' + provider + '_key').val($(this).val());
        });

        // Store all models globally for filtering
        var allModels = {};

        // Function to filter models by provider
        function filterModelsByProvider(provider, preserveValue) {
            var $modelSelect = $('select[name="wpuf_ai[ai_model]"]');
            if (!$modelSelect.length) return;

            // If we don't have models loaded yet, try to load them first
            if (Object.keys(allModels).length === 0) {
                addProviderDataAttributes();
                return;
            }

            // Get the value to preserve (either passed in or current selection)
            var valueToPreserve = preserveValue || $modelSelect.val();
            var options = '';

            // Build options only for selected provider
            for (var modelId in allModels) {
                if (allModels.hasOwnProperty(modelId)) {
                    var modelConfig = allModels[modelId];
                    if (modelConfig.provider === provider) {
                        var selected = (modelId === valueToPreserve) ? ' selected' : '';
                        options += '<option value="' + modelId + '" data-provider="' + modelConfig.provider + '"' + selected + '>' + modelConfig.name + '</option>';
                    }
                }
            }

            // Update dropdown with filtered options
            if (options) {
                $modelSelect.html(options);
            }
        }

        // Function to refresh all models from API
        function refreshAllModels() {
            var $modelSelect = $('select[name="wpuf_ai[ai_model]"]');
            if (!$modelSelect.length) return;

            // Show loading state
            var originalHtml = $modelSelect.html();
            var currentValue = $modelSelect.val();
            var currentProvider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
            $modelSelect.html('<option>Loading models...</option>').prop('disabled', true);

            // Call API to refresh models
            $.ajax({
                url: '<?php echo esc_url(rest_url('wpuf/v1/ai-form-builder/refresh-google-models')); ?>',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function(response) {
                    if (response && response.success && response.models) {
                        // Store all models globally
                        allModels = response.models;

                        // Enable dropdown
                        $modelSelect.prop('disabled', false);

                        // Filter by current provider
                        filterModelsByProvider(currentProvider);

                        // Try to restore previous selection if it exists and matches current provider
                        if (currentValue) {
                            var currentModel = allModels[currentValue];
                            if (currentModel && currentModel.provider === currentProvider) {
                                $modelSelect.val(currentValue);
                            }
                        }

                    } else {
                        $modelSelect.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $modelSelect.html(originalHtml).prop('disabled', false);
                }
            });
        }

        // Function to load all models and add provider data attributes
        function addProviderDataAttributes() {
            var $modelSelect = $('select[name="wpuf_ai[ai_model]"]');
            if (!$modelSelect.length) return;

            // Save the current selected value before we fetch
            var savedModelValue = $modelSelect.val();

            // Fetch model configs via REST API to get provider info
            $.ajax({
                url: '<?php echo esc_url(rest_url('wpuf/v1/ai-form-builder/models')); ?>',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function(response) {
                    if (response && response.success && response.models) {
                        // Store all models globally
                        allModels = response.models;

                        // Filter by current provider and preserve saved selection
                        var currentProvider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
                        if (currentProvider) {
                            filterModelsByProvider(currentProvider, savedModelValue);
                        }
                    }
                }
            });
        }

        // Initialize provider data attributes on page load
        addProviderDataAttributes();

        // Function to refresh Google models (backward compatibility)
        function refreshGoogleModels() {
            var $modelSelect = $('select[name="wpuf_ai[ai_model]"]');
            if (!$modelSelect.length) return;

            // Show loading state
            var originalHtml = $modelSelect.html();
            var currentValue = $modelSelect.val();
            $modelSelect.html('<option>Loading Google models...</option>').prop('disabled', true);

            // Call API to refresh models
            $.ajax({
                url: '<?php echo esc_url(rest_url('wpuf/v1/ai-form-builder/refresh-google-models')); ?>',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                success: function(response) {
                    if (response && response.success && response.models) {
                        // Update dropdown with new models
                        var options = '';
                        var models = response.models;

                        // Check if models is an object
                        if (typeof models === 'object' && models !== null) {
                            for (var modelId in models) {
                                if (models.hasOwnProperty(modelId)) {
                                    var modelConfig = models[modelId];
                                    var modelName = modelConfig.name || modelId;
                                    options += '<option value="' + modelId + '">' + modelName + '</option>';
                                }
                            }
                        }

                        if (options) {
                            $modelSelect.html(options).prop('disabled', false);
                            // Try to restore previous selection
                            if (currentValue) {
                                $modelSelect.val(currentValue);
                            }
                        } else {
                            $modelSelect.html(originalHtml).prop('disabled', false);
                        }
                    } else {
                        $modelSelect.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $modelSelect.html(originalHtml).prop('disabled', false);
                }
            });
        }
    });
    </script>
    <?php
}
