<?php use WeDevs\Wpuf\Free\Pro_Prompt; ?>
<div class="wpuf-form-template-modal wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] wpuf-bg-gray-100 wpuf-hidden">
    <div class="wpuf-relative wpuf-mx-auto wpuf-p-20">
        <button
            class="wpuf-absolute wpuf-right-4 wpuf-top-4 wpuf-text-gray-400 hover:wpuf-text-gray-600 focus:wpuf-outline-none wpuf-close-btn wpuf-border wpuf-border-gray-200 wpuf-rounded-3xl wpuf-p-2 hover:wpuf-border-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Header -->
        <div class="wpuf-mb-12 wpuf-text-center">
            <h1 class="wpuf-text-2xl wpuf-font-bold wpuf-text-gray-800">
                <?php esc_html_e( 'Select a Post Form Template', 'wp-user-frontend' ); ?>
            </h1>
            <p class="wpuf-text-gray-600">
                <?php esc_html_e( 'Select from a pre-defined template or from a blank form', 'wp-user-frontend' ); ?>
            </p>
        </div>

        <!-- Templates Grid -->
        <div class="wpuf-grid wpuf-place-content-center wpuf-text-center">
            <div class="wpuf-grid wpuf-gap-6 wpuf-grid-cols-3 wpuf-max-w-[768px]">
                <div class="wpuf-relative wpuf-group">
                    <img src="<?php esc_attr_e( WPUF_ASSET_URI . '/images/templates/blank.svg' ); ?>" alt="Blank Form">
                    <p class="wpuf-font-medium wpuf-text-gray-700"><?php echo esc_html( 'Blank Form' ); ?></p>
                    <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-between wpuf-bg-green-700/70 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md">
                        <div class="wpuf-text-2xl"><?php echo esc_html( 'Blank Form' ); ?></div>
                        <br>
                        <a href="<?php echo esc_url( $blank_form_url ); ?>" class="wpuf-btn-primary" title="<?php echo esc_attr( 'Blank Form' ); ?>">
                            <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                        </a>
                    </div>
                </div>

                <?php
                    $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';

                    foreach ( $registry as $key => $template ) {
                        ?>
                        <div class="wpuf-relative wpuf-group">
                            <?php
                                $class     = 'template-active';
                                $title     = $template->title;
                                $image     = $template->image ? $template->image : '';
                                $disabled  = '';
                                $btn_class = 'wpuf-btn-primary';

                                $url   = esc_url( add_query_arg( [
                                    'action'   => $action_name,
                                    'template' => $key,
                                    '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                                ], admin_url( 'admin.php' ) ) );

                                if ( ! $template->is_enabled() ) {
                                    $url      = '#';
                                    $class    = 'template-inactive';
                                    $title    = __( 'This integration is not installed.', 'wp-user-frontend' );
                                    $disabled = 'disabled';
                                    $btn_class = 'wpuf-btn wpuf-btn-disabled';
                                }

                                if ( $image ) {
                                    printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                } else {
                                    printf( '<h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">%s</h2>', esc_html( $title ) );
                                }
                            ?>
                            <p class="wpuf-font-medium wpuf-text-gray-700"><?php echo esc_html( $title ); ?></p>
                            <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-between wpuf-bg-green-700/70 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md">
                                <div class="wpuf-text-2xl"><?php echo esc_html( $title ); ?></div>
                                <div class="description"><?php echo esc_html( $template->get_description() ); ?></div>
                                <br>
                                <a
                                    href="<?php echo esc_url( $url ); ?>"
                                    class="<?php echo esc_attr( $btn_class ); ?>"
                                    title="<?php echo esc_attr( $template->get_title() ); ?>" <?php echo esc_attr($disabled ); ?>>
                                    <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php
                    }
                    foreach ( $pro_templates as $template ) {
                        $class = 'template-inactive is-pro-template';
                        $image = $template->get_image();
                        $title = $template->get_title();
                        ?>

                        <div class="wpuf-relative wpuf-group">
                            <?php
                                if ( $image ) {
                                    printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                }
                            ?>
                            <p class="wpuf-font-medium wpuf-text-gray-700 wpuf-flex wpuf-items-center wpuf-justify-center">
                                <?php
                                    echo esc_html( $title );
                                    if ( file_exists( $crown_icon ) ) {
                                        printf( '<span class="pro-icon-title wpuf-ml-2"> %s</span>', file_get_contents( $crown_icon ) );
                                    }
                                ?>
                            </p>

                            <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-bg-green-700/70 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md wpuf-flex wpuf-items-center wpuf-justify-center">
                                <a href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>"
                                   target="_blank"
                                   class="wpuf-btn-primary wpuf-flex wpuf-items-center wpuf-w-max"
                                   title="<?php echo esc_attr( $template->get_title() ); ?>" >
                                    <?php
                                    esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' );
                                    if ( file_exists( $crown_icon ) ) {
                                        printf( '<span class="pro-icon wpuf-ml-2"> %s</span>', file_get_contents( $crown_icon ) );
                                    }
                                    ?>
                                </a>
                            </div>
                        </li>

                        <?php
                    }
                ?>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    ( function ( $ ) {
        var popup = {
            init: function () {
                $( '.wrap' ).on( 'click', 'a.page-title-action.add-form', this.openModal );
                $( '.wpuf-form-template-modal .wpuf-close-btn' ).on( 'click', $.proxy( this.closeModal, this ) );

                $( 'body' ).on( 'keydown', $.proxy( this.onEscapeKey, this ) );
            },

            openModal: function ( e ) {
                e.preventDefault();

                $( '.wpuf-form-template-modal' ).show();
                $( '#wpbody-content .wrap' ).hide();
            },

            onEscapeKey: function ( e ) {
                if (27 === e.keyCode) {
                    this.closeModal( e );
                }
            },

            closeModal: function ( e ) {
                if (typeof e !== 'undefined') {
                    e.preventDefault();
                }

                $( '.wpuf-form-template-modal' ).hide();
                $( '#wpbody-content .wrap' ).show();
            }
        };

        $( function () {
            popup.init();
        } );

    } )( jQuery );
</script>
