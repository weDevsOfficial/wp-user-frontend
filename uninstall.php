<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * WPUF Uninstall
 *
 * Runs when WP User Frontend is deleted and only removes what the free plugin
 * created itself, each switch on User Frontend > Tools > Uninstall Settings.
 * WP User Frontend Pro's options, tables and post types are left to Pro.
 *
 * @since WPUF_SINCE
 */
class WPUF_Uninstaller {

    /**
     * Constructor for the class WPUF_Uninstaller
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        $uninstall_settings = get_option( 'wpuf_uninstall' );

        if ( ! empty( $uninstall_settings['delete_database'] ) ) {
            $this->drop_tables();
        }

        if ( ! empty( $uninstall_settings['delete_forms'] ) ) {
            $this->delete_forms();
        }

        // pages are found through the settings, so this runs before the settings go
        if ( ! empty( $uninstall_settings['delete_pages'] ) ) {
            $this->delete_pages();
        }

        if ( ! empty( $uninstall_settings['delete_settings'] ) ) {
            $this->delete_settings();
        }

        // reset Tools > Uninstall Settings anyway upon plugin deletion
        delete_option( 'wpuf_uninstall' );

        // Clear any cached data that has been removed.
        wp_cache_flush();
    }

    /**
     * Delete the pages the WPUF installer created
     *
     * A page counts as created by WPUF when the installer marked it, or when it
     * still has exactly the installer's title and content (the pages in WPUF's
     * page settings, plus Dashboard and Account). Pages the site owner built or
     * edited are kept, even with a [wpuf shortcode.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function delete_pages() {
        global $wpdb;

        $page_ids = get_posts(
            [
                'post_type'   => 'page',
                'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ],
                'numberposts' => -1,
                'fields'      => 'ids',
                'meta_key'    => '_wpuf_installer_page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            ]
        );

        // Sites installed before the marker existed: the page settings the installer fills.
        foreach ( $this->get_installer_page_settings() as $setting ) {
            $options = get_option( $setting['option'], [] );
            $page_id = is_array( $options ) && ! empty( $options[ $setting['key'] ] ) ? absint( $options[ $setting['key'] ] ) : 0;

            if ( $page_id && $this->is_installer_page( $page_id, $setting['title'], $setting['content'] ) ) {
                $page_ids[] = $page_id;
            }
        }

        // The installer's Dashboard and Account pages are not stored in a setting.
        if ( get_option( '_wpuf_page_created' ) ) {
            foreach ( [ 'Dashboard' => '[wpuf_dashboard]', 'Account' => '[wpuf_account]' ] as $title => $content ) {
                $page_ids = array_merge(
                    $page_ids,
                    $wpdb->get_col(
                        $wpdb->prepare(
                            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_title = %s AND TRIM(post_content) = %s",
                            $title,
                            $content
                        )
                    )
                );
            }
        }

        // The private preview page WPUF creates for the form preview.
        $preview_page_id = absint( get_option( 'wpuf_preview_page', 0 ) );

        if ( $preview_page_id && 'page' === get_post_type( $preview_page_id ) ) {
            $page_ids[] = $preview_page_id;
        }

        foreach ( array_unique( array_map( 'absint', $page_ids ) ) as $page_id ) {
            wp_delete_post( $page_id, true );
        }
    }

    /**
     * The page settings the WPUF installer fills, with the title and content it creates the page with
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_installer_page_settings() {
        return [
            [
                'option'  => 'wpuf_frontend_posting',
                'key'     => 'edit_page_id',
                'title'   => 'Edit',
                'content' => '[wpuf_edit]',
            ],
            [
                'option'  => 'wpuf_profile',
                'key'     => 'login_page',
                'title'   => 'Login',
                'content' => '[wpuf-login]',
            ],
            [
                'option'  => 'wpuf_my_account',
                'key'     => 'account_page',
                'title'   => 'Account',
                'content' => '[wpuf_account]',
            ],
            [
                'option'  => 'wpuf_payment',
                'key'     => 'subscription_page',
                'title'   => 'Subscription',
                'content' => '[wpuf_sub_pack]',
            ],
            [
                'option'  => 'wpuf_payment',
                'key'     => 'payment_page',
                'title'   => 'Payment',
                'content' => 'Please select a gateway for payment',
            ],
            [
                'option'  => 'wpuf_payment',
                'key'     => 'payment_success',
                'title'   => 'Thank You',
                'content' => '<h1>Payment is complete</h1><p>Congratulations, your payment has been completed!</p>',
            ],
            [
                'option'  => 'wpuf_payment',
                'key'     => 'bank_success',
                'title'   => 'Order Received',
                'content' => 'Hi, we have received your order. We will validate the order and will take necessary steps to move forward.',
            ],
        ];
    }

    /**
     * Whether a page still has exactly the title and content the installer created it with
     *
     * @since WPUF_SINCE
     *
     * @param int    $page_id Page ID.
     * @param string $title   Installer title.
     * @param string $content Installer content.
     *
     * @return bool
     */
    private function is_installer_page( $page_id, $title, $content ) {
        $page = get_post( $page_id );

        return $page && 'page' === $page->post_type && $title === $page->post_title && trim( $page->post_content ) === $content;
    }

    /**
     * Delete all WPUF post forms, registration forms, form fields and subscription packs
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function delete_forms() {
        $posts = get_posts(
            [
                'post_type'   => [ 'wpuf_forms', 'wpuf_profile', 'wpuf_input', 'wpuf_subscription' ],
                'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ],
                'numberposts' => -1,
                'fields'      => 'ids',
            ]
        );

        // wp_delete_post() also removes each post's own meta
        foreach ( $posts as $post_id ) {
            wp_delete_post( $post_id, true );
        }
    }

    /**
     * Delete the options WPUF free stores, by exact name
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function delete_settings() {
        foreach ( $this->get_options() as $option ) {
            delete_option( $option );
        }

        foreach ( [ 'wpuf_activation_redirect', 'wpuf_ai_models_cache', 'wpuf_sort_order_migration_done', 'wpuf_ud_directory_pages' ] as $transient ) {
            delete_transient( $transient );
        }
    }

    /**
     * The options WPUF free stores
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_options() {
        return [
            // settings sections of User Frontend > Settings
            'wpuf_general',
            'wpuf_frontend_posting',
            'wpuf_dashboard',
            'wpuf_my_account',
            'wpuf_profile',
            'wpuf_payment',
            'wpuf_mails',
            'wpuf_privacy',
            'wpuf_ai',
            // other free options
            '_wpuf_page_created',
            'wpuf_address_options',
            'wpuf_ai_form_creation_log',
            'wpuf_ai_generation_log',
            'wpuf_auth_keys',
            'wpuf_base_country_state',
            'wpuf_dismiss_notice_acf',
            'wpuf_failed_post_expiration_time',
            'wpuf_free_active_modules',
            'wpuf_guest_mails',
            'wpuf_installed',
            'wpuf_migrate_acf',
            'wpuf_paypal_settings_notice_dismissed',
            'wpuf_paypal_webhook_flushed',
            'wpuf_preview_page',
            'wpuf_review_notice_dismiss',
            'wpuf_setup_wizard',
            'wpuf_subscription_settings',
            'wpuf_tax_rates',
            'wpuf_taxonomy_view_restrictions_enabled',
            'wpuf_ud_flush_rewrite_rules',
            'wpuf_version',
            'wpuf_whats_new',
            // usage tracking of the bundled Appsero client
            'wp-user-frontend_allow_tracking',
            'wp-user-frontend_tracking_notice',
            'wp-user-frontend_tracking_last_send',
            'wp-user-frontend_tracking_skipped',
        ];
    }

    /**
     * Drop the tables WPUF free creates
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function drop_tables() {
        global $wpdb;

        foreach ( [ 'wpuf_transaction', 'wpuf_subscribers' ] as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
        }
    }
}

new WPUF_Uninstaller();
