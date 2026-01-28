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
                'options' => apply_filters('wpuf_ai_model_options', \WeDevs\Wpuf\AI\Config::get_model_options()),
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
        $crown_icon = sprintf( '<span class="pro-icon"><img src="%s" alt="PRO"></span>', WPUF_ASSET_URI . '/images/pro-badge.svg' );
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

    // Get current provider key
    $current_key = $settings[ $current_provider . '_api_key' ] ?? '';

    // Mask the API key for display
    $masked_key = '';
    if ( ! empty( $current_key ) ) {
        $key_length = strlen( $current_key );
        if ( $key_length > 8 ) {
            $masked_key = substr( $current_key, 0, 4 ) . str_repeat( '*', $key_length - 8 ) . substr( $current_key, -4 );
        } else {
            $masked_key = str_repeat( '*', $key_length );
        }
    }

    ?>
    <div class="wpuf-ai-api-key-wrapper">
        <?php if ( ! empty( $current_key ) ) : ?>
            <!-- Show masked key as text -->
            <input type="text"
                   id="wpuf_ai_api_key_display"
                   class="regular-text wpuf-api-key-display"
                   value="<?php echo esc_attr( $masked_key ); ?>"
                   readonly
                   style="background-color: #f0f0f1; cursor: default;">

            <button type="button"
                    id="wpuf-change-api-key-btn"
                    class="button button-secondary"
                    style="margin-left: 5px;">
                <?php esc_html_e( 'Change', 'wp-user-frontend' ); ?>
            </button>
        <?php endif; ?>

        <!-- Actual password input field -->
        <input type="password"
               id="wpuf_ai_api_key_field"
               name="wpuf_ai[<?php echo esc_attr( $current_provider ); ?>_api_key]"
               class="regular-text wpuf-ai-api-key-dynamic"
               value="<?php echo esc_attr( $current_key ); ?>"
               placeholder="<?php esc_attr_e( 'Enter your API key', 'wp-user-frontend' ); ?>"
               autocomplete="off"
               <?php echo ! empty( $current_key ) ? 'style="display: none;"' : ''; ?>>

        <button type="button"
                id="wpuf-test-connection-btn"
                class="button button-secondary"
                style="margin-left: 10px;">
            <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
            <?php esc_html_e( 'Test Connection', 'wp-user-frontend' ); ?>
        </button>

        <span id="wpuf-connection-status" style="margin-left: 10px;"></span>
    </div>

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

        // Handle "Change" button click to show password field
        $('#wpuf-change-api-key-btn').on('click', function() {
            $('#wpuf_ai_api_key_display').hide();
            $(this).hide();
            $('#wpuf_ai_api_key_field').show().focus().select();
            $('#wpuf-connection-status').html('').show();
        });

        // Function to mask API key display
        function maskApiKey(key) {
            if (!key || key.length === 0) return '';
            var keyLength = key.length;
            if (keyLength > 8) {
                return key.substr(0, 4) + '*'.repeat(keyLength - 8) + key.substr(-4);
            }
            return '*'.repeat(keyLength);
        }

        // Function to show/hide API key fields based on whether key exists
        function updateApiKeyDisplay(provider) {
            var apiKey = $('#wpuf_ai_' + provider + '_key').val();
            var hasKey = apiKey && apiKey.trim().length > 0;

            if (hasKey) {
                var masked = maskApiKey(apiKey);
                $('#wpuf_ai_api_key_display').val(masked).show();
                $('#wpuf-change-api-key-btn').show();
                $('#wpuf_ai_api_key_field').hide();
            } else {
                $('#wpuf_ai_api_key_display').hide();
                $('#wpuf-change-api-key-btn').hide();
                $('#wpuf_ai_api_key_field').show();
            }
        }

        // Update API key field and link when provider changes
        $('input[name="wpuf_ai[ai_provider]"]').on('change', function() {
            var provider = $(this).val();
            var apiKey = $('#wpuf_ai_' + provider + '_key').val();
            $('#wpuf_ai_api_key_field').val(apiKey);

            // Update the visible input's name attribute to match the new provider
            updateVisibleInputName(provider);

            // Update the API key link
            updateApiKeyLink(provider);

            // Update display/input visibility
            updateApiKeyDisplay(provider);

            // Enable/disable test connection button
            $('#wpuf-test-connection-btn').prop('disabled', !apiKey || apiKey.trim().length < 10);

            // Update model field state
            updateModelFieldState(apiKey);

            // Filter model list by provider
            filterModelsByProvider(provider);

            // Refresh model list if API key exists
            if (apiKey && apiKey.trim().length >= 10) {
                refreshAllModels();
            }

            // Clear connection status
            $('#wpuf-connection-status').html('').show();
        });

        // Save API key to hidden field when typing
        $('#wpuf_ai_api_key_field').on('input', function() {
            var provider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
            var apiKey = $(this).val();
            $('#wpuf_ai_' + provider + '_key').val(apiKey);

            // Enable/disable test connection button
            $('#wpuf-test-connection-btn').prop('disabled', !apiKey || apiKey.trim().length < 10);

            // Enable/disable model field based on API key
            updateModelFieldState(apiKey);
        });

        // Function to update model field state
        function updateModelFieldState(apiKey) {
            var $modelSelect = $('select[name="wpuf_ai[ai_model]"]');
            var hasValidKey = apiKey && apiKey.trim().length >= 10;

            if (!hasValidKey) {
                $modelSelect.prop('disabled', true);
                $modelSelect.closest('tr').find('.description').html(
                    '<?php esc_html_e( 'Please enter a valid API key and test the connection first.', 'wp-user-frontend' ); ?>'
                );
            } else {
                $modelSelect.prop('disabled', false);
                $modelSelect.closest('tr').find('.description').html(
                    '<?php esc_html_e( 'Select the AI model to use for content generation.', 'wp-user-frontend' ); ?>'
                );
            }
        }

        // Initialize model field state on page load
        var initialApiKey = $('#wpuf_ai_api_key_field').val();
        updateModelFieldState(initialApiKey);

        // Temperature field validation (HTML5 handles basic validation, this adds visual feedback)
        $('#wpuf_ai_temperature').on('input change', function() {
            var $input = $(this);
            var value = parseFloat($input.val());

            // Use HTML5 validity check
            if (!this.checkValidity() || isNaN(value) || value < 0 || value > 1) {
                $input.css('border-color', '#d63638');
            } else {
                $input.css('border-color', '#50C878');
                // Auto-clear green border after 1 second
                setTimeout(function() {
                    $input.css('border-color', '');
                }, 1000);
            }
        });

        // Store timeout ID for clearing
        var statusTimeout = null;

        // Helper function to escape HTML and prevent XSS
        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        // Test connection button handler
        $('#wpuf-test-connection-btn').on('click', function() {
            var $btn = $(this);
            var $status = $('#wpuf-connection-status');
            var provider = $('input[name="wpuf_ai[ai_provider]"]:checked').val();
            var apiKey = $('#wpuf_ai_api_key_field').val();
            var model = $('select[name="wpuf_ai[ai_model]"]').val();

            // Clear any previous timeout
            if (statusTimeout) {
                clearTimeout(statusTimeout);
            }

            // Make sure status is visible
            $status.stop(true, true).show().css('opacity', '1');

            if (!apiKey || apiKey.trim().length < 10) {
                $status.html('<span style="color: #d63638;">⚠ <?php esc_html_e( 'Please enter a valid API key', 'wp-user-frontend' ); ?></span>');
                return;
            }

            // Update button state
            $btn.prop('disabled', true);
            $btn.find('.dashicons').addClass('spin');
            $status.html('<span style="color: #999;">⏳ <?php esc_html_e( 'Testing connection...', 'wp-user-frontend' ); ?></span>');

            // Call test connection endpoint with API key, provider, and model
            $.ajax({
                url: '<?php echo esc_url( rest_url( 'wpuf/v1/ai-form-builder/test' ) ); ?>',
                method: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    api_key: apiKey,
                    provider: provider,
                    model: model
                }),
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>');
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    $btn.find('.dashicons').removeClass('spin');

                    // Ensure status is visible
                    $status.stop(true, true).show().css('opacity', '1');

                    if (response && response.success) {
                        $status.html('<span style="color: #00a32a;">✓ ' + escapeHtml(response.message || '<?php esc_html_e( 'Connection successful!', 'wp-user-frontend' ); ?>') + '</span>');

                        // Enable model field and refresh models
                        updateModelFieldState(apiKey);
                        refreshAllModels();

                        // Auto-hide success message after 5 seconds
                        statusTimeout = setTimeout(function() {
                            $status.fadeOut(400);
                        }, 5000);
                    } else {
                        var errorMsg = response.message || '<?php esc_html_e( 'Connection failed', 'wp-user-frontend' ); ?>';
                        $status.html('<span style="color: #d63638;">✗ ' + escapeHtml(errorMsg) + '</span>');
                        // Error messages stay visible
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false);
                    $btn.find('.dashicons').removeClass('spin');

                    // Ensure status is visible
                    $status.stop(true, true).show().css('opacity', '1');

                    var errorMsg = '<?php esc_html_e( 'Connection failed. Please check your API key.', 'wp-user-frontend' ); ?>';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $status.html('<span style="color: #d63638;">✗ ' + escapeHtml(errorMsg) + '</span>');
                    // Error messages stay visible
                }
            });
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
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .dashicons.spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        .wpuf-ai-api-key-wrapper {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        #wpuf-connection-status {
            display: inline-block;
            min-width: 200px;
        }
    </style>
    <?php
}

/**
 * Render temperature field with proper HTML5 attributes
 *
 * @param array $args Field arguments (unused but required by Settings API)
 */
function wpuf_ai_temperature_field( $args ) {
    $settings = get_option( 'wpuf_ai', [] );
    $value = isset( $settings['temperature'] ) ? floatval( $settings['temperature'] ) : 0.7;

    // Ensure value is within valid range
    $value = max( 0.0, min( 1.0, $value ) );

    ?>
    <input type="number"
           id="wpuf_ai_temperature"
           name="wpuf_ai[temperature]"
           class="regular-text wpuf-ai-temperature"
           value="<?php echo esc_attr( $value ); ?>"
           min="0"
           max="1"
           step="0.1"
           required>
    <p class="description">
        <?php esc_html_e( 'Controls randomness in responses. Lower values (0.1-0.3) are more focused and deterministic. Higher values (0.7-1.0) are more creative and varied.', 'wp-user-frontend' ); ?>
    </p>
    <?php
}
