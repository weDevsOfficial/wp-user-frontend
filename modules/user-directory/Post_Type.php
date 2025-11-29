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
        // Don't register if Pro module is handling it
        if ( $this->is_pro_module_active() ) {
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

        register_post_type( User_Directory::POST_TYPE, $args );
    }

    /**
     * Check if Pro User Directory module is active
     *
     * @since 4.3.0
     *
     * @return bool
     */
    private function is_pro_module_active() {
        // Check if Pro is active and User Directory module is enabled
        if ( ! wpuf_is_pro_active() ) {
            return false;
        }

        // Check if the Pro module class exists
        if ( class_exists( 'WPUF_User_Listing' ) ) {
            return true;
        }

        return false;
    }
}
