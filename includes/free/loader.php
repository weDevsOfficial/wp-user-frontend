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
        add_filter( 'wpuf_settings_fields', [$this, 'settings_login_prompt'] );

        // post form templates
        add_action( 'wpuf_get_post_form_templates', [$this, 'post_form_templates'] );
        add_action( 'wpuf_get_pro_form_previews', [$this, 'pro_form_previews'] );
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
        add_action( 'wpuf_modules_page_contents', [ $this, 'modules_page_contents' ] );
        add_action( 'wpuf_modules_page_contents', [ $this, 'load_modules_scripts' ] );
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_modules_scripts() {
        wp_enqueue_style( 'wpuf-pro-modules', WPUF_ASSET_URI . '/css/admin/wpuf-module.css', false, WPUF_VERSION );
    }

    /**
     * Get the info of the pro modules as an array
     *
     * @since WPUF_SINCE
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
     * @since WPUF_SINCE
     *
     * @param array $modules
     *
     * @return void
     */
    public function modules_page_contents( $modules ) {
        ?>
        <div class="wrap weforms-modules">
            <h1><?php esc_attr_e( 'Modules', 'wpuf-pro' ); ?></h1>
            <div class="wp-list-table widefat wpuf-modules">
                <?php if ( $modules ) {
                    $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
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
                            <div class="form-create-overlay">
                                <a href="<?php echo esc_url( WPUF_Pro_Prompt::get_pro_url() ); ?>"
                                    target="_blank"
                                    class="wpuf-button button-upgrade-to-pro">
                                    <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                                    <?php if ( file_exists( $crown_icon ) ) {
                                        printf( '<span class="pro-icon"> %s</span>', file_get_contents( $crown_icon ) );
                                    } ?>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
<?php
    }
}
