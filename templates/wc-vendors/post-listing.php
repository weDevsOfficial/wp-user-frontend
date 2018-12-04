<header class="wpuf-dashboard-header">
    <span class="pull-right">
        <a class="wpuf-add-post-button" href="<?php echo get_permalink(get_option( 'wcvendors_vendor_dashboard_page_id' ) ); ?>?action=new-post" class="dokan-btn dokan-btn-theme"><?php _e( '+ Add New Post', 'wp-user-frontend' ); ?></a>
    </span>
</header><!-- .dokan-dashboard-header -->

<?php echo do_shortcode( '[wpuf_dashboard]' ); ?>