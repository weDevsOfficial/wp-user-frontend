<div id="wpuf-form-template-modal">
    <div class="wpuf-form-template-modal">

        <span id="modal-label" class="screen-reader-text"><?php _e( 'Modal window. Press escape to close.',  'wp-user-frontend'  ); ?></span>
        <a href="#" class="close">× <span class="screen-reader-text"><?php _e( 'Close modal window',  'wp-user-frontend'  ); ?></span></a>

        <header class="modal-header">
            <h2>
                <?php _e( 'Select a Template', 'wp-user-frontend' ); ?>
                <small><?php
                printf(
                    __( 'Select from a pre-defined template or from a <a href="%s">blank form</a>', 'wp-user-frontend' ),
                    $blank_form_url
                ); ?></small>
            </h2>
        </header>

        <div class="content-container modal-footer">
            <div class="content">

                <ul>
                    <li class="blank-form">
                        <h3><?php _e( 'Blank Form', 'wp-user-frontend' ); ?></h3>

                        <div class="form-middle-text">
                            <span class="dashicons dashicons-plus"></span>
                            <div class="title"><?php _e( 'Blank Form', 'wp-user-frontend' ); ?></div>
                        </div>

                        <div class="form-create-overlay">
                            <div class="title"><?php _e( 'Blank Form', 'wp-user-frontend' ); ?></div>
                            <br>
                            <a href="<?php echo $blank_form_url; ?>" class="button button-primary" title="<?php echo esc_attr('Blank Form'); ?>">
                                <?php _e('Create Form', 'wp-user-frontend' );  ?>
                            </a>
                        </div>
                    </li>

                    <?php
                    foreach ($registry as $key => $template ) {
                        $class = 'template-active';
                        $title = $template->title;
                        $image = $template->image ? $template->image : '';
                        $disabled = '';

                        $url   = esc_url( add_query_arg( array(
                            'action'   => $action_name,
                            'template' => $key,
                            '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' )
                        ), admin_url( 'admin.php' ) ) );

                        if ( ! $template->is_enabled() ) {
                            $url   = '#';
                            $class = 'template-inactive';
                            $title = __( 'This integration is not installed.', 'wp-user-frontend' );
                            $disabled = 'disabled';
                        }
                        ?>

                        <li class="<?php echo $class; ?>">
                            <h3><?php echo $template->get_title(); ?></h3>
                            <?php if ( $image ) { printf( '<img src="%s" alt="%s">', $image, $title );   }  ?>

                            <div class="form-create-overlay">
                                <div class="title"><?php echo $title; ?></div>
                                <div class="description"><?php echo $template->get_description(); ?></div>
                                <br>
                                <a href="<?php echo $url; ?>" class="button button-primary" title="<?php echo $template->get_title(); ?>" <?php echo $disabled ?>>
                                    <?php _e('Create Form', 'wp-user-frontend' );  ?>
                                </a>
                            </div>
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
