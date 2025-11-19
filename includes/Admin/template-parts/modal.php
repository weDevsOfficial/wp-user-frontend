<?php use WeDevs\Wpuf\Free\Pro_Prompt; ?>
<div id="wpuf-form-template-modal">
    <div class="wpuf-form-template-modal">

        <span id="modal-label" class="screen-reader-text"><?php esc_html_e( 'Modal window. Press escape to close.', 'wp-user-frontend'  ); ?></span>
        <a href="#" class="close">× <span class="screen-reader-text"><?php esc_html_e( 'Close modal window', 'wp-user-frontend'  ); ?></span></a>

        <header class="modal-header">
            <h2>
                <?php esc_html_e( 'Select a Template', 'wp-user-frontend' ); ?>
                <small>
                    <?php
                    printf(
                        // translators: %s is a URL leading to a blank form.
                        wp_kses_post( __( 'Select from a pre-defined template or from a <a href="%s">blank form</a>', 'wp-user-frontend' ) ),
                        esc_attr( $blank_form_url )
                     );
                    ?>
                </small>
            </h2>
        </header>

        <div class="content-container modal-footer">
            <div class="content">

                <ul>
                    <li class="blank-form">
                        <h3><?php esc_html_e( 'Blank Form', 'wp-user-frontend' ); ?></h3>

                        <div class="form-middle-text">
                            <span class="dashicons dashicons-plus"></span>
                            <div class="title"><?php esc_html_e( 'Blank Form', 'wp-user-frontend' ); ?></div>
                        </div>

                        <div class="form-create-overlay">
                            <div class="title"><?php esc_html_e( 'Blank Form', 'wp-user-frontend' ); ?></div>
                            <br>
                            <a href="<?php echo esc_url( $blank_form_url ); ?>" class="button button-primary" title="<?php echo esc_attr( 'Blank Form' ); ?>">
                                <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                            </a>
                        </div>
                    </li>

                    <?php
                    // Add AI Forms template
                    $ai_configured = wpuf_check_ai_configuration();
                    $ai_form_url = $ai_configured ? add_query_arg( [
                        'action'   => $action_name,
                        'template' => 'ai_form',
                        '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                    ], admin_url( 'admin.php' ) ) : '#';
                    ?>
                    <li class="ai-forms-template <?php echo $ai_configured ? 'template-active' : 'template-inactive'; ?>">
                        <h3><?php esc_html_e( 'AI Forms', 'wp-user-frontend' ); ?></h3>

                        <div class="wpuf-bg-white wpuf-rounded-lg wpuf-flex wpuf-items-center wpuf-justify-center" style="padding: 20px;">
                            <svg width="120" height="120" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="form-create-overlay">
                            <div class="title"><?php echo $ai_configured ? esc_html__( 'Create with AI', 'wp-user-frontend' ) : esc_html__( 'AI not configured', 'wp-user-frontend' ); ?></div>
                            <div class="description"><?php esc_html_e( 'Generate forms automatically using AI', 'wp-user-frontend' ); ?></div>
                            <br>
                            <a href="<?php echo esc_url( $ai_form_url ); ?>" class="button button-primary" <?php echo !$ai_configured ? 'disabled' : ''; ?>>
                                <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                            </a>
                        </div>
                    </li>

                    <?php
                    foreach ( $registry as $key => $template ) {
                        $class    = 'template-active';
                        $title    = $template->title;
                        $image    = $template->image ? $template->image : '';
                        $disabled = '';

                        $url   = esc_url( add_query_arg( [
                            'action'   => $action_name,
                            'template' => $key,
                            '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                        ], admin_url( 'admin.php' ) ) );

                        if ( !$template->is_enabled() ) {
                            $url      = '#';
                            $class    = 'template-inactive';
                            $title    = __( 'This integration is not installed.', 'wp-user-frontend' );
                            $disabled = 'disabled';
                        } ?>

                        <li class="<?php echo esc_attr( $class ); ?>">
                            <h3><?php echo esc_html( $template->get_title() ); ?></h3>
                            <?php if ( $image ) {
                                printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                            } ?>

                            <div class="form-create-overlay">
                                <div class="title"><?php echo esc_html( $title ); ?></div>
                                <div class="description"><?php echo esc_html( $template->get_description() ); ?></div>
                                <br>
                                <a href="<?php echo esc_url( $url ); ?>" class="button button-primary" title="<?php echo esc_attr( $template->get_title() ); ?>" <?php echo esc_attr($disabled ); ?>>
                                    <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                </a>
                            </div>
                        </li>

                        <?php
                    }

                    $crown_icon = WPUF_ROOT . '/assets/images/pro-badge.svg';
                    foreach ( $pro_templates as $template ) {
                        $class = 'template-inactive is-pro-template';
                        $image = $template->get_image();
                        $title = $template->get_title();
                        ?>

                        <li class="<?php echo esc_attr( $class ); ?>">
                            <h3>
                                <?php
                                    echo esc_html( $title );
                                    if ( file_exists( $crown_icon ) ) {
                                        printf( '<span class="pro-icon-title"> %s</span>', wp_kses_post( file_get_contents( wp_kses($crown_icon, array('svg' => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewBox' => true, 'fill' => true ], 'path' => [ 'd' => true, 'fill' => true ], 'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ] ) ) ) ) );
                                    }
                                ?>
                            </h3>
                            <?php if ( $image ) {
                                printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                            } ?>

                            <div class="form-create-overlay">
                                <a href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>"
                                   target="_blank"
                                   class="wpuf-button button-upgrade-to-pro"
                                   title="<?php echo esc_attr( $template->get_title() ); ?>" >
                                    <?php
                                        esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' );
                                        if ( file_exists( $crown_icon ) ) {
                                            printf( '<span class="pro-icon"> %s</span>', wp_kses_post( file_get_contents( wp_kses($crown_icon, array('svg' => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewBox' => true, 'fill' => true ], 'path' => [ 'd' => true, 'fill' => true ], 'circle' => [ 'cx' => true, 'cy' => true, 'r' => true ] ) ) ) ) );
                                        }
                                    ?>
                                </a>
                            </div>
                        </li>

                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>

        <?php if ( $footer_help ) { ?>
            <footer>
                <?php echo wp_kses_post( $footer_help ); ?>
            </footer>
        <?php } ?>
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
