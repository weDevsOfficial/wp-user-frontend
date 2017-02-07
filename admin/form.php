<?php
/**
 * Post Forms or wp_forms form builder class
 *
 * @package WP User Frontend
 */

class WPUF_Admin_Form {
    /**
     * Form type of which we're working on
     *
     * @var string
     */
    private $form_type = 'post';

    /**
     * Form settings key
     *
     * @var string
     */
    private $form_settings_key = 'wpuf_form_settings';

    /**
     * WP post types
     *
     * @var string
     */
    private $wp_post_types = array();

    /**
     * Add neccessary actions and filters
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', array($this, 'register_post_type') );

        $user_frontend = sanitize_title( __( 'User Frontend', 'wpuf' ) );
        add_action( "load-{$user_frontend}_page_wpuf-post-forms", array( $this, 'post_forms_builder_init' ) );
    }

    /**
     * Register form post types
     *
     * @return void
     */
    public function register_post_type() {
        $capability = wpuf_admin_role();

        register_post_type( 'wpuf_forms', array(
            'label'           => __( 'Forms', 'wpuf' ),
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false, //false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => array('title'),
            'capabilities' => array(
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ),
            'labels' => array(
                'name'               => __( 'Forms', 'wpuf' ),
                'singular_name'      => __( 'Form', 'wpuf' ),
                'menu_name'          => __( 'Forms', 'wpuf' ),
                'add_new'            => __( 'Add Form', 'wpuf' ),
                'add_new_item'       => __( 'Add New Form', 'wpuf' ),
                'edit'               => __( 'Edit', 'wpuf' ),
                'edit_item'          => __( 'Edit Form', 'wpuf' ),
                'new_item'           => __( 'New Form', 'wpuf' ),
                'view'               => __( 'View Form', 'wpuf' ),
                'view_item'          => __( 'View Form', 'wpuf' ),
                'search_items'       => __( 'Search Form', 'wpuf' ),
                'not_found'          => __( 'No Form Found', 'wpuf' ),
                'not_found_in_trash' => __( 'No Form Found in Trash', 'wpuf' ),
                'parent'             => __( 'Parent Form', 'wpuf' ),
            ),
        ) );

        register_post_type( 'wpuf_profile', array(
            'label'           => __( 'Registraton Forms', 'wpuf' ),
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => array('title'),
            'capabilities' => array(
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ),
            'labels' => array(
                'name'               => __( 'Forms', 'wpuf' ),
                'singular_name'      => __( 'Form', 'wpuf' ),
                'menu_name'          => __( 'Registration Forms', 'wpuf' ),
                'add_new'            => __( 'Add Form', 'wpuf' ),
                'add_new_item'       => __( 'Add New Form', 'wpuf' ),
                'edit'               => __( 'Edit', 'wpuf' ),
                'edit_item'          => __( 'Edit Form', 'wpuf' ),
                'new_item'           => __( 'New Form', 'wpuf' ),
                'view'               => __( 'View Form', 'wpuf' ),
                'view_item'          => __( 'View Form', 'wpuf' ),
                'search_items'       => __( 'Search Form', 'wpuf' ),
                'not_found'          => __( 'No Form Found', 'wpuf' ),
                'not_found_in_trash' => __( 'No Form Found in Trash', 'wpuf' ),
                'parent'             => __( 'Parent Form', 'wpuf' ),
            ),
        ) );

        register_post_type( 'wpuf_input', array(
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false,
        ) );
    }

    /**
     * Initiate form builder for wpuf_forms post type
     *
     * @since 2.5
     *
     * @return void
     */
    public function post_forms_builder_init() {
        if ( isset( $_GET['action'] ) && ( 'edit' === $_GET['action'] ) && ! empty( $_GET['id'] ) ) {
            add_action( 'wpuf-form-builder-tabs-post', array( $this, 'add_primary_tabs' ) );
            add_action( 'wpuf-form-builder-tab-contents-post', array( $this, 'add_primary_tab_contents' ) );
            add_action( 'wpuf-form-builder-settings-tabs-post', array( $this, 'add_settings_tabs' ) );
            add_action( 'wpuf-form-builder-settings-tab-contents-post', array( $this, 'add_settings_tab_contents' ) );
            add_action( 'wpuf-form-builder-fields-section-before', array( $this, 'add_post_field_section' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'wpuf-form-builder-js-deps', array( $this, 'js_dependencies' ) );
            add_filter( 'wpuf-form-builder-js-root-mixins', array( $this, 'js_root_mixins' ) );
            add_filter( 'wpuf-form-builder-js-builder-stage-mixins', array( $this, 'js_builder_stage_mixins' ) );
            add_filter( 'wpuf-form-builder-js-field-options-mixins', array( $this, 'js_field_options_mixins' ) );
            add_action( 'wpuf-form-builder-template-builder-stage-submit-area', array( $this, 'add_form_submit_area' ) );
            add_action( 'wpuf-form-builder-localize-script', array( $this, 'add_to_localize_script' ) );
            add_action( 'wpuf-form-builder-field-settings', array( $this, 'add_field_settings' ) );
            add_filter( 'wpuf-form-builder-i18n', array( $this, 'i18n' ) );

            do_action( 'wpuf-form-builder-init-type-wpuf_forms' );

            $this->set_wp_post_types();

            $settings = array(
                'form_type'         => 'post',
                'post_type'         => 'wpuf_forms',
                'post_id'           => $_GET['id'],
                'form_settings_key' => $this->form_settings_key,
                'shortcode_attrs'   => array(
                    'type' => array(
                        'profile'       => __( 'Profile', 'wpuf' ),
                        'registration'  => __( 'Registration', 'wpuf' )
                    )
                )
            );

            new WPUF_Admin_Form_Builder( $settings );
        }
    }

    /**
     * Additional primary tabs
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_primary_tabs() {
        ?>

        <a href="#wpuf-form-builder-notification" class="nav-tab">
            <?php _e( 'Notification', 'wpuf' ); ?>
        </a>

        <?php
    }

    /**
     * Add primary tab contents
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_primary_tab_contents() {
        ?>

        <div id="wpuf-form-builder-notification" class="group">
            <?php do_action('wpuf_form_settings_post_notification'); ?>
        </div><!-- #wpuf-form-builder-notification -->

        <?php
    }

    /**
     * Add settings tabs
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_settings_tabs() {
        ?>

            <a href="#wpuf-metabox-settings" class="nav-tab"><?php _e( 'Post Settings', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-settings-update" class="nav-tab"><?php _e( 'Edit Settings', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-post_expiration" class="nav-tab"><?php _e( 'Post Expiration', 'wpuf' ); ?></a>

        <?php
    }

    /**
     * Add settings tabs
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_settings_tab_contents() {
        ?>

            <div id="wpuf-metabox-settings" class="group">
                <?php $this->form_settings_posts(); ?>
            </div>

            <div id="wpuf-metabox-settings-update" class="group">
                <?php $this->form_settings_posts_edit(); ?>
            </div>

            <div id="wpuf-metabox-post_expiration" class="group wpuf-metabox-post_expiration">
                <?php $this->form_post_expiration(); ?>
            </div>

            <?php do_action( 'wpuf_post_form_tab_content' ); ?>

        <?php
    }

    /**
     * Displays settings on post form builder
     *
     * @since 2.3.2
     *
     * @return void
     */
    public function form_settings_posts() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $post_status_selected  = isset( $form_settings['post_status'] ) ? $form_settings['post_status'] : 'publish';
        $restrict_message      = __( "This page is restricted. Please Log in / Register to view this page.", 'wpuf' );

        $post_type_selected    = isset( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';

        $post_format_selected  = isset( $form_settings['post_format'] ) ? $form_settings['post_format'] : 0;
        $default_cat           = isset( $form_settings['default_cat'] ) ? $form_settings['default_cat'] : -1;

        $guest_post            = isset( $form_settings['guest_post'] ) ? $form_settings['guest_post'] : 'false';
        $guest_details         = isset( $form_settings['guest_details'] ) ? $form_settings['guest_details'] : 'true';
        $name_label            = isset( $form_settings['name_label'] ) ? $form_settings['name_label'] : __( 'Name' );
        $email_label           = isset( $form_settings['email_label'] ) ? $form_settings['email_label'] : __( 'Email' );
        $message_restrict      = isset( $form_settings['message_restrict'] ) ? $form_settings['message_restrict'] : $restrict_message;

        $redirect_to           = isset( $form_settings['redirect_to'] ) ? $form_settings['redirect_to'] : 'post';
        $message               = isset( $form_settings['message'] ) ? $form_settings['message'] : __( 'Post saved', 'wpuf' );
        $update_message        = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Post updated successfully', 'wpuf' );
        $page_id               = isset( $form_settings['page_id'] ) ? $form_settings['page_id'] : 0;
        $url                   = isset( $form_settings['url'] ) ? $form_settings['url'] : '';
        $comment_status        = isset( $form_settings['comment_status'] ) ? $form_settings['comment_status'] : 'open';

        $submit_text           = isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Submit', 'wpuf' );
        $draft_text            = isset( $form_settings['draft_text'] ) ? $form_settings['draft_text'] : __( 'Save Draft', 'wpuf' );
        $preview_text          = isset( $form_settings['preview_text'] ) ? $form_settings['preview_text'] : __( 'Preview', 'wpuf' );
        $draft_post            = isset( $form_settings['draft_post'] ) ? $form_settings['draft_post'] : 'false';
        $subscription_disabled = isset( $form_settings['subscription_disabled'] ) ? $form_settings['subscription_disabled'] : '';

        ?>
            <table class="form-table">

                <tr class="">
                    <th><?php _e( 'Disable Subscription', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="wpuf_settings[subscription_disabled]" value="yes" <?php checked( $subscription_disabled, 'yes' ); ?> />
                            <?php _e( 'Disable Subscription', 'wpuf' ); ?>
                        </label>

                        <p class="description"><?php echo __( 'If checked, any subscription and pay-per-post will be disabled on the form and will take no effect.', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="wpuf-post-type">
                    <th><?php _e( 'Post Type', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[post_type]">
                            <?php
                            $post_types = get_post_types();
                            unset($post_types['attachment']);
                            unset($post_types['revision']);
                            unset($post_types['nav_menu_item']);
                            unset($post_types['wpuf_forms']);
                            unset($post_types['wpuf_profile']);

                            foreach ($post_types as $post_type) {
                                printf('<option value="%s"%s>%s</option>', $post_type, selected( $post_type_selected, $post_type, false ), $post_type );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-post-status">
                    <th><?php _e( 'Post Status', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[post_status]">
                            <?php
                            $statuses = get_post_statuses();
                            foreach ($statuses as $status => $label) {
                                printf('<option value="%s"%s>%s</option>', $status, selected( $post_status_selected, $status, false ), $label );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-post-fromat">
                    <th><?php _e( 'Post Format', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[post_format]">
                            <option value="0"><?php _e( '- None -', 'wpuf' ); ?></option>
                            <?php
                            $post_formats = get_theme_support( 'post-formats' );

                            if ( isset($post_formats[0]) && is_array( $post_formats[0] ) ) {
                                foreach ($post_formats[0] as $format) {
                                    printf('<option value="%s"%s>%s</option>', $format, selected( $post_format_selected, $format, false ), $format );
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-default-cat">
                    <th><?php _e( 'Default Post Category', 'wpuf' ); ?></th>
                    <td>
                        <?php
                        wp_dropdown_categories( array(
                            'hide_empty'       => false,
                            'hierarchical'     => true,
                            'selected'         => $default_cat,
                            'name'             => 'wpuf_settings[default_cat]',
                            'show_option_none' => __( '- None -', 'wpuf' ),
                            'taxonomy'         => ( $post_type_selected == 'product' ) ? 'product_cat' : 'category'
                        ) );
                        ?>
                        <p class="description"><?php echo __( 'If users are not allowed to choose any category, this category will be used instead (if post type supports)', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Guest Post', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="wpuf_settings[guest_post]" value="false">
                            <input type="checkbox" name="wpuf_settings[guest_post]" value="true"<?php checked( $guest_post, 'true' ); ?> />
                            <?php _e( 'Enable Guest Post', 'wpuf' ) ?>
                        </label>
                        <p class="description"><?php _e( 'Unregistered users will be able to submit posts', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="show-if-guest">
                    <th><?php _e( 'User Details', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="wpuf_settings[guest_details]" value="false">
                            <input type="checkbox" name="wpuf_settings[guest_details]" value="true"<?php checked( $guest_details, 'true' ); ?> />
                            <?php _e( 'Require Name and Email address', 'wpuf' ) ?>
                        </label>
                        <p class="description"><?php _e( 'If requires, users will be automatically registered to the site using the name and email address', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="show-if-guest show-if-details">
                    <th><?php _e( 'Name Label', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="text" name="wpuf_settings[name_label]" value="<?php echo esc_attr( $name_label ); ?>" />
                        </label>
                        <p class="description"><?php _e( 'Label text for name field', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="show-if-guest show-if-details">
                    <th><?php _e( 'E-Mail Label', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="text" name="wpuf_settings[email_label]" value="<?php echo esc_attr( $email_label ); ?>" />
                        </label>
                        <p class="description"><?php _e( 'Label text for email field', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="show-if-not-guest">
                    <th><?php _e( 'Unauthorized Message', 'wpuf' ); ?></th>
                    <td>
                        <textarea rows="3" cols="40" name="wpuf_settings[message_restrict]"><?php echo esc_textarea( $message_restrict ); ?></textarea>
                        <p class="description"><?php _e( 'Not logged in users will see this message', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <tr class="wpuf-redirect-to">
                    <th><?php _e( 'Redirect To', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[redirect_to]">
                            <?php
                            $redirect_options = array(
                                'post' => __( 'Newly created post', 'wpuf' ),
                                'same' => __( 'Same Page', 'wpuf' ),
                                'page' => __( 'To a page', 'wpuf' ),
                                'url' => __( 'To a custom URL', 'wpuf' )
                            );

                            foreach ($redirect_options as $to => $label) {
                                printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php _e( 'After successfull submit, where the page will redirect to', $domain = 'default' ) ?>
                        </p>
                    </td>
                </tr>

                <tr class="wpuf-same-page">
                    <th><?php _e( 'Message to show', 'wpuf' ); ?></th>
                    <td>
                        <textarea rows="3" cols="40" name="wpuf_settings[message]"><?php echo esc_textarea( $message ); ?></textarea>
                    </td>
                </tr>

                <tr class="wpuf-page-id">
                    <th><?php _e( 'Page', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[page_id]">
                            <?php
                            $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                            foreach ($pages as $page) {
                                printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-url">
                    <th><?php _e( 'Custom URL', 'wpuf' ); ?></th>
                    <td>
                        <input type="url" name="wpuf_settings[url]" value="<?php echo esc_attr( $url ); ?>">
                    </td>
                </tr>

                <tr class="wpuf-comment">
                    <th><?php _e( 'Comment Status', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[comment_status]">
                            <option value="open" <?php selected( $comment_status, 'open'); ?>><?php _e('Open'); ?></option>
                            <option value="closed" <?php selected( $comment_status, 'closed'); ?>><?php _e('Closed'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-submit-text">
                    <th><?php _e( 'Submit Post Button text', 'wpuf' ); ?></th>
                    <td>
                        <input type="text" name="wpuf_settings[submit_text]" value="<?php echo esc_attr( $submit_text ); ?>">
                    </td>
                </tr>

                <tr>
                    <th><?php _e( 'Post Draft', 'wpuf' ); ?></th>
                    <td>
                        <label>
                            <input type="hidden" name="wpuf_settings[draft_post]" value="false">
                            <input type="checkbox" name="wpuf_settings[draft_post]" value="true"<?php checked( $draft_post, 'true' ); ?> />
                            <?php _e( 'Enable Saving as draft', 'wpuf' ) ?>
                        </label>
                        <p class="description"><?php _e( 'It will show a button to save as draft', 'wpuf' ); ?></p>
                    </td>
                </tr>

                <?php do_action( 'wpuf_form_setting', $form_settings, $post ); ?>
            </table>
        <?php
    }

    /**
     * Displays settings on post form builder
     *
     * @global object $post
     */
    public function form_settings_posts_edit() {
        global $post;

        $form_settings        = wpuf_get_form_settings( $post->ID );

        $post_status_selected = isset( $form_settings['edit_post_status'] ) ? $form_settings['edit_post_status'] : 'publish';
        $redirect_to          = isset( $form_settings['edit_redirect_to'] ) ? $form_settings['edit_redirect_to'] : 'same';
        $update_message       = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Post updated successfully', 'wpuf' );
        $page_id              = isset( $form_settings['edit_page_id'] ) ? $form_settings['edit_page_id'] : 0;
        $url                  = isset( $form_settings['edit_url'] ) ? $form_settings['edit_url'] : '';
        $update_text          = isset( $form_settings['update_text'] ) ? $form_settings['update_text'] : __( 'Update', 'wpuf' );
        $subscription         = isset( $form_settings['subscription'] ) ? $form_settings['subscription'] : null;
        ?>
            <table class="form-table">

                <tr class="wpuf-post-status">
                    <th><?php _e( 'Set Post Status to', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[edit_post_status]">
                            <?php
                            $statuses = get_post_statuses();

                            foreach ($statuses as $status => $label) {
                                printf('<option value="%s"%s>%s</option>', $status, selected( $post_status_selected, $status, false ), $label );
                            }

                            printf( '<option value="_nochange"%s>%s</option>', selected( $post_status_selected, '_nochange', false ), __( 'No Change', 'wpuf' ) );
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-redirect-to">
                    <th><?php _e( 'Redirect To', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[edit_redirect_to]">
                            <?php
                            $redirect_options = array(
                                'post' => __( 'Newly created post', 'wpuf' ),
                                'same' => __( 'Same Page', 'wpuf' ),
                                'page' => __( 'To a page', 'wpuf' ),
                                'url' => __( 'To a custom URL', 'wpuf' )
                            );

                            foreach ($redirect_options as $to => $label) {
                                printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php _e( 'After successfull submit, where the page will redirect to', $domain = 'default' ) ?>
                        </p>
                    </td>
                </tr>

                <tr class="wpuf-same-page">
                    <th><?php _e( 'Post Update Message', 'wpuf' ); ?></th>
                    <td>
                        <textarea rows="3" cols="40" name="wpuf_settings[update_message]"><?php echo esc_textarea( $update_message ); ?></textarea>
                    </td>
                </tr>

                <tr class="wpuf-page-id">
                    <th><?php _e( 'Page', 'wpuf' ); ?></th>
                    <td>
                        <select name="wpuf_settings[edit_page_id]">
                            <?php
                            $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                            foreach ($pages as $page) {
                                printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-url">
                    <th><?php _e( 'Custom URL', 'wpuf' ); ?></th>
                    <td>
                        <input type="url" name="wpuf_settings[edit_url]" value="<?php echo esc_attr( $url ); ?>">
                    </td>
                </tr>

                <tr class="wpuf-subscription-pack" style="display: none;">
                    <th><?php _e( 'Subscription Title'); ?></th>
                    <td>
                        <select id="wpuf-subscription-list" name="wpuf_settings[subscription]">
                            <?php $this->subscription_dropdown( $subscription ); ?>
                        </select>
                    </td>
                </tr>

                <tr class="wpuf-update-text">
                    <th><?php _e( 'Update Post Button text', 'wpuf' ); ?></th>
                    <td>
                        <input type="text" name="wpuf_settings[update_text]" value="<?php echo esc_attr( $update_text ); ?>">
                    </td>
                </tr>
            </table>
        <?php
    }

    /**
     * Subscription dropdown
     *
     * @since 2.5
     *
     * @param string $selected
     *
     * @return void
     */
    public function subscription_dropdown( $selected = null ) {
        $subscriptions = WPUF_Subscription::init()->get_subscriptions();

        if ( ! $subscriptions ) {
            printf( '<option>%s</option>', __( '- Select -' ), 'wpuf' );
            return;
        }

        printf( '<option>%s</option>', __( '- Select -', 'wpuf' ) );

        foreach ( $subscriptions as $key => $subscription ) {
            ?>
                <option value="<?php echo esc_attr( $subscription->ID ); ?>" <?php selected( $selected, $subscription->ID ); ?> ><?php echo $subscription->post_title; ?></option>
            <?php
        }
    }

    /**
     * Settings for post expiration
     *
     * @since 2.2.7
     *
     * @global $post
     */
    public function form_post_expiration(){
        do_action('wpuf_form_post_expiration');
    }

    /**
     * Add post fields in form builder
     *
     * @since 2.5
     *
     * @return array
     */
    public function add_post_field_section() {
        $post_fields = apply_filters( 'wpuf-form-builder-wp_forms-fields-section-post-fields', array(
            'post_title', 'post_content', 'post_excerpt', 'featured_image'
        ) );

        return array(
            array(
                'title'     => __( 'Post Fields', 'wpuf' ),
                'id'        => 'post-fields',
                'fields'    => $post_fields
            ),

            array(
                'title'     => __( 'Taxonomies', 'wpuf' ),
                'id'        => 'taxonomies',
                'fields'    => array()
            )
        );
    }

    /**
     * Admin script form wpuf_forms form builder
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        wp_register_script(
            'wpuf-form-builder-wpuf-forms',
            WPUF_ASSET_URI . '/js/wpuf-form-builder-wpuf-forms.js',
            array( 'jquery', 'underscore', 'wpuf-vue', 'wpuf-vuex' ),
            WPUF_VERSION,
            true
        );
    }

    /**
     * Add dependencies to form builder script
     *
     * @since 2.5
     *
     * @param array $deps
     *
     * @return array
     */
    public function js_dependencies( $deps ) {
        array_push( $deps, 'wpuf-form-builder-wpuf-forms' );

        return $deps;
    }

    /**
     * Add mixins to root instance
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function js_root_mixins( $mixins ) {
        array_push( $mixins , 'wpuf_forms_mixin_root' );

        return $mixins;
    }

    /**
     * Add mixins to form builder builder stage component
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function js_builder_stage_mixins( $mixins ) {
        array_push( $mixins , 'wpuf_forms_mixin_builder_stage' );

        return $mixins;
    }

    /**
     * Add mixins to form builder field options component
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function js_field_options_mixins( $mixins ) {
        array_push( $mixins , 'wpuf_forms_mixin_field_options' );

        return $mixins;
    }

    /**
     * Add buttons in form submit area
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_form_submit_area() {
        ?>
            <input @click.prevent="" type="submit" name="submit" :value="post_form_settings.submit_text">

            <a
                v-if="post_form_settings.draft_post"
                @click.prevent=""
                href="#"
                class="btn"
                id="wpuf-post-draft"
            >
                <?php _e( 'Save Draft', 'wpuf' ); ?>
            </a>
        <?php
    }

    /**
     * Populate available wp post types
     *
     * @since 2.5
     *
     * @return void
     */
    public function set_wp_post_types() {
        $args = array( '_builtin' => true );

        $wpuf_post_types = wpuf_get_post_types( $args );

        $ignore_taxonomies = apply_filters( 'wpuf-ignore-taxonomies', array(
            'post_format'
        ) );

        foreach ( $wpuf_post_types as $post_type ) {
            $this->wp_post_types[ $post_type ] = array();

            $taxonomies = get_object_taxonomies( $post_type, 'object' );

            foreach ( $taxonomies as $tax_name => $taxonomy ) {
                if ( ! in_array( $tax_name, $ignore_taxonomies ) ) {
                    $this->wp_post_types[ $post_type ][ $tax_name ] = array(
                        'title'         => $taxonomy->label,
                        'hierarchical'  => $taxonomy->hierarchical
                    );

                    $this->wp_post_types[ $post_type ][ $tax_name ]['terms'] = get_terms( array(
                        'taxonomy' => $tax_name,
                        'hide_empty' => false
                    ) );
                }
            }
        }
    }

    /**
     * Add data to localize_script
     *
     * @since 2.5
     *
     * @param array $data
     *
     * @return array
     */
    public function add_to_localize_script( $data ) {
        return array_merge( $data, array(
            'wp_post_types' => $this->wp_post_types
        ) );
    }

    /**
     * Add field settings
     *
     * @since 2.5
     *
     * @param array $field_settings
     *
     * @return array
     */
    public function add_field_settings( $field_settings ) {
        $field_settings = array_merge( $field_settings, array(
            'post_title'     => self::post_title(),
            'post_content'   => self::post_content(),
            'post_excerpt'   => self::post_excerpt(),
            'featured_image' => self::featured_image()
        ) );

        $taxonomy_templates = array();

        foreach ( $this->wp_post_types as $post_type => $taxonomies ) {

            if ( ! empty( $taxonomies ) ) {

                foreach ( $taxonomies as $tax_name => $taxonomy ) {
                    if ( 'post_tag' === $tax_name ) {
                        $taxonomy_templates['post_tag'] = self::post_tags();
                    } else {
                        $taxonomy_templates[ $tax_name ] = self::taxonomy_template( $tax_name, $taxonomy );
                    }
                }

            }

        }

        $field_settings = array_merge( $field_settings, $taxonomy_templates );

        return $field_settings;
    }

    /**
     * Post Title field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function post_title() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'post_title',
            'title'         => __( 'Post Title', 'wpuf' ),
            'icon'          => 'header',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'post_title',
                'required'      => 'no',
                'label'         => __( 'Post Title', 'wpuf' ),
                'name'          => 'post_title',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Post Content field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function post_content() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_textarea_properties() );

        return array(
            'template'      => 'post_content',
            'title'         => __( 'Post Body', 'wpuf' ),
            'icon'          => 'file-text',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'textarea',
                'template'         => 'post_content',
                'required'         => 'no',
                'label'            => __( 'Post Body', 'wpuf' ),
                'name'             => 'post_content',
                'is_meta'          => 'no',
                'help'             => '',
                'css'              => '',
                'rows'             => 5,
                'cols'             => 25,
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'yes',
                'word_restriction' => '',
                'id'               => 0,
                'is_new'           => true,
                'wpuf_cond'        => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Post Excerpt field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function post_excerpt() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_textarea_properties() );

        return array(
            'template'      => 'post_excerpt',
            'title'         => __( 'Excerpt', 'wpuf' ),
            'icon'          => 'compress',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'textarea',
                'template'         => 'post_excerpt',
                'required'         => 'no',
                'label'            => __( 'Excerpt', 'wpuf' ),
                'name'             => 'post_excerpt',
                'is_meta'          => 'no',
                'help'             => '',
                'css'              => '',
                'rows'             => 5,
                'cols'             => 25,
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'no',
                'word_restriction' => '',
                'id'               => 0,
                'is_new'           => true,
                'wpuf_cond'        => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Featured Image
     *
     * @since 2.5
     *
     * @return array
     */
    public static function featured_image() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wpuf' ),
            ),

            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wpuf' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wpuf' ),
            ),
        ) );

        return array(
            'template'      => 'featured_image',
            'title'         => __( 'Featured Image', 'wpuf' ),
            'icon'          => 'picture-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'image_upload',
                'template'      => 'featured_image',
                'required'      => 'no',
                'label'         => __( 'Featured Image', 'wpuf' ),
                'name'          => 'featured_image',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'max_size'      => '1024',
                'count'         => '1',
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Post Tag
     *
     * @since 2.5
     *
     * @return array
     */
    public static function post_tags() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'post_tags',
            'title'         => __( 'Tags', 'wpuf' ),
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'post_tags',
                'required'      => 'no',
                'label'         => __( 'Tags', 'wpuf' ),
                'name'          => 'tags',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Common settings for taxonomy fields
     *
     * @since 2.5
     *
     * @return array
     */
    public static function taxonomy_template( $tax_name, $taxonomy ) {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );

        $settings = array_merge( $settings, array(
            array(
                'name'      => 'type',
                'title'     => __( 'Type', 'wpuf' ),
                'type'      => 'select',
                'options'   => array(
                    'select'        => __( 'Select', 'wpuf' ),
                    'multiselect'   => __( 'Multi Select', 'wpuf' ),
                    'checkbox'      => __( 'Checkbox', 'wpuf' ),
                    'text'          => __( 'Text Input', 'wpuf' ),
                    'ajax'          => __( 'Ajax', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 23,
                'default'   => 'select',
            ),

            array(
                'name'      => 'orderby',
                'title'     => __( 'Order By', 'wpuf' ),
                'type'      => 'select',
                'options'   => array(
                    'name'          => __( 'Name', 'wpuf' ),
                    'term_id'       => __( 'Term ID', 'wpuf' ), // NOTE: before 2.5 the key was 'id' not 'term_id'
                    'slug'          => __( 'Slug', 'wpuf' ),
                    'count'         => __( 'Count', 'wpuf' ),
                    'term_group'    => __( 'Term Group', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 24,
                'default'   => 'name',
            ),

            array(
                'name'      => 'order',
                'title'     => __( 'Order', 'wpuf' ),
                'type'      => 'radio',
                'inline'    => true,
                'options'   => array(
                    'ASC'           => __( 'ASC', 'wpuf' ),
                    'DESC'          => __( 'DESC', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 25,
                'default'   => 'ASC',
            ),

            array(
                'name'      => 'exclude_type',
                'title'     => __( 'Selection Type', 'wpuf' ),
                'type'      => 'select',
                'options'   => array(
                    'exclude'       => __( 'Exclude', 'wpuf' ),
                    'include'       => __( 'Include', 'wpuf' ),
                    'child_of'      => __( 'Child of', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 26,
                'default'   => '',
            ),

            array(
                'name'      => 'exclude',
                'title'     => __( 'Selection Terms', 'wpuf' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 27,
                'help_text' => __( 'Enter the term IDs as comma separated (without space) to exclude/include in the form.', 'wpuf' ),
            ),

            array(
                'name'          => 'woo_attr',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'This taxonomy is a WooCommerce attribute', 'wpuf' )
                ),
                'section'       => 'advanced',
                'priority'      => 28,
            ),

            array(
                'name'          => 'woo_attr_vis',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'Visible on product page', 'wpuf' )
                ),
                'section'       => 'advanced',
                'priority'      => 29,
                'dependencies' => array(
                    'woo_attr' => 'yes'
                )
            ),
        ) );

        return array(
            'template'      => 'taxonomy',
            'title'         => $taxonomy['title'],
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'taxonomy',
                'template'      => 'taxonomy',
                'required'      => 'no',
                'label'         => $taxonomy['title'],
                'name'          => $tax_name,
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'type'          => 'select',
                'orderby'       => 'name',
                'order'         => 'ASC',
                'exclude_type'  => '',
                'exclude'       => '',
                'woo_attr'      => '',
                'woo_attr_vis'  => '',
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * i18n strings specially for Post Forms
     *
     * @since 2.5
     *
     * @param array $i18n
     *
     * @return array
     */
    public function i18n( $i18n ) {
        return array_merge( $i18n, array(
            'any_of_three_needed' => __( 'Post Forms must have either Post Title, Post Body or Excerpt field', 'wpuf' )
        ) );
    }
}
