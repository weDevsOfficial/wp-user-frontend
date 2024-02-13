<div id="subscription-page">
    <div class="header wpuf-flex wpuf-flex-row wpuf-mt-4 wpuf-justify-between">
        <div class="wpuf-basis-1/2 wpuf-flex wpuf-justify-start wpuf-items-center">
                <img src="<?php echo esc_url( WPUF_ASSET_URI ) . '/images/icon-128x128.png'; ?>" alt="WPUF Icon" class="wpuf-w-12 wpuf-mr-4">
            <?php esc_html_e( 'WP User Frontend', 'wp-user-frontend' ); ?>
            <span class="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20">V4.0.6</span>
        </div>
        <div class="wpuf-basis-1/2">
            support button
        </div>
    </div>
    <div class="wpuf-flex wpuf-flex-row wpuf-mt-12">
        <div class="wpuf-basis-1/4 wpuf-border-r-2 wpuf-border-zinc-300">
            <h2><?php esc_html_e( 'Subscriptions', 'wp-user-frontend' ); ?></h2>
        </div>
        <div class="wpuf-basis-1/2">
            <?php wpuf_include_once( WPUF_ROOT . '/includes/Admin/template-parts/no-subscription.php' ); ?>
        </div>
    </div>
</div>
