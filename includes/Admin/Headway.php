<?php

namespace WeDevs\Wpuf\Admin;

class Headway {
    public function __construct() {
        add_action( 'admin_bar_menu', [ $this, 'add_item_in_admin_bar' ], 100 );
        add_action( 'admin_footer', [ $this, 'load_badge' ] );
    }

    /**
     * Adds menu item showing number of notifications.
     *
     * @param \WP_Admin_Bar $admin_bar WordPress admin bar.
     */
    public function add_item_in_admin_bar( $admin_bar ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $admin_bar->add_menu(
            [
                'id'     => 'wpuf_notification_count',
                'title'  => esc_html__( 'WPUF Whats New', 'wp-user-frontend' ),
                'href'   => '#',
                'parent' => 'top-secondary',
                'meta'   => [
                    'class' => 'attach_headway_badge',
                ],
            ]
        );
    }

    public function load_badge() {
            ?>
        <style>
            li#wp-admin-bar-wpuf_notification_count {
                display: flex;
                flex-direction: row-reverse;
            }

            li#wp-admin-bar-wpuf_notification_count span#HW_badge {
                border-radius: 20px;
                padding: 0 10px;
            }
        </style>
            <script>
                var HW_config = {
                    selector: '#wp-admin-bar-wpuf_notification_count', // CSS selector where to inject the badge
                    account: '7vbOM7'
                };
            </script>
            <script async src="//cdn.headwayapp.co/widget.js"></script>
            <?php
        }
}
