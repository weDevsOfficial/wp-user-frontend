<?php

if ( !class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' ) ) {
    function wpuf_get_upgrade_to_pro_popup_url() {
        return '#';
    }
} else {
    function wpuf_get_upgrade_to_pro_popup_url() {
        return \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url();
    }
}

$form_type = ! empty( $form_type ) ?  $form_type : 'Post Form';

// Define categories based on form type
$categories = [];
if ( strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ) {
    // Profile/Registration form categories
    $categories = [
        'ecommerce'    => [
            'label'    => __( 'E-commerce', 'wp-user-frontend' ),
            'keywords' => [ 'vendor', 'marketplace', 'product' ],
        ],
        'membership'   => [
            'label'    => __( 'Membership', 'wp-user-frontend' ),
            'keywords' => [ 'membership' ],
        ],
        // 'community'    => [
        //     'label'    => __( 'Community', 'wp-user-frontend' ),
        //     'keywords' => [],
        // ],
        // 'associations' => [
        //     'label'    => __( 'Associations', 'wp-user-frontend' ),
        //     'keywords' => [],
        // ],
    ];
} else {
    // Post form categories
    $categories = [
        'ecommerce'    => [
            'label'    => __( 'E-commerce', 'wp-user-frontend' ),
            'keywords' => [ 'vendor', 'marketplace', 'product', 'WooCommerce','edd' ],
        ],
        'post'   => [
            'label'    => __( 'Post Form', 'wp-user-frontend' ),
            'keywords' => [ 'post', 'article', 'blog' ],
        ],
    ];
}

// Helper function to determine a template's category based on its title
if ( ! function_exists( 'wpuf_get_template_category' ) ) {
    function wpuf_get_template_category( $template_title, $categories, $form_type = 'Post Form' ) {
        $template_title_lower = strtolower( $template_title );

        foreach ( $categories as $slug => $category ) {
            if ( ! empty( $category['keywords'] ) ) {
                foreach ( $category['keywords'] as $keyword ) {
                    if ( strpos( $template_title_lower, $keyword ) !== false ) {
                        return $slug;
                    }
                }
            }
        }

        // Default category based on form type
        if ( strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ) {
            return 'registration';
        } else {
            return 'post';
        }
    }
}

$category_counts = array_fill_keys( array_keys( $categories ), 0 );

if ( ! empty( $registry ) ) {
    foreach ( $registry as $template ) {
        $category = wpuf_get_template_category( $template->get_title(), $categories, $form_type );

        if ( isset( $category_counts[ $category ] ) ) {
            $category_counts[ $category ]++;
        }
    }
}
?>
<div class="wpuf-form-template-modal wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-gray-100 wpuf-hidden wpuf-z-[999999]" role="dialog" aria-modal="true" aria-labelledby="template-modal-title" aria-describedby="template-modal-description" style="background-color: #F8FAFC;">
    <button
        class="wpuf-absolute wpuf-right-8 wpuf-top-4 wpuf-text-gray-400 hover:wpuf-text-gray-600 focus:wpuf-outline-none wpuf-close-btn wpuf-border wpuf-border-gray-200 wpuf-rounded-full wpuf-p-2 hover:wpuf-border-gray-300 wpuf-bg-white wpuf-z-[1000000]">
        <svg xmlns="http://www.w3.org/2000/svg" class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    
    <div class="wpuf-relative wpuf-mx-auto wpuf-p-8 wpuf-h-full wpuf-overflow-y-auto wpuf-max-w-[1400px]">
        <div class="wpuf-max-w-full wpuf-mx-auto wpuf-relative wpuf-z-[999998]">
            <!-- Header -->
            <div class="wpuf-mb-14 wpuf-mt-10 wpuf-ml-10">
                <h1 class="wpuf-text-3xl wpuf-text-gray-900 wpuf-m-0 wpuf-p-0" id="template-modal-title">
                    <?php
                        echo esc_html( sprintf( __( 'Select a %s Template', 'wp-user-frontend' ), $form_type ) );
                    ?>
                </h1>
                <p class="wpuf-text-base wpuf-text-gray-500 wpuf-mt-3 wpuf-p-0" id="template-modal-description">
                    <?php esc_html_e( 'Select from a pre-defined template to get started quickly, or start from a blank form to build your own from scratch', 'wp-user-frontend' ); ?>
                </p>
            </div>

            <div class="wpuf-flex wpuf-gap-12">
                <!-- Left Sidebar -->
                <div class="wpuf-w-80 wpuf-flex-shrink-0">
                    <!-- Search Box -->
                    <div class="wpuf-mb-8 wpuf-mx-10">
                        <div class="wpuf-relative wpuf-group">
                            <input
                                type="text"
                                id="template-search"
                                placeholder="<?php esc_attr_e( 'Search Templates', 'wp-user-frontend' ); ?>"
                                class="wpuf-w-full !wpuf-py-[4px] !wpuf-px-[14px] wpuf-border !wpuf-border-gray-300 wpuf-rounded-lg wpuf-text-base wpuf-bg-white wpuf-transition-all wpuf-duration-200 focus:wpuf-outline-none focus:!wpuf-border-[#10b981] focus:wpuf-ring-1  placeholder:wpuf-text-gray-400 wpuf-shadow-primary"
                            />
                            <div class="wpuf-absolute wpuf-right-4 wpuf-top-1/2 wpuf--translate-y-1/2 wpuf-pointer-events-none">
                                <svg class="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="wpuf-mx-4">
                        <ul class="wpuf-space-y-2">
                            <li>
                                <button class="wpuf-template-category wpuf-w-64 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-text-left wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-transition-all wpuf-duration-200 wpuf-bg-gray-100 wpuf-text-primary wpuf-rounded-md !wpuf-ml-6 !wpuf-mr-10 wpuf-pl-4 wpuf-pr-4" data-category="all">
                                    <span><?php esc_html_e( 'All Templates', 'wp-user-frontend' ); ?></span>
                                    <span class="wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6">
                                        <?php
                                        $total_count = count($registry) + 1; // +1 for blank form
                                        if (!empty($pro_templates)) {
                                            $total_count += count($pro_templates);
                                        }
                                        echo esc_html( $total_count );
                                        ?>
                                    </span>
                                </button>
                            </li>
                            <?php foreach ( $categories as $slug => $category ) : ?>
                                <li>

                                    <button class="wpuf-template-category wpuf-w-64 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-text-left wpuf-py-2 wpuf-text-sm wpuf-transition-all wpuf-duration-200 wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100 wpuf-rounded-md !wpuf-ml-6 !wpuf-mr-10 wpuf-pl-4 wpuf-pr-4" data-category="<?php echo esc_attr( $slug ); ?>">
                                        <span><?php echo esc_html( $category['label'] ); ?></span>
                                        <span class="wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6">
                                            <?php echo esc_html( $category_counts[ $slug ] ); ?>
                                        </span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="wpuf-flex-1">
                    <!-- Templates Grid -->
                    <div class="wpuf-flex wpuf-flex-wrap wpuf-gap-4" id="templates-grid">
                        <!-- Blank Form -->
                        <?php 
                        $blank_form_category = strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ? 'registration' : 'post';
                        ?>
                        <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($blank_form_category); ?>" data-title="blank form" style="width: calc(25% - 12px);">
                            <div class="wpuf-relative wpuf-group wpuf-shadow-base">
                                <img src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/templates/blank.svg' ); ?>" alt="Blank Form">
                                <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md"></div>
                                <a href="<?php echo esc_url( $blank_form_url ); ?>" class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none" title="<?php echo esc_attr( 'Blank Form' ); ?>">
                                    <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                </a>
                            </div>
                            <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( 'Blank Form' ); ?></p>
                        </div>

                        <?php
                            $crown_icon = WPUF_ROOT . '/assets/images/crown.svg';
                            $pro_badge = WPUF_ASSET_URI . '/images/pro-badge.svg';

                            foreach ( $registry as $key => $template ) {
                                $template_title = $template->get_title();
                                $category = wpuf_get_template_category( $template_title, $categories, $form_type );
                                ?>
                                <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($category); ?>" data-title="<?php echo esc_attr(strtolower($template_title)); ?>" style="width: calc(25% - 12px);">
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
                                            echo '<div class="wpuf-aspect-square wpuf-flex wpuf-items-center wpuf-justify-center wpuf-bg-gray-50 wpuf-rounded-lg" role="img" aria-label="' . esc_attr( $title ) . '">';
                                            printf( '<h2 class="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-800 wpuf-text-center wpuf-px-2">%s</h2>', esc_html( $title ) );
                                            echo '</div>';
                                        }
                                    ?>
                                        <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                        <?php
                                        if ( ! $template->is_enabled() ) {
                                        ?>
                                            <div class="wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-w-full wpuf-h-full wpuf-p-3 wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center">
                                                <h1 class="wpuf-text-sm wpuf-text-white wpuf-mb-1 wpuf-text-center"><?php esc_html_e( 'This integration is not installed.', 'wp-user-frontend' ) ?></h1>
                                                <p class="wpuf-text-white wpuf-text-xs wpuf-text-center"><?php echo esc_html( $description ); ?></p>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                        <a
                                            href="<?php echo esc_url( $url ); ?>"
                                            class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none wpuf-transition-all"
                                            title="<?php echo esc_attr( $template->get_title() ); ?>" <?php echo esc_attr($disabled ); ?>
                                        >
                                            <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                        </a>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( $title ); ?></p>
                                </div>
                            <?php
                            }

                            // Pro Templates
                            if (!empty($pro_templates)) {
                                foreach ( $pro_templates as $template ) {
                                    $class = 'template-inactive is-pro-template';
                                    $image = $template->get_image();
                                    $title = $template->get_title();
                                    $pro_template_category = wpuf_get_template_category( $title, $categories, $form_type );
                                    ?>
                                    <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($pro_template_category); ?>" data-title="<?php echo esc_attr(strtolower($title)); ?>" style="width: calc(25% - 12px);">
                                        <div class="wpuf-relative wpuf-group">
                                        <?php
                                            if ( $image ) {
                                                printf( '<img class="wpuf-opacity-50" src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                            } else {
                                                echo '<div class="wpuf-aspect-square wpuf-flex wpuf-items-center wpuf-justify-center wpuf-bg-gray-50 wpuf-rounded-lg wpuf-opacity-50">';
                                                printf( '<h2 class="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-800 wpuf-text-center wpuf-px-2">%s</h2>', esc_html( $title ) );
                                                echo '</div>';
                                            }
                                        ?>
                                            <img class="wpuf-absolute wpuf-top-3 wpuf-right-3 wpuf-w-6 wpuf-h-6" src="<?php echo esc_attr( $pro_badge ); ?>" alt="Pro">
                                            <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                            <a
                                                href="<?php echo esc_url( class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' ) ? \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url() : '#' ); ?>"
                                                target="_blank"
                                                class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none wpuf-transition-all"
                                                title="<?php echo esc_attr( $template->get_title() ); ?>" >
                                                <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                                            </a>
                                        </div>
                                        <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( $title ); ?></p>
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                    </div>
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

                $( '#template-search' ).on( 'input', this.searchTemplates );
                $( '.wpuf-template-category' ).on( 'click', this.filterByCategory );
                $( document ).on( 'keydown', $.proxy( this.handleKeyboardShortcuts, this ) );
            },

            handleKeyboardShortcuts: function( e ) {
                if ( ( e.ctrlKey || e.metaKey ) && e.keyCode === 75 ) {
                    e.preventDefault();
                    $( '#template-search' ).focus();
                }

                if ( e.keyCode === 27 && $( '#template-search' ).is( ':focus' ) ) {
                    e.preventDefault();
                    $( '#template-search' ).val( '' ).trigger( 'input' );
                    this.searchTemplates({ target: document.getElementById('template-search') });
                }
            },

            openModal: function ( e ) {
                e.preventDefault();
                var $modal = $( '.wpuf-form-template-modal' );
                $modal.show().removeClass( 'wpuf-hidden' );
                
                $modal[0].offsetHeight;
                
                setTimeout( function() {
                    $modal.addClass( 'wpuf-modal-show' );
                }, 10 );
                
                $( 'body' ).addClass( 'wpuf-modal-open' );
                $( 'body' ).css( 'overflow', 'hidden' );
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
                
                var $modal = $( '.wpuf-form-template-modal' );
                $modal.removeClass( 'wpuf-modal-show' );
                
                setTimeout( function() {
                    $modal.hide().addClass( 'wpuf-hidden' );
                }, 300 ); // Match the CSS transition duration
                
                $( 'body' ).removeClass( 'wpuf-modal-open' );
                $( 'body' ).css( 'overflow', '' );
                $( '#wpbody-content .wrap' ).show();
            },

            searchTemplates: function ( e ) {
                var searchTerm = $( e.target ).val().toLowerCase();
                var $templates = $( '.wpuf-template-item' );

                if (searchTerm.length > 0) {
                    var $allButtons = $('.wpuf-template-category');
                    $allButtons.removeClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' ).addClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' );
                    $allButtons.find('span:last-child').attr('class', 'wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6');

                    var $allCategoryButton = $allButtons.filter('[data-category="all"]');
                    $allCategoryButton.removeClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' ).addClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' );
                    $allCategoryButton.find('span:last-child').attr('class', 'wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6');
                }

                $templates.each( function() {
                    var $template = $( this );
                    var title = $template.data( 'title' ) || '';

                    if ( title.indexOf( searchTerm ) !== -1 ) {
                        $template.show();
                    } else {
                        $template.hide();
                    }
                });
            },

            filterByCategory: function ( e ) {
                e.preventDefault();
                var $button = $( this );
                var category = $button.data( 'category' );

                // Update active state
                var $allButtons = $('.wpuf-template-category');
                
                // Reset all buttons to inactive state
                $allButtons.removeClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' ).addClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' );
                $allButtons.find('span:last-child').attr('class', 'wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6');
                
                // Set active state for the clicked button
                $button.removeClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' ).addClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' );
                $button.find('span:last-child').attr('class', 'wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6');

                // Filter templates
                var $templates = $( '.wpuf-template-item' );

                if ( category === 'all' ) {
                    $templates.show();
                } else {
                    $templates.hide();
                    $templates.filter( '[data-category="' + category + '"]' ).show();
                }

                // Clear search when filtering by category
                $( '#template-search' ).val( '' );
            },
        };

        $( document ).ready( function () {
            popup.init();
        } );

    } )( jQuery );
</script>
