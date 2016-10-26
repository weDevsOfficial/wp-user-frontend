<div id="wpuf-form-template-modal">
    <div class="wpuf-form-template-modal">

        <span id="modal-label" class="screen-reader-text"><?php _e( 'Modal window. Press escape to close.', 'erp' ); ?></span>
        <a href="#" class="close">× <span class="screen-reader-text"><?php _e( 'Close modal window', 'erp' ); ?></span></a>

        <header class="modal-header">
            <h2>
                <?php _e( 'Select a Template', 'wpuf' ); ?>
                <small><?php
                printf(
                    __( 'Select from a pre-defined template or from a <a href="%s">blank form</a>', 'wpuf' ),
                    admin_url( 'post-new.php?post_type=wpuf_forms' )
                ); ?></small>
            </h2>
        </header>

        <div class="content-container modal-footer">
            <div class="content">

                <ul>
                    <li class="blank-form">
                        <a href="<?php echo admin_url( 'post-new.php?post_type=wpuf_forms' ); ?>">
                            <span class="dashicons dashicons-plus"></span>
                            <div class="title"><?php _e( 'Blank Form', 'wpuf' ); ?></div>
                        </a>
                    </li>

                    <?php
                    foreach ($registry as $key => $template ) {
                        $class = 'template-active';
                        $title = '';
                        $url   = esc_url( add_query_arg( array(
                            'action'   => 'wpuf_post_form_template',
                            'template' => $key,
                            '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' )
                        ), admin_url( 'admin.php' ) ) );

                        if ( ! $template->is_enabled() ) {
                            $url   = '#';
                            $class = 'template-inactive';
                            $title = __( 'This integration is not installed.', 'wpuf' );
                        }
                        ?>

                        <li class="<?php echo $class; ?>">
                            <a href="<?php echo $url; ?>" title="<?php echo esc_attr( $title ); ?>">
                                <div class="title"><?php echo $template->get_title(); ?></div>
                                <div class="description"><?php echo $template->get_description(); ?></div>
                            </a>
                        </li>

                    <?php } ?>
                </ul>
            </div>
        </div>

        <footer>
            <?php printf( __( 'List of available templates can be found <a href="%s" target="_blank">here</a>.', 'wpuf' ), 'http://docs.wedevs.com/?p=3718' ); ?>
            <?php printf( __( 'Want a new integration? <a href="%s" target="_blank">Let us know</a>.'), 'mailto:support@wedevs.com?subject=WPUF Custom Post Template Integration Request' ); ?>
        </footer>
    </div>
    <div class="wpuf-form-template-modal-backdrop"></div>
</div>


<script type="text/javascript">
(function($) {
    var popup = {
        init: function() {
            $('a.page-title-action').on('click', this.openModal);
            $('.wpuf-form-template-modal-backdrop, .wpuf-form-template-modal .close').on('click', $.proxy(this.closeModal, this) );

            $('body').on( 'keydown', $.proxy(this.onEscapeKey, this) );
        },

        openModal: function(e) {
            e.preventDefault();

            $('.wpuf-form-template-modal').show();
            $('.wpuf-form-template-modal-backdrop').show();
        },

        onEscapeKey: function(e) {
            if ( 27 === e.keyCode ) {
                this.closeModal(e);
            }
        },

        closeModal: function(e) {
            if ( typeof e !== 'undefined' ) {
                e.preventDefault();
            }

            $('.wpuf-form-template-modal').hide();
            $('.wpuf-form-template-modal-backdrop').hide();
        }
    };

    $(function() {
        popup.init();
    });

})(jQuery);
</script>