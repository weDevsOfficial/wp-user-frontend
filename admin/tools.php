<?php

require_once dirname( __FILE__ ) . '/class-tools.php';

$tools = new WPUF_Admin_Tools();
?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>

    <h2 class="nav-tab-wrapper">
        <a class="nav-tab <?php echo (!isset( $_GET['action'] ) ) ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array( 'page' => 'wpuf_tools' ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'Import', 'wpuf' ); ?></a>
        <a class="nav-tab <?php echo ( isset( $_GET['action'] ) && $_GET['action'] == 'export' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array( 'page'   => 'wpuf_tools', 'action' => 'export' ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'Export', 'wpuf' ); ?></a>
        <a class="nav-tab <?php echo ( isset( $_GET['action'] ) && $_GET['action'] == 'tools' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array( 'page'   => 'wpuf_tools', 'action' => 'tools' ), admin_url( 'admin.php' ) ); ?>"><?php _e( 'Tools', 'wpuf' ); ?></a>
    </h2>

    <?php
    $action  = isset( $_GET['action'] ) ? $_GET['action'] : '';

    switch ( $action ) {
        case 'export':
            $tools->list_forms();
            $tools->list_regis_forms();
            break;

        case 'tools':
            $tools->tool_page();
            break;

        default:
            $tools->import_data();
            break;
    }
    ?>
</div>

<style>
    select.formlist{
        display: block;
        width: 300px;
    }

</style>

<script>
    (function($){

        $('.formlist').hide();
        $('input.export_type').on('change',function(){
            $(this).closest('form').find('.formlist').slideUp(200);

            if( $(this).attr('value') == 'selected' ) {
                $(this).closest('form').find('.formlist').slideDown(200);
            }
        });


    })(jQuery);

</script>

