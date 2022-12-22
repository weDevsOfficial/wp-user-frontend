<?php

require_once __DIR__ . '/prompt.php';

class WPUF_Free_Loader extends WPUF_Pro_Prompt {

    public $edit_profile = null;

    public function __construct() {
        $this->includes();
        $this->instantiate();

        add_action( 'add_meta_boxes_wpuf_forms', [$this, 'add_meta_box_post'], 99 );

        add_action( 'wpuf_form_buttons_custom', [ $this, 'wpuf_form_buttons_custom_runner' ] );
        add_action( 'wpuf_form_buttons_other', [ $this, 'wpuf_form_buttons_other_runner'] );
        add_action( 'wpuf_form_post_expiration', [ $this, 'wpuf_form_post_expiration_runner'] );
        add_action( 'wpuf_form_setting', [ $this, 'form_setting_runner' ], 10, 2 );
        add_action( 'wpuf_form_settings_post_notification', [ $this, 'post_notification_hook_runner'] );
        add_action( 'wpuf_edit_form_area_profile', [ $this, 'wpuf_edit_form_area_profile_runner' ] );
        add_action( 'registration_setting', [$this, 'registration_setting_runner'] );
        add_action( 'wpuf_check_post_type', [ $this, 'wpuf_check_post_type_runner' ], 10, 2 );
        add_action( 'wpuf_form_custom_taxonomies', [ $this, 'wpuf_form_custom_taxonomies_runner' ] );
        add_action( 'wpuf_conditional_field_render_hook', [ $this, 'wpuf_conditional_field_render_hook_runner' ], 10, 3 );

        //subscription
        add_action( 'wpuf_admin_subscription_detail', [$this, 'wpuf_admin_subscription_detail_runner'], 10, 4 );

        //coupon
        add_action( 'wpuf_coupon_settings_form', [$this, 'wpuf_coupon_settings_form_runner'], 10, 1 );
        add_action( 'wpuf_check_save_permission', [$this, 'wpuf_check_save_permission_runner'], 10, 2 );

        // admin menu
        add_action( 'wpuf_admin_menu_top', [$this, 'admin_menu_top'] );
        add_action( 'wpuf_admin_menu', [$this, 'admin_menu'] );

        // plugin settings
        add_action( 'admin_footer', [$this, 'remove_login_from_settings'] );
        add_filter( 'wpuf_settings_sections', [ $this, 'pro_sections' ] );
        add_filter( 'wpuf_settings_fields', [ $this, 'pro_settings' ] );
        // post form templates
        add_action( 'wpuf_get_post_form_templates', [$this, 'post_form_templates'] );
        add_filter( 'wpuf_get_pro_form_previews', [$this, 'pro_form_previews'] );

        // payment gateway added for previewing
        add_filter( 'wpuf_payment_gateways', [ $this, 'wpuf_payment_gateways' ] );

        // navigation tabs added for previewing in Subscription > Add/Edit Subscription
        add_action( 'wpuf_admin_subs_nav_tab', [ $this, 'subscription_tabs' ] );
        add_action( 'wpuf_admin_subs_nav_content', [ $this, 'subscription_tab_contents' ]);
    }

    public function includes() {

        //class files to include pro elements
        require_once __DIR__ . '/form.php';
        require_once __DIR__ . '/form-element.php';
        require_once __DIR__ . '/subscription.php';
        require_once __DIR__ . '/edit-profile.php';
        require_once __DIR__ . '/edit-user.php';
    }

    public function instantiate() {
        $this->edit_profile = new WPUF_Edit_Profile();

        if ( is_admin() ) {

            /**
             * Conditionally load the free loader
             *
             * @since 2.5.7
             *
             * @var bool
             */
            $load_free = apply_filters( 'wpuf_free_loader', true );

            if ( $load_free ) {
                new WPUF_Admin_Form_Free();
            }
        }
    }

    public function admin_menu_top() {
        $capability = wpuf_admin_role();
        $parent_slug = 'wp-user-frontend';

        add_submenu_page( $parent_slug, __( 'Registration Forms', 'wp-user-frontend' ), __( 'Registration Forms', 'wp-user-frontend' ), $capability, 'wpuf-profile-forms', [$this, 'admin_reg_forms_page'] );
        $modules = add_submenu_page( $parent_slug, __( 'Modules', 'wp-user-frontend' ), __( 'Modules', 'wp-user-frontend' ), $capability, 'wpuf-modules', [ $this, 'modules_preview_page' ] );
        add_action( 'wpuf_modules_page_contents', [ $this, 'load_modules_scripts' ] );
        add_action( 'wpuf_modules_page_contents', [ $this, 'modules_page_contents' ] );
    }

    public function admin_menu() {
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $capability = wpuf_admin_role();
            add_submenu_page( 'wp-user-frontend', __( 'Coupons', 'wp-user-frontend' ), __( 'Coupons', 'wp-user-frontend' ), $capability, 'wpuf_coupon', [$this, 'admin_coupon_page' ] );
        }
    }

    public function admin_reg_forms_page() {
        ?>
        <div class="wpuf-registration-form-notice">
            <div class="wpuf-notice wpuf-registration-shortcode-notice" style="padding: 20px;background: #fff;border: 1px solid #ddd;max-width: 360px;">
                <h3 style="margin: 0;"><?php esc_html_e( 'Registration Form', 'wp-user-frontend' ); ?></h3>
                <p>
                    <?php printf( __( 'Use the shortcode %s for a simple and default WordPress registration form.', 'wp-user-frontend' ), '<code>[wpuf-registration]</code>' ); ?>
                </p>
                <p>
                    <a target="_blank" class="button" href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/how-to-setup-registrationlogin-page/">
                        <span class="dashicons dashicons-sos" style="margin-top: 3px;"></span>
                        <?php esc_html_e( 'Learn How to Setup', 'wp-user-frontend' ); ?>
                    </a>
                </p>
            </div>
            <div class="wpuf-notice" style="padding: 20px;background: #fff;border: 1px solid #ddd;max-width: 360px;">
                <h3 style="margin: 0;"><?php esc_html_e( 'Pro Features', 'wp-user-frontend' ); ?></h3>

                <p>
                    <?php echo wp_kses_post( __( 'Registration form builder is a two way form which can be used both for <strong>user registration</strong> and <strong>profile editing</strong>.', 'wp-user-frontend' ) ); ?>
                </p>

                <ul class="wpuf-pro-features">
                    <li>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="feature"><?php esc_html_e( 'Registration Form Builder', 'wp-user-frontend' ); ?></span>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="feature"><?php esc_html_e( 'Profile Form Builder', 'wp-user-frontend' ); ?></span>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes"></span>
                        <span class="feature"><?php esc_html_e( 'Register by Subscription Package Purchase', 'wp-user-frontend' ); ?></span>
                    </li>
                </ul>

                <p style="margin-top: 30px;">
                    <a href="<?php echo esc_url(self::get_pro_url() ); ?>" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                    <a href="https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/" target="_blank" class="button"><?php esc_html_e( 'Learn More', 'wp-user-frontend' ); ?></a>
                </p>
            </div>
        </div>

        <style type="text/css">
            ul.wpuf-pro-features span.dashicons.dashicons-yes {
                background: #4CAF50;
                border-radius: 50%;
                color: #fff;
                margin-right: 7px;
            }
        </style>
        <?php
    }

    public function admin_coupon_page() {
        ?>
        <h2><?php esc_html_e( 'Coupons', 'wp-user-frontend' ); ?></h2>

        <div class="wpuf-notice" style="padding: 20px; background: #fff; border: 1px solid #ddd;">
            <p>
                <?php esc_html_e( 'Use Coupon codes for subscription for discounts.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <?php esc_html_e( 'This feature is only available in the Pro Version.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <a href="<?php echo esc_url( self::get_pro_url() ); ?>" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                <a href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/coupons/" target="_blank" class="button"><?php esc_html_e( 'Learn more about Coupons', 'wp-user-frontend' ); ?></a>
            </p>
        </div>

        <?php
    }

    public function remove_login_from_settings() {
        global $current_screen;

        if ( $current_screen->id == 'user-frontend_page_wpuf-settings' ) {
            ?>
            <!-- <script type="text/javascript">
            jQuery(function($){
                $('#wpuf_profile').find('input, select').each(function(i, el){ $(el).attr('disabled','disabled'); });
            });
            </script> -->
            <?php
        }
    }

    public function settings_login_prompt( $fields ) {

        // $new_field = array(
        //     'name'    => 'something',
        //     'label'   => __( 'Pro Feature', 'wpuf' ),
        //     'desc'    => 'These Features are ' . self::get_pro_prompt_text() . ' Only.',
        //     'type'    => 'html',
        // );

        // array_unshift( $fields['wpuf_profile'], $new_field );

        return $fields;
    }

    /**
     * The pro settings preview on the free version
     *
     * @since 3.6.0
     *
     * @param $settings_fields
     *
     * @return array
     */
    public function pro_sections( $sections ) {
        $crown_icon_path = WPUF_ROOT . '/assets/images/crown.svg';
        $new_sections    = [
            [
                'id'             => 'wpuf_sms',
                'title'          => __( 'SMS', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-format-status',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_social_api',
                'title'          => __( 'Social Login', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-share',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'user_directory',
                'title'          => __( 'User Directory', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-list-view',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_payment_invoices',
                'title'          => __( 'Invoices', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-media-spreadsheet',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_payment_tax',
                'title'          => __( 'Tax', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-media-text',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ],
            [
                'id'             => 'wpuf_content_restriction',
                'title'          => __( 'Content Filtering', 'wp-user-frontend' ) . '<span class="pro-icon-title"> ' . file_get_contents( $crown_icon_path ) . '</span>',
                'icon'           => 'dashicons-admin-network',
                'class'          => 'pro-preview-html',
                'is_pro_preview' => true,
            ]
        ];

        return array_merge( $sections, $new_sections );
    }

    /**
     * The pro settings preview on the free version
     *
     * @since 3.6.0
     *
     * @param $settings_fields
     *
     * @return array
     */
    public function pro_settings( $settings_fields ) {
        $crown_icon_path = WPUF_ROOT . '/assets/images/crown.svg';
        $settings_fields['wpuf_general'][] = [
            'name'           => 'comments_per_page',
            'label'          => __( 'Comments Per Page',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'Show how many comments per page in comments add-on', 'wp-user-frontend' ),
            'type'           => 'number',
            'default'        => '20',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_general'][] = [
            'name'           => 'ipstack_key',
            'label'          => __( 'Ipstack API Key',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( '<a target="_blank" href="https://ipstack.com/dashboard">Register here</a> to get your free ipstack api key',
                                    'wp-user-frontend' ),
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_general'][] = [
            'name'           => 'gmap_api_key',
            'label'          => __( 'Google Map API',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( '<a target="_blank" href="https://developers.google.com/maps/documentation/javascript">API</a> key is needed to render Google Maps',
                                    'wp-user-frontend' ),
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_my_account'][] = [
            'name'           => 'show_edit_profile_menu',
            'label'          => __( 'Edit Profile',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'Allow user to update their profile information from the account page',
                                    'wp-user-frontend' ),
            'type'           => 'checkbox',
            'default'        => 'off',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_my_account'][] = [
            'name'           => 'edit_profile_form',
            'label'          => __( 'Profile Form',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'User will use this form to update their information from the account page,',
                                    'wp-user-frontend' ),
            'type'           => 'select',
            'options'        => [ 'Default Form' ],
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'avatar_size',
            'label'          => __( 'Avatar Size',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'Avatar size to crop when upload using the registration/profile form.(e.g:100x100)',
                                    'wpuf' ),
            'type'           => 'text',
            'default'        => '100x100',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'pending_user_message',
            'label'          => __( 'Pending User Message',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'Pending user will see this message when try to log in.', 'wp-user-frontend' ),
            'default'        => __( '<strong>ERROR:</strong> Your account has to be approved by an administrator before you can login.',
                                    'wp-user-frontend' ),
            'type'           => 'textarea',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_profile'][] = [
            'name'           => 'denied_user_message',
            'label'          => __( 'Denied User Message',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'desc'           => __( 'Denied user will see this message when try to log in.', 'wp-user-frontend' ),
            'default'        => __( '<strong>ERROR:</strong> Your account has been denied by an administrator, please contact admin to approve your account.',
                                    'wp-user-frontend' ),
            'type'           => 'textarea',
            'class'          => 'pro-preview',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'subscription_setting',
            'label'          => __( '<span class="dashicons dashicons-money"></span> Subscription',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'subscription-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'email_setting',
            'label'          => __( '<span class="dashicons dashicons-admin-generic"></span> Template Settings',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'reset_email_setting',
            'label'          => __( '<span class="dashicons dashicons-unlock"></span> Reset Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'reset-email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'confirmation_email_setting',
            'label'          => __( '<span class="dashicons dashicons-email-alt"></span> Resend Confirmation Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'confirmation-email-setting pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'pending_user_email',
            'label'          => __( '<span class="dashicons dashicons-groups"></span> Pending User Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'pending-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'denied_user_email',
            'label'          => __( '<span class="dashicons dashicons-dismiss"></span> Denied User Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'denied-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'approved_user_email',
            'label'          => __( '<span class="dashicons dashicons-smiley"></span> Approved User Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'approved-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'account_activated_user_email',
            'label'          => __( '<span class="dashicons dashicons-smiley"></span> Account Activated Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
            'type'           => 'html',
            'class'          => 'account-activated-user-email pro-preview-html',
            'is_pro_preview' => true,
        ];
        $settings_fields['wpuf_mails'][] = [
            'name'           => 'approved_post_email',
            'label'          => __( '<span class="dashicons dashicons-saved"></span> Approved Post Email',
                                    'wp-user-frontend' ) . '<span class="pro-icon"> ' . file_get_contents( $crown_icon_path ) . '</span>',
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
                'desc'           => __( 'Enabling this will add Social Icons under registration form to allow users to login or register using Social Profiles',
                                        'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            'facebook_app_label'   => [
                'name'  => 'fb_app_label',
                'label' => __( 'Facebook App Settings', 'wp-user-frontend' ),
                'type'  => 'html',
                'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __( 'Create an App',
                                                                                                    'wp-user-frontend' ) . '</a>' . __( ' if you don\'t have one and fill App ID and App Secret below. ',
                                                                                                                                'wp-user-frontend' ),
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
                'desc'           => '<a target="_blank" href="https://apps.twitter.com/">' . __( 'Create an App',
                                                                                                 'wp-user-frontend' ) . '</a>' . __( ' if you don\'t have one and fill Consumer key and Consumer Secret below.',
                                                                                                                                     'wp-user-frontend' ),
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
                'desc'           => '<a target="_blank" href="https://console.developers.google.com/project">' . __( 'Create an App',
                                                                                                                     'wp-user-frontend' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.',
                                                                                                                                                         'wp-user-frontend' ),
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
                'desc'           => '<a target="_blank" href="https://www.linkedin.com/developer/apps">' . __( 'Create an App',
                                                                                                               'wp-user-frontend' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.',
                                                                                                                                                   'wp-user-frontend' ),
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
                'desc'           => '<a target="_blank" href="https://www.instagram.com/developer/">' . __( 'Create an App',
                                                                                                            'wp-user-frontend' ) . '</a>' . __( ' if you don\'t have one and fill Client ID and Client Secret below.',
                                                                                                                                                'wp-user-frontend' ),
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
                'options'        => [ '32' => '32 x 32', ],
                'is_pro_preview' => true,
            ],
            [
                'name'    => 'profile_header_template',
                'label'   => __( 'Profile Header Template', 'wp-user-frontend' ),
                'type'    => 'radio',
                'default' => 'layout',
                'options' => [
                    'layout'  => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-1.jpg' . '" />',
                    'layout1' => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-2.jpg' . '" />',
                    'layout2' => '<img class="profile-header" src="' . WPUF_ASSET_URI . '/images/profile-header-template-3.jpg' . '" />',
                ],
                'is_pro_preview' => true,
            ],
            [
                'name'    => 'user_listing_template',
                'label'   => __( 'User Listing Template', 'wp-user-frontend' ),
                'type'    => 'radio',
                'default' => 'list',
                'options' => [
                    'list'  => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-1.jpg' . '" />',
                    'list1' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-2.jpg' . '" />',
                    'list2' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-3.jpg' . '" />',
                    'list3' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-4.jpg' . '" />',
                    'list4' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-5.jpg' . '" />',
                    'list5' => '<img class="user-listing" src="' . WPUF_ASSET_URI . '/images/user-listing-template-6.jpg' . '" />',
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
                'desc'           => __( 'Show Invoices option where <code>[wpuf_account]</code> is located',
                                        'wp-user-frontend' ),
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
                'type'           => 'text',
                'default'        => '#e435226',
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'set_from_address',
                'label'          => __( 'From Address', 'wp-user-frontend' ),
                'desc'           => __( 'This sets the provider information of the Invoice. Note: use the <xmp class="wpuf-xmp-tag"><br></xmp> tag to enter line breaks.',
                                        'wp-user-frontend' ),
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
                'desc'           => __( 'This sets the payment information paragraph of the Invoice',
                                        'wp-user-frontend' ),
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
                'desc'           => __( 'This sets the mail body of the Invoice', 'wp-user-frontend' ),
                'type'           => 'textarea',
                'is_pro_preview' => true,
            ],
        ];
        $settings_fields['wpuf_payment_tax'] = [
            [
                'name'    => 'tax_help',
                'label'   => __( 'Need help?', 'wp-user-frontend' ),
                'desc'    => sprintf( __( 'Visit the <a href="%s" target="_blank">Tax setup documentation</a> for guidance on how to setup tax.', 'wp-user-frontend' ), 'https://wedevs.com/docs/wp-user-frontend-pro/settings/tax/' ),
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
                'label'          => '<strong>' . __( 'Base Country and State', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __( 'Select your base country and state', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'wpuf_tax_rates',
                'label'          => '<strong>' . __( 'Tax Rates', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __( 'Add tax rates for specific regions. Enter a percentage, such as 5 for 5%',
                                        'wp-user-frontend' ),
                'is_pro_preview' => true,
            ],
            [
                'name'           => 'fallback_tax_rate',
                'label'          => '<strong>' . __( 'Fallback Tax Rate', 'wp-user-frontend' ) . '</strong>',
                'desc'           => __( 'Customers not in a specific rate will be charged this tax rate. Enter a percentage, such as 5 for 5%',
                                        'wp-user-frontend' ),
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
                'desc'           => __( 'Enter Keywords to Remove. Separate keywords with commas.',
                                        'wp-user-frontend' ),
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

        return $settings_fields;
    }

    /**
     * Add meta boxes to post form builder
     *
     * @return void
     */
    public function add_meta_box_post() {
        add_meta_box( 'wpuf-metabox-fields-banner', __( 'Upgrade to Pro', 'wp-user-frontend' ), [$this, 'show_banner_metabox'], 'wpuf_forms', 'side', 'core' );
    }

    public function show_banner_metabox() {
        printf( 'Upgrade to in <a href="%s" target="_blank">Pro Version</a> to get more fields and features.',esc_url( self::get_pro_url() )  );
    }

    public function wpuf_form_buttons_custom_runner() {

        //add formbuilder widget pro buttons
        WPUF_form_element::add_form_custom_buttons();
    }

    public function wpuf_form_buttons_other_runner() {
        WPUF_form_element::add_form_other_buttons();
    }

    public function wpuf_form_post_expiration_runner() {
        WPUF_form_element::render_form_expiration_tab();
    }

    public function form_setting_runner( $form_settings, $post ) {
        WPUF_form_element::add_form_settings_content( $form_settings, $post );
    }

    public function post_notification_hook_runner() {
        WPUF_form_element::add_post_notification_content();
    }

    public function wpuf_edit_form_area_profile_runner() {
        WPUF_form_element::render_registration_form();
    }

    public function registration_setting_runner() {
        WPUF_form_element::render_registration_settings();
    }

    public function wpuf_check_post_type_runner( $post, $update ) {
        WPUF_form_element::check_post_type( $post, $update );
    }

    public function wpuf_form_custom_taxonomies_runner() {
        WPUF_form_element::render_custom_taxonomies_element();
    }

    public function wpuf_conditional_field_render_hook_runner( $field_id, $con_fields, $obj ) {
        WPUF_form_element::render_conditional_field( $field_id, $con_fields, $obj );
    }

    //subscription
    public function wpuf_admin_subscription_detail_runner( $sub_meta, $hidden_recurring_class, $hidden_trial_class, $obj ) {
        WPUF_subscription_element::add_subscription_element( $sub_meta, $hidden_recurring_class, $hidden_trial_class, $obj );
    }

    //coupon
    public function wpuf_coupon_settings_form_runner( $obj ) {
        WPUF_Coupon_Elements::add_coupon_elements( $obj );
    }

    public function wpuf_check_save_permission_runner( $post, $update ) {
        WPUF_Coupon_Elements::check_saving_capability( $post, $update );
    }

    /**
     * Post form templates
     *
     * @since 2.4
     *
     * @param array $integrations
     *
     * @return array
     */
    public function post_form_templates( $integrations ) {
        require_once __DIR__ . '/post-form-templates/woocommerce.php';
        require_once __DIR__ . '/post-form-templates/the_events_calendar.php';

        $integrations['WPUF_Post_Form_Template_WooCommerce']        = new WPUF_Post_Form_Template_WooCommerce();
        $integrations['WPUF_Post_Form_Template_Events_Calendar']    = new WPUF_Post_Form_Template_Events_Calendar();

        return $integrations;
    }

    /**
     * Pro form templates for previewing
     *
     * @since 3.6.0
     *
     * @param array $integrations
     *
     * @return array
     */
    public function pro_form_previews( $integrations ) {
        include_once __DIR__ . '/post-form-templates/easy_digital_download.php';

        $integrations['WPUF_Pro_Form_Preview_EDD'] = new WPUF_Pro_Form_Preview_EDD();

        return $integrations;
    }

    /**
     * A preview page to show the Pro Modules of WPUF
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function modules_preview_page() {
        $modules = $this->pro_modules_info();
        do_action( 'wpuf_modules_page_contents', $modules );
    }

    /**
     * Load required style and js for Modules page
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function load_modules_scripts() {
        wp_enqueue_style( 'wpuf-pro-modules', WPUF_ASSET_URI . '/css/admin/wpuf-module.css', false, WPUF_VERSION );
        wp_enqueue_script( 'wpuf_pro_admin', WPUF_ASSET_URI . '/js/admin/wpuf-module.js', [ 'jquery' ], WPUF_VERSION, true );
    }

    /**
     * Get the info of the pro modules as an array
     *
     * @since 3.6.0
     *
     * @return string[][]
     */
    public function pro_modules_info() {
        return [
            'campaign-monitor/campaign-monitor.php' => [
                'name'        => 'Campaign Monitor',
                'description' => 'Subscribe a contact to Campaign Monitor when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/campaign-monitor/',
                'thumbnail'   => 'campaign_monitor.png',
            ],
            'social-login/wpuf-social-login.php' => [
                'name'        => 'Social Login & Registration',
                'description' => 'Add Social Login and registration feature in WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/social-login-registration/',
                'thumbnail'   => 'Social-Media-Login.png',
            ],
            'bp-profile/wpuf-bp.php' => [
                'name'        => 'BuddyPress Profile',
                'description' => 'Register and upgrade user profiles and sync data with BuddyPress',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/buddypress-profile-integration/',
                'thumbnail'   => 'wpuf-buddypress.png',
            ],
            'comments/comments.php' => [
                'name'        => 'Comments Manager',
                'description' => 'Handle comments in frontend',
                'plugin_uri'  => 'https://wedevs.com/wp-user-frontend-pro/modules/comments-manager/',
                'thumbnail'   => 'wpuf-comment.png',
            ],
            'mailpoet/wpuf-mailpoet.php' => [
                'name'        => 'Mailpoet',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet/',
                'thumbnail'   => 'wpuf-mailpoet.png',
            ],
            'pmpro/wpuf-pmpro.php' => [
                'name'        => 'Paid Membership Pro Integration',
                'description' => 'Membership Integration of WP User Frontend PRO with Paid Membership Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/install-and-configure-pmpro-add-on-for-wpuf/',
                'thumbnail'   => 'wpuf-pmpro.png',
            ],
            'sms-notification/wpuf-sms.php' => [
                'name'        => 'SMS Notification',
                'description' => 'SMS notification for post',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/sms-notification/',
                'thumbnail'   => 'wpuf-sms.png',
            ],
            'email-templates/email-templates.php' => [
                'name'        => 'HTML Email Templates',
                'description' => 'Send Email Notifications with HTML Template',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/html-email-templates/',
                'thumbnail'   => 'email-templates.png',
            ],
            'getresponse/getresponse.php' => [
                'name'        => 'GetResponse',
                'description' => 'Subscribe a contact to GetResponse when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/get-response/',
                'thumbnail'   => 'getresponse.png',
            ],
            'zapier/zapier.php' => [
                'name'        => 'Zapier',
                'description' => 'Subscribe a contact to Zapier when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/zapier/',
                'thumbnail'   => 'zapier.png',
            ],
            'convertkit/convertkit.php' => [
                'name'        => 'ConvertKit',
                'description' => 'Subscribe a contact to ConvertKit when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/convertkit/',
                'thumbnail'   => 'convertkit.png',
            ],
            'private-message/private-message.php' => [
                'name'        => 'Private Message',
                'description' => 'User to user message from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/private-messaging/',
                'thumbnail'   => 'message.gif',
            ],
            'user-analytics/wpuf-user-analytics.php' => [
                'name'        => 'User Analytics',
                'description' => 'Show user tracking info during post and registration from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-analytics/',
                'thumbnail'   => 'wpuf-ua.png',
            ],
            'mailchimp/wpuf-mailchimp.php' => [
                'name'        => 'Mailchimp',
                'description' => 'Add subscribers to Mailchimp mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/add-users-to-mailchimp-subscribers-list-upon-registration-from-frontend/',
                'thumbnail'   => 'wpuf-mailchimp.png',
            ],
            'user-activity/user_activity.php' => [
                'name'        => 'User Activity',
                'description' => 'Handle user activity in frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-activity/',
                'thumbnail'   => 'wpuf-activity.png',
            ],
            'report/wpuf-report.php' => [
                'name'        => 'Reports',
                'description' => 'Show various reports in WP User Frontend menu',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/reports/',
                'thumbnail'   => 'reports.png',
            ],
            'qr-code-field/wpuf-qr-code.php' => [
                'name'        => 'QR Code',
                'description' => 'Post Qr code generator plugin',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/qr-code/',
                'thumbnail'   => 'wpuf-qr.png',
            ],
            'mailpoet3/wpuf-mailpoet-3.php' => [
                'name'        => 'Mailpoet 3',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet3/',
                'thumbnail'   => 'mailpoet3.png',
            ],
            'user-directory/userlisting.php' => [
                'name'        => 'User Directory',
                'description' => 'Handle user listing and user profile in frontend',
                'plugin_uri'  => 'https://wedevs.com/products/plugins/wp-user-frontend-pro/user-listing-profile/',
                'thumbnail'   => 'wpuf-ul.png',
            ],
            'stripe/wpuf-stripe.php' => [
                'name'        => 'Stripe Payment',
                'description' => 'Stripe payment gateway for WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/stripe/',
                'thumbnail'   => 'wpuf-stripe.png',
            ],
        ];
    }

    /**
     * The content of the module page
     *
     * @since 3.6.0
     *
     * @param array $modules
     *
     * @return void
     */
    public function modules_page_contents( $modules ) {
        $diamond_icon = file_exists( WPUF_ROOT . '/assets/images/diamond.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/diamond.svg' ) : '';
        $check_icon   = file_exists( WPUF_ROOT . '/assets/images/check.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/check.svg' ) : '';
        $crown_icon   = file_exists( WPUF_ROOT . '/assets/images/crown.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/crown.svg' ) : '';
        $close_icon   = file_exists( WPUF_ROOT . '/assets/images/x.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/x.svg' ) : '';
        $suffix       = '.min';

        wp_enqueue_style( 'swiffy-slider', WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider' . $suffix . '.css', false, '1.6.0' );
        wp_enqueue_script( 'swiffy-slider', WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider' . $suffix . '.js', [ 'jquery' ], '1.6.0', true );
        wp_enqueue_script( 'swiffy-slider-extention', WPUF_ASSET_URI . '/vendor/swiffy-slider/swiffy-slider-extensions' . $suffix . '.js', [ 'jquery' ], '1.6.0', true );
        ?>
        <div id="wpuf-upgrade-popup" class="wpuf-popup-window">
            <div class="modal-window">
                <div class="modal-window-inner">
                    <div class="content-area">
                        <div class="popup-close-button">
                            <?php echo $close_icon; ?>
                        </div>
                        <div class="popup-diamond">
                            <?php echo $diamond_icon; ?>
                        </div>
                        <div class="wpuf-popup-header">
                            <h2 class="font-orange header-one">Upgrade to</h2>
                            <h2 class="header-two">WP User Frontend <span class="font-bold">Pro</span></h2>
                            <h2 class="header-three font-gray">to experience even more powerful<span class="line-break"></span>features 🎉</h2>
                        </div>
                        <div class="wpuf-popup-list-area">
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php echo $check_icon; ?>
                                </div>
                                <div class="check-list">
                                    <p>Get custom <span class="bold font-black">Post Type</span> and <span class="bold font-black">Taxonomy</span> support with
                                        <span class="line-break"></span> subscription-based <span class="bold font-black">restrictions</span> for post <span class="line-break"></span> submission.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php echo $check_icon; ?>
                                </div>
                                <div class="check-list">
                                    <p>Enable <span class="bold font-black">conditional logic</span> and <span class="bold font-black">multi-step</span><span class="line-break"></span> functionalities on your forms.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php echo $check_icon; ?>
                                </div>
                                <div class="check-list">
                                    <p>Show or hide <span class="bold font-black">menus, pages,</span> and <span class="bold font-black">content</span> based on<span class="line-break"></span> user roles or login status of a user.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php echo $check_icon; ?>
                                </div>
                                <div class="check-list">
                                    <p><span class="bold font-black">20+ Premium Modules</span> (Social Login, User<span class="line-break"></span> Directory, User Activity, Stripe, MailChimp, Private<span class="line-break"></span> Messaging, Zapier, & more)</p>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo self::get_upgrade_to_pro_popup_url(); ?>"
                           target="_blank"
                           class="wpuf-button button-upgrade-to-pro">
                            <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                            <?php printf( '<span class="pro-icon"> %s</span>', $crown_icon );  ?>
                        </a>
                    </div>
                    <div class="slider-area">
                        <div class="wpuf-slider slider-indicators-outside slider-indicators-round slider-nav-mousedrag slider-nav-autoplay slider-nav-autopause"" id="wpuf-slider">
                            <div class="swiffy-slider">
                                <ul class="slider-container">
                                    <li><img src="<?php echo WPUF_ASSET_URI . '/images/woocommerce-form-template.png'; ?>"></li>
                                    <li><img src="<?php echo WPUF_ASSET_URI . '/images/conditional-form.png'; ?>"></li>
                                    <li><img src="<?php echo WPUF_ASSET_URI . '/images/content-restriction.png'; ?>"></li>
                                    <li><img src="<?php echo WPUF_ASSET_URI . '/images/modules.png'; ?>"></li>
                                </ul>

                                <div class="slider-indicators">
                                    <button class="active"></button>
                                    <button></button>
                                    <button></button>
                                    <button></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="footer-feature">
                        <p>
                            <?php echo $check_icon; ?> Industry leading 24x7 support
                        </p>
                        <p>
                            <?php echo $check_icon; ?> 14 days no questions asked refund policy
                        </p>
                        <p>
                            <?php echo $check_icon; ?> Secured payment
                        </p>

                    </div>
                </div>
            </div>
        </div>
        <div class="wrap wpuf-modules">
            <h1><?php esc_attr_e( 'Modules', 'wp-user-frontend' ); ?></h1>
            <div class="wp-list-table widefat wpuf-modules">
                <?php if ( $modules ) {
                    foreach ( $modules as $slug => $module ) {
                        ?>
                        <div class="plugin-card">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <span class="plugin-name"><a href="<?php echo $module['plugin_uri']; ?>" target="_blank"><?php echo $module['name']; ?></a></span>
                                        <a href="<?php echo $module['plugin_uri']; ?>" target="_blank"><img class="plugin-icon" src="<?php echo WPUF_ASSET_URI . '/images/modules/' . $module['thumbnail']; ?>" alt="" /></a>
                                    </h3>
                                </div>

                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li data-module="<?php echo $slug; ?>">
                                            <label class="wpuf-toggle-switch">
                                                <input type="checkbox" name="module_toggle" class="wpuf-toggle-module" disabled>
                                                <span class="slider round"></span>
                                            </label>
                                        </li>
                                    </ul>
                                    <div class="wpuf-doc-link" ><a href="<?php echo $module['plugin_uri']; ?>" target="_blank">Documentation</a></div>
                                </div>

                                <div class="desc column-description">
                                    <p>
                                        <?php echo $module['description']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="form-create-overlay">
                <a href="#wpuf-upgrade-popup"
                   class="wpuf-button button-upgrade-to-pro">
                    <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                    <?php printf( '<span class="pro-icon"> %s</span>', $crown_icon ); ?>
                </a>
            </div>
        </div>
<?php
    }

    /**
     * payment gateways for previewing in the free version
     *
     * @since 3.6.0
     *
     * @param $gateways
     *
     * @return void
     */
    public function wpuf_payment_gateways( $gateways ) {
        $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
        $crown      = '';

        if ( file_exists( $crown_icon ) ) {
            $crown = sprintf( '<span class="pro-icon-title"> %s</span>', file_get_contents( $crown_icon ) );
        }

        $gateways['stripe'] = [
            'admin_label'    => __( 'Credit Card ' . $crown, 'wp-user-frontend' ),
            'checkout_label' => __( 'Credit Card', 'wp-user-frontend' ),
            'label_class'    => 'pro-preview',
        ];

        return $gateways;
    }

    /**
     * The subscription tabs from User Frontend > Subscription > Add/Edit Subscription
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function subscription_tabs() {
        $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
        $crown      = '';

        if ( file_exists( $crown_icon ) ) {
            $crown = sprintf( '<span class="pro-icon-title"> %s</span>', file_get_contents( $crown_icon ) );
        }

        echo '<li><a href="#taxonomy-restriction"><span class="dashicons dashicons-image-filter"></span> ' . __( 'Taxonomy Restriction ', 'wp-user-frontend' ) . $crown . '</a></li>';
    }

    /**
     * The subscription tab contents from User Frontend > Subscription > Add/Edit Subscription
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function subscription_tab_contents() {
        $allowed_tax_id_arr = get_post_meta( get_the_ID() , '_sub_allowed_term_ids', true );
        if ( ! $allowed_tax_id_arr ) {
            $allowed_tax_id_arr = array();
        }
        ?>
        <section id="taxonomy-restriction" class="pro-preview-html">
            <table class='form-table' method='post'>
                <tr><?php _e( 'Choose the taxonomy terms you want to enable for this pack:', 'wpuf' ); ?></tr>
                <tr>
                    <td>
                        <?php
                        $cts = get_taxonomies(array('_builtin'=>true), 'objects'); ?>
                        <?php foreach ($cts as $ct) {
                            if ( is_taxonomy_hierarchical( $ct->name ) ) { ?>
                                <div class="metabox-holder" style="float:left; padding:5px;">
                                    <div class="postbox">
                                        <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                        <div class="inside" style="padding:0 10px;">
                                            <div class="taxonomydiv">
                                                <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                    <?php
                                                    $tax_terms = get_terms ( array(
                                                                                 'taxonomy' => $ct->name,
                                                                                 'hide_empty' => false,
                                                                             ) );
                                                    foreach ($tax_terms as $tax_term) {
                                                        $selected[] = $tax_term;
                                                        ?>
                                                        <ul class="categorychecklist form-no-clear">
                                                            <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>" disabled> <?php echo $tax_term->name; ?>
                                                        </ul>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <p style="padding-left:10px;">
                                                <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                                <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" disabled> Select All
                                            </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </td>

                    <?php
                    $cts = get_taxonomies(array('_builtin'=>false), 'objects'); ?>
                    <?php foreach ($cts as $ct) {
                        if ( is_taxonomy_hierarchical( $ct->name ) ) {
                            $selected = array();
                            ?>
                            <td>
                                <div class="metabox-holder" style="float:left; padding:5px;">
                                    <div class="postbox">
                                        <h3 class="handle"><span><?php  echo  $ct->label; ?></span></h3>
                                        <div class="inside" style="padding:0 10px;">
                                            <div class="taxonomydiv">
                                                <div class="tabs-panel" style="height: 200px; overflow-y:auto">
                                                    <?php
                                                    $tax_terms = get_terms ( array(
                                                                                 'taxonomy' => $ct->name,
                                                                                 'hide_empty' => false,
                                                                             ) );
                                                    foreach ($tax_terms as $tax_term) {
                                                        $selected[] = $tax_term;
                                                        ?>
                                                        <ul class="categorychecklist form-no-clear">
                                                            <input type="checkbox" class="tax-term-class" name="allowed-term[]" value="<?php echo $tax_term->term_id; ?>" <?php echo in_array( $tax_term->term_id, $allowed_tax_id_arr ) ? ' checked="checked"' : ''; ?> name="<?php echo $tax_term->name; ?>" disabled> <?php echo $tax_term->name; ?>
                                                        </ul>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <p style="padding-left:10px;">
                                                <strong><?php echo count( $selected ); ?></strong> <?php echo ( count( $selected ) > 1 || count( $selected ) == 0 ) ? 'categories' : 'category'; ?> total
                                                <span class="list-controls" style="float:right; margin-top: 0;">
                                                <input type="checkbox" class="select-all" disabled> Select All
                                            </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        <?php }
                    } ?>
                </tr>
            </table>
            <?php
                echo wpuf_get_pro_preview_html();
            ?>
        </section>

        <?php
    }
}
