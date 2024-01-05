<?php

class WPUF_Frontend_Render_Form {

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'wpuf_upgrade_notice' ] );
    }

    public function wpuf_upgrade_notice() {
        // check whether the version of wpuf pro is prior to the code restructure
        if ( defined( 'WPUF_PRO_VERSION' ) && version_compare( WPUF_PRO_VERSION, '4', '<' ) ) {
            // deactivate_plugins( WPUF_PRO_FILE );
            ?>
        <div class="notice error" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php esc_html_e( 'Your WP User Frontend Pro is almost ready!', 'wp-user-frontend' ); ?></h2>
            <p>
                <?php
                /* translators: 1: opening anchor tag, 2: closing anchor tag. */
                echo sprintf( __( 'We\'ve pushed a major update on both <b>WP User Frontend Free</b> and <b>WP User Frontend Pro</b> that requires you to use latest version of both. Please update the WPUF pro to the latest version. <br><strong>Please make sure to take a complete backup of your site before updating.</strong>', 'wpuf-pro' ), '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">', '</a>' );
                ?>
            </p>
        </div>
            <?php
        }
    }
}

new WPUF_Frontend_Render_Form();
