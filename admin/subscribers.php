<?php
$option = 'per_page';
$args   = array(
    'label'   => __( 'Number of subscribers per page:', 'wpuf' ),
    'default' => 20,
    'option'  => 'subscribers_per_page'
);

add_screen_option( $option, $args );

if ( ! class_exists( 'WPUF_Subscribers_List_Table' ) ) {
    require_once WPUF_ROOT . '/class/subscribers-list-table.php';
}

$this->subscribers_list_table_obj = new WPUF_Subscribers_List_Table();
?>
<div class="wrap">
    <h2><?php _e( 'Subscribers', 'wpuf' ); ?></h2>

    <form method="post">
        <input type="hidden" name="page" value="subscribers">
        <?php
            $this->subscribers_list_table_obj->prepare_items();
            $this->subscribers_list_table_obj->get_views();
            $this->subscribers_list_table_obj->display();
        ?>
    </form>
</div>
