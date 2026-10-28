<?php

namespace WeDevs\Wpuf\Admin\Forms;

use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Free\Pro_Prompt;
use WeDevs\Wpuf\Builder\HookBridge;
use WeDevs\Wpuf\Builder\HookDeprecations;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Form Builder framework
 */
class Admin_Form_Builder {
    /**
     * Transient key to store pro field assets info
     *
     * @since 4.1.0
     *
     * @var string
     */
    const PRO_FIELD_ASSETS = 'wpuf_pro_field_assets';

    /**
     * Form Settings
     *
     * @since 2.5
     *
     * @var string
     */
    private $settings = [];

    /**
     * The builder created for the current request (the builder screen, or the
     * builder boot of the React admin app).
     *
     * @since WPUF_SINCE
     *
     * @var Admin_Form_Builder|null
     */
    public static $current = null;

    /**
     * Stylesheets enqueue_app_assets() enqueued, null before it ran.
     *
     * @var string[]|null
     */
    private static $app_styles = null;

    /**
     * Class contructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct( $settings ) {
        global $post;
        $defaults = [
            'form_type'         => '',
            // e.g 'post', 'profile' etc
            'post_type'         => '',
            // e.g 'wpuf_forms', 'wpuf_profile' etc,
            'form_settings_key' => '',
            'post_id'           => 0,
            'shortcodes'        => [],
            // [ [ 'name' => 'wpuf_form', 'type' => 'profile' ], [ 'name' => 'wpuf_form', 'type' => 'registration' ] ]
        ];
        $this->settings = wp_parse_args( $settings, $defaults );
        self::$current  = $this;
        // Set the form as the global $post: builder code in free and pro
        // (Post_Form, Fields_Manager) reads it on this screen.
        $post = get_post( $this->settings['post_id'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        // if we have an existing post, then let's start
        if ( ! empty( $post->ID ) ) {
            add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
            add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
            add_action( 'admin_footer', [ $this, 'custom_dequeue' ] );
            add_action( 'admin_footer', [ $this, 'admin_footer' ] );
        }

        // The stage submit area hook is retired (Builder\HookDeprecations); develop's
        // submit preview was hidden, so the React stage has no counterpart.
    }

    /**
     * Enqueue admin scripts
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        $this->enqueue_builder_assets();

        // React form builder bundle, registered in the shared Assets registry (task 2.5b).
        wp_enqueue_script( 'wpuf-form-builder-screen' );
        wpuf()->platform()->get( BootPayload::class )->attach( 'form_builder', 'wpuf-form-builder-screen' );

        $data = $this->localize_data();

        wp_localize_script( 'wpuf-form-builder-screen', 'wpuf_form_builder', $data['wpuf_form_builder'] );
        // Develop printed the single-instance field list as its own global; scripts
        // that read window.wpuf_single_objects keep working.
        wp_localize_script( 'wpuf-form-builder-screen', 'wpuf_single_objects', $data['wpuf_single_objects'] );
        // Retired Vue mixin filters still fire (deprecated when an outside plugin
        // listens) and window.wpuf_mixins stays defined (4.4g).
        wp_localize_script( 'wpuf-form-builder-screen', 'wpuf_mixins', $data['wpuf_mixins'] );
    }

    /**
     * Enqueue the builder's libraries and fire the builder enqueue hooks, in
     * develop's order. Pro, modules and add-ons enqueue their builder assets on
     * these hooks.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_builder_assets() {
        self::enqueue_libraries();
    }

    /**
     * The builder's libraries and enqueue hooks (enqueue_builder_assets()),
     * without a builder: the React admin app page loads them once and opens
     * any form's builder without a page load.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public static function enqueue_libraries() {
        wp_enqueue_style( 'wpuf-font-awesome' );
        wp_enqueue_style( 'wpuf-dialog' );
        wp_enqueue_style( 'wpuf-selectize' );
        wp_enqueue_style( 'wpuf-tooltip' );
        wp_enqueue_style( 'wpuf-jquery-ui' );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'wpuf-admin-form-builder' );
        do_action( 'wpuf_form_builder_enqueue_style' );

        wp_enqueue_media();
        // Rich text settings (registration email bodies) use the WordPress editor,
        // as develop's wp_editor() did.
        wp_enqueue_editor();

        wp_enqueue_script( 'wpuf-subscriptions' );
        wp_enqueue_script( 'wpuf-dialog' );
        wp_enqueue_script( 'wpuf-selectize' );
        wp_enqueue_script( 'wpuf-tooltip' );
        wp_enqueue_script( 'wpuf-timepicker' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'zxcvbn' );
        wp_enqueue_script( 'password-strength-meter' );
        wp_enqueue_script( 'wp-color-picker' );

        // The stage's rich text previews are a static TinyMCE mockup: they need the
        // editor and skin styles, which the Vue text-editor template enqueued.
        wp_enqueue_style( 'editor-css', includes_url( 'css/editor.css' ), [], get_bloginfo( 'version' ) );
        wp_enqueue_style( 'skin-css', includes_url( 'js/tinymce/skins/lightgray/skin.min.css' ), [], get_bloginfo( 'version' ) );

        do_action( 'wpuf_form_builder_enqueue_after_mixins' );
        do_action( 'wpuf_form_builder_enqueue_after_components' );

        do_action( 'wpuf_form_builder_enqueue_after_main_instance' );
    }

    /**
     * The builder on the React admin app page: its libraries, bundle and
     * footer steps, as the builder screen loads them. Each form's data comes
     * over REST when its builder opens (Builder\BuilderBoot).
     *
     * @since WPUF_SINCE
     *
     * @return array Window globals before the first builder opens.
     */
    public static function enqueue_app_assets() {
        if ( null === self::$app_styles ) {
            $before = wp_styles()->queue;

            self::enqueue_libraries();
            wp_enqueue_script( 'wpuf-form-builder-screen' );
                add_action( 'admin_footer', [ __CLASS__, 'custom_dequeue' ] );
            add_action( 'admin_footer', [ __CLASS__, 'retired_footer' ] );

            self::$app_styles = array_values( array_diff( wp_styles()->queue, $before ) );
        } else {
            // The hooks fire once; a second builder group (registration forms)
            // gets the same stylesheets.
            array_map( 'wp_enqueue_style', self::$app_styles );
        }

        // Scripts that read these at load time find them; a builder's own
        // values replace them when it opens.
        return [
            'wpuf_form_builder'   => new \stdClass(),
            'wpuf_single_objects' => array_values( self::single_objects() ),
        ];
    }

    /**
     * The builder's hidden form inputs: form ID, form type, settings key and
     * the save nonce; the app builds the form with them.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function form_attributes() {
        return [
            'form_id'           => (int) $this->settings['post_id'],
            'form_type'         => $this->settings['form_type'],
            'post_type'         => $this->settings['post_type'],
            'form_settings_key' => $this->settings['form_settings_key'],
            'nonce'             => wp_create_nonce( 'wpuf_form_builder_save_form' ),
        ];
    }

    /**
     * Unique fields: only one of each can be added to a form.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public static function single_objects() {
        $single_objects = apply_filters(
            'wpuf_single_form_field',
            [
                'post_title',
                'post_content',
                'post_excerpt',
                'featured_image',
                'user_login',
                'first_name',
                'last_name',
                'nickname',
                'user_email',
                'user_url',
                'user_bio',
                'password',
                'user_avatar',
                'taxonomy',
                'cloudflare_turnstile',
                'recaptcha',
                'signature_field',
            ]
        );

        return array_merge( $single_objects, array_keys( get_taxonomies() ) );
    }

    /**
     * The data the builder reads: `wpuf_form_builder`, `wpuf_single_objects`
     * and `wpuf_mixins`. Printed by the builder screen, and returned over REST
     * by the React admin app's builder boot (Builder\BuilderBoot).
     *
     * @since WPUF_SINCE
     *
     * @return array { wpuf_form_builder, wpuf_single_objects, wpuf_mixins }
     */
    public function localize_data() {
        global $post;

        $single_objects = self::single_objects();

        /*
         * Data required for building the form
         */
        wpuf_require_once( WPUF_ROOT . '/includes/Free/Pro_Prompt.php' );
        $lock_icon = WPUF_ASSET_URI . '/images/crown-circle.svg';
        $free_icon = WPUF_ASSET_URI . '/images/free-circle.svg';

        // Filter pro taxonomy fields and check if any were removed
        $original_fields = wpuf_get_form_fields( $post->ID );
        $filtered_fields = $this->filter_pro_taxonomy_fields( $original_fields );
        $has_hidden_taxonomies = $this->has_filtered_taxonomies( $original_fields, $filtered_fields );
        $hidden_taxonomy_ids = $this->get_hidden_taxonomy_ids( $original_fields, $filtered_fields );

        // Load icon configuration
        $icon_config = $this->get_icon_config();

        // AI configuration: one answer for every screen (Provider_Settings::status()).
        $ai_configured = wpuf()->platform()->get( Provider_Settings::class )->status()['configured'];
        $ai_settings_url = admin_url( 'admin.php?page=wpuf-settings#wpuf_ai' );

        $wpuf_form_builder = apply_filters(
            'wpuf_form_builder_localize_script',
            [
                'i18n'             => $this->i18n(),
                'post'             => $post,
                'form_type'        => $post->post_type,
                'form_fields'      => $filtered_fields,
                'has_hidden_taxonomies' => $has_hidden_taxonomies,
                'hidden_taxonomy_ids' => $hidden_taxonomy_ids,
                'panel_sections'   => wpuf()->fields->get_field_groups(),
                'field_settings'   => wpuf()->fields->get_js_settings(),
                'form_settings'    => wpuf_get_form_settings( $post->ID ),
                'notifications'    => wpuf_get_form_notifications( $post->ID ),
                'pro_link'         => Pro_Prompt::get_pro_url(),
                'site_url'         => site_url( '/' ),
                'asset_url'        => WPUF_ASSET_URI,
                'root_dir'         => WPUF_ROOT,
                'recaptcha_site'   => wpuf_get_option( 'recaptcha_public', 'wpuf_general' ),
                // Secret keys are never printed: the builder only needs to know a key
                // is set, so these carry a marker ('1') instead of the value.
                'recaptcha_secret' => '' !== (string) wpuf_get_option( 'recaptcha_private', 'wpuf_general' ) ? '1' : '',
                'turnstile_site'   => wpuf_get_option( 'turnstile_site_key', 'wpuf_general' ),
                'turnstile_secret' => '' !== (string) wpuf_get_option( 'turnstile_secret_key', 'wpuf_general' ) ? '1' : '',
                'nonce'            => wp_create_nonce( 'form-builder-setting-nonce' ),
                'is_pro_active'    => wpuf_is_pro_active(),
                'pro_asset_url'    => defined( 'WPUF_PRO_ASSET_URI' ) ? WPUF_PRO_ASSET_URI : '',
                'settings_titles'  => wpuf_get_post_form_builder_setting_menu_titles(),
                'settings_items'   => wpuf_get_post_form_builder_setting_menu_contents(),
                'lock_icon'        => $lock_icon,
                'free_icon'        => $free_icon,
                'icons'            => $icon_config['icons'],
                'defaultIcons'     => $icon_config['defaultIcons'],
                'ai_configured'    => $ai_configured,
                'ai_settings_url'  => $ai_settings_url,
                'forms'            => $this->get_form_list(),
                'shortcodes'       => $this->settings['shortcodes'],
                // Roles the current user may assign, as the Vue visibility template listed.
                'roles'            => wp_list_pluck( get_editable_roles(), 'name' ),
                'subscriptions'    => $this->get_subscriptions(),
                'preview_url'      => get_wpuf_preview_page(),
                // Prerequisites the builder points out (WPUF_SINCE): the Turnstile
                // switch, WordPress registration, the Account page post types.
                'turnstile_enabled'     => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_turnstile', 'wpuf_general', 'off' ) ),
                'registration_open'     => is_multisite() ? in_array( get_site_option( 'registration' ), [ 'user', 'all' ], true ) : (bool) get_option( 'users_can_register' ),
                'registration_url'      => is_multisite() ? network_admin_url( 'settings.php#registration' ) : admin_url( 'options-general.php#users_can_register' ),
                'account_post_types'    => array_values( array_filter( array_map( 'strval', (array) wpuf_get_option( 'cp_on_acc_page', 'wpuf_my_account', [ 'post' ] ) ) ) ),
                'admin_url'             => admin_url(),
                // Site payment setup, for the settings recommendations: a form
                // that charges needs payments on, a gateway and a payment page.
                'site_payments'         => [
                    'enabled'      => wpuf_is_checkbox_or_toggle_on( wpuf_get_option( 'enable_payment', 'wpuf_payment', 'off' ) ),
                    'gateways'     => count( array_filter( (array) wpuf_get_option( 'active_gateways', 'wpuf_payment', [] ) ) ),
                    'payment_page' => (bool) wpuf_get_option( 'payment_page', 'wpuf_payment', 0 ),
                    'settings_url' => wpuf_admin_app_url( '/settings', [ 'tab' => 'wpuf_payment' ] ),
                    'plans_url'    => wpuf_admin_app_url( '/subscriptions', [ 'action' => 'new' ] ),
                ],
            ]
        );
        $wpuf_form_builder = wpuf_unset_conditional( $wpuf_form_builder );

        // Fire the PHP hooks the Vue builder views fired (settings rows, tabs, Vue
        // slots) and hand other plugins' output to the React builder per slot.
        $hook_bridge                       = wpuf()->platform()->get(
            HookBridge::class,
            $this->settings['form_type'],
            isset( $wpuf_form_builder['form_settings'] ) ? $wpuf_form_builder['form_settings'] : []
        );
        $wpuf_form_builder['legacy_slots'] = $hook_bridge->collect(
            isset( $wpuf_form_builder['settings_items'] ) ? $wpuf_form_builder['settings_items'] : []
        );

        // The builder prints these setting texts as HTML, like develop's views did
        // (`wp_kses_post`), so they are filtered the same way before they leave PHP.
        if ( isset( $wpuf_form_builder['settings_items'] ) && is_array( $wpuf_form_builder['settings_items'] ) ) {
            $wpuf_form_builder['settings_items'] = self::kses_setting_texts( $wpuf_form_builder['settings_items'] );
        }
        $wpuf_form_builder['wpuf_single_objects'] = $single_objects;

        // Retired Vue mixin filters still fire (deprecated when an outside plugin
        // listens).
        $retired = wpuf()->platform()->get( HookDeprecations::class );

        // The retired script-dependency filter still fires too; the React bundle
        // declares its own dependencies, so the result is not used.
        $deps = $retired->filter( 'wpuf_form_builder_js_deps', [ 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-resizable', 'underscore', 'wpuf-sweetalert2', 'wpuf-selectize', 'wpuf-tooltip' ] );

        // Develop's post form builder ran its own filter inside that one (Admin_Form::js_dependencies()).
        if ( 'wpuf_forms' === $this->settings['post_type'] ) {
            $retired->filter( 'wpuf_form_builder_wpuf_forms_js_deps', $deps );
        }

        return [
            'wpuf_form_builder'   => $wpuf_form_builder,
            'wpuf_single_objects' => array_values( $single_objects ),
            'wpuf_mixins'         => [
                'root'          => $retired->filter( 'wpuf_form_builder_js_root_mixins', [] ),
                'builder_stage' => $retired->filter( 'wpuf_form_builder_js_builder_stage_mixins', [] ),
                'form_fields'   => $retired->filter( 'wpuf_form_builder_js_form_fields_mixins', [] ),
                'field_options' => $retired->filter( 'wpuf_form_builder_js_field_options_mixins', [] ),
            ],
        ];
    }

    /**
     * Include vue component templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_footer() {
        self::retired_footer();
    }

    /**
     * Vue cleanup: x-template includes removed (React renders all components).
     * The retired template hooks still fire once per builder page; their Vue
     * output is discarded (Builder\HookDeprecations, 4.4g).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public static function retired_footer() {
        $retired = wpuf()->platform()->get( HookDeprecations::class );
        $retired->action( 'wpuf_form_builder_add_js_templates' );
        $retired->fire_template_actions();
    }

    /**
     * Dequeue style and script to avoid conflict with Imagify Image Optimizer plugin
     *
     * @since 2.5
     *
     * @param string $template
     * @param string $file_path
     *
     * @return void
     */
    public static function custom_dequeue() {
        wp_dequeue_style( 'imagify-css-sweetalert' );
        wp_deregister_style( 'imagify-css-sweetalert' );
        wp_dequeue_script( 'imagify-js-sweetalert' );
        wp_deregister_script( 'imagify-js-sweetalert' );
    }

    /**
     * WordPress i18n translatable strings
     *
     * @since 2.5
     *
     * @return array
     */
    private function i18n() {
        $crown_icon     = WPUF_ASSET_URI . '/images/crown-white.svg';
        $field_messages = $this->get_pro_field_messages();

        return apply_filters(
            'wpuf_form_builder_i18n', [
                'advanced_options'        => __( 'Advanced Options', 'wp-user-frontend' ),
                'delete_field_warn_title' => __( 'Delete Field Confirmation', 'wp-user-frontend' ),
                'delete_field_warn_msg'   => __(
                    'Are you sure you want to delete this field? This action cannot be undone.', 'wp-user-frontend'
                ),
                'yes_delete_it'           => __( 'Yes, delete it', 'wp-user-frontend' ),
                'no_cancel_it'            => __( 'Cancel', 'wp-user-frontend' ),
                'ok'                      => __( 'Okay', 'wp-user-frontend' ),
                'cancel'                  => __( 'Cancel', 'wp-user-frontend' ),
                'close'                   => __( 'Close', 'wp-user-frontend' ),
                'last_choice_warn_msg'    => __( 'This field must contain at least one choice', 'wp-user-frontend' ),
                'option'                  => __( 'Option', 'wp-user-frontend' ),
                'column'                  => __( 'Column', 'wp-user-frontend' ),
                'last_column_warn_msg'    => __( 'This field must contain at least one column', 'wp-user-frontend' ),
                'is_a_pro_feature'        => __( 'is a pro feature', 'wp-user-frontend' ),
                'pro_feature_msg'         => '<p class="wpuf-text-gray-500 wpuf-font-medium wpuf-text-xl">' . __(
                    'Please upgrade to the Pro version to unlock all these awesome features',
                    'wp-user-frontend'
                ) . '</p>',
                'upgrade_to_pro'          => __( 'Upgrade to PRO', 'wp-user-frontend' ),
                'select'                  => __( 'Select', 'wp-user-frontend' ),
                'saved_form_data'         => __( 'Saved form data', 'wp-user-frontend' ),
                'unsaved_changes'         => __( 'You have unsaved changes.', 'wp-user-frontend' ),
                'copy_shortcode'          => __( 'Click to copy shortcode', 'wp-user-frontend' ),
                'empty_field_options_msg' => __( 'To view field options, please start adding fields in the builder', 'wp-user-frontend' ),
                'pro_field_message'       => $field_messages,
                'something_went_wrong'    => __( 'Something went wrong. Please try again.', 'wp-user-frontend' ),
                'custom_image'            => __( 'Custom image', 'wp-user-frontend' ),
                'select_icon_or_upload'   => __( 'Select an icon or upload image', 'wp-user-frontend' ),
                'select_icon_image'       => __( 'Select Icon Image', 'wp-user-frontend' ),
                'use_as_icon'             => __( 'Use as Icon', 'wp-user-frontend' ),
                'icons_found'             => __( 'icons found', 'wp-user-frontend' ),
                'icons_available'         => __( 'icons available', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Save form data
     *
     * @since 2.5
     *
     * @param array $data Contains form_fields, form_settings, form_settings_key data
     *
     * @return array
     */
    public static function save_form( $data ) {
        // Forwards to the form store (task 2.4a). The store refuses a post that is
        // not a form or a settings key outside the allowlist; nothing is written
        // then and no fields come back.
        $saved = Stores::forms()->save(
            isset( $data['form_id'] ) ? $data['form_id'] : 0,
            [
                'post_title'        => isset( $data['post_title'] ) ? $data['post_title'] : '',
                'form_fields'       => isset( $data['form_fields'] ) ? $data['form_fields'] : [],
                'form_settings'     => isset( $data['form_settings'] ) ? $data['form_settings'] : [],
                'form_settings_key' => isset( $data['form_settings_key'] ) ? $data['form_settings_key'] : 'wpuf_form_settings',
                'notifications'     => isset( $data['notifications'] ) ? $data['notifications'] : [],
                'integrations'      => isset( $data['integrations'] ) ? $data['integrations'] : null,
            ]
        );

        return is_wp_error( $saved ) ? [] : $saved;
    }

    /**
     * Run `wp_kses_post` on the setting texts the builder renders as HTML
     * (`note`, `long_help`) anywhere in the settings items tree.
     *
     * @since WPUF_SINCE
     *
     * @param array $items Builder settings items.
     *
     * @return array
     */
    protected static function kses_setting_texts( $items ) {
        foreach ( $items as $key => $item ) {
            if ( is_array( $item ) ) {
                $items[ $key ] = self::kses_setting_texts( $item );
            } elseif ( is_string( $item ) && in_array( $key, [ 'note', 'long_help' ], true ) ) {
                $items[ $key ] = wp_kses_post( $item );
            }
        }

        return $items;
    }

    /**
     * Get icon configuration from JSON file
     *
     * @since 4.2.0
     *
     * @return array
     */
    protected function get_icon_config() {
        $config_file = WPUF_ROOT . '/config/icons-config.json';

        if ( ! file_exists( $config_file ) ) {
            $icon_config = [
                'icons' => [],
                'defaultIcons' => [],
            ];

            /** This filter is documented in includes/Admin/Forms/Admin_Form_Builder.php */
            return apply_filters( 'wpuf_form_builder_icon_config', $icon_config );
        }

        $config = json_decode( file_get_contents( $config_file ), true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $icon_config = [
                'icons' => [],
                'defaultIcons' => [],
            ];

            /** This filter is documented in includes/Admin/Forms/Admin_Form_Builder.php */
            return apply_filters( 'wpuf_form_builder_icon_config', $icon_config );
        }

        $icon_config = [
            'icons' => $config['icons'] ?? [],
            'defaultIcons' => $config['defaultIcons'] ?? [],
        ];

        /**
         * Filter the icon configuration for form builder
         *
         * @since 4.1.14
         *
         * @param array $icon_config The icon configuration array
         *                           - icons: array of icon data
         *                           - defaultIcons: array of default icons
         */
        return apply_filters( 'wpuf_form_builder_icon_config', $icon_config );
    }

    /**
     * Get list of forms for the form switcher dropdown.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    protected function get_form_list() {
        $forms = get_posts(
            [
                'post_type'   => $this->settings['post_type'],
                'post_status' => 'any',
                'numberposts' => -1,
                'orderby'     => 'title',
                'order'       => 'ASC',
            ]
        );

        return array_map(
            function ( $form ) {
                return [
                    'id'    => $form->ID,
                    'title' => '' !== $form->post_title ? $form->post_title : __( '(no title)', 'wp-user-frontend' ),
                ];
            },
            $forms
        );
    }

    /**
     * Get subscription packs for visibility settings.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    protected function get_subscriptions() {
        // Same list and order (`_sort_order`) the Vue visibility setting used.
        $packs = wpuf()->subscription->get_subscriptions();
        $packs = is_array( $packs ) ? $packs : [];

        return array_map(
            function ( $pack ) {
                return [
                    'id'    => $pack->ID,
                    'title' => $pack->post_title,
                ];
            },
            $packs
        );
    }

    /**
     * Check and get pro-field related text, image, video etc. to show when user clicks on a pro field
     *
     * @since 4.1.0
     *
     * @return array
     */
    protected function get_pro_field_messages() {
        $info = get_transient( self::PRO_FIELD_ASSETS );

        if ( false === $info ) {
            $url      = 'https://raw.githubusercontent.com/weDevsOfficial/wpuf-util/master/pro-field-assets.json';
            $response = wp_remote_get( $url, [ 'timeout' => 15 ] );
            $info     = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $response ) || ( 200 !== $response['response']['code'] ) ) {
                return [];
            }

            set_transient( self::PRO_FIELD_ASSETS, $info, DAY_IN_SECONDS );
        }

        $info = json_decode( $info, true );

        if ( empty( $info ) ) {
            return [];
        }

        return $info;
    }

    /**
     * Filter out custom taxonomy fields when pro is not active
     *
     * @param array $form_fields
     * @return array
     */
    protected function filter_pro_taxonomy_fields( $form_fields ) {
        // If pro is active, return all fields
        if ( wpuf_is_pro_active() ) {
            return $form_fields;
        }

        // Get free taxonomies (built-in + taxonomies for post/page)
        $free_taxonomies = wpuf_get_free_taxonomies();

        // Filter out custom taxonomy fields that are not in the free list
        $filtered_fields = array();

        foreach ( $form_fields as $field ) {
            // Skip custom taxonomy fields when pro is not active
            if ( isset( $field['input_type'] ) && $field['input_type'] === 'taxonomy' ) {
                if ( isset( $field['name'] ) && ! in_array( $field['name'], $free_taxonomies, true ) ) {
                    continue; // Skip this custom taxonomy field
                }
            }

            // Keep all other fields including built-in taxonomies
            $filtered_fields[] = $field;
        }

        return $filtered_fields;
    }

    /**
     * Check if any custom taxonomy fields exist that would be filtered out
     *
     * @param array $original_fields
     * @param array $filtered_fields (unused but kept for backward compatibility)
     * @return bool
     */
    protected function has_filtered_taxonomies( $original_fields, $filtered_fields ) {
        if ( wpuf_is_pro_active() ) {
            return false;
        }
        // Get free taxonomies (built-in + taxonomies for post/page)
        $free_taxonomies = wpuf_get_free_taxonomies();
        $stack = is_array( $original_fields ) ? $original_fields : [];
        while ( $stack ) {
            $f = array_pop( $stack );
            if ( isset( $f['template'] ) && $f['template'] === 'column_field' && ! empty( $f['inner_fields'] ) && is_array( $f['inner_fields'] ) ) {
                foreach ( $f['inner_fields'] as $inner ) {
                    if ( is_array( $inner ) ) {
                        foreach ( $inner as $child ) {
                            $stack[] = $child;
                        }
                    }
                }
            } elseif ( ! empty( $f['inner_fields'] ) && is_array( $f['inner_fields'] ) ) {
                foreach ( $f['inner_fields'] as $child ) {
                    $stack[] = $child;
                }
            }
            if ( isset( $f['input_type'] ) && $f['input_type'] === 'taxonomy' ) {
                $slug = $f['name'] ?? ( $f['taxonomy'] ?? null );
                if ( $slug && ! in_array( $slug, $free_taxonomies, true ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Extract IDs of hidden taxonomy fields for client-side preservation
     *
     * @param array $original_fields
     * @param array $filtered_fields
     * @return array Array of hidden taxonomy field IDs
     */
    protected function get_hidden_taxonomy_ids( $original_fields, $filtered_fields ) {
        // If pro is active, no fields are hidden
        if ( wpuf_is_pro_active() ) {
            return array();
        }

        $hidden_ids = array();
        // Get free taxonomies (built-in + taxonomies for post/page)
        $free_taxonomies = wpuf_get_free_taxonomies();

        // Extract IDs from filtered fields for quick lookup
        $filtered_ids = array();
        foreach ( $filtered_fields as $field ) {
            if ( isset( $field['id'] ) ) {
                $filtered_ids[] = $field['id'];
            }
        }

        // Find original fields that are custom taxonomy fields and were filtered out
        foreach ( $original_fields as $field ) {
            if ( isset( $field['input_type'] ) && $field['input_type'] === 'taxonomy' ) {
                if ( isset( $field['name'] ) && ! in_array( $field['name'], $free_taxonomies, true ) ) {
                    if ( isset( $field['id'] ) && ! in_array( $field['id'], $filtered_ids, true ) ) {
                        $hidden_ids[] = $field['id'];
                    }
                }
            }
        }

        return $hidden_ids;
    }
}
