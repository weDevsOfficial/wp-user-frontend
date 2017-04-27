<div id="wpuf-form-template-modal">
    <div class="wpuf-form-template-modal">

        <span id="modal-label" class="screen-reader-text"><?php _e( 'Modal window. Press escape to close.',  'wpuf'  ); ?></span>
        <a href="#" class="close">× <span class="screen-reader-text"><?php _e( 'Close modal window',  'wpuf'  ); ?></span></a>

        <header class="modal-header">
            <h2>
                <?php _e( 'Select a Template', 'wpuf' ); ?>
                <small><?php
                printf(
                    __( 'Select from a pre-defined template or from a <a href="%s">blank form</a>', 'wpuf' ),
                    $blank_form_url
                ); ?></small>
            </h2>
        </header>

        <div class="content-container modal-footer">
            <div class="content">

                <ul>
                    <li class="blank-form">
                        <a href="<?php echo $blank_form_url; ?>">
                            <span class="dashicons dashicons-plus"></span>
                            <div class="title"><?php _e( 'Blank Form', 'wpuf' ); ?></div>
                        </a>
                    </li>

                    <?php
                    foreach ($registry as $key => $template ) {
                        $class = 'template-active';
                        $title = '';
                        $url   = esc_url( add_query_arg( array(
                            'action'   => $action_name,
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

        <?php if ( $footer_help ) : ?>
            <footer>
                <?php echo $footer_help; ?>
            </footer>
        <?php endif; ?>
    </div>
    <div class="wpuf-form-template-modal-backdrop"></div>
</div>


<script type="text/javascript">
(function($) {
    var popup = {
        init: function() {
            $('.wrap').on('click', 'a.page-title-action.add-form', this.openModal);
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
