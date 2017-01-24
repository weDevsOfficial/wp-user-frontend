<h2 class="page-head">
    <span class="colour"><?php printf( __( "%s's Dashboard", 'wpuf' ), $userdata->user_login ); ?></span>
</h2>

<?php if ( wpuf_get_option( 'show_post_count', 'wpuf_dashboard', 'on' ) == 'on' ) { ?>
    <div class="post_count"><?php printf( __( 'You have created <span>%d</span> %s', 'wpuf' ), $dashboard_query->found_posts, $post_type_obj->label ); ?></div>
<?php } ?>

<?php do_action( 'wpuf_dashboard_top', $userdata->ID, $post_type_obj ) ?>

<?php if ( $dashboard_query->have_posts() ) { ?>

    <?php
    $featured_img = wpuf_get_option( 'show_ft_image', 'wpuf_dashboard' );
    $featured_img_size = wpuf_get_option( 'ft_img_size', 'wpuf_dashboard' );
    $charging_enabled = wpuf_get_option( 'charge_posting', 'wpuf_payment' );
    ?>
    <table class="wpuf-table <?php echo $post_type; ?>" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <?php
                if ( 'on' == $featured_img ) {
                    echo '<th>' . __( 'Featured Image', 'wpuf' ) . '</th>';
                }
                ?>
                <th><?php _e( 'Title', 'wpuf' ); ?></th>
                <th><?php _e( 'Status', 'wpuf' ); ?></th>

                <?php do_action( 'wpuf_dashboard_head_col', $args ) ?>

                <?php
                if ( 'yes' == $charging_enabled ) {
                    echo '<th>' . __( 'Payment', 'wpuf' ) . '</th>';
                }
                ?>
                <th><?php _e( 'Options', 'wpuf' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $post;

            while ( $dashboard_query->have_posts() ) {
                $dashboard_query->the_post();
                $show_link = !in_array( $post->post_status, array('draft', 'future', 'pending') );
                ?>
                <tr>
                    <?php if ( 'on' == $featured_img ) { ?>
                        <td>
                            <?php
                            echo $show_link ? '<a href="' . get_permalink( $post->ID ) . '">' : '';

                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail( $featured_img_size );
                            } else {
                                printf( '<img src="%1$s" class="attachment-thumbnail wp-post-image" alt="%2$s" title="%2$s" />', apply_filters( 'wpuf_no_image', plugins_url( '/assets/images/no-image.png', dirname( __FILE__ ) ) ), __( 'No Image', 'wpuf' ) );
                            }

                            echo $show_link ? '</a>' : '';
                            ?>
                        </td>
                    <?php } ?>
                    <td>
                        <?php if ( !$show_link ) { ?>

                            <?php the_title(); ?>

                        <?php } else { ?>

                            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpuf' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                        <?php } ?>
                    </td>
                    <td>
                        <?php wpuf_show_post_status( $post->post_status ) ?>
                    </td>

                    <?php do_action( 'wpuf_dashboard_row_col', $args, $post ) ?>

                    <?php
                    if ( $charging_enabled == 'yes' ) {
                        $order_id = get_post_meta( $post->ID, '_wpuf_order_id', true );
                        ?>
                        <td>
                            <?php if ( $post->post_status == 'pending' && $order_id ) { ?>
                                <a href="<?php echo trailingslashit( get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo $post->ID; ?>"><?php _e( 'Pay Now', 'wpuf' ); ?></a>
                            <?php } ?>
                        </td>
                    <?php } ?>

                    <td>
                        <?php
                        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                            $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
                            $edit_page = (int) wpuf_get_option( 'edit_page_id', 'wpuf_general' );
                            $url = add_query_arg( array('pid' => $post->ID), get_permalink( $edit_page ) );

                            if ( $post->post_status == 'pending' && $disable_pending_edit == 'on' ) {
                                // don't show the edit link
                            } else {
                                ?>
                                <a href="<?php echo wp_nonce_url( $url, 'wpuf_edit' ); ?>"><?php _e( 'Edit', 'wpuf' ); ?></a>
                                <?php
                            }
                        }
                        ?>

                        <?php
                        if ( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                            $del_url = add_query_arg( array('action' => 'del', 'pid' => $post->ID) );
                            ?>
                            <a href="<?php echo wp_nonce_url( $del_url, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete?');"><span style="color: red;"><?php _e( 'Delete', 'wpuf' ); ?></span></a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }

            wp_reset_postdata();
            ?>

        </tbody>
    </table>

    <div class="wpuf-pagination">
        <?php
        $pagination = paginate_links( array(
            'base'      => add_query_arg( 'pagenum', '%#%' ),
            'format'    => '',
            'prev_text' => __( '&laquo;', 'wpuf' ),
            'next_text' => __( '&raquo;', 'wpuf' ),
            'total'     => $dashboard_query->max_num_pages,
            'current'   => $pagenum,
            'add_args'  => false
        ) );

        if ( $pagination ) {
            echo $pagination;
        }
        ?>
    </div>

    <?php
} else {
    printf( '<div class="wpuf-message">' . __( 'No %s found', 'wpuf' ) . '</div>', $post_type_obj->label );
    do_action( 'wpuf_dashboard_nopost', $userdata->ID, $post_type_obj );
}

do_action( 'wpuf_dashboard_bottom', $userdata->ID, $post_type_obj );