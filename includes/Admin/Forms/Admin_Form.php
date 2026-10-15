<?php

namespace WeDevs\Wpuf\Admin\Forms;

use WeDevs\Wpuf\Traits\FieldableTrait;

/**
 * Post Forms or wpuf_forms form builder class
 */
class Admin_Form {

    use FieldableTrait;

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
    private $wp_post_types = [];

    /**
     * Add neccessary actions and filters
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'wpuf_load_post_forms', [ $this, 'post_forms_builder_init' ] );
    }

    /**
     * Register form post types
     *
     * @return void
     */
    public function register_post_type() {
        $capability = wpuf_admin_role();
        register_post_type(
            'wpuf_forms', [
				'label'           => __( 'Forms', 'wp-user-frontend' ),
				'public'          => false,
				'show_ui'         => false,
				'show_in_menu'    => false, //false,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'query_var'       => false,
				'supports'        => [ 'title' ],
				'capabilities'    => [
					'publish_posts'       => $capability,
					'edit_posts'          => $capability,
					'edit_others_posts'   => $capability,
					'delete_posts'        => $capability,
					'delete_others_posts' => $capability,
					'read_private_posts'  => $capability,
					'edit_post'           => $capability,
					'delete_post'         => $capability,
					'read_post'           => $capability,
				],
				'labels'          => [
					'name'               => __( 'Forms', 'wp-user-frontend' ),
					'singular_name'      => __( 'Form', 'wp-user-frontend' ),
					'menu_name'          => __( 'Forms', 'wp-user-frontend' ),
					'add_new'            => __( 'Add Form', 'wp-user-frontend' ),
					'add_new_item'       => __( 'Add New Form', 'wp-user-frontend' ),
					'edit'               => __( 'Edit', 'wp-user-frontend' ),
					'edit_item'          => __( 'Edit Form', 'wp-user-frontend' ),
					'new_item'           => __( 'New Form', 'wp-user-frontend' ),
					'view'               => __( 'View Form', 'wp-user-frontend' ),
					'view_item'          => __( 'View Form', 'wp-user-frontend' ),
					'search_items'       => __( 'Search Form', 'wp-user-frontend' ),
					'not_found'          => __( 'No Form Found', 'wp-user-frontend' ),
					'not_found_in_trash' => __(
                        'No Form Found in Trash',
                        'wp-user-frontend'
                    ),
					'parent'             => __( 'Parent Form', 'wp-user-frontend' ),
				],
			]
        );
        register_post_type(
            'wpuf_profile', [
				'label'           => __( 'Registraton Forms', 'wp-user-frontend' ),
				'public'          => false,
				'show_ui'         => false,
				'show_in_menu'    => false,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'query_var'       => false,
				'supports'        => [ 'title' ],
				'capabilities'    => [
					'publish_posts'       => $capability,
					'edit_posts'          => $capability,
					'edit_others_posts'   => $capability,
					'delete_posts'        => $capability,
					'delete_others_posts' => $capability,
					'read_private_posts'  => $capability,
					'edit_post'           => $capability,
					'delete_post'         => $capability,
					'read_post'           => $capability,
				],
				'labels'          => [
					'name'               => __( 'Forms', 'wp-user-frontend' ),
					'singular_name'      => __( 'Form', 'wp-user-frontend' ),
					'menu_name'          => __(
                        'Registration Forms',
                        'wp-user-frontend'
                    ),
					'add_new'            => __( 'Add Form', 'wp-user-frontend' ),
					'add_new_item'       => __( 'Add New Form', 'wp-user-frontend' ),
					'edit'               => __( 'Edit', 'wp-user-frontend' ),
					'edit_item'          => __( 'Edit Form', 'wp-user-frontend' ),
					'new_item'           => __( 'New Form', 'wp-user-frontend' ),
					'view'               => __( 'View Form', 'wp-user-frontend' ),
					'view_item'          => __( 'View Form', 'wp-user-frontend' ),
					'search_items'       => __( 'Search Form', 'wp-user-frontend' ),
					'not_found'          => __( 'No Form Found', 'wp-user-frontend' ),
					'not_found_in_trash' => __(
						'No Form Found in Trash',
						'wp-user-frontend'
					),
					'parent'             => __( 'Parent Form', 'wp-user-frontend' ),
				],
			]
        );
        register_post_type(
            'wpuf_input', [
				'public'       => false,
				'show_ui'      => false,
				'show_in_menu' => false,
			]
        );
    }

    /**
     * Initiate form builder for wpuf_forms post type
     *
     * @since 2.5
     *
     * @return void
     */
    public function post_forms_builder_init() {
        if ( empty( $_GET['action'] ) ) {
            return;
        }

        if ( 'add-new' === $_GET['action'] && empty( $_GET['id'] ) ) {
            $form_id          = wpuf_create_sample_form( 'Sample Form', 'wpuf_forms', true );
            $add_new_page_url = add_query_arg(
                [ 'id' => $form_id ],
                admin_url( 'admin.php?page=wpuf-post-forms&action=edit' )
            );
            wp_safe_redirect( $add_new_page_url );
        }
        if ( ( 'edit' === $_GET['action'] ) && ! empty( $_GET['id'] ) ) {
            add_action( 'wpuf_form_builder_settings_tabs_post', [ $this, 'add_settings_tabs' ] );
            add_filter( 'wpuf_form_fields_section_before', [ $this, 'add_post_field_section' ] );

            add_filter( 'wpuf_form_builder_localize_script', [ $this, 'add_to_localize_script' ] );
            add_filter( 'wpuf_form_fields', [ $this, 'add_field_settings' ] );
            add_filter( 'wpuf_form_builder_i18n', [ $this, 'i18n' ] );

            do_action( 'wpuf_form_builder_init_type_wpuf_forms' );

            $this->set_wp_post_types();
            $settings = [
                'form_type'         => 'post',
                'post_type'         => 'wpuf_forms',
                'post_id'           => ! empty( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : '',
                'form_settings_key' => $this->form_settings_key,
                'shortcodes'        => [ [ 'name' => 'wpuf_form' ] ],
            ];
            wpuf()->container['form_builder'] = new Admin_Form_Builder( $settings );
        }
    }

    /**
     * Add settings tabs
     *
     * Listener of `wpuf_form_builder_settings_tabs_post`. The Vue builder's
     * settings view it printed (admin/form-builder/views/post-form-settings.php)
     * is gone: the React builder renders the settings from
     * `wpuf_get_post_form_builder_setting_menu_contents()`, and
     * `Builder\HookBridge` fires the tab hooks, this one and
     * `wpuf_post_form_tab` included, with WPUF's own listeners left out. The
     * method stays for code that calls it; `wpuf_post_form_tab` still fires.
     *
     * @since 2.5
     * @since WPUF_SINCE No longer prints the Vue settings view.
     *
     * @return void
     */
    public function add_settings_tabs() {
        do_action( 'wpuf_post_form_tab' );
    }

    /**
     * Add post fields in form builder
     *
     * @since 2.5
     *
     * @return array
     */
    public function add_post_field_section() {
        $post_fields = apply_filters(
            'wpuf-form-builder-wp_forms-fields-section-post-fields', [
				'post_title',
				'post_content',
				'post_excerpt',
				'featured_image',
			]
        );

        return [
            [
                'title'  => __( 'Post Fields', 'wp-user-frontend' ),
                'id'     => 'post-fields',
                'fields' => $post_fields,
            ],
            [
                'title'  => __( 'Taxonomies', 'wp-user-frontend' ),
                'id'     => 'taxonomies',
                'fields' => [],
            ],
        ];
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
        return array_merge(
            $data, [
				'wp_post_types' => $this->wp_post_types,
			]
        );
    }

    /**
     * Translatable strings specially for Post Forms
     *
     * @since 2.5
     *
     * @param array $i18n
     *
     * @return array
     */
    public function i18n( $i18n ) {
        return array_merge(
            $i18n, [
				'any_of_three_needed' =>
					sprintf(
						// translators: %1$s, %2$s, %3$s, %4$s, %5$s, %6$s, %7$s, %8$s are HTML markup for styling
						__( '%1$sSome required fields are missing. Please include a %2$sTitle%3$s, %4$sBody%5$s, or %6$sExcerpt%7$s to continue.%8$s', 'wp-user-frontend' ),
						'<p class="!wpuf-m-0 wpuf-text-xl wpuf-text-gray-500">',
						'<span class="wpuf-font-semibold">',
						'</span>',
						'<span class="wpuf-font-semibold">',
						'</span>',
						'<span class="wpuf-font-semibold">',
						'</span>',
						'</p>'
					),
			]
        );
    }
}
