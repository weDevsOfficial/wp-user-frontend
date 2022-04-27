<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * WPUF Uninstall
 *
 * Uninstalling WPUF deletes forms, tables, pages, meta data and options.
 *
 * @since WPUF
 */
class WPUF_Uninstaller {
    /**
     * Constructor for the class WPUF_Uninstaller
     *
     * @since WPUF
     */
    public function __construct() {
        global $wpdb;
        $uninstall_settings = get_option( 'wpuf_uninstall' );

        if ( ! empty( $uninstall_settings['delete_database'] ) ) {
            $this->drop_tables();
        }

        if ( ! empty( $uninstall_settings['delete_forms'] ) ) {
            $this->delete_forms();
        }

        if ( ! empty( $uninstall_settings['delete_pages'] ) ) {
            $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'page' AND post_content LIKE '[wpuf%'" );
        }

        if ( ! empty( $uninstall_settings['delete_settings'] ) ) {
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wpuf_%'" );
        }

        // Clear any cached data that has been removed.
        wp_cache_flush();
    }

    /**
     * Delete all WordPress page ids as an array
     *
     * @since WPUF
     *
     * @return void
     */
    private function get_all_page_ids( $post_type = 'page' ) {
        $page_ids = [];
        $pages = get_posts(
            [
                'post_type'              => $post_type,
                'numberposts'            => - 1,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]
        );

        if ( $pages ) {
            foreach ( $pages as $page ) {
                $page_ids[] = $page->ID;
            }
        }

        return $page_ids;
    }

    /**
     * Delete all WPUF post and registration forms
     *
     * @since WPUF
     *
     * @return void
     */
    private function delete_forms() {
        global $wpdb;

        $post_types = [ 'wpuf_forms', 'wpuf_profile', 'wpuf_input', 'wpuf_subscription', 'wpuf_coupon' ];

        // get all wpuf related posts
        $query = new WP_Query(
            [
                'post_type'      => $post_types,
                'posts_per_page' => -1,
                'post_status'    => [ 'publish', 'draft', 'pending', 'trash' ],
            ]
        );

        $posts = $query->get_posts();

        if ( $posts ) {
            foreach ( $posts as $item ) {
                wp_delete_post( $item->ID, true );
            }
        }

        $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%wpuf_%'" );

        wp_reset_postdata();
    }

    /**
     * Drop all tables created by WPUF
     *
     * @since WPUF
     *
     * @return void
     */
    private function drop_tables() {
        global $wpdb;

        $tables = $this->get_tables();

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" ); // phpcs:ignore
        }
    }

    /**
     * Return a list of tables. Used to make sure all WPUF are dropped
     * when uninstalling the plugin
     *
     * @since WPUF
     *
     * @return array
     */
    private function get_tables() {
        return [
            'wpuf_transaction',
            'wpuf_subscribers',
            'wpuf_message',
            'wpuf_activity',
        ];
    }
}

new WPUF_Uninstaller();
