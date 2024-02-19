<div id="subscription-page" class="wpuf-pr-[20px]">
    {{ message }}
    <?php wpuf_include_once( WPUF_ROOT . '/includes/Admin/template-parts/subscription-header.php' ); ?>
    <div class="wpuf-flex wpuf-flex-row wpuf-mt-12">
        <div class="wpuf-basis-1/4 wpuf-border-r-2 wpuf-border-zinc-300">
            <h2><?php esc_html_e( 'Subscriptions', 'wp-user-frontend' ); ?></h2>
        </div>
        <div class="wpuf-basis-1/2">
            <?php wpuf_include_once( WPUF_ROOT . '/includes/Admin/template-parts/subscription-no-item.php' ); ?>
        </div>
    </div>
</div>
