<?php

namespace WeDevs\Wpuf\Free;

use WeDevs\Wpuf\Hooks\Form_Settings_Cleanup;

class Free_Loader extends Pro_Prompt {

    /**
     * @var Settings_Preview|null
     */
    private $settings_preview = null;

    /**
     * @var Modules_Preview|null
     */
    private $modules_preview = null;

    /**
     * @var Form_Settings_Preview|null
     */
    private $form_settings_preview = null;

    /**
     * @var Subscription_Preview|null
     */
    private $subscription_preview = null;

    /**
     * @var Promo_Pages|null
     */
    private $promo_pages = null;

    public $edit_profile = null;

    /**
     * Run the hooks to load free elements on places
     *
     * @since 4.1.4
     *
     * @return void
     */
    public function run_hooks() {
        add_action( 'add_meta_boxes_wpuf_forms', [ $this, 'add_meta_box_post' ], 99 );

        // admin menu
        add_action( 'wpuf_admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'wpuf_admin_menu_top', [ $this, 'admin_menu_top' ] );

        // Free module toggle AJAX

        // plugin settings
        add_filter( 'wpuf_settings_sections', [ $this, 'pro_sections' ] );
        add_filter( 'wpuf_settings_fields', [ $this, 'pro_settings' ] );

        // post form templates
        add_filter( 'wpuf_get_post_form_templates', [ $this, 'post_form_templates' ] );
        add_filter( 'wpuf_get_pro_form_previews', [ $this, 'pro_form_previews' ] );

        // post form settings
        add_filter( 'wpuf_form_builder_settings_general', [ $this, 'form_settings_preview_general' ] );
        add_filter( 'wpuf_form_builder_settings_notification', [ $this, 'form_settings_preview_notification' ] );
        add_filter( 'wpuf_form_builder_settings_display', [ $this, 'form_settings_preview_display' ] );
        add_filter( 'wpuf_form_builder_settings_advanced', [ $this, 'form_settings_preview_advanced' ] );
        add_filter( 'wpuf_form_builder_settings_post_expiration', [ $this, 'form_settings_preview_post_expiration' ] );
        add_filter( 'wpuf_form_builder_post_settings_menu_items', [ $this, 'form_settings_modules' ] );

        // payment gateway added for previewing
        add_filter( 'wpuf_payment_gateways', [ $this, 'wpuf_payment_gateways' ] );

        // subscription
        add_filter( 'wpuf_subscription_section_advanced', [ $this, 'add_taxonomy_restriction_section' ] );
        add_filter( 'wpuf_subscriptions_fields', [ $this, 'add_taxonomy_restriction_fields' ], 11 );

        // field option data actions
    }

    public function includes() {
        // class files to include pro elements
        require_once WPUF_INCLUDES . '/functions/user/edit-user.php';
        require_once WPUF_INCLUDES . '/Hooks/Form_Settings_Cleanup.php';

        // User Directory Free - only load if Pro module is not active AND Free module is enabled
        if ( ! $this->is_pro_user_directory_active() && $this->is_free_user_directory_active() ) {
            require_once WPUF_ROOT . '/modules/user-directory/User_Directory.php';
        }
    }

    /**
     * Check if Pro User Directory module is active
     *
     * @since 4.3.0
     *
     * @return bool
     */
    private function is_pro_user_directory_active() {
        if ( ! wpuf_is_pro_active() ) {
            return false;
        }

        return class_exists( 'WPUF_User_Listing' );
    }

    /**
     * Check if Free User Directory module is enabled
     *
     * @since 4.3.0
     *
     * @return bool
     */
    private function is_free_user_directory_active() {
        return wpuf_free_is_module_active( 'user_directory' );
    }

    public function instantiate() {
        $this->edit_profile = wpuf()->platform()->get( Edit_Profile::class );

        // Initialize User Directory Free if Pro module is not active AND Free module is enabled
        if ( ! $this->is_pro_user_directory_active() && $this->is_free_user_directory_active() ) {
            \WeDevs\Wpuf\Modules\User_Directory\User_Directory::get_instance();
        }

        if ( is_admin() ) {

            /**
             * Conditionally load the Free loader
             *
             * @since 2.5.7
             *
             * @var bool
             */
            $load_free = apply_filters( 'wpuf_free_loader', true );

            if ( $load_free ) {
                wpuf()->platform()->get( Form_Settings_Cleanup::class );
            }
        }

        // The builder saves over REST (wpuf/v1/admin/forms/{id}), outside wp-admin:
        // strip the Pro-only settings there too, as the AJAX save in wp-admin did.
        add_action( 'rest_api_init', [ $this, 'boot_rest_cleanup' ] );
    }

    /**
     * Pro-only settings cleanup for REST requests (the builder save).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function boot_rest_cleanup() {
        if ( is_admin() || ! apply_filters( 'wpuf_free_loader', true ) || $this->has_settings_cleanup() ) {
            return;
        }

        wpuf()->platform()->get( Form_Settings_Cleanup::class );
    }

    /**
     * Whether a Form_Settings_Cleanup listener is already attached.
     *
     * @return bool
     */
    private function has_settings_cleanup() {
        global $wp_filter;

        if ( empty( $wp_filter['wpuf_form_builder_save_form'] ) ) {
            return false;
        }

        foreach ( $wp_filter['wpuf_form_builder_save_form']->callbacks as $callbacks ) {
            foreach ( $callbacks as $callback ) {
                if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof Form_Settings_Cleanup ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @see \WeDevs\Wpuf\Free\Settings_Preview::pro_sections()
     *
     * @since WPUF_SINCE
     */
    public function pro_sections( $sections ) {
        return $this->settings_preview()->pro_sections( $sections );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Settings_Preview::pro_settings()
     *
     * @since WPUF_SINCE
     */
    public function pro_settings( $settings_fields ) {
        return $this->settings_preview()->pro_settings( $settings_fields );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Modules_Preview::module_menu_action()
     *
     * @since WPUF_SINCE
     */
    public function module_menu_action() {
        return $this->modules_preview()->module_menu_action();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Modules_Preview::pro_modules_info()
     *
     * @since WPUF_SINCE
     */
    public function pro_modules_info() {
        return $this->modules_preview()->pro_modules_info();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_preview_general()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_preview_general( $general_settings ) {
        return $this->form_settings_preview()->form_settings_preview_general( $general_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_preview_notification()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_preview_notification( $notification_settings ) {
        return $this->form_settings_preview()->form_settings_preview_notification( $notification_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_preview_advanced()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_preview_advanced( $advanced_settings ) {
        return $this->form_settings_preview()->form_settings_preview_advanced( $advanced_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_preview_display()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_preview_display( $display_settings ) {
        return $this->form_settings_preview()->form_settings_preview_display( $display_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_preview_post_expiration()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_preview_post_expiration( $expiration_settings ) {
        return $this->form_settings_preview()->form_settings_preview_post_expiration( $expiration_settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::form_settings_modules()
     *
     * @since WPUF_SINCE
     */
    public function form_settings_modules( $settings ) {
        return $this->form_settings_preview()->form_settings_modules( $settings );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::add_taxonomy_restriction_section()
     *
     * @since WPUF_SINCE
     */
    public function add_taxonomy_restriction_section( $sections ) {
        return $this->form_settings_preview()->add_taxonomy_restriction_section( $sections );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Form_Settings_Preview::add_taxonomy_restriction_fields()
     *
     * @since WPUF_SINCE
     */
    public function add_taxonomy_restriction_fields( $fields ) {
        return $this->form_settings_preview()->add_taxonomy_restriction_fields( $fields );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Subscription_Preview::wpuf_payment_gateways()
     *
     * @since WPUF_SINCE
     */
    public function wpuf_payment_gateways( $gateways ) {
        return $this->subscription_preview()->wpuf_payment_gateways( $gateways );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::admin_menu_top()
     *
     * @since WPUF_SINCE
     */
    public function admin_menu_top() {
        return $this->promo_pages()->admin_menu_top();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::reg_form_menu_action()
     *
     * @since WPUF_SINCE
     */
    public function reg_form_menu_action() {
        return $this->promo_pages()->reg_form_menu_action();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::admin_menu()
     *
     * @since WPUF_SINCE
     */
    public function admin_menu() {
        return $this->promo_pages()->admin_menu();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::admin_coupon_page()
     *
     * @since WPUF_SINCE
     */
    public function admin_coupon_page() {
        return $this->promo_pages()->admin_coupon_page();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::add_meta_box_post()
     *
     * @since WPUF_SINCE
     */
    public function add_meta_box_post() {
        return $this->promo_pages()->add_meta_box_post();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::show_banner_metabox()
     *
     * @since WPUF_SINCE
     */
    public function show_banner_metabox() {
        return $this->promo_pages()->show_banner_metabox();
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::post_form_templates()
     *
     * @since WPUF_SINCE
     */
    public function post_form_templates( $integrations ) {
        return $this->promo_pages()->post_form_templates( $integrations );
    }

    /**
     * @see \WeDevs\Wpuf\Free\Promo_Pages::pro_form_previews()
     *
     * @since WPUF_SINCE
     */
    public function pro_form_previews( $integrations ) {
        return $this->promo_pages()->pro_form_previews( $integrations );
    }

    /**
     * @since WPUF_SINCE
     *
     * @return Settings_Preview
     */
    private function settings_preview() {
        if ( ! $this->settings_preview ) {
            $this->settings_preview = new Settings_Preview( $this );
        }

        return $this->settings_preview;
    }

    /**
     * @since WPUF_SINCE
     *
     * @return Modules_Preview
     */
    private function modules_preview() {
        if ( ! $this->modules_preview ) {
            $this->modules_preview = new Modules_Preview( $this );
        }

        return $this->modules_preview;
    }

    /**
     * @since WPUF_SINCE
     *
     * @return Form_Settings_Preview
     */
    private function form_settings_preview() {
        if ( ! $this->form_settings_preview ) {
            $this->form_settings_preview = new Form_Settings_Preview( $this );
        }

        return $this->form_settings_preview;
    }

    /**
     * @since WPUF_SINCE
     *
     * @return Subscription_Preview
     */
    private function subscription_preview() {
        if ( ! $this->subscription_preview ) {
            $this->subscription_preview = new Subscription_Preview( $this );
        }

        return $this->subscription_preview;
    }

    /**
     * @since WPUF_SINCE
     *
     * @return Promo_Pages
     */
    private function promo_pages() {
        if ( ! $this->promo_pages ) {
            $this->promo_pages = new Promo_Pages( $this );
        }

        return $this->promo_pages;
    }
}
