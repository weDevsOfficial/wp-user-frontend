<?php
/**
 * Post Forms or wpuf_forms form builder class
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
        add_action( "load-user-frontend_page_wpuf-post-forms", array( $this, 'post_forms_builder_init' ) );
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

        if ( ! isset( $_GET['action'] ) ) {
            return;
        }

        if ( 'add-new' === $_GET['action'] && empty( $_GET['id'] ) ) {
            $form_id          = wpuf_create_sample_form( 'Sample Form', 'wpuf_forms', true );
            $add_new_page_url = add_query_arg( array( 'id' => $form_id ), admin_url( 'admin.php?page=wpuf-post-forms&action=edit' ) );
            wp_redirect( $add_new_page_url );
        }

        if ( ( 'edit' === $_GET['action'] ) && ! empty( $_GET['id'] ) ) {

            add_action( 'wpuf-form-builder-tabs-post', array( $this, 'add_primary_tabs' ) );
            add_action( 'wpuf-form-builder-tab-contents-post', array( $this, 'add_primary_tab_contents' ) );
            add_action( 'wpuf-form-builder-settings-tabs-post', array( $this, 'add_settings_tabs' ) );
            add_action( 'wpuf-form-builder-settings-tab-contents-post', array( $this, 'add_settings_tab_contents' ) );
            add_filter( 'wpuf-form-builder-fields-section-before', array( $this, 'add_post_field_section' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'wpuf-form-builder-js-deps', array( $this, 'js_dependencies' ) );
            add_filter( 'wpuf-form-builder-js-root-mixins', array( $this, 'js_root_mixins' ) );
            add_filter( 'wpuf-form-builder-js-builder-stage-mixins', array( $this, 'js_builder_stage_mixins' ) );
            add_filter( 'wpuf-form-builder-js-field-options-mixins', array( $this, 'js_field_options_mixins' ) );
            add_action( 'wpuf-form-builder-template-builder-stage-submit-area', array( $this, 'add_form_submit_area' ) );
            add_action( 'wpuf-form-builder-localize-script', array( $this, 'add_to_localize_script' ) );
            add_filter( 'wpuf-form-builder-field-settings', array( $this, 'add_field_settings' ) );
            add_filter( 'wpuf-form-builder-i18n', array( $this, 'i18n' ) );

            do_action( 'wpuf-form-builder-init-type-wpuf_forms' );

            $this->set_wp_post_types();

            $settings = array(
                'form_type'         => 'post',
                'post_type'         => 'wpuf_forms',
                'post_id'           => $_GET['id'],
                'form_settings_key' => $this->form_settings_key,
                'shortcodes'        => array( array( 'name' => 'wpuf_form' ) )
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
            <a href="#wpuf-metabox-settings-display" class="nav-tab"><?php _e( 'Display Settings', 'wpuf' ); ?></a>
            <a href="#wpuf-metabox-post_expiration" class="nav-tab"><?php _e( 'Post Expiration', 'wpuf' ); ?></a>

            <?php do_action( 'wpuf_post_form_tab' ); ?>

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
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );
        ?>

            <div id="wpuf-metabox-settings" class="group">
                <?php include_once dirname( __FILE__ ) . '/html/form-settings-post.php'; ?>
            </div>

            <div id="wpuf-metabox-settings-update" class="group">
                <?php include_once dirname( __FILE__ ) . '/html/form-settings-post-edit.php'; ?>
            </div>

            <div id="wpuf-metabox-settings-display" class="group">
                <?php include_once dirname( __FILE__ ) . '/html/form-settings-display.php'; ?>
            </div>

            <div id="wpuf-metabox-post_expiration" class="group wpuf-metabox-post_expiration">
                <?php $this->form_post_expiration(); ?>
            </div>

            <?php do_action( 'wpuf_post_form_tab_content' ); ?>

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
        $deps[] = 'wpuf-form-builder-wpuf-forms';

        return apply_filters( 'wpuf-form-builder-wpuf-forms-js-deps', $deps );
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
            'wp_post_types'     => $this->wp_post_types
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
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties( true ) );

        return array(
            'template'      => 'post_title',
            'title'         => __( 'Post Title', 'wpuf' ),
            'icon'          => 'header',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'text',
                'template'          => 'post_title',
                'required'          => 'no',
                'label'             => __( 'Post Title', 'wpuf' ),
                'name'              => 'post_title',
                'is_meta'           => 'no',
                'help'              => '',
                'css'               => '',
                'placeholder'       => '',
                'default'           => '',
                'size'              => 40,
                'word_restriction'  => '',
                'id'                => 0,
                'is_new'            => true,
                'wpuf_cond'         => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
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

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'insert_image',
                'title'         => __( 'Enable Image Insertion', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'options'       => array( 'yes' => __( 'Enable image upload in post area', 'wpuf-pro' ) ),
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 14,
            ),
        ) );

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

        $index    = array_search( 'rich', array_column( $settings, 'name' ) );
        if ( ! empty( $index ) ) {
            unset( $settings[ $index ] );
        }


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
            )
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
