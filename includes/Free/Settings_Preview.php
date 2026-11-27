<?php
/**
 * Settings Preview (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

/**
 * Pro settings previewed in the free plugin: the Pro sections and fields of Settings, shown locked.
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Settings_Preview extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    /**
     * The pro settings preview on the Free version
     *
     * @since 3.6.0
     *
     * @param $sections
     *
     * @return array
     */
    public function pro_sections( $sections ) {
        $crown_icon_path = WPUF_ROOT . '/assets/images/pro-badge.svg';
        $new_sections    = [
            [
                'id'             => 'wpuf_sms',
                'title'          => __( 'SMS', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-format-status',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_social_api',
                'title'          => __( 'Social Login', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-share',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'user_directory',
                'title'          => __( 'User Directory', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-list-view',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_payment_invoices',
                'title'          => __( 'Invoices', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-media-spreadsheet',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_payment_tax',
                'title'          => __( 'Tax', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-media-text',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_content_restriction',
                'title'          => __( 'Content Filtering', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-admin-network',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_seo_settings',
                'title'          => __( 'SEO Settings', 'wp-user-frontend' ) . '<span class="pro-icon-title"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'icon'           => 'dashicons-search',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
        ];

        return array_merge( $sections, $new_sections );
    }

    /**
     * The pro settings preview on the Free version
     *
     * @since 3.6.0
     *
     * @param $settings_fields
     *
     * @return array
     */
    public function pro_settings( $settings_fields ) {
        $crown_icon_path = WPUF_ROOT . '/assets/images/pro-badge.svg';
        $settings_fields['wpuf_general'][] = [
            'name'           => 'comments_per_page',
            'label'          => __(
                'Comments Per Page',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __( 'Show how many comments per page in comments add-on', 'wp-user-frontend' ),
            'type'           => 'number',
            'default'        => '20',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_general'][] = [
            'name'           => 'ipstack_key',
            'label'          => __(
                'Ipstack API Key',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => sprintf(
            // translators: %1$s: opening anchor tag, %2$s: closing anchor tag
                __( '%1$sRegister here%2$s to get your free ipstack api key', 'wp-user-frontend' ),
                '<a target="_blank" href="https://ipstack.com/dashboard">', '</a>'
            ),
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_general'][] = [
            'name'           => 'gmap_api_key',
            'label'          => __(
                'Google Map API',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __(
                '<a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">API</a> key is needed to render Google Maps',
                'wp-user-frontend'
            ),
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_my_account'][] = [
            'name'           => 'show_edit_profile_menu',
            'label'          => __(
                'Edit Profile',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __(
                'Allow user to update their profile information from the account page',
                'wp-user-frontend'
            ),
            'type'           => 'checkbox',
            'default'        => 'off',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_my_account'][] = [
            'name'           => 'edit_profile_form',
            'label'          => __(
                'Profile Form',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __(
                'User will use this form to update their information from the account page,',
                'wp-user-frontend'
            ),
            'type'           => 'select',
            'options'        => [ 'Default Form' ],
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'avatar_size',
            'label'          => __(
                'Avatar Size',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __(
                'Avatar size to crop when upload using the registration/profile form.(e.g:100x100)',
                'wp-user-frontend'
            ),
            'type'           => 'text',
            'default'        => '100x100',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'pending_user_message',
            'label'          => __(
                'Pending User Message',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __( 'Pending user will see this message when try to log in.', 'wp-user-frontend' ),
            'default'        => __(
                '<strong>ERROR:</strong> Your account has to be approved by an administrator before you can login.',
                'wp-user-frontend'
            ),
            'type'           => 'textarea',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'denied_user_message',
            'label'          => __(
                'Denied User Message',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'desc'           => __( 'Denied user will see this message when try to log in.', 'wp-user-frontend' ),
            'default'        => __(
                '<strong>ERROR:</strong> Your account has been denied by an administrator, please contact admin to approve your account.',
                'wp-user-frontend'
            ),
            'type'           => 'textarea',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'subscription_setting',
            'label'          => __(
                '<span class="dashicons dashicons-money"></span> Subscription',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'subscription-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'email_setting',
            'label'          => __(
                '<span class="dashicons dashicons-admin-generic"></span> Template Settings',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'reset_email_setting',
            'label'          => __(
                '<span class="dashicons dashicons-unlock"></span> Reset Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'reset-email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'confirmation_email_setting',
            'label'          => __(
                '<span class="dashicons dashicons-email-alt"></span> Resend Confirmation Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'confirmation-email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'pending_user_email',
            'label'          => __(
                '<span class="dashicons dashicons-groups"></span> Pending User Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'pending-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'denied_user_email',
            'label'          => __(
                '<span class="dashicons dashicons-dismiss"></span> Denied User Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'denied-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'approved_user_email',
            'label'          => __(
                '<span class="dashicons dashicons-smiley"></span> Approved User Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'approved-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'account_activated_user_email',
            'label'          => __(
                '<span class="dashicons dashicons-smiley"></span> Account Activated Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'account-activated-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'approved_post_email',
            'label'          => __(
                '<span class="dashicons dashicons-saved"></span> Approved Post Email',
                'wp-user-frontend'
            ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
            'type'           => 'html',
            'class'          => 'approved-post-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_sms'] = [
            [
                'name'           => 'clickatell_name',
                'label'          => __( 'Clickatell name', 'wp-user-frontend' ),
                'desc'           => __( 'Clickatell name', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'clickatell_password',
                'label'          => __( 'Clickatell Password', 'wp-user-frontend' ),
                'desc'           => __( 'Clickatell Password', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'clickatell_api',
                'label'          => __( 'Clickatell api', 'wp-user-frontend' ),
                'desc'           => __( 'Clickatell api', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'smsglobal_name',
                'label'          => __( 'SMSGlobal Name', 'wp-user-frontend' ),
                'desc'           => __( 'SMSGlobal Name', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'smsglobal_password',
                'label'          => __( 'SMSGlobal Passord', 'wp-user-frontend' ),
                'desc'           => __( 'SMSGlobal Passord', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'nexmo_api',
                'label'          => __( 'Nexmo API', 'wp-user-frontend' ),
                'desc'           => __( 'Nexmo API', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'nexmo_api_Secret',
                'label'          => __( 'Nexmo API Secret', 'wp-user-frontend' ),
                'desc'           => __( 'Nexmo API Secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'twillo_number',
                'label'          => __( 'Twillo From Number', 'wp-user-frontend' ),
                'desc'           => __( 'Twillo From Number', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'twillo_sid',
                'label'          => __( 'Twillo Account SID', 'wp-user-frontend' ),
                'desc'           => __( 'Twillo Account SID', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'twillo_token',
                'label'          => __( 'Twillo Authro Token', 'wp-user-frontend' ),
                'desc'           => __( 'Twillo Authro Token', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['wpuf_social_api'] = [
            'enabled'              => [
                'name'           => 'enabled',
                'label'          => __( 'Enable Social Login', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'desc'           => __(
                    'Enabling this will add Social Icons under registration form to allow users to login or register using Social Profiles',
                    'wp-user-frontend'
                ),
                'is_pro_preview' => true,
            ],
            'facebook_app_label'   => [
                'name'  => 'fb_app_label',
                'label' => __( 'Facebook App Settings', 'wp-user-frontend' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __(
                    'Create an App',
                    'wp-user-frontend'
                ) . '</a>' . __(
                    ' if you don\'t have one and fill App ID and App Secret below. ',
                    'wp-user-frontend'
                ),
            ],
            'facebook_app_url'     => [
                'name'           => 'fb_app_url',
                'label'          => __( 'Redirect URI', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => "<input class='regular-text' type='text' disabled value=''>",
                'is_pro_preview' => true,
            ],
            'facebook_app_id'      => [
                'name'           => 'fb_app_id',
                'label'          => __( 'App Id', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'facebook_app_secret'  => [
                'name'           => 'fb_app_secret',
                'label'          => __( 'App Secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'twitter_app_label'    => [
                'name'           => 'twitter_app_label',
                'label'          => __( 'Twitter App Settings', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => '<a target="_blank" href="https://apps.twitter.com/">' . __(
                    'Create an App',
                    'wp-user-frontend'
                ) . '</a>' . __(
                    ' if you don\'t have one and fill Consumer key and Consumer Secret below.',
                    'wp-user-frontend'
                ),
                'is_pro_preview' => true,
            ],
            'twitter_app_url'      => [
                'name'           => 'twitter_app_url',
                'label'          => __( 'Callback URL', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => "<input class='regular-text' type='text' disabled value=''>",
                'is_pro_preview' => true,
            ],
            'twitter_app_id'       => [
                'name'           => 'twitter_app_id',
                'label'          => __( 'Consumer Key', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'twitter_app_secret'   => [
                'name'           => 'twitter_app_secret',
                'label'          => __( 'Consumer Secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'google_app_label'     => [
                'name'           => 'google_app_label',
                'label'          => __( 'Google App Settings', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => '<a target="_blank" href="https://console.developers.google.com/project">'
                    . __( 'Create an App', 'wp-user-frontend' ) . '</a>'
                    . __( ' if you don\'t have one and fill Client ID and Client Secret below.', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            'google_app_url'       => [
                'name'           => 'google_app_url',
                'label'          => __( 'Redirect URI', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => "<input class='regular-text' type='text' disabled value=''>",
                'is_pro_preview' => true,
            ],
            'google_app_id'        => [
                'name'           => 'google_app_id',
                'label'          => __( 'Client ID', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'google_app_secret'    => [
                'name'           => 'google_app_secret',
                'label'          => __( 'Client secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'linkedin_app_label'   => [
                'name'           => 'linkedin_app_label',
                'label'          => __( 'Linkedin App Settings', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => '<a target="_blank" href="https://www.linkedin.com/developer/apps">'
                    . __( 'Create an App', 'wp-user-frontend' ) . '</a>'
                    . __( ' if you don\'t have one and fill Client ID and Client Secret below.', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            'linkedin_app_url'     => [
                'name'           => 'linkedin_app_url',
                'label'          => __( 'Redirect URL', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => "<input class='regular-text' type='text' disabled value=''>",
                'is_pro_preview' => true,
            ],
            'linkedin_app_id'      => [
                'name'           => 'linkedin_app_id',
                'label'          => __( 'Client ID', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'linkedin_app_secret'  => [
                'name'           => 'linkedin_app_secret',
                'label'          => __( 'Client Secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'instagram_app_label'  => [
                'name'           => 'instagram_app_label',
                'label'          => __( 'Instagram App Settings', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => '<a target="_blank" href="https://www.instagram.com/developer/">' . __(
                    'Create an App',
                    'wp-user-frontend'
                ) . '</a>' . __(
                    ' if you don\'t have one and fill Client ID and Client Secret below.',
                    'wp-user-frontend'
                ),
                'is_pro_preview' => true,
            ],
            'instagram_app_url'    => [
                'name'           => 'instagram_app_url',
                'label'          => __( 'Redirect URI', 'wp-user-frontend' ),
                'type'           => 'html',
                'desc'           => "<input class='regular-text' type='text' disabled value=''>",
                'is_pro_preview' => true,
            ],
            'instagram_app_id'     => [
                'name'           => 'instagram_app_id',
                'label'          => __( 'Client ID', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            'instagram_app_secret' => [
                'name'           => 'instagram_app_secret',
                'label'          => __( 'Client Secret', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['user_directory'] = [
            [
                'name'           => 'pro_img_size',
                'label'          => __( 'Profile Gallery Image Size ', 'wp-user-frontend' ),
                'desc'           => __( 'Set the image size of picture gallery in frontend', 'wp-user-frontend' ),
                'type'           => 'select',
                'options'        => wpuf_get_image_sizes(),
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'avatar_size',
                'label'          => __( 'Avatar Size ', 'wp-user-frontend' ),
                'desc'           => __( 'Set the image size of profile picture in frontend', 'wp-user-frontend' ),
                'type'           => 'select',
                'options'        => [ '32' => '32 x 32' ],
                'is_pro_preview' => true,
            ],
            [
                'name'    => 'profile_header_template',
                'label'   => __( 'Profile Header Template', 'wp-user-frontend' ),
                'type'    => 'radio',
                'default' => 'layout',
                'options' => [
                    'layout'  => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-1.jpg" />',
                    'layout1' => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-2.jpg" />',
                    'layout2' => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-3.jpg" />',
                ],
                'is_pro_preview' => true,
            ],
            [
                'name'    => 'user_listing_template',
                'label'   => __( 'User Listing Template', 'wp-user-frontend' ),
                'type'    => 'radio',
                'default' => 'list',
                'options' => [
                    'list'  => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-1.jpg" />',
                    'list1' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-2.jpg" />',
                    'list2' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-3.jpg" />',
                    'list3' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-4.jpg" />',
                    'list4' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-5.jpg" />',
                    'list5' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-6.jpg" />',
                ],
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['wpuf_payment_invoices'] = [
            [
                'name'           => 'enable_invoices',
                'label'          => __( 'Enable Invoices', 'wp-user-frontend' ),
                'desc'           => __( 'Enable sending invoices for completed payments', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'default'        => 'on',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'show_invoices',
                'label'          => __( 'Show Invoices', 'wp-user-frontend' ),
                'desc'           => __(
                    'Show Invoices option where <code>[wpuf_account]</code> is located',
                    'wp-user-frontend'
                ),
                'type'           => 'checkbox',
                'default'        => 'on',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_logo',
                'label'          => __( 'Set Invoice Logo', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the company Logo to be used in Invoice', 'wp-user-frontend' ),
                'type'           => 'file',
                'default'        => false,
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_color',
                'label'          => __( 'Set Invoice Color', 'wp-user-frontend' ),
                'desc'           => __( 'Set color code to be used in invoice', 'wp-user-frontend' ),
                'type'           => 'color',
                'default'        => '#e43522',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_from_address',
                'label'          => __( 'From Address', 'wp-user-frontend' ),
                'desc'           => sprintf(
                    /* translators: %s: the <br> tag, shown as code */
                    __( 'This sets the provider information of the Invoice. Note: use the %s tag to enter line breaks.', 'wp-user-frontend' ),
                    '<code>&lt;br&gt;</code>'
                ),
                'type'           => 'textarea',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_title',
                'label'          => __( 'Invoice Title', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the payment information title of the Invoice', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_paragraph',
                'label'          => __( 'Invoice Paragraph', 'wp-user-frontend' ),
                'desc'           => __(
                    'This sets the payment information paragraph of the Invoice',
                    'wp-user-frontend'
                ),
                'type'           => 'textarea',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_footernote',
                'label'          => __( 'Invoice Footer', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the footer of the Invoice', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_filename',
                'label'          => __( 'Invoice Filename Prefix', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the filename prefix of the Invoice', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_mail_sub',
                'label'          => __( 'Set Invoice Mail Subject', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the mail subject of the Invoice', 'wp-user-frontend' ),
                'type'           => 'text',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_mail_body',
                'label'          => __( 'Set Invoice Mail Body', 'wp-user-frontend' ),
                'desc'           => sprintf(
                    /* translators: 1: line break, 2: strong tag open, 3: strong tag close, 4-9: code tags for invoice placeholders */
                    __( 'This sets the mail body of the Invoice.%1$s%2$sYou may use:%3$s %4$s %5$s %6$s %7$s %8$s %9$s', 'wp-user-frontend' ),
                    '<br>',
                    '<strong>',
                    '</strong>',
                    '<code>{username}</code>',
                    '<code>{user_email}</code>',
                    '<code>{display_name}</code>',
                    '<code>{invoice_id}</code>',
                    '<code>{payment_amount}</code>',
                    '<code>{payment_type}</code>',
                ),
                'type'           => 'wysiwyg',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'send_attachment',
                'label'          => __( 'Send Invoice as Attachment', 'wp-user-frontend' ),
                'desc'           => __( 'Enable to send the invoice PDF as an email attachment', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'default'        => 'on',
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['wpuf_payment_tax'] = [
            [
                'name'    => 'tax_help',
                'label'   => __( 'Need help?', 'wp-user-frontend' ),
                'desc'    => sprintf(
                // translators: %1$s: opening anchor tag, %2$s: closing anchor tag
                    __( 'Visit the %1$sTax setup documentation%2$s for guidance on how to setup tax.', 'wp-user-frontend' ), '<a href="https://wedevs.com/docs/wp-user-frontend-pro/settings/tax/" target="_blank">',
                    '</a>'
                ),
                'callback'    => 'wpuf_descriptive_text',
            ],
            [
                'name'           => 'enable_tax',
                'label'          => __( 'Enable Tax', 'wp-user-frontend' ),
                'desc'           => __( 'Enable tax on payments', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'default'        => 'on',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'wpuf_base_country_state',
                'label'          => '<strong>' . __( 'Business Country and State', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __( 'Select your business country and state', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'wpuf_tax_rates',
                'label'          => '<strong>' . __( 'Tax Rates', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __(
                    'Add tax rates for specific regions. Enter a percentage, such as 5 for 5%',
                    'wp-user-frontend'
                ),
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'fallback_tax_rate',
                'label'          => '<strong>' . __( 'Fallback Tax Rate', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __(
                    'Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 5 for 5%',
                    'wp-user-frontend'
                ),
                'type'           => 'number',
                'default'        => 0,
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'prices_include_tax',
                'label'          => __( 'Show prices with tax', 'wp-user-frontend' ),
                'desc'           => __( 'If frontend prices will include tax or not', 'wp-user-frontend' ),
                'type'           => 'radio',
                'default'        => 'yes',
                'options'        => array(
                    'yes' => __( 'Show prices with tax', 'wp-user-frontend' ),
                    'no'  => __( 'Show prices without tax', 'wp-user-frontend' ),
                ),
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['wpuf_content_restriction'] = [
            [
                'name'           => 'enable_content_filtering',
                'label'          => __( 'Enable Content Filtering', 'wp-user-frontend' ),
                'desc'           => __( 'Enable Content Filtering in frontend', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'default'        => 'off',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'keyword_dictionary',
                'label'          => __( 'Keyword Dictionary', 'wp-user-frontend' ),
                'desc'           => __( 'Enter Keywords to Remove. Separate keywords with commas.', 'wp-user-frontend' ),
                'type'           => 'textarea',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'filter_contents',
                'label'          => __( 'Filter main content', 'wp-user-frontend' ),
                'desc'           => __( 'Choose which content to filter.', 'wp-user-frontend' ),
                'type'           => 'multicheck',
                'options'        => array(
                    'post_title'   => __( 'Post Titles', 'wp-user-frontend' ),
                    'post_content' => __( 'Post Content', 'wp-user-frontend' ),
                ),
                'default'        => array( 'post_content', 'post_title' ),
                'is_pro_preview' => true,
            ],
        ];

        // SEO Settings
        $settings_fields['wpuf_seo_settings'] = [
            [
                'name'  => 'user_directory_section',
                'label' => __( 'User Directory SEO Settings', 'wp-user-frontend' ),
                'type'  => 'html',
                'class' => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'avoid_indexing_profiles',
                'label'          => __( 'Avoid indexing profile by search engines', 'wp-user-frontend' ) . '<span class="pro-icon"> <img src="' . WPUF_ASSET_URI . '/images/pro-badge.svg" alt="PRO"></span>',
                'desc'           => __( 'Enable this to add a noindex meta tag to all user profile pages across directories. Useful if you want some profiles hidden from search engines.', 'wp-user-frontend' ),
                'type'           => 'checkbox',
                'default'        => 'off',
                'class'          => 'pro-preview',
                'is_pro_preview' => true,
            ],
        ];

        return $settings_fields;
    }
}
