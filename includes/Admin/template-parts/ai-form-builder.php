<?php
/**
 * AI Form Builder: mount point of the React app (all stages)
 *
 * The data the app reads (`wpufAIFormBuilder`, stage keys included) is
 * localized by Admin\Screens\AiFormBuilder::enqueue().
 *
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<style>
    #wpcontent {
        padding-left: unset;
    }
</style>

<div id="wpuf-ai-form-builder" style="background-color: #FFFFFF;">
    <noscript>
        <strong>
            <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
        </strong>
    </noscript>
    <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
</div>
