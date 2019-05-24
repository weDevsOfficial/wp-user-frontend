<?php

require_once dirname( __FILE__ ) . '/prompt.php';

class WPUF_Free_Loader extends WPUF_Pro_Prompt {

    public function __construct() {

        $this->includes();
        $this->instantiate();

        add_action( 'add_meta_boxes_wpuf_forms', array($this, 'add_meta_box_post'), 99 );

        add_action( 'wpuf_form_buttons_custom', array( $this, 'wpuf_form_buttons_custom_runner' ) );
        add_action( 'wpuf_form_buttons_other', array( $this, 'wpuf_form_buttons_other_runner') );
        add_action( 'wpuf_form_post_expiration', array( $this, 'wpuf_form_post_expiration_runner') );
        add_action( 'wpuf_form_setting', array( $this, 'form_setting_runner' ),10,2 );
        add_action( 'wpuf_form_settings_post_notification', array( $this, 'post_notification_hook_runner') );
        add_action( 'wpuf_edit_form_area_profile', array( $this, 'wpuf_edit_form_area_profile_runner' ) );
        add_action( 'registration_setting' , array($this,'registration_setting_runner') );
        add_action( 'wpuf_check_post_type' , array( $this, 'wpuf_check_post_type_runner' ),10,2 );
        add_action( 'wpuf_form_custom_taxonomies', array( $this, 'wpuf_form_custom_taxonomies_runner' ) );
        add_action( 'wpuf_conditional_field_render_hook', array( $this, 'wpuf_conditional_field_render_hook_runner' ),10,3 );

        //subscription
        add_action( 'wpuf_admin_subscription_detail', array($this, 'wpuf_admin_subscription_detail_runner'), 10, 4 );

        //coupon
        add_action( 'wpuf_coupon_settings_form', array($this, 'wpuf_coupon_settings_form_runner'),10,1 );
        add_action( 'wpuf_check_save_permission', array($this, 'wpuf_check_save_permission_runner'),10,2 );

        // admin menu
        add_action( 'wpuf_admin_menu_top', array($this, 'admin_menu_top') );
        add_action( 'wpuf_admin_menu', array($this, 'admin_menu') );

        // plugin settings
        add_action( 'admin_footer', array($this, 'remove_login_from_settings') );
        add_filter( 'wpuf_settings_fields', array($this, 'settings_login_prompt') );

        // post form templates
        add_action( 'wpuf_get_post_form_templates', array($this, 'post_form_templates') );
    }

    public function includes() {

        //class files to include pro elements
        require_once dirname( __FILE__ ) . '/form.php';
        require_once dirname( __FILE__ ) . '/form-element.php';
        require_once dirname( __FILE__ ) . '/subscription.php';
        require_once dirname( __FILE__ ) . '/edit-profile.php';
        require_once dirname( __FILE__ ) . '/edit-user.php';
    }

    public function instantiate(){
        new WPUF_Edit_Profile();

        if ( is_admin() ) {

            /**
             * Conditionally load the free loader
             *
             * @since 2.5.7
             *
             * @var boolean
             */
            $load_free = apply_filters( 'wpuf_free_loader', true );

            if ( $load_free ) {
                new WPUF_Admin_Form_Free();
            }
        }
    }

    function admin_menu_top() {
        $capability = wpuf_admin_role();

        add_submenu_page( 'wp-user-frontend', __( 'Registration Forms', 'wp-user-frontend' ), __( 'Registration Forms', 'wp-user-frontend' ), $capability, 'wpuf-profile-forms', array($this, 'admin_reg_forms_page') );
    }

    function admin_menu() {
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $capability = wpuf_admin_role();
            add_submenu_page( 'wp-user-frontend', __( 'Coupons', 'wp-user-frontend' ), __( 'Coupons', 'wp-user-frontend' ), $capability, 'wpuf_coupon', array($this, 'admin_coupon_page' ) );
        }
    }

    function admin_reg_forms_page() {
        ?>
        <h2><?php _e( 'Registration Form', 'wp-user-frontend' ); ?></h2>

        <p>
            <?php _e( 'Use the shortcode <code>[wpuf-registration]</code> for a simple and default WordPress registration form.', 'wp-user-frontend' ); ?>
        </p>

        <p>
            <a target="_blank" class="button" href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/how-to-setup-registrationlogin-page/">
                <span class="dashicons dashicons-sos" style="margin-top: 3px;"></span>
                Learn How to Setup
            </a>
        </p>

        <div class="wpuf-notice" style="padding: 20px;background: #fff;border: 1px solid #ddd;max-width: 360px;margin: 100px auto 0 auto;">
            <h3 style="margin: 0;">Pro Features</h3>

            <p>
                <?php _e( 'Registration form builder is a two way form which can be used both for <strong>user registration</strong> and <strong>profile editing</strong>.', 'wp-user-frontend' ); ?>
            </p>

            <ul class="wpuf-pro-features">
                <li>
                    <span class="dashicons dashicons-yes"></span>
                    <span class="feature">Registration Form Builder</span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes"></span>
                    <span class="feature">Profile Form Builder</span>
                </li>
                <li>
                    <span class="dashicons dashicons-yes"></span>
                    <span class="feature">Register by Subscription Package Purchase</span>
                </li>
            </ul>

            <p style="margin-top: 30px;">
                <a href="<?php echo self::get_pro_url(); ?>" target="_blank" class="button-primary"><?php _e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                <a href="https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/" target="_blank" class="button"><?php _e( 'Learn More', 'wp-user-frontend' ); ?></a>
            </p>
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

    function admin_coupon_page() {
        ?>
        <h2><?php _e( 'Coupons', 'wp-user-frontend' ); ?></h2>

        <div class="wpuf-notice" style="padding: 20px; background: #fff; border: 1px solid #ddd;">
            <p>
                <?php _e( 'Use Coupon codes for subscription for discounts.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <?php _e( 'This feature is only available in the Pro Version.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <a href="<?php echo self::get_pro_url(); ?>" target="_blank" class="button-primary"><?php _e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                <a href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/coupons/" target="_blank" class="button"><?php _e( 'Learn more about Coupons', 'wp-user-frontend' ); ?></a>
            </p>
        </div>

        <?php
    }

    function remove_login_from_settings() {
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

    function settings_login_prompt( $fields ) {

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
    function add_meta_box_post() {
        add_meta_box( 'wpuf-metabox-fields-banner', __( 'Upgrade to Pro', 'wp-user-frontend' ), array($this, 'show_banner_metabox'), 'wpuf_forms', 'side', 'core' );
    }

    function show_banner_metabox() {
        printf( 'Upgrade to in <a href="%s" target="_blank">Pro Version</a> to get more fields and features.', self::get_pro_url() );
    }

    public function wpuf_form_buttons_custom_runner() {

        //add formbuilder widget pro buttons
        WPUF_form_element::add_form_custom_buttons();
    }

    public function wpuf_form_buttons_other_runner() {
        WPUF_form_element::add_form_other_buttons();
    }

    public function wpuf_form_post_expiration_runner(){
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
     * @param  array $integrations
     *
     * @return array
     */
    public function post_form_templates( $integrations ) {
        require_once dirname( __FILE__ ) . '/post-form-templates/woocommerce.php';
        require_once dirname( __FILE__ ) . '/post-form-templates/the_events_calendar.php';

        $integrations['WPUF_Post_Form_Template_WooCommerce']        = new WPUF_Post_Form_Template_WooCommerce();
        $integrations['WPUF_Post_Form_Template_Events_Calendar']    = new WPUF_Post_Form_Template_Events_Calendar();

        return $integrations;
    }
}
