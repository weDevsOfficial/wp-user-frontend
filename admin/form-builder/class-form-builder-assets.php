<?php

/**
 * The Assets Class
 */
class WeForms_Form_Builder_Assets {

    public function __construct() {
        $this->init_actions();
    }

    public function init_actions() {

        add_action( 'in_admin_header', array( $this, 'remove_admin_notices' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'builder_enqueue_scripts' ) );
        add_action( 'admin_print_scripts', array( $this, 'builder_mixins_script' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer_js_templates' ) );

        add_action( 'wpuf-form-builder-template-builder-stage-submit-area', array( $this, 'add_form_submit_area' ) );

        add_action( 'wpuf-form-builder-tabs-contact_form', array( $this, 'add_primary_tabs' ) );
        add_action( 'wpuf-form-builder-tab-contents-contact_form', array( $this, 'add_primary_tab_contents' ) );

        add_action( 'wpuf-form-builder-settings-tabs-contact_form', array( $this, 'add_settings_tabs' ) );
        add_action( 'wpuf-form-builder-settings-tab-contents-contact_form', array( $this, 'add_settings_tab_contents' ) );

        do_action( 'weforms_form_builder_init' );
    }

    /**
     * Remove all kinds of admin notices
     *
     * Since we don't have much space left on top of the page,
     * we have to remove all kinds of admin notices
     *
     * @since 1.2.6
     *
     * @return void
     */
    public function remove_admin_notices() {
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    }


    public function builder_enqueue_scripts() {
        $prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        weforms()->scripts->enqueue_backend();

        $recaptcha = weforms_get_settings( 'recaptcha' );

        $wpuf_form_builder = apply_filters( 'wpuf-form-builder-localize-script', array(
            'i18n'                => $this->i18n(),
            'panel_sections'      => weforms()->fields->get_field_groups(),
            'field_settings'      => weforms()->fields->get_js_settings(),
            'pro_link'            => self::get_pro_url(),
            'site_url'            => site_url('/'),
            'defaultNotification' => array(
                'active'       => 'true',

                'type'         => 'email',
                'smsTo'        => '',
                'smsText'      => '[{form_name}] ' . __( 'New Form Submission', 'weforms' ) . ' #{entry_id}',

                'name'         => 'Admin Notification',
                'subject'      => '[{form_name}] New Form Submission #{entry_id}',
                'to'           => '{admin_email}',
                'replyTo'      => '',
                'message'      => '{all_fields}',
                'fromName'     => '',
                'fromAddress'  => '{admin_email}',
                'cc'           => '',
                'bcc'          => '',
                'weforms_cond' => array(
                    'condition_status' => 'no',
                    'cond_logic'       => 'any',
                    'conditions'       => array(
                        array(
                            'name'     => '',
                            'operator' => '=',
                            'option'   => ''
                        )
                    )
                )
            ),
            'integrations'     => weforms()->integrations->get_integration_js_settings(),
            'recaptcha_site'   => isset( $recaptcha->key ) ? $recaptcha->key : '',
            'recaptcha_secret' => isset( $recaptcha->secret ) ? $recaptcha->secret : '',
        ) );

        // mixins
        $wpuf_mixins = array(
            'root'           => apply_filters( 'weforms-builder-js-root-mixins', array( 'weforms_mixin_builder_root' ) ),
            'builder_stage'  => apply_filters( 'weforms-builder-js-builder-stage-mixins', array( 'weforms_mixin_builder_stage' ) ),
            'form_fields'    => apply_filters( 'weforms-builder-js-form-fields-mixins', array() ),
            'field_options'  => apply_filters( 'weforms-builder-js-field-options-mixins', array() ),
        );

        $weforms = apply_filters( 'weforms_localized_script', array(
            'nonce'           => wp_create_nonce( 'weforms' ),
            'confirm'         => __( 'Are you sure?', 'weforms' ),
            'is_pro'          => class_exists( 'WeForms_Pro' ) ? 'true' : 'false',
            'has_payment'     => class_exists( 'WeForms_Payment' ) ? 'true' : 'false',
            'has_sms'         => class_exists( 'WeForms_SMS_Notification' ) ? 'true' : 'false',
            'routes'          => $this->get_vue_routes(),
            'routeComponents' => array( 'default' => null ),
            'mixins'          => array( 'default' => null ),
            'assetsURL'       => WEFORMS_ASSET_URI,
            'shortcodes'      => $this->shortcodes(),
        ) );

        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_form_builder', $wpuf_form_builder );
        wp_localize_script( 'wpuf-form-builder-mixins', 'wpuf_mixins', $wpuf_mixins );
        wp_localize_script( 'weforms-mixins', 'weForms', $weforms );

        do_action( 'weforms_admin_after_scripts_loaded' );
    }

    /**
     * The pro url
     *
     * @return string
     */
    public static function get_pro_url() {
        $link = 'https://wedevs.com/weforms-upgrade/';

        if ( $aff = get_option( '_weforms_aff_ref' ) ) {
            $link = add_query_arg( array( 'ref' => $aff ), $link );
        }

        return $link;
    }

    /**
     * SPA Routes
     *
     * @return array
     */
    public function get_vue_routes() {
        $routes = array(
            array(
                'path'      => '/',
                'name'      => 'home',
                'component' => 'Home'
            ),
            array(
                'path'      => '/form/:id',
                'component' => 'FormHome',
                'children'  => array(
                    array(
                        'path'      => '',
                        'name'      => 'form',
                        'component' => 'SingleForm'
                    ),
                    array(
                        'path'      => 'entries',
                        'component' => 'FormEntriesHome',
                        'children'  => array(
                            array(
                                'path'      => '',
                                'name'      => 'formEntries',
                                'component' => 'FormEntries',
                                'props'     => true
                            ),
                            array(
                                'path'      => ':entryid',
                                'name'      => 'formEntriesSingle',
                                'component' => 'FormEntriesSingle'
                            )
                        )
                    ),
                    array(
                        'path'      => 'edit',
                        'name'      => 'edit',
                        'component' => 'FormEditComponent'
                    )
                )
            ),
            array(
                'path'      => '/tools',
                'name'      => 'tools',
                'component' => 'Tools'
            ),
            array(
                'path'      => '/entries',
                'name'      => 'entries',
                'component' => 'Entries'
            ),
            array(
                'path'      => '/help',
                'name'      => 'help',
                'component' => 'Help'
            ),
            array(
                'path'      => '/premium',
                'name'      => 'premium',
                'component' => 'Premium'
            ),
            array(
                'path'      => '/settings',
                'name'      => 'settings',
                'component' => 'Settings'
            ),
        );

        return apply_filters( 'weforms_vue_routes', $routes );
    }

    /**
     * Print js scripts in admin head
     *
     * @since 2.5
     *
     * @return void
     */
    public function builder_mixins_script() {
        ?>
            <script>
                if (!window.Promise) {
                    var promise_polyfill = document.createElement('script');
                    promise_polyfill.setAttribute('src','https://cdn.polyfill.io/v2/polyfill.min.js');
                    document.head.appendChild(promise_polyfill);
                }
            </script>

            <script>
                var wpuf_form_builder_mixins = function(mixins, mixin_parent) {
                    if (!mixins || !mixins.length) {
                        return [];
                    }

                    if (!mixin_parent) {
                        mixin_parent = window;
                    }

                    return mixins.map(function (mixin) {
                        return mixin_parent[mixin];
                    });
                };
            </script>
        <?php
    }

    /**
     * Include vue component templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_footer_js_templates() {

        if ( ! defined( 'WPUF_ASSET_URI' ) ) {
            define( 'WPUF_ASSET_URI', WEFORMS_ROOT_URI . '/assets' );
        }

        include WEFORMS_ROOT . '/assets/wpuf/js-templates/form-components.php';
        include WEFORMS_ROOT . '/assets/js-templates/form-components.php';
        include WEFORMS_ROOT . '/assets/js-templates/spa-components.php';

        do_action( 'wpuf-form-builder-add-js-templates' );
    }

    /**
     * i18n translatable strings
     *
     * @since 2.5
     *
     * @return array
     */
    private function i18n() {
        return apply_filters( 'wpuf-form-builder-i18n', array(
            'advanced_options'      => __( 'Advanced Options', 'weforms' ),
            'delete_field_warn_msg' => __( 'Are you sure you want to delete this field?', 'weforms' ),
            'yes_delete_it'         => __( 'Yes, delete it', 'weforms' ),
            'no_cancel_it'          => __( 'No, cancel it', 'weforms' ),
            'ok'                    => __( 'OK', 'weforms' ),
            'cancel'                => __( 'Cancel', 'weforms' ),
            'close'                 => __( 'Close', 'weforms' ),
            'disable'               => __( 'Disable', 'weforms' ),
            'last_choice_warn_msg'  => __( 'This field must contain at least one choice', 'weforms' ),
            'option'                => __( 'Option', 'weforms' ),
            'row'                   => __( 'Row', 'weforms' ),
            'column'                => __( 'Column', 'weforms' ),
            'last_column_warn_msg'  => __( 'This field must contain at least one column', 'weforms' ),
            'is_a_pro_feature'      => __( 'is available in Pro version', 'weforms' ),
            'pro_feature_msg'       => __( 'Please upgrade to the Pro version to unlock all these awesome features', 'weforms' ),
            'upgrade_to_pro'        => __( 'Get the Pro version', 'weforms' ),
            'select'                => __( 'Select', 'weforms' ),
            'saved_form_data'       => __( 'Saved form data', 'weforms' ),
            'unsaved_changes'       => __( 'You have unsaved changes.', 'weforms' ),
            'areYouSureToLeave'     => __( 'Are you sure to leave this page?', 'weforms' ),
            'copy_shortcode'        => __( 'Click to copy shortcode', 'weforms' ),

            'selectAnImage'         => __( 'Select an image', 'weforms' ),
            'pleaseSelectAnImage'   => __( 'Please select an image', 'weforms' ),
            'uploadAnImage'         => __( 'Upload an image', 'weforms' ),

            'shareYourForm'         => __( 'Share Your Form', 'weforms' ),
            'shareYourFormDesc'  => __( "Sharing your form enables <strong>anyone</strong> to view and submit the form without inserting the shortcode to a page.", 'weforms' ),
            'shareYourFormText'     => __( "Anyone with this URL will be able to view and submit this form.", 'weforms' ),
            'areYouSure'            => __( 'Are you sure?', 'weforms' ),
            'areYouSureDesc'        => __( 'Anyone with existing URL won\'t be able to view and submit the form anymore.', 'weforms' ),
            'disableSharing'        => __( 'Disable Sharing', 'weforms' ),

        ) );
    }

    /**
     * Add buttons in form submit area
     *
     * @return void
     */
    public function add_form_submit_area() {
        ?>
            <input @click.prevent="" type="submit" name="submit" v-model="settings.submit_text">
        <?php
    }

    /**
     * Additional primary tabs
     *
     * @return void
     */
    public function add_primary_tabs() {
        $tabs = apply_filters( 'wpuf_contact_form_editor_tabs', array(
            'notification' => __( 'Notifications', 'weforms' ),
            'integration'  => __( 'Integrations', 'weforms' )
        ) );

        foreach ($tabs as $key => $label) {
            ?>
            <a href="#wpuf-form-builder-tab-<?php echo $key; ?>" :class="['nav-tab', isActiveTab( '<?php echo $key; ?>' ) ? 'nav-tab-active' : '']" v-on:click.prevent="makeActive('<?php echo $key; ?>')"><?php echo $label; ?></a>
            <?php
        }
    }

    public function add_primary_tab_contents() {
        include dirname( __FILE__ ) . '/views/notification-integration.php';
    }

    /**
     * Add settings tabs
     *
     * @return void
     */
    public function add_settings_tabs() {
        ?>

            <a href="#" :class="['nav-tab', isActiveSettingsTab( 'form' ) ? 'nav-tab-active' : '']" v-on:click.prevent="makeActiveSettingsTab( 'form' )" class="nav-tab"><?php _e( 'Form Settings', 'weforms' ); ?></a>
            <a href="#" :class="['nav-tab', isActiveSettingsTab( 'restriction' ) ? 'nav-tab-active' : '']" v-on:click.prevent="makeActiveSettingsTab( 'restriction' )" class="nav-tab"><?php _e( 'Submission Restriction', 'weforms' ); ?></a>
            <a href="#" :class="['nav-tab', isActiveSettingsTab( 'display' ) ? 'nav-tab-active' : '']" v-on:click.prevent="makeActiveSettingsTab( 'display' )" class="nav-tab"><?php _e( 'Display Settings', 'weforms' ); ?></a>

            <?php do_action( 'wpuf_contact_form_settings_tab' ); ?>

        <?php
    }

    /**
     * Add settings tabs
     *
     * @return void
     */
    public function add_settings_tab_contents() {
        ?>
            <div id="wpuf-metabox-settings" class="tab-content" v-show="isActiveSettingsTab('form')">
                <?php include_once dirname( __FILE__ ) . '/views/form-settings.php'; ?>
            </div>

            <div id="wpuf-metabox-settings-restriction" class="tab-content" v-show="isActiveSettingsTab('restriction')">
                <?php include_once dirname( __FILE__ ) . '/views/submission-restriction.php'; ?>
            </div>

            <div id="wpuf-metabox-settings-display" class="tab-content" v-show="isActiveSettingsTab('display')">
                <?php include_once dirname( __FILE__ ) . '/views/display-settings.php'; ?>
            </div>

            <?php do_action( 'wpuf_contact_form_settings_tab_content' ); ?>

        <?php
    }


    public function shortcodes( $type = '' ) {
        $shortcodes = [];

        $shortcodes['user'] = [
            'title' => __( 'User', 'weforms' ),
            'codes' => [
                'first_name'        => [ 'title' => __( 'First Name', 'weforms' ), 'default' => 'reader' ],
            ]
        ];

        if ( !empty( $type ) && !empty( $shortcodes[ $type ] ) ) {
            return $shortcodes[ $type ];
        }

        return $shortcodes;
    }
}
