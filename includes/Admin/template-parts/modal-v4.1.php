<?php
use WeDevs\Wpuf\Free\Pro_Prompt;

$form_type = ! empty( $form_type ) ?  $form_type : 'Post Form';
?>
<div class="wpuf-form-template-modal wpuf-absolute wpuf-top-0 wpuf-left-0 wpuf-w-[calc(100%+20px)] !wpuf-h-[150vh] !wpuf--mb-[30px] wpuf-ml-[-20px] wpuf-bg-gray-100 wpuf-hidden">
    <div class="wpuf-relative wpuf-mx-auto wpuf-p-20">
        <button
            class="wpuf-absolute wpuf-right-4 wpuf-top-4 wpuf-text-gray-400 hover:wpuf-text-gray-600 focus:wpuf-outline-none wpuf-close-btn wpuf-border wpuf-border-gray-200 wpuf-rounded-3xl wpuf-p-2 hover:wpuf-border-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="wpuf-w-2/3 wpuf-max-w-2/3 wpuf-mx-auto">
            <!-- Header -->
            <div class="wpuf-mb-12">
                <h1 class="wpuf-text-3xl wpuf-text-black wpuf-m-0 wpuf-p-0">
                    <?php
                        esc_html_e( sprintf( 'Select a %s Template', $form_type ), 'wp-user-frontend' );
                    ?>
                </h1>
                <p class="wpuf-text-base wpuf-text-gray-500 wpuf-mt-3 wpuf-p-0">
                    <?php esc_html_e( 'Select from a pre-defined template or from a blank form', 'wp-user-frontend' ); ?>
                </p>
            </div>

            <!-- Templates Grid -->
            <div class="wpuf-grid wpuf-text-center">
                <div class="wpuf-grid wpuf-gap-6 wpuf-grid-cols-3 wpuf-max-w-[768px]">
                    <div class="template-box">
                        <div class="wpuf-relative wpuf-group wpuf-shadow-base">
                            <img src="<?php esc_attr_e( WPUF_ASSET_URI . '/images/templates/blank.svg' ); ?>" alt="Blank Form">
                            <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md"></div>
                            <a href="<?php echo esc_url( $blank_form_url ); ?>" class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none" title="<?php echo esc_attr( 'Blank Form' ); ?>">
                                <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                            </a>
                        </div>
                        <p class="wpuf-text-sm wpuf-text-gray-700"><?php echo esc_html( 'Blank Form' ); ?></p>
                    </div>

                    <?php
                        $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
                        $pro_badge = WPUF_ASSET_URI . '/images/pro-badge.svg';

                        foreach ( $registry as $key => $template ) {
                            ?>
                            <div class="template-box">
                                <div class="wpuf-relative wpuf-group">
                                <?php
                                    $class     = 'template-active';
                                    $title     = $template->title;
                                    $image     = $template->image ? $template->image : '';
                                    $disabled  = '';
                                    $description = ! empty( $template->description ) ? $template->description : '';
                                    $btn_class = 'wpuf-btn-primary';

                                    $url   = esc_url( add_query_arg( [
                                        'action'   => $action_name,
                                        'template' => $key,
                                        '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                                    ], admin_url( 'admin.php' ) ) );

                                    if ( ! $template->is_enabled() ) {
                                        $url      = '#';
                                        $class    = 'template-inactive';
                                        $disabled = 'disabled';
                                        $btn_class = 'wpuf-btn wpuf-btn-disabled';
                                    }

                                    if ( $image ) {
                                        printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                    } else {
                                        printf( '<h2 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-800">%s</h2>', esc_html( $title ) );
                                    }
                                ?>
                                    <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                    <?php
                                    if ( ! $template->is_enabled() ) {
                                    ?>
                                        <div class="wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-w-full wpuf-h-full wpuf-p-5">
                                            <h1 class="wpuf-text-lg wpuf-text-white"><?php esc_html_e( 'This integration is not installed.', 'wp-user-frontend' ) ?></h1>
                                            <p class="wpuf-text-white wpuf-text-sm"><?php echo esc_html( $description ); ?></p>
                                        </div>
                                    <?php
                                    } else {
                                    ?>
                                    <a
                                        href="<?php echo esc_url( $url ); ?>"
                                        class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none"
                                        title="<?php echo esc_attr( $template->get_title() ); ?>" <?php echo esc_attr($disabled ); ?>
                                    >
                                        <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                    </a>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <p class="wpuf-text-sm wpuf-text-gray-700"><?php echo esc_html( $title ); ?></p>
                            </div>
                        <?php
                        }
                        foreach ( $pro_templates as $template ) {
                            $class = 'template-inactive is-pro-template';
                            $image = $template->get_image();
                            $title = $template->get_title();
                            ?>

                            <div class="template-box">
                                <div class="wpuf-relative wpuf-group">
                                <?php
                                    if ( $image ) {
                                        printf( '<img class="wpuf-opacity-50" src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                    }
                                ?>
                                    <img class="wpuf-absolute wpuf-top-3 wpuf-right-3" src="<?php echo esc_attr( $pro_badge ); ?>" alt="">
                                    <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                    <a
                                        href="<?php echo esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>"
                                        target="_blank"
                                        class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none"
                                        title="<?php echo esc_attr( $template->get_title() ); ?>" >
                                        <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                                    </a>
                                </div>
                                <p class="wpuf-text-sm wpuf-text-gray-700"><?php echo esc_html( $title ); ?></p>
                            </div>
                            </li>

                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    ( function ( $ ) {
        var popup = {
            init: function () {
                $( 'a.new-wpuf-form' ).on( 'click', this.openModal );

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

        $( document ).ready( function () {
            popup.init();
        } );

    } )( jQuery );
</script>
