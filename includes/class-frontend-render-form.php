<?php

class WPUF_Frontend_Render_Form {

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'wpuf_upgrade_notice' ] );
    }

    public function wpuf_upgrade_notice() {
        // check whether the version of wpuf pro is prior to the code restructure
        if ( defined( 'WPUF_PRO_VERSION' ) && version_compare( WPUF_PRO_VERSION, '4', '<' ) ) {
            deactivate_plugins( WPUF_PRO_FILE );
            ?>
        <div class="notice error" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php esc_html_e( 'Your WP User Frontend Pro is almost ready!', 'wp-user-frontend' ); ?></h2>
            <p><?php esc_html_e( 'You just need to upgrade the Plugin version 4.0 or above to make it functional.', 'wp-user-frontend' ); ?></p>
        </div>
            <?php
        }
    }
}

new WPUF_Frontend_Render_Form();
