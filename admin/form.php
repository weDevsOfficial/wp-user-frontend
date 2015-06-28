<?php
/**
 * Admin Form UI Builder
 *
 * @package WP User Frontend
 */


class WPUF_Admin_Form {

    private $form_data_key = 'wpuf_form';
    private $form_settings_key = 'wpuf_form_settings';

    /**
     * Add neccessary actions and filters
     *
     * @return void
     */
    function __construct() {
        add_action( 'init', array($this, 'register_post_type') );
        add_filter( 'post_updated_messages', array($this, 'form_updated_message') );

        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'admin_footer-edit.php', array($this, 'add_form_button_style') );
        add_action( 'admin_footer-post.php', array($this, 'add_form_button_style') );

        add_action( 'admin_head', array( $this, 'menu_icon' ) );

        // form duplication
        add_filter( 'post_row_actions', array( $this, 'row_action_duplicate' ), 10, 2 );
        add_filter( 'admin_action_wpuf_duplicate', array( $this, 'duplicate_form' ) );

        // meta boxes
        add_action( 'add_meta_boxes', array($this, 'add_meta_box_form_select') );
        add_action( 'add_meta_boxes_wpuf_forms', array($this, 'add_meta_box_post') );
        add_action( 'add_meta_boxes_wpuf_profile', array($this, 'add_meta_box_profile') );

        // custom columns
        add_filter( 'manage_edit-wpuf_forms_columns', array( $this, 'admin_column' ) );
        add_filter( 'manage_edit-wpuf_profile_columns', array( $this, 'admin_column_profile' ) );
        add_action( 'manage_wpuf_forms_posts_custom_column', array( $this, 'admin_column_value' ), 10, 2 );
        add_action( 'manage_wpuf_profile_posts_custom_column', array( $this, 'admin_column_value_profile' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ) );

        // ajax actions for post forms
        add_action( 'wp_ajax_wpuf_form_dump', array( $this, 'form_dump' ) );
        add_action( 'wp_ajax_wpuf_form_add_el', array( $this, 'ajax_post_add_element' ) );

        add_action( 'save_post', array( $this, 'save_form_meta' ), 1, 3 ); // save the custom fields
        add_action( 'save_post', array( $this, 'form_selection_metabox_save' ), 1, 2 ); // save the custom fields


    }

    function remove_quick_edit( $actions ) {
        global $current_screen;

        if ( ! $current_screen ) {
            return $actions;
        }

        if ( $current_screen->post_type == 'wpuf_forms' || $current_screen->post_type == 'wpuf_profile' ) {
            unset( $actions['inline hide-if-no-js'] );
        }

        return $actions;
    }

    public static function insert_form_field( $form_id, $fields = array(), $field_id = null, $order = 0 ) {

        $args = array(
            'post_type'    => 'wpuf_input',
            'post_parent'  => $form_id,
            'post_status'  => 'publish',
            'post_content' => maybe_serialize( wp_unslash( $fields ) ),
            'menu_order'   => $order
        );

        if ( $field_id ) {
            $args['ID'] = $field_id;
        }

        if ( $field_id ) {
            wp_update_post( $args );
        } else {
            wp_insert_post( $args );
        }
    }

    /**
     * Enqueue scripts and styles for form builder
     *
     * @global string $pagenow
     * @return void
     */
    function enqueue_scripts() {
        global $pagenow, $post;

        if ( !in_array( $pagenow, array( 'post.php', 'post-new.php') ) ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-autocomplete' );

        if ( !in_array( $post->post_type, array( 'wpuf_forms', 'wpuf_profile' ) ) ) {
            return;
        }

        // scripts
        wp_enqueue_script( 'jquery-smallipop', WPUF_ASSET_URI . '/js/jquery.smallipop-0.4.0.min.js', array('jquery') );
        wp_enqueue_script( 'wpuf-formbuilder-script', WPUF_ASSET_URI . '/js/formbuilder.js', array('jquery', 'jquery-ui-sortable') );
        wp_enqueue_script( 'wpuf-conditional-script', WPUF_ASSET_URI . '/js/conditional.js' );

        // styles
        wp_enqueue_style( 'jquery-smallipop', WPUF_ASSET_URI . '/css/jquery.smallipop.css' );
        wp_enqueue_style( 'wpuf-formbuilder', WPUF_ASSET_URI . '/css/formbuilder.css' );
        wp_enqueue_style( 'jquery-ui-core', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );
    }

    function add_form_button_style() {
        global $pagenow, $post_type;

        if ( !in_array( $post_type, array( 'wpuf_forms', 'wpuf_profile') ) ) {
            return;
        }

        $fixed_sidebar = wpuf_get_option( 'fixed_form_element', 'wpuf_general' );
        ?>
        <style type="text/css">
            .wrap .add-new-h2, .wrap .add-new-h2:active {
                background: #21759b;
                color: #fff;
                text-shadow: 0 1px 1px #446E81;
            }

            <?php if ( $fixed_sidebar == 'on' ) { ?>
            #wpuf-metabox-fields{
                position: fixed;
                bottom: 10px;
            }
            <?php } ?>
        </style>
        <?php
    }

    /**
     * Register form post types
     *
     * @return void
     */
    function register_post_type() {
        $capability = wpuf_admin_role();

        register_post_type( 'wpuf_forms', array(
            'label'           => __( 'Forms', 'wpuf' ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'wpuf-admin-opt', //false,
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
            'show_ui'         => true,
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
     * Custom post update message
     *
     * @param  array $messages
     * @return array
     */
    function form_updated_message( $messages ) {
        $message = array(
             0 => '',
             1 => __('Form updated.'),
             2 => __('Custom field updated.'),
             3 => __('Custom field deleted.'),
             4 => __('Form updated.'),
             5 => isset($_GET['revision']) ? sprintf( __('Form restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
             6 => __('Form published.'),
             7 => __('Form saved.'),
             8 => __('Form submitted.' ),
             9 => '',
            10 => __('Form draft updated.'),
        );

        $messages['wpuf_forms'] = $message;
        $messages['wpuf_profile'] = $message;

        return $messages;
    }

    function menu_icon() {
        ?>
        <style type="text/css">
            .icon32-posts-wpuf_forms,
            .icon32-posts-wpuf_profile {
                background: url('<?php echo admin_url( "images/icons32.png" ); ?>') no-repeat 2% 35%;
            }
        </style>
        <?php
    }

    /**
     * Columns form builder list table
     *
     * @param type $columns
     * @return string
     */
    function admin_column( $columns ) {
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Form Name', 'wpuf' ),
            'post_type'   => __( 'Post Type', 'wpuf' ),
            'post_status' => __( 'Post Status', 'wpuf' ),
            'guest_post'  => __( 'Guest Post', 'wpuf' ),
            'shortcode'   => __( 'Shortcode', 'wpuf' )
        );

        return $columns;
    }

    /**
     * Columns form builder list table
     *
     * @param type $columns
     * @return string
     */
    function admin_column_profile( $columns ) {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title'     => __( 'Form Name', 'wpuf' ),
            'role'      => __( 'User Role', 'wpuf' ),
            'shortcode' => __( 'Shortcode', 'wpuf' )
        );

        return $columns;
    }

    /**
     * Custom Column value for post form builder
     *
     * @param string $column_name
     * @param int $post_id
     */
    function admin_column_value( $column_name, $post_id ) {
        switch ($column_name) {
            case 'shortcode':
                printf( '[wpuf_form id="%d"]', $post_id );
                break;

            case 'post_type':
                $settings = wpuf_get_form_settings( $post_id );
                echo isset( $settings['post_type'] ) ? $settings['post_type'] : 'post';
                break;

            case 'post_status':
                $settings = wpuf_get_form_settings( $post_id );
                $status   = isset( $settings['post_status'] ) ? $settings['post_status'] : 'publish';
                echo wpuf_admin_post_status( $status );
                break;

            case 'guest_post':
                $settings                    = wpuf_get_form_settings( $post_id );
                $guest                       = isset( $settings['guest_post'] ) ? $settings['guest_post'] : 'false';
                $url                         = WPUF_ASSET_URI . '/images/';
                $image                       = '<img src="%s" alt="%s">';
                echo $settings['guest_post'] == 'false' ? sprintf( $image, $url . 'cross.png', __( 'No', 'wpuf' ) ) : sprintf( $image, $url . 'tick.png', __( 'Yes', 'wpuf' ) ) ;
                break;

            default:
                # code...
                break;
        }
    }

    /**
     * Custom Column value for profile form builder
     *
     * @param string $column_name
     * @param int $post_id
     */
    function admin_column_value_profile( $column_name, $post_id ) {

        switch ($column_name) {
            case 'shortcode':
                printf( 'Registration: [wpuf_profile type="registration" id="%d"]<br>', $post_id );
                printf( 'Edit Profile: [wpuf_profile type="profile" id="%d"]', $post_id );
                break;

            case 'role':
                $settings = wpuf_get_form_settings( $post_id );
                $role = isset( $settings['role'] ) ? $settings['role'] : 'subscriber';
                echo ucfirst( $role );
                break;
        }
    }

    /**
     * Duplicate form row action link
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    function row_action_duplicate($actions, $post) {
        if ( !current_user_can( 'activate_plugins' ) ) {
            return $actions;
        }

        if ( !in_array( $post->post_type, array( 'wpuf_forms', 'wpuf_profile') ) ) {
            return $actions;
        }

        $actions['duplicate'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'wpuf_duplicate', 'id' => $post->ID, '_wpnonce' => wp_create_nonce( 'wpuf_duplicate' ) ), admin_url( 'admin.php' ) ) ) . '" title="' . esc_attr( __( 'Duplicate form', 'wpuf' ) ) . '">' . __( 'Duplicate', 'wpuf' ) . '</a>';
        return $actions;
    }

    /**
     * Form Duplication handler
     *
     * @return type
     */
    function duplicate_form() {
        check_admin_referer( 'wpuf_duplicate' );

        if ( !current_user_can( 'activate_plugins' ) ) {
            return;
        }

        $post_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $post = get_post( $post_id );

        if ( !$post ) {
            return;
        }

        $contents = self::get_form_fields( $post_id );

        $new_form = array(
            'post_title'  => $post->post_title,
            'post_type'   => $post->post_type,
            'post_status' => 'draft'
        );


        $form_id = wp_insert_post( $new_form );

        foreach ( $contents as $content ) {
            $post_content = maybe_unserialize( $content->post_content );
            self::insert_form_field( $form_id, $post_content, null, $order );
        }

        if ( $form_id ) {
            $form_settings = wpuf_get_form_settings( $post_id );
            update_post_meta( $form_id, $this->form_settings_key, $form_settings );
            $location = admin_url( 'edit.php?post_type=' . $post->post_type );
            wp_redirect( $location );
        }
    }

    /**
     * Meta box for all Post form selection
     *
     * Registers a meta box in public post types to select the desired WPUF
     * form select box to assign a form id.
     *
     * @return void
     */
    function add_meta_box_form_select() {

        // remove the submit div, because submit button placed on form elements
        remove_meta_box('submitdiv', 'wpuf_forms', 'side');
        remove_meta_box('submitdiv', 'wpuf_profile', 'side');

        $post_types = get_post_types( array('public' => true) );
        foreach ($post_types as $post_type) {
            add_meta_box( 'wpuf-select-form', __('WPUF Form'), array($this, 'form_selection_metabox'), $post_type, 'side', 'high' );
        }
    }

    /**
     * Add meta boxes to post form builder
     *
     * @return void
     */
    function add_meta_box_post() {
        add_meta_box( 'wpuf-metabox-editor', __( 'Form Editor', 'wpuf' ), array($this, 'metabox_post_form'), 'wpuf_forms', 'normal', 'high' );
        add_meta_box( 'wpuf-metabox-fields', __( 'Form Elements', 'wpuf' ), array($this, 'form_elements_post'), 'wpuf_forms', 'side', 'core' );
        add_meta_box( 'wpuf-metabox-fields-shortcode', __( 'Shortcode', 'wpuf' ), array($this, 'form_elements_shortcode'), 'wpuf_forms', 'side', 'core' );
    }

    /**
     * Adds meta boxes to profile form builder
     *
     * @return void
     */
    function add_meta_box_profile() {
        add_meta_box( 'wpuf-metabox-editor', __( 'Form Editor', 'wpuf' ), array($this, 'metabox_profile_form'), 'wpuf_profile', 'normal', 'high' );
        add_meta_box( 'wpuf-metabox-fields', __( 'Form Elements', 'wpuf' ), array($this, 'form_elements_profile'), 'wpuf_profile', 'side', 'core' );
        add_meta_box( 'wpuf-metabox-fields-shortcode', __( 'Shortcode', 'wpuf' ), array($this, 'form_elements_profile_shortcode'), 'wpuf_profile', 'side', 'core' );
    }

    /**
     * Prints form shortcode
     *
     * @since 2.3
     *
     * @return void
     */
    public function form_elements_shortcode() {
        global $post;
        ?>
        <p>
            <em><?php _e( 'Copy and insert this shortcode to a page:', 'wpuf' ); ?></em>
        </p>
        <input type='text' readonly value='[wpuf_form id="<?php echo $post->ID; ?>"]' style="width: 100%" />
        <?php
    }

    /**
     * Prints form shortcode
     *
     * @since 2.3
     *
     * @return void
     */
    public function form_elements_profile_shortcode() {
        global $post;
        ?>
        <p>
            <em><?php _e( 'Copy and insert this shortcode to a page:', 'wpuf' ); ?></em>
        </p>

        <label for="wpuf-profile-shortcode">
            <?php _e( 'Profile Shortcode:', 'wpuf' ); ?>
            <input type='text' id="wpuf-profile-shortcode" readonly value='[wpuf_profile type="profile" id="<?php echo $post->ID; ?>"]' style="width: 100%" />
        </label>

        <br>
        <br>

        <label for="wpuf-reg-shortcode">
            <?php _e( 'Registration Shortcode:', 'wpuf' ); ?>
            <input type='text' id="wpuf-reg-shortcode" readonly value='[wpuf_profile type="registration" id="<?php echo $post->ID; ?>"]' style="width: 100%" />
        </label>
        <?php
    }

    /**
     * Replaces the core publish button with ours
     *
     * @global object $post
     * @global string $pagenow
     */
    function publish_button() {
        global $post, $pagenow;

        $post_type        = $post->post_type;
        $post_type_object = get_post_type_object($post_type);
        $can_publish      = current_user_can($post_type_object->cap->publish_posts);
        ?>
        <div class="submitbox" id="submitpost">
            <div id="major-publishing-actions">
                <div id="publishing-action">
                    <?php if( $pagenow == 'post.php' ) { ?>
                        <a class="button button-primary button-large" target="_blank" href="<?php printf('%s?action=wpuf_form_preview&form_id=%s', admin_url( 'admin-ajax.php' ), $post->ID ); ?>"><?php _e( 'Preview Form', 'wpuf' ); ?></a>
                    <?php } ?>

                    <span class="spinner"></span>
                        <?php
                        if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
                            if ( $can_publish ) :
                                if ( !empty( $post->post_date_gmt ) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) :
                                    ?>
                                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Schedule' ) ?>" />
                            <?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false, array('accesskey' => 'p') ); ?>
                        <?php else : ?>
                                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
                            <?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array('accesskey' => 'p') ); ?>
                        <?php endif;
                    else :
                        ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review' ) ?>" />
                        <?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array('accesskey' => 'p') ); ?>
                    <?php
                    endif;
                    } else {
                        ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
                        <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>" />
                    <?php }
                ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Form selection meta box in post types
     *
     * Registered via $this->add_meta_box_form_select()
     *
     * @global object $post
     */
    function form_selection_metabox() {
        global $post;

        $forms = get_posts( array('post_type' => 'wpuf_forms', 'numberposts' => '-1') );
        $selected = get_post_meta( $post->ID, '_wpuf_form_id', true );
        ?>

        <input type="hidden" name="wpuf_form_select_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

        <select name="wpuf_form_select">
            <option value="">--</option>
            <?php foreach ($forms as $form) { ?>
            <option value="<?php echo $form->ID; ?>"<?php selected($selected, $form->ID); ?>><?php echo $form->post_title; ?></option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Saves the form ID from form selection meta box
     *
     * @param int $post_id
     * @param object $post
     * @return int|void
     */
    function form_selection_metabox_save( $post_id, $post ) {
        if ( !isset($_POST['wpuf_form_select'])) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_form_select_nonce'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        update_post_meta( $post->ID, '_wpuf_form_id', $_POST['wpuf_form_select'] );
    }

    /**
     * Displays settings on post form builder
     *
     * @global object $post
     */
    function form_settings_posts() {
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
                        'show_option_none' => __( '- None -', 'wpuf' )
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
    function form_settings_posts_edit() {
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

    function subscription_dropdown( $selected = null ) {
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
     * Displays settings on post form builder
     *
     * @global object $post
     */
    function form_settings_posts_notification() {
        do_action('wpuf_form_settings_post_notification');
    }

    /**
     * Settings for post expiration
     *
     * @since 2.2.7
     *
     * @global $post
     */
    function form_post_expiration(){
        do_action('wpuf_form_post_expiration');
    }

    /**
     * Display settings for user profile builder
     *
     * @return void
     */
    function form_settings_profile() {

        ?>
        <table class="form-table">
            <?php do_action( 'registration_setting' ); ?>
        </table>
        <?php
    }

    function metabox_post_form( $post ) {
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="#wpuf-metabox" class="nav-tab" id="wpuf-editor-tab"><?php _e( 'Form Editor', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-settings" class="nav-tab" id="wpuf-post-settings-tab"><?php _e( 'Post Settings', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-settings-update" class="nav-tab" id="wpuf-edit-settings-tab"><?php _e( 'Edit Settings', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-notification" class="nav-tab" id="wpuf-notification-tab"><?php _e( 'Notification', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-post_expiration" class="nav-tab" id="wpuf-notification-tab"><?php _e( 'Post Expiration', 'wpuf' ); ?></a>

            <?php do_action( 'wpuf_post_form_tab' ); ?>
        </h2>

        <div class="tab-content">
            <div id="wpuf-metabox" class="group">
                <?php $this->edit_form_area(); ?>
            </div>

            <div id="wpuf-metabox-settings" class="group">
                <?php $this->form_settings_posts(); ?>
            </div>

            <div id="wpuf-metabox-settings-update" class="group">
                <?php $this->form_settings_posts_edit(); ?>
            </div>

            <div id="wpuf-metabox-notification" class="group">
                <?php $this->form_settings_posts_notification(); ?>
            </div>

            <div id="wpuf-metabox-post_expiration" class="group wpuf-metabox-post_expiration">
                <?php $this->form_post_expiration(); ?>
            </div>

            <?php do_action( 'wpuf_post_form_tab_content' ); ?>
        </div>
        <?php
    }

    function metabox_profile_form( $post ) {

        ?>

        <h2 class="nav-tab-wrapper">
            <a href="#wpuf-metabox" class="nav-tab" id="wpuf_general-tab"><?php _e( 'Form Editor', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-settings" class="nav-tab" id="wpuf_dashboard-tab"><?php _e( 'Settings', 'wpuf' ); ?></a>

            <?php do_action( 'wpuf_profile_form_tab' ); ?>
        </h2>

        <div class="tab-content">
            <div id="wpuf-metabox" class="group">
                <?php $this->edit_form_area_profile(); ?>
            </div>

            <div id="wpuf-metabox-settings" class="group">
                <?php $this->form_settings_profile(); ?>
            </div>

            <?php do_action( 'wpuf_profile_form_tab_content' ); ?>
        </div>
        <?php
    }

    function form_elements_common() {
        $title = esc_attr( __( 'Click to add to the editor', 'wpuf' ) );
        ?>
        <h2><?php _e( 'Custom Fields', 'wpuf' ); ?></h2>
        <div class="wpuf-form-buttons">
            <button class="button" data-name="custom_text" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Text', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_textarea" data-type="textarea" title="<?php echo $title; ?>"><?php _e( 'Textarea', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_select" data-type="select" title="<?php echo $title; ?>"><?php _e( 'Dropdown', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_multiselect" data-type="multiselect" title="<?php echo $title; ?>"><?php _e( 'Multi Select', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_radio" data-type="radio" title="<?php echo $title; ?>"><?php _e( 'Radio', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_checkbox" data-type="checkbox" title="<?php echo $title; ?>"><?php _e( 'Checkbox', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_url" data-type="url" title="<?php echo $title; ?>"><?php _e( 'URL', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_email" data-type="email" title="<?php echo $title; ?>"><?php _e( 'Email', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_hidden" data-type="hidden" title="<?php echo $title; ?>"><?php _e( 'Hidden Field', 'wpuf' ); ?></button>



            <?php do_action( 'wpuf_form_buttons_custom' ); ?>
        </div>

        <h2><?php _e( 'Others', 'wpuf' ); ?></h2>
        <div class="wpuf-form-buttons">
            <button class="button" data-name="section_break" data-type="break" title="<?php echo $title; ?>"><?php _e( 'Section Break', 'wpuf' ); ?></button>
            <button class="button" data-name="custom_html" data-type="html" title="<?php echo $title; ?>"><?php _e( 'HTML', 'wpuf' ); ?></button></button>

            <?php do_action( 'wpuf_form_buttons_other' ); ?>
        </div>

        <?php
    }

    /**
     * Form elements for post form builder
     *
     * @return void
     */
    function form_elements_post() {
        ?>
        <div class="wpuf-loading hide"></div>

        <h2><?php _e( 'Post Fields', 'wpuf' ); ?></h2>
        <div class="wpuf-form-buttons">
            <button class="button" data-name="post_title" data-type="text" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Post Title', 'wpuf' ); ?></button>
            <button class="button" data-name="post_content" data-type="textarea" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Post Body', 'wpuf' ); ?></button>
            <button class="button" data-name="post_excerpt" data-type="textarea" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Excerpt', 'wpuf' ); ?></button>
            <button class="button" data-name="tags" data-type="text" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Tags', 'wpuf' ); ?></button>
            <button class="button" data-name="category" data-type="category" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Category', 'wpuf' ); ?></button>
            <button class="button" data-name="featured_image" data-type="image" title="<?php _e( 'Click to add to the editor', 'wpuf' ); ?>"><?php _e( 'Featured Image', 'wpuf' ); ?></button>

            <?php do_action( 'wpuf_form_buttons_post' ); ?>
        </div>


        <h2><?php _e( 'Custom Taxonomies', 'wpuf' ); ?></h2>
        <div class="wpuf-form-buttons">

            <?php do_action( 'wpuf_form_custom_taxonomies' ); ?>

        </div>


        <?php

        $this->form_elements_common();
        $this->publish_button();
    }

    /**
     * Form elements for Profile Builder
     *
     * @return void
     */
    function form_elements_profile() {
        ?>

        <div class="wpuf-loading hide"></div>

        <h2><?php _e( 'Profile Fields', 'wpuf' ); ?></h2>
        <div class="wpuf-form-buttons">
            <button class="button" data-name="user_login" data-type="text"><?php _e( 'Username', 'wpuf' ); ?></button>
            <button class="button" data-name="first_name" data-type="textarea"><?php _e( 'First Name', 'wpuf' ); ?></button>
            <button class="button" data-name="last_name" data-type="textarea"><?php _e( 'Last Name', 'wpuf' ); ?></button>
            <button class="button" data-name="nickname" data-type="text"><?php _e( 'Nickname', 'wpuf' ); ?></button>
            <button class="button" data-name="user_email" data-type="category"><?php _e( 'E-mail', 'wpuf' ); ?></button>
            <button class="button" data-name="user_url" data-type="text"><?php _e( 'Website', 'wpuf' ); ?></button>
            <button class="button" data-name="user_bio" data-type="textarea"><?php _e( 'Biographical Info', 'wpuf' ); ?></button>
            <button class="button" data-name="password" data-type="password"><?php _e( 'Password', 'wpuf' ); ?></button>
            <button class="button" data-name="user_avatar" data-type="avatar"><?php _e( 'Avatar', 'wpuf' ); ?></button>

            <?php do_action( 'wpuf_form_buttons_user' ); ?>
        </div>

        <?php
        $this->form_elements_common();
        $this->publish_button();
    }

    /**
     * Saves the form settings
     *
     * @param int $post_id
     * @param object $post
     * @return int|void
     */
    function save_form_meta( $post_id, $post, $update ) {

        do_action( 'wpuf_check_post_type', $post, $update );

        if ( ! in_array( $post->post_type, array( 'wpuf_forms', 'wpuf_profile' ) ) ) {
            return;
        }

        if ( !isset($_POST['wpuf_form_editor'] ) ) {
            return $post->ID;
        }

        if ( !wp_verify_nonce( $_POST['wpuf_form_editor'], plugin_basename( __FILE__ ) ) ) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if ( !current_user_can( 'edit_post', $post->ID ) ) {
            return $post->ID;
        }

        $conditions = isset( $_POST['wpuf_cond'] ) ? $_POST['wpuf_cond'] : array();

        if ( count( $conditions ) ) {
            foreach ($conditions as $key => $condition) {
                if ( $condition['condition_status'] == 'no' ) {
                    unset( $conditions[$key] );
                }
            }
        }

        $_POST['wpuf_input'] = isset( $_POST['wpuf_input'] ) ? $_POST['wpuf_input'] : array();

        foreach ( $_POST['wpuf_input'] as $key => $field_val ) {
            if ( array_key_exists( 'options', $field_val) ) {
                $view_option = array();

                foreach ( $field_val['options'] as $options_key => $options_value ) {
                    $opt_value = ( $field_val['options_values'][$options_key] == '' ) ? $options_value : $field_val['options_values'][$options_key];
                    $view_option[$opt_value] =   $options_value;//$_POST['wpuf_input'][$key]['options'][$opt_value] = $options_value;
                }

                unset($_POST['wpuf_input'][$key]['options_values']);
                $_POST['wpuf_input'][$key]['options'] = $view_option;
            }


            if ( $field_val['input_type'] == 'taxonomy' ) {
               $tax = get_terms( $field_val['name'],  array(
                    'orderby'    => 'count',
                    'hide_empty' => 0
                ) );

                $tax = is_array( $tax ) ? $tax : array();

                foreach($tax as $tax_obj) {
                    $terms[$tax_obj->term_id] = $tax_obj->name;
                }

                $_POST['wpuf_input'][$key]['options'] = $terms;
                $terms = '';
            }
        }

        $contents = self::get_form_fields( $post->ID );

        $db_id = wp_list_pluck( $contents, 'ID' );

        $order = 0;
        foreach( $_POST['wpuf_input'] as $key => $content ) {
            $content['wpuf_cond'] = $_POST['wpuf_cond'][$key];

            $field_id = isset( $content['id'] ) ? intval( $content['id'] ) : 0;

            if ( $field_id ) {
                $compare_id[$field_id] = $field_id;
                unset( $content['id'] );

                self::insert_form_field( $post->ID, $content, $field_id, $order );

            } else {
                self::insert_form_field( $post->ID, $content, null, $order );
            }

            $order++;
        }

        // delete fields from previous form
        $del_post_id = array_diff_key( $db_id, $compare_id );

        if ( $del_post_id ) {

            foreach ($del_post_id as $key => $post_id ) {
                wp_delete_post( $post_id , true );
            }

        } else if ( !count( $_POST['wpuf_input'] ) && count( $db_id ) ) {

           foreach ( $db_id as $key => $post_id ) {

                wp_delete_post( $post_id , true );
            }
        }

        update_post_meta( $post->ID, $this->form_settings_key, $_POST['wpuf_settings'] );
    }

    /**
     * Get form fields only
     *
     * @param  int $form_id
     * @return array
     */
    public static function get_form_fields( $form_id ) {

        $contents = get_children(array(
            'post_parent' => $form_id,
            'post_status' => 'publish',
            'post_type'   => 'wpuf_input',
            'numberposts' => '-1',
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
        ));

        return $contents;
    }

    /**
     * Edit form elements area for post
     *
     * @global object $post
     * @global string $pagenow
     */
    function edit_form_area() {

        global $post, $pagenow, $form_inputs;

        $form_inputs = wpuf_get_form_fields( $post->ID );
        ?>

        <input type="hidden" name="wpuf_form_editor" id="wpuf_form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

        <div style="margin-bottom: 10px">
            <button class="button wpuf-collapse"><?php _e( 'Toggle All', 'wpuf' ); ?></button>
        </div>

        <div class="wpuf-updated">
            <p><?php _e( 'Click on a form element to add to the editor', 'wpuf' ); ?></p>
        </div>

        <ul id="wpuf-form-editor" class="wpuf-form-editor unstyled">

        <?php

        if ($form_inputs) {

            $count = 0;

            $con_fields = $this->get_conditional_fields( $form_inputs );

            foreach ( $form_inputs as $order => $input_field ) {

                $input_field['template'] = isset( $input_field['template'] ) ? $input_field['template'] : '';
                $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );
                if ( isset( $cond_inputs[$order] ) ) {
                    $input_field = array_merge( $input_field, $cond_inputs[$order] );
                }

                if ( $input_field['template'] == 'taxonomy') {

                    WPUF_Admin_Template_Post::$input_field['template']( $count, $name, $input_field['name'], $input_field );

                } else if ( method_exists( 'WPUF_Admin_Template_Post', $input_field['template'] ) ) {

                    WPUF_Admin_Template_Post::$input_field['template']( $count, $name, $input_field );

                } else {
                    do_action( 'wpuf_admin_template_post_' . $input_field['template'], $name, $count, $input_field, 'WPUF_Admin_Template_Post', $this );
                }

                $count++;
            }
        }
        ?>
        </ul>

        <?php
    }

    /**
     * Get all conditional fields
     *
     * @param  array $fields
     * @return array
     */
    public static function get_conditional_fields( $fields ) {

        $conditionals = array(
            'fields' => array(),
            'options' => array()
        );

        foreach ($fields as $field) {

            if ( !isset( $field['input_type'] ) ) {
                continue;
            }

            if ( !in_array( $field['input_type'], array('select', 'radio', 'checkbox', 'taxonomy')) ) {
                continue;
            }

            $conditionals['fields'][$field['name']] = $field['label'];
            $conditionals['options'][$field['name']] = $field['options'];
        }

        return $conditionals;
    }

    /**
     * Get only conditional options from fields
     *
     * @param  array $fields
     * @return array
     */
    public static function get_conditional_option( $fields ) {

        $conditionals = array(
            'fields' => array(),
            'options' => array()
        );

        foreach ($fields as $field) {

            if ( !in_array( $field['input_type'], array('select', 'radio', 'checkbox')) ) {
                continue;
            }

            $conditionals['fields'][$field['name']] = $field['label'];
            $conditionals['options'][$field['name']] = $field['options'];
        }

        return $conditionals;
    }

    /**
     * Generate a conditional field dropdown
     *
     * @param  array $fields
     * @return array
     */
    public static function get_conditional_fields_dropdown( $fields ) {

        $options = array('' => '- select -');

        if ( count( $fields ) ) {



            foreach ($fields as $key => $label) {
                $options[$key] = $label;
            }
        }

        return $options;
    }

    /**
     * Generate a conditional field dropdown
     *
     * @param  array $fields
     * @return array
     */
    public static function get_conditional_option_dropdown( $fields ) {

        $options = array('' => '- select -');

        if ( count( $fields ) ) {
            foreach ($fields as $key => $label) {
                $options[$key] = $label;
            }
        }

        return $options;
    }

    /**
     * Edit form elements area for profile
     *
     * @global object $post
     * @global string $pagenow
     */
    function edit_form_area_profile() {

        ?>
        <input type="hidden" name="wpuf_form_editor" id="wpuf_form_editor" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
        <?php
        do_action( 'wpuf_edit_form_area_profile' );
    }

    /**
     * Ajax Callback handler for insrting fields in forms
     *
     * @return void
     */
    function ajax_post_add_element() {

        $name = $_POST['name'];
        $type = $_POST['type'];
        $field_id = $_POST['order'];

        switch ($name) {
            case 'post_title':
                WPUF_Admin_Template_Post::post_title( $field_id, 'Post Title');
                break;

            case 'post_content':
                WPUF_Admin_Template_Post::post_content( $field_id, 'Post Body');
                break;

            case 'post_excerpt':
                WPUF_Admin_Template_Post::post_excerpt( $field_id, 'Excerpt');
                break;

            case 'tags':
                WPUF_Admin_Template_Post::post_tags( $field_id, 'Tags');
                break;

            case 'featured_image':
                WPUF_Admin_Template_Post::featured_image( $field_id, 'Featured Image');
                break;

            case 'custom_text':
                WPUF_Admin_Template_Post::text_field( $field_id, 'Custom field: Text');
                break;

            case 'custom_textarea':
                WPUF_Admin_Template_Post::textarea_field( $field_id, 'Custom field: Textarea');
                break;

            case 'custom_select':
                WPUF_Admin_Template_Post::dropdown_field( $field_id, 'Custom field: Select');
                break;

            case 'custom_image':
                WPUF_Admin_Template::image_upload( $field_id, 'Custom field: Image' );
                break;

            case 'custom_multiselect':
                WPUF_Admin_Template_Post::multiple_select( $field_id, 'Custom field: Multiselect');
                break;

            case 'custom_radio':
                WPUF_Admin_Template_Post::radio_field( $field_id, 'Custom field: Radio');
                break;

            case 'custom_checkbox':
                WPUF_Admin_Template_Post::checkbox_field( $field_id, 'Custom field: Checkbox');
                break;

            case 'custom_url':
                WPUF_Admin_Template_Post::website_url( $field_id, 'Custom field: URL');
                break;

            case 'custom_email':
                WPUF_Admin_Template_Post::email_address( $field_id, 'Custom field: E-Mail');
                break;

            case 'custom_html':
                WPUF_Admin_Template_Post::custom_html( $field_id, 'HTML' );
                break;

            case 'category':
                WPUF_Admin_Template_Post::taxonomy( $field_id, 'Category', $type );
                break;

            case 'taxonomy':
                WPUF_Admin_Template_Post::taxonomy( $field_id, 'Taxonomy: ' . $type, $type );
                break;

            case 'section_break':
                WPUF_Admin_Template_Post::section_break( $field_id, 'Section Break' );
                break;

            case 'custom_hidden':
                WPUF_Admin_Template_Post::custom_hidden_field( $field_id, 'Hidden Field' );
                break;

            case 'user_login':
                WPUF_Admin_Template_Profile::user_login( $field_id, __( 'Username', 'wpuf' ) );
                break;

            case 'first_name':
                WPUF_Admin_Template_Profile::first_name( $field_id, __( 'First Name', 'wpuf' ) );
                break;

            case 'last_name':
                WPUF_Admin_Template_Profile::last_name( $field_id, __( 'Last Name', 'wpuf' ) );
                break;

            case 'nickname':
                WPUF_Admin_Template_Profile::nickname( $field_id, __( 'Nickname', 'wpuf' ) );
                break;

            case 'user_email':
                WPUF_Admin_Template_Profile::user_email( $field_id, __( 'E-mail', 'wpuf' ) );
                break;

            case 'user_url':
                WPUF_Admin_Template_Profile::user_url( $field_id, __( 'Website', 'wpuf' ) );
                break;

            case 'user_bio':
                WPUF_Admin_Template_Profile::description( $field_id, __( 'Biographical Info', 'wpuf' ) );
                break;

            case 'password':
                WPUF_Admin_Template_Profile::password( $field_id, __( 'Password', 'wpuf' ) );
                break;

            case 'user_avatar':
                WPUF_Admin_Template_Profile::avatar( $field_id, __( 'Avatar', 'wpuf' ) );
                break;

            default:
                do_action( 'wpuf_admin_field_' . $name, $type, $field_id, 'WPUF_Admin_Template_Post', $this );
                break;
        }

        exit;
    }

}