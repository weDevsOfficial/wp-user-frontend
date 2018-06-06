<header class="dokan-dashboard-header">
    <span class="pull-right">
        <a href="<?php echo dokan_get_navigation_url( 'posts' ); ?>?action=new-post" class="dokan-btn dokan-btn-theme"><?php _e( '+ Add Post', 'wp-user-frontend' ); ?></a>
    </span>
</header><!-- .dokan-dashboard-header -->

<?php echo do_shortcode( '[wpuf_dashboard]' ); ?>