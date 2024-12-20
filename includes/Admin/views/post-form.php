<?php
if ( defined( 'WPUF_PRO_VERSION' ) && version_compare( WPUF_PRO_VERSION, '4.1', '<' ) ) {
    ?>
    <div class="wrap">
        <?php do_action( 'wpuf_admin_form_builder' ); ?>
    </div>
    <?php
} else {
    do_action( 'wpuf_admin_form_builder_view' );
}
?>
