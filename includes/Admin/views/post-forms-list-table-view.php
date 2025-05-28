<div id="wpuf-post-forms-list-table-view" class="wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] wpuf-py-0 wpuf-px-[20px]">
    <noscript>
        <strong>
            <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
        </strong>
    </noscript>

    <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
</div>

<div class="wpuf-footer-help">
    <span class="wpuf-footer-help-content">
        <span class="dashicons dashicons-editor-help"></span>
        <?php printf( wp_kses_post(
            // translators: %s is url
            __( 'Learn more about <a href="%s" target="_blank">Frontend Posting</a>', 'wp-user-frontend' ) ), 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/?utm_source=wpuf-footer-help&utm_medium=text-link&utm_campaign=learn-more-frontend-posting'
            ); ?>
    </span>
</div>
