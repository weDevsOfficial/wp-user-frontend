<?php
$option = 'per_page';
$args   = [
    'label'   => __( 'Number of subscribers per page:', 'wp-user-frontend' ),
    'default' => 20,
    'option'  => 'subscribers_per_page',
];

add_screen_option( $option, $args );

wpuf()->subscriber_list_table = new WeDevs\Wpuf\Admin\List_Table_Subscribers();
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Subscribers', 'wp-user-frontend' ); ?></h2>

    <form method="post">
        <input type="hidden" name="page" value="subscribers">
        <?php
            wpuf()->subscriber_list_table->prepare_items();
            wpuf()->subscriber_list_table->get_views();
            wpuf()->subscriber_list_table->display();
        ?>
    </form>
</div>

<script type="text/javascript">
    jQuery(function($) {
        $('.toplevel_page_wp-user-frontend').each(function(index, el) {
            $(el).removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
        });
    });
</script>
