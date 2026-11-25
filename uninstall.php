<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * WPUF Uninstall
 *
 * Runs when WP User Frontend is deleted and only removes what the free plugin
 * created itself, each switch on User Frontend > Tools > Uninstall Settings.
 * WP User Frontend Pro's options, tables and post types are left to Pro, and
 * the settings both plugins share are kept while Pro is still installed.
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
     * A page counts as created by WPUF when the installer marked it and its
     * content is still the installer's, or when it still has exactly the
     * installer's title and content (the pages in WPUF's page settings, plus
     * Dashboard and Account). Pages the site owner built or edited are kept,
     * even with a [wpuf shortcode.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function delete_pages() {
        global $wpdb;

        $marked_ids = get_posts(
            [
                'post_type'   => 'page',
                'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ],
                'numberposts' => -1,
                'fields'      => 'ids',
                'meta_key'    => '_wpuf_installer_page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            ]
        );

        // A marked page the site owner edited is kept, like an unmarked one.
        $installer_contents = array_merge( wp_list_pluck( $this->get_installer_page_settings(), 'content' ), [ '[wpuf_dashboard]' ] );
        $page_ids           = [];

        foreach ( $marked_ids as $marked_id ) {
            if ( in_array( trim( (string) get_post_field( 'post_content', $marked_id ) ), $installer_contents, true ) ) {
                $page_ids[] = $marked_id;
            }
        }

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
        $post_types = [ 'wpuf_forms', 'wpuf_input', 'wpuf_subscription' ];
        $pro        = $this->is_pro_installed();

        // WP User Frontend Pro builds its registration forms on wpuf_profile, keep them while Pro is installed
        if ( ! $pro ) {
            $post_types[] = 'wpuf_profile';
        }

        $posts = get_posts(
            [
                'post_type'   => $post_types,
                'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future', 'trash' ],
                'numberposts' => -1,
                'fields'      => 'ids',
                'orderby'     => 'ID',
                'order'       => 'ASC',
            ]
        );

        // wp_delete_post() also removes each post's own meta
        foreach ( $posts as $post_id ) {
            // the fields of a kept registration form stay with it
            if ( $pro && 'wpuf_input' === get_post_type( $post_id ) && 'wpuf_profile' === get_post_type( wp_get_post_parent_id( $post_id ) ) ) {
                continue;
            }

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
        global $wpdb;

        $options = $this->get_options();

        // WP User Frontend Pro stores its own fields in these options too.
        if ( $this->is_pro_installed() ) {
            $options = array_diff( $options, $this->get_options_shared_with_pro() );
        }

        foreach ( $options as $option ) {
            delete_option( $option );
        }

        // Two-factor login user data. The TwoFactor code arrives with #1885, so the keys are
        // matched by its known prefix: _wpuf_2fa_method_{id}_*, _wpuf_2fa_audit_log,
        // _wpuf_2fa_failures and _wpuf_2fa_locked_until.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $wpdb->esc_like( '_wpuf_2fa_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

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
            'wpuf_2fa',
            // Pro feature sections free registers in its settings (Pro shares them)
            'wpuf_content_restriction',
            'wpuf_payment_invoices',
            'wpuf_payment_tax',
            'wpuf_seo_settings',
            'wpuf_sms',
            'wpuf_social_api',
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
            'wpuf_submission_form_cleared',
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
     * The options WPUF free shares with WP User Frontend Pro
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    private function get_options_shared_with_pro() {
        return [
            'wpuf_profile',
            'wpuf_mails',
            'wpuf_tax_rates',
            'wpuf_base_country_state',
            'wpuf_address_options',
            'wpuf_taxonomy_view_restrictions_enabled',
            'wpuf_content_restriction',
            'wpuf_payment_invoices',
            'wpuf_payment_tax',
            'wpuf_seo_settings',
            'wpuf_sms',
            'wpuf_social_api',
        ];
    }

    /**
     * Whether WP User Frontend Pro is still installed (active or not)
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function is_pro_installed() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ( array_keys( get_plugins() ) as $plugin ) {
            if ( 'wpuf-pro.php' === basename( $plugin ) ) {
                return true;
            }
        }

        return false;
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
