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
            $this->delete_pages();
        }
        if ( ! empty( $uninstall_settings['delete_settings'] ) ) {
            $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wpuf_%'" );
        }
        // reset Tools > Uninstall Settings anyway upon plugin deletion
        delete_option( 'wpuf_uninstall' );
        // Clear any cached data that has been removed.
        wp_cache_flush();
    }

    /**
     * Delete WPUF pages
     *
     * @since WPUF
     *
     * @return void
     */
    private function delete_pages() {
        global $wpdb;
        // Fetch pages that contain WPUF shortcodes
        $page_ids = $wpdb->get_col(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_content LIKE '%[wpuf%'"
        );
        if ( $page_ids ) {
            foreach ( $page_ids as $page_id ) {
                wp_delete_post( $page_id, true );
            }
        }
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
                'post_type' => $post_types,
                'posts_per_page' => -1,
                'post_status' => [ 'publish', 'draft', 'pending', 'trash' ],
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
