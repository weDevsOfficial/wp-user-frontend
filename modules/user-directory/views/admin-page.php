<?php
/**
 * User Directory Admin Page Template
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="wpuf-ud-free-app" class="wpuf-user-directory wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] !wpuf-py-0 wpuf-px-[20px]">
    <noscript>
        <strong>
            <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
        </strong>
    </noscript>
    <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
</div>
