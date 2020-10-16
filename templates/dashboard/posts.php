<?php

$post_type = 'post';
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
    $pid = isset( $_REQUEST['pid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pid'] ) ) : '';
    $maybe_delete = get_post( $pid );

    if ( ( $maybe_delete->post_author == $userdata->ID ) || current_user_can( 'delete_others_pages' ) ) {
        wp_delete_post( $pid );

        //redirect
        $redirect = add_query_arg( [ 'section' => 'posts', 'msg' => 'deleted'], get_permalink() );
        wp_redirect( $redirect );
        exit;
    } else {
        echo wp_kses_post( '<div class="error">' . __( 'You are not the post author. Cheeting huh!', 'wp-user-frontend' ) . '</div>' );
    }
}

// show delete success message
$msg = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
if ( $msg == 'deleted' ) {
    echo wp_kses_post( '<div class="success">' . __( 'Post Deleted', 'wp-user-frontend' ) . '</div>' );
}

$args = [
    'author'         => get_current_user_id(),
    'post_status'    => ['draft', 'future', 'pending', 'publish', 'private'],
    'post_type'      => $post_type,
    'posts_per_page' => wpuf_get_option( 'per_page', 'wpuf_dashboard', 10 ),
    'paged'          => $pagenum,
];

$original_post   = $post;
$dashboard_query = new WP_Query( apply_filters( 'wpuf_dashboard_query', $args ) );
$post_type_obj   = get_post_type_object( $post_type );

?>

<?php if ( wpuf_get_option( 'show_post_count', 'wpuf_dashboard', 'on' ) == 'on' ) { ?>
    <div class="post_count"><?php printf( wp_kses_post( __( 'You have created <span>%d</span> %s', 'wp-user-frontend'  ) ), esc_attr( $dashboard_query->found_posts ), esc_attr( $post_type_obj->label ) ); ?></div>
<?php } ?>

<?php do_action( 'wpuf_account_posts_top', $userdata->ID, $post_type_obj ); ?>

<?php if ( $dashboard_query->have_posts() ) { ?>

    <?php
    $featured_img      = wpuf_get_option( 'show_ft_image', 'wpuf_dashboard' );
    $featured_img_size = wpuf_get_option( 'ft_img_size', 'wpuf_dashboard' );
    $payment_column    = wpuf_get_option( 'show_payment_column', 'wpuf_dashboard', 'on' );
    $enable_payment    = wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' );
    $current_user      = wpuf_get_user();
    $user_subscription = new WPUF_User_Subscription( $current_user );
    $user_sub          = $user_subscription->current_pack();
    $sub_id            = $current_user->subscription()->current_pack_id();

    if ( $sub_id ) {
        $subs_expired = $user_subscription->expired();
    } else {
        $subs_expired = false;
    }
    ?>
    <div class="items-table-container">
        <table class="items-table <?php echo esc_attr( $post_type ); ?>" cellpadding="0" cellspacing="0">
            <thead>
                <tr class="items-list-header">
                    <?php
                    if ( 'on' == $featured_img ) {
                        echo wp_kses_post( '<th>' . __( 'Featured Image', 'wp-user-frontend' ) . '</th>' );
                    }
                    ?>
                    <th><?php esc_html_e( 'Title', 'wp-user-frontend' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'wp-user-frontend' ); ?></th>

                    <?php do_action( 'wpuf_account_posts_head_col', $args ); ?>

                    <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                        <th><?php esc_html_e( 'Payment', 'wp-user-frontend' ); ?></th>
                    <?php } ?>

                    <th><?php esc_html_e( 'Options', 'wp-user-frontend' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $post;

                while ( $dashboard_query->have_posts() ) {
                    $dashboard_query->the_post();
                    $show_link        = !in_array( $post->post_status, ['draft', 'future', 'pending'] );
                    $payment_status   = get_post_meta( $post->ID, '_wpuf_payment_status', true ); ?>
                    <tr>
                        <?php if ( 'on' == $featured_img ) { ?>
                            <td>
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
                        <td>
                            <?php if ( !$show_link ) { ?>

                                <?php the_title(); ?>

                            <?php } else { ?>

                                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wp-user-frontend' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                            <?php } ?>
                        </td>
                        <td>
                            <?php wpuf_show_post_status( $post->post_status ); ?>
                        </td>

                        <?php do_action( 'wpuf_account_posts_row_col', $args, $post ); ?>

                        <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                            <td>
                                <?php if ( empty( $payment_status ) ) { ?>
                                    <?php esc_html_e( 'Not Applicable', 'wp-user-frontend' ); ?>
                                    <?php } elseif ( $payment_status != 'completed' ) { ?>
                                        <a href="<?php echo esc_attr( trailingslashit( get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) ) ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Pay Now', 'wp-user-frontend' ); ?></a>
                                        <?php } elseif ( $payment_status == 'completed' ) { ?>
                                            <?php esc_html_e( 'Completed', 'wp-user-frontend' ); ?>
                                        <?php } ?>
                                    </td>
                                <?php } ?>

                                <td>
                                    <?php
                                    if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                        $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
                                        $edit_page            = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
                                        $url                  = add_query_arg( ['pid' => $post->ID], get_permalink( $edit_page ) );

                                        $show_edit = true;

                                        if ( $post->post_status == 'pending' && $disable_pending_edit == 'on' ) {
                                            $show_edit  = false;
                                        }

                                        if ( ( $post->post_status == 'draft' || $post->post_status == 'pending' ) && ( !empty( $payment_status ) && $payment_status != 'completed' ) ) {
                                            $show_edit  = false;
                                        }

                                        if ( $subs_expired ) {
                                            $show_edit  = false;
                                        }

                                        if ( $show_edit ) {
                                            ?>
                                            <a class="wpuf-posts-options wpuf-posts-edit" href="<?php echo esc_url( wp_nonce_url( $url, 'wpuf_edit' ) ); ?>"><?php esc_html_e( 'Edit', 'wp-user-frontend' ); ?></a>
                                            <?php
                                        }
                                    } ?>

                                    <?php
                                    if ( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                        $del_url = add_query_arg( ['action' => 'del', 'pid' => $post->ID] );
                                        $message = __( 'Are you sure to delete?', 'wp-user-frontend' ); ?>
                                        <a class="wpuf-posts-options wpuf-posts-delete" style="color: red;" href="<?php echo esc_url_raw( wp_nonce_url( $del_url, 'wpuf_del' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $message ); ?>');"><?php esc_html_e( 'Delete', 'wp-user-frontend' ); ?></a>
                                    <?php
                                    } ?>
                                </td>
                            </tr>
                            <?php
                }

                        wp_reset_postdata();
                        ?>

                    </tbody>
                </table>
            </div>

            <div class="wpuf-pagination">
                <?php
                $pagination = paginate_links( [
                    'base'      => add_query_arg( 'pagenum', '%#%' ),
                    'format'    => '',
                    'prev_text' => __( '&laquo;', 'wp-user-frontend' ),
                    'next_text' => __( '&raquo;', 'wp-user-frontend' ),
                    'total'     => $dashboard_query->max_num_pages,
                    'current'   => $pagenum,
                    'add_args'  => false,
                ] );

                if ( $pagination ) {
                    echo wp_kses( $pagination, [
                        'span' => [
                            'aria-current' => [],
                            'class' => [],
                        ],
                        'a' => [
                            'href' => [],
                            'class' => [],
                        ]
                    ] );
                }
                ?>
            </div>

            <?php
        } else {
            printf( '<div class="wpuf-message">' . esc_attr( __( 'No %s found', 'wp-user-frontend' ) ) . '</div>', esc_html( $post_type_obj->label ) );
            do_action( 'wpuf_account_posts_nopost', $userdata->ID, $post_type_obj );
        }

        wp_reset_postdata();
