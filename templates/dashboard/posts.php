<?php

$post_type = ! empty( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'post';

global $userdata;

$userdata = get_userdata( $userdata->ID ); //wp 3.3 fix

global $post;

$pagenum = isset( $_GET['pagenum'] ) ? intval( wp_unslash( $_GET['pagenum'] ) ) : 1;
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
// delete post
if ( $action == 'del' ) {
    $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

    if ( isset( $nonce ) && !wp_verify_nonce( $nonce, 'wpuf_del' ) ) {
        return ;
    }

    //check, if the requested user is the post author
    $pid  = isset( $_REQUEST['pid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pid'] ) ) : '';
    $type = isset( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : '';
    $maybe_delete = get_post( $pid );

    if ( ( $maybe_delete->post_author == $userdata->ID ) || current_user_can( 'delete_others_pages' ) ) {
        wp_trash_post( $pid );

        //redirect
        $redirect = add_query_arg( [ 'section' => $type, 'msg' => 'deleted'], get_permalink() );
        wp_redirect( $redirect );
        exit;
    } else {
        echo wp_kses_post( '<div class="wpuf-bg-red-50 wpuf-border wpuf-border-red-200 wpuf-text-red-800 wpuf-rounded-lg wpuf-p-4">' . __( 'You are not the post author. Cheating huh!', 'wp-user-frontend' ) . '</div>' );
    }
}
?>

<div>
    <?php
    // show delete success message
    $msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
    if ( $msg == 'deleted' ) :
    ?>
        <div id="wpuf-delete-msg" class="wpuf-bg-green-50 wpuf-border wpuf-border-green-200 wpuf-text-green-800 wpuf-rounded-lg wpuf-p-4 wpuf-flex wpuf-items-center wpuf-justify-between">
            <p class="wpuf-mb-0"><?php esc_html_e( 'Item Deleted successfully!', 'wp-user-frontend' ); ?></p>
            <button class="wpuf-text-green-600 hover:wpuf-text-green-800">
                <span class="dashicons dashicons-dismiss"></span>
            </button>
        </div>
        <script>
            (function ($) {
                var delete_div = $("#wpuf-delete-msg");
                if ((location.search.split('msg' + '=')[1] || '').split('&')[0]==='deleted'){
                    delete_div.show();
                    if (delete_div.is(':visible')){
                        setTimeout(function (e) {
                            delete_div.fadeOut();
                        },5000)
                    }

                    $("#wpuf-delete-msg button").on('click',function (e) {
                        delete_div.fadeOut();
                    })
                }
            })(jQuery);
        </script>
    <?php endif; ?>

    <?php
    $args = [
        'author'         => get_current_user_id(),
        'post_status'    => ['draft', 'future', 'pending', 'publish', 'private'],
        'post_type'      => $post_type,
        'posts_per_page' => wpuf_get_option( 'per_page', 'wpuf_dashboard', 5 ),
        'paged'          => $pagenum,
    ];

    $original_post   = $post;
    $dashboard_query = new WP_Query( apply_filters( 'wpuf_dashboard_query', $args ) );
    $post_type_obj   = get_post_type_object( $post_type );

    ?>

    <?php do_action( 'wpuf_account_posts_top', $userdata->ID, $post_type_obj ); ?>

    <?php if ( $dashboard_query->have_posts() ) { ?>

        <?php
        $featured_img      = wpuf_get_option( 'show_ft_image', 'wpuf_dashboard' );
        $featured_img_size = wpuf_get_option( 'ft_img_size', 'wpuf_dashboard' );
        $payment_column    = wpuf_get_option( 'show_payment_column', 'wpuf_dashboard', 'on' );
        $enable_payment    = wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' );
        ?>

        <div class="wpuf-bg-transparent wpuf-rounded-t-lg wpuf-mb-3">
            <h2 class="wpuf-text-gray-700 wpuf-font-bold wpuf-text-[32px] wpuf-leading-[48px] wpuf-tracking-[0.13px] wpuf-m-0">
                <?php
                printf(
                    // translators: %s is post type label
                    esc_html__( 'My %s', 'wp-user-frontend' ),
                    esc_html( $post_type_obj->labels->name )
                );
                ?>
            </h2>
        </div>

        <div class="wpuf-bg-transparent wpuf-mb-[48px]">
            <p class="wpuf-text-gray-400 wpuf-font-normal wpuf-text-[18px] wpuf-leading-[24px] wpuf-tracking-[0.13px] wpuf-m-0">
                <?php
                printf(
                    // translators: %s is post count
                    esc_html( _n( '%s item', '%s items', $dashboard_query->found_posts, 'wp-user-frontend' ) ),
                    number_format_i18n( $dashboard_query->found_posts )
                );
                ?>
            </p>
        </div>

        <div class="wpuf-bg-white wpuf-rounded-b-lg wpuf-shadow-sm wpuf-border wpuf-border-gray-200 wpuf-rounded-[6px] wpuf-max-w-[868px]">
            <table class="items-table <?php echo esc_attr( $post_type ); ?> wpuf-w-full wpuf-border-separate wpuf-border-spacing-0">
                <thead class="wpuf-bg-[#ECFDF5]">
                    <tr class="items-list-header wpuf-border-b wpuf-border-[#ECFDF5]">
                        <?php
                        if ( 'on' == $featured_img ) {
                            echo wp_kses_post( '<th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider">' . __( 'Featured Image', 'wp-user-frontend' ) . '</th>' );
                        }
                        ?>
                        <th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider"><?php esc_html_e( 'Title', 'wp-user-frontend' ); ?></th>
                        <th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider"><?php esc_html_e( 'Status', 'wp-user-frontend' ); ?></th>
                        <th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider"><?php esc_html_e( 'Link', 'wp-user-frontend' ); ?></th>

                        <?php do_action( 'wpuf_account_posts_head_col', $args ); ?>

                        <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                            <th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider"><?php esc_html_e( 'Payment', 'wp-user-frontend' ); ?></th>
                        <?php } ?>

                        <th class="wpuf-px-6 wpuf-py-4 wpuf-text-left wpuf-text-xs wpuf-font-medium wpuf-text-gray-500 wpuf-uppercase wpuf-tracking-wider"><?php esc_html_e( 'Options', 'wp-user-frontend' ); ?></th>
                    </tr>
                </thead>
                <tbody class="wpuf-bg-white">
                    <?php
                    global $post;
                    $stickies      = get_option( 'sticky_posts' );
                    while ( $dashboard_query->have_posts() ) {
                        $dashboard_query->the_post();
                        $show_link        = !in_array( $post->post_status, ['draft', 'future', 'pending'] );
                        $payment_status   = get_post_meta( $post->ID, '_wpuf_payment_status', true );
                        $is_featured      = in_array( intval( $post->ID ), $stickies, true ) ? ' - ' . esc_html__( 'Featured', 'wp-user-frontend' ) . ucfirst( $post_type ) : '';
                        $title            = wp_trim_words( get_the_title(), 5 ) . $is_featured;
                        ?>
                        <tr class="wpuf-h-[96px] hover:wpuf-bg-gray-50 wpuf-transition-colors">
                            <?php if ( 'on' == $featured_img ) { ?>
                                <td data-label="<?php esc_attr_e( 'Featured Image: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 wpuf-whitespace-nowrap" style="border-top: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB;">
                                    <?php
                                    echo $show_link ? wp_kses_post( '<a href="' . get_permalink( $post->ID ) . '">' ) : '';

                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( $featured_img_size );
                                    } else {
                                        printf( '<img src="%1$s" class="attachment-thumbnail wp-post-image" alt="%2$s" title="%2$s" />', esc_attr( apply_filters( 'wpuf_no_image', plugins_url( '../assets/images/no-image.png', __DIR__ ) ) ), esc_html( __( 'No Image', 'wp-user-frontend' ) ) );
                                    }

                                    echo $show_link ? '</a>' : '';
                                    ?>
                                </td>
                            <?php } ?>
                            <td data-label="<?php esc_attr_e( 'Title: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 <?php echo 'on' === $featured_img ? 'data-column' : '' ; ?>" style="border-bottom: 1px solid #E5E7EB;">
                                <?php if ( ! $show_link ) { ?>

                                    <?php echo esc_html( $title ); ?>

                                <?php } else { ?>

                                    <a href="<?php the_permalink(); ?>" title="<?php printf(
                                        // translators: %s is title
                                        esc_attr__( 'Permalink to %s', 'wp-user-frontend' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php echo esc_html( $title ); ?></a>

                                <?php } ?>
                            </td>
                            <td data-label="<?php esc_attr_e( 'Status: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 data-column !wpuf-text-gray-600" style="border-bottom: 1px solid #E5E7EB;">
                                <?php
                                $current_post_status = $post->post_status;
                                wpuf_show_post_status( $current_post_status );
                                ?>
                            </td>
                            <td data-label="<?php esc_attr_e( 'Link: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 data-column !wpuf-text-gray-600" style="border-bottom: 1px solid #E5E7EB;">
                                <?php
                                if ( 'publish' === $current_post_status ) {
                                    $link_text = esc_html__( 'Live', 'wp-user-frontend' );
                                    $the_link  = get_permalink();
                                } else {
                                    $link_text = esc_html__( 'Preview', 'wp-user-frontend' );
                                    $the_link  = get_preview_post_link();
                                }
                                ?>
                                <a href="<?php echo esc_url( $the_link ); ?>" target="_blank" class="wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-px-[12px] wpuf-py-[8px] !wpuf-bg-gray-900 !wpuf-text-white wpuf-text-base wpuf-rounded-sm hover:wpuf-bg-gray-950 hover:wpuf-text-gray-300 wpuf-transition-colors wpuf-no-underline">
                                    <?php echo esc_html( $link_text ); ?>
                                </a>
                            </td>

                            <?php do_action( 'wpuf_account_posts_row_col', $args, $post ); ?>

                            <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                                <td data-label="<?php esc_attr_e( 'Payment: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 data-column" style="border-bottom: 1px solid #E5E7EB;">
                                    <?php if ( empty( $payment_status ) ) { ?>
                                        <?php esc_html_e( 'Not Applicable', 'wp-user-frontend' ); ?>
                                        <?php } elseif ( $payment_status != 'completed' ) { ?>
                                            <a href="<?php echo esc_attr( trailingslashit( get_permalink( wpuf_get_option( 'payment_page',
                                                                                                                         'wpuf_payment' ) ) ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Pay Now', 'wp-user-frontend' ); ?></a>
                                            <?php } elseif ( $payment_status == 'completed' ) { ?>
                                                <?php esc_html_e( 'Completed', 'wp-user-frontend' ); ?>
                                            <?php } ?>
                                </td>
                            <?php } ?>

                            <td data-label="<?php esc_attr_e( 'Options: ', 'wp-user-frontend' ); ?>" class="wpuf-px-6 wpuf-py-4 data-column wpuf-whitespace-nowrap" style="border-bottom: 1px solid #E5E7EB;">
                                <div class="wpuf-relative wpuf-inline-block wpuf-text-left wpuf-posts-menu-wrapper">
                                    <button type="button" class="wpuf-posts-menu-button wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-p-2 wpuf-rounded-md hover:wpuf-bg-gray-100 wpuf-transition-colors wpuf-text-gray-600" aria-haspopup="true">
                                        <span class="wpuf-text-xl wpuf-font-bold wpuf-leading-none">&#8942;</span>
                                    </button>
                                    <div class="wpuf-posts-menu wpuf-hidden wpuf-absolute wpuf-right-0 wpuf-w-48 wpuf-rounded-md wpuf-shadow-lg wpuf-bg-white wpuf-z-50 wpuf-border wpuf-border-gray-200" style="display: none;">
                                        <div class="wpuf-py-1" role="menu">
                                            <?php
                                            if ( wpuf_is_post_editable( $post ) ) {
                                                $edit_page = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
                                                $url = add_query_arg( [ 'pid' => $post->ID ], get_permalink( $edit_page ) );
                                                ?>
                                                <a href="<?php echo esc_url( wp_nonce_url( $url, 'wpuf_edit' ) ); ?>" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100 wpuf-no-underline" role="menuitem">
                                                    <?php esc_html_e( 'Edit', 'wp-user-frontend' ); ?>
                                                </a>
                                                <?php
                                            }

                                            if ( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                                $del_url = add_query_arg( ['action' => 'del', 'pid' => $post->ID] );
                                                $message = __( 'Are you sure to delete?', 'wp-user-frontend' );
                                                ?>
                                                <a href="<?php echo esc_url_raw( wp_nonce_url( $del_url, 'wpuf_del' ) ); ?>" class="wpuf-block wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-red-600 hover:wpuf-bg-red-50 wpuf-no-underline" onclick="return confirm('<?php echo esc_attr( $message ); ?>');" role="menuitem">
                                                    <?php esc_html_e( 'Delete', 'wp-user-frontend' ); ?>
                                                </a>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
            }

                    wp_reset_postdata();
                    ?>

                </tbody>
            </table>
            </div>

            <script>
            (function($) {
                $(document).ready(function() {
                    // Toggle menu on button click
                    $('.wpuf-posts-menu-button').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var menu = $(this).siblings('.wpuf-posts-menu');
                        var isVisible = menu.is(':visible');
                        var $row = $(this).closest('tr');
                        var $tbody = $row.closest('tbody');
                        var isLastRow = $row.is(':last-child');

                        // Close all other menus
                        $('.wpuf-posts-menu').hide().removeClass('wpuf-block').addClass('wpuf-hidden');
                        $('.wpuf-posts-menu').css({
                            'top': '',
                            'bottom': '',
                            'margin-top': '',
                            'margin-bottom': ''
                        });

                        // Toggle current menu
                        if (!isVisible) {
                            // Check if it's the last row or second to last row
                            if (isLastRow || $row.next('tr').length === 0) {
                                // Position menu upward for last row
                                menu.css({
                                    'bottom': '100%',
                                    'top': 'auto',
                                    'margin-bottom': '0.5rem',
                                    'margin-top': '0'
                                });
                            } else {
                                // Position menu downward for other rows
                                menu.css({
                                    'top': '100%',
                                    'bottom': 'auto',
                                    'margin-top': '0.5rem',
                                    'margin-bottom': '0'
                                });
                            }
                            menu.show().removeClass('wpuf-hidden').addClass('wpuf-block');
                        }
                    });

                    // Close menu when clicking outside
                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('.wpuf-posts-menu-wrapper').length) {
                            $('.wpuf-posts-menu').hide().removeClass('wpuf-block').addClass('wpuf-hidden');
                        }
                    });
                });
            })(jQuery);
            </script>

            <?php
            // Add modern pagination if there are multiple pages
            if ( $dashboard_query->max_num_pages > 1 ) {
                $current = $pagenum;
                $total = $dashboard_query->max_num_pages;

                // Calculate visible page range - show max 7 pages
                $max_visible = 7;
                $pages = [];

                if ( $total <= $max_visible ) {
                    for ( $i = 1; $i <= $total; $i++ ) {
                        $pages[] = $i;
                    }
                } else {
                    $pages[] = 1;
                    $start = max( 2, $current - 2 );
                    $end = min( $total - 1, $current + 2 );

                    if ( $start > 2 ) {
                        $pages[] = '...';
                    }

                    for ( $i = $start; $i <= $end; $i++ ) {
                        $pages[] = $i;
                    }

                    if ( $end < $total - 1 ) {
                        $pages[] = '...';
                    }

                    $pages[] = $total;
                }
                ?>

                <div class="wpuf-pagination !wpuf-mt-10">
                    <nav class="!wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-gap-2" aria-label="<?php esc_attr_e( 'Posts Pagination', 'wp-user-frontend' ); ?>">

                        <!-- Previous Button -->
                        <?php if ( $current > 1 ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'pagenum', $current - 1 ) ); ?>"
                               class="wpuf-pagination-link !wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-700 hover:!wpuf-text-gray-900 !wpuf-no-underline">
                                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                <?php esc_html_e( 'Previous', 'wp-user-frontend' ); ?>
                            </a>
                        <?php else : ?>
                            <span class="!wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-300 !wpuf-cursor-not-allowed">
                                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                <?php esc_html_e( 'Previous', 'wp-user-frontend' ); ?>
                            </span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <div class="!wpuf-flex !wpuf-items-center !wpuf-gap-1">
                            <?php foreach ( $pages as $page ) : ?>
                                <?php if ( $page === '...' ) : ?>
                                    <span class="!wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-500">
                                        &hellip;
                                    </span>
                                <?php elseif ( $page == $current ) : ?>
                                    <span aria-current="page"
                                          class="!wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-600 !wpuf-border-t-2 !wpuf-border-gray-900">
                                        <?php echo esc_html( $page ); ?>
                                    </span>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( add_query_arg( 'pagenum', $page ) ); ?>"
                                       class="!wpuf-no-underline wpuf-pagination-link !wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-700 hover:!wpuf-text-gray-900 hover:!wpuf-border-gray-900 hover:!wpuf-border-t-2 !wpuf-transition-colors">
                                        <?php echo esc_html( $page ); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Next Button -->
                        <?php if ( $current < $total ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'pagenum', $current + 1 ) ); ?>"
                               class="wpuf-pagination-link !wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-700 hover:!wpuf-text-gray-900 !wpuf-no-underline">
                                <?php esc_html_e( 'Next', 'wp-user-frontend' ); ?>
                                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        <?php else : ?>
                            <span class="!wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-300 !wpuf-cursor-not-allowed">
                                <?php esc_html_e( 'Next', 'wp-user-frontend' ); ?>
                                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        <?php endif; ?>

                    </nav>
                </div>
            <?php } ?>

            <?php
        } else {
            printf(
                // translators: %s is label
                '<div class="wpuf-message">' . esc_attr( __( 'No %s found', 'wp-user-frontend' ) ) . '</div>',
                esc_html( $post_type_obj->label )
            );
            do_action( 'wpuf_account_posts_nopost', $userdata->ID, $post_type_obj );
        }

        wp_reset_postdata();
