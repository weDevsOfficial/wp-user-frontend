<?php
/**
 * User Directory Post Type Registration
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Modules\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Post Type Class
 *
 * Registers the wpuf_user_listing post type for storing directory configurations.
 * Provides hooks for Pro version to modify registration.
 *
 * @since 4.3.0
 */
class Post_Type {

    /**
     * Constructor
     *
     * @since 4.3.0
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register_post_type' ] );
    }

    /**
     * Register the wpuf_user_listing post type
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function register_post_type() {
        /**
         * Filter to allow Pro to take over post type registration
         *
         * When Pro is active, it can return true to prevent Free from registering.
         * This is useful when Pro has different post type arguments.
         *
         * @since 4.3.0
         *
         * @param bool $skip_registration Whether to skip Free post type registration. Default false.
         */
        if ( apply_filters( 'wpuf_ud_skip_free_post_type', false ) ) {
            return;
        }

        $labels = [
            'name'               => __( 'User Directories', 'wp-user-frontend' ),
            'singular_name'      => __( 'User Directory', 'wp-user-frontend' ),
            'add_new'            => __( 'Add New', 'wp-user-frontend' ),
            'add_new_item'       => __( 'Add New Directory', 'wp-user-frontend' ),
            'edit_item'          => __( 'Edit Directory', 'wp-user-frontend' ),
            'new_item'           => __( 'New Directory', 'wp-user-frontend' ),
            'view_item'          => __( 'View Directory', 'wp-user-frontend' ),
            'search_items'       => __( 'Search Directories', 'wp-user-frontend' ),
            'not_found'          => __( 'No directories found', 'wp-user-frontend' ),
            'not_found_in_trash' => __( 'No directories found in Trash', 'wp-user-frontend' ),
        ];

        /**
         * Filter post type labels
         *
         * @since 4.3.0
         *
         * @param array $labels The post type labels.
         */
        $labels = apply_filters( 'wpuf_ud_post_type_labels', $labels );

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_rest'        => true,
            'rest_base'           => 'wpuf_user_listing',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title' ],
        ];

        /**
         * Filter post type arguments
         *
         * Pro can modify registration arguments.
         *
         * @since 4.3.0
         *
         * @param array $args The post type arguments.
         */
        $args = apply_filters( 'wpuf_ud_post_type_args', $args );

        register_post_type( User_Directory::POST_TYPE, $args );

        /**
         * Action fired after User Directory post type is registered
         *
         * @since 4.3.0
         *
         * @param string $post_type The registered post type name.
         */
        do_action( 'wpuf_ud_post_type_registered', User_Directory::POST_TYPE );
    }
}
