<?php

/**
 * Handle's user dashboard functionality
 *
 * Insert shortcode [wpuf_dashboard] in a page to
 * show the user dashboard
 *
 * @since Version 0.1
 * @author Tareq Hasan
 * @package WP User Frontend
 */
function wpuf_user_dashboard( $atts ) {

    extract( shortcode_atts( array('post_type' => 'post'), $atts ) );

    ob_start();

    if ( is_user_logged_in() ) {
        wpuf_user_dashboard_post_list( $post_type );
    } else {
        printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( '', false ) );
    }

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

add_shortcode( 'wpuf_dashboard', 'wpuf_user_dashboard' );

/**
 * List's all the posts by the user
 *
 * @since version 0.1
 * @author Tareq Hasan
 *
 * @global object $wpdb
 * @global object $userdata
 */
function wpuf_user_dashboard_post_list( $post_type ) {
    global $wpdb, $userdata, $post;

    $userdata = get_userdata( $userdata->ID );
    //delete post
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'wpuf_del' ) )
            die( "Security check" );

        //check, if the requested user is the post author
        $maybe_delete = get_post( $_REQUEST['pid'] );

        if ( $maybe_delete->post_author == $userdata->ID ) {
            wp_delete_post( $_REQUEST['pid'] );

            //redirect
            $redirect = add_query_arg( array('msg' => 'deleted'), get_permalink() );
            wp_redirect( $redirect );
        } else {
            echo '<div class="error">' . __( 'You are not the post author. Cheeting huh!', 'wpuf' ) . '</div>';
        }
    }

    //show delete success message
    if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'deleted' ) {
        echo '<div class="success">' . __( 'Post Deleted', 'wpuf' ) . '</div>';
    }

    $pagenum = isset( $_GET['pagenum'] ) ? intval( $_GET['pagenum'] ) : 1;
    $args = array(
        'author' => get_current_user_id(),
        'post_status' => array('draft', 'future', 'pending', 'publish'),
        'post_type' => $post_type,
        'posts_per_page' => get_option( 'wpuf_list_post_range', 10 ),
        'paged' => $pagenum
    );

    $dashboard_query = new WP_Query( $args );
    //var_dump( $dashboard_query );
    ?>

    <h2 class="page-head">
        <span class="colour"><?php printf( __( "%s's Dashboard", 'wpuf' ), $userdata->user_login ); ?></span>
    </h2>

    <?php if ( get_option( 'wpuf_list_post_count' ) == 'yes' ) { ?>
        <div class="post_count"><?php printf( __( 'You have created <span>%s</span> %s', 'wpuf' ), $dashboard_query->found_posts, $post_type ); ?></div>
    <?php } ?>

    <?php do_action( 'wpuf_dashboard', $userdata->ID, $post_type ) ?>

    <table class="wpuf-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?php _e( 'Title', 'wpuf' ); ?></th>
                <th><?php _e( 'Status', 'wpuf' ); ?></th>
                <?php if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' )
                    echo '<th>' . __( 'Payment', 'wpuf' ) . '</th>'; ?>
                <th><?php _e( 'Options', 'wpuf' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($dashboard_query->have_posts()) {
                $dashboard_query->the_post();
                ?>
                <tr>
                    <td>
                        <?php if ( in_array( $post->post_status, array('draft', 'future', 'pending') ) ) { ?>

                            <?php the_title(); ?>

                        <?php } else { ?>

                            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wpuf' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                        <?php } ?>
                    </td>
                    <td>
                        <?php wpuf_show_post_status( $post->post_status ) ?>
                    </td>

                    <?php
                    if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' ) {
                        $order_id = get_post_meta( $post->ID, 'wpuf_order_id', true );
                        ?>
                        <td>
                            <?php if ( $post->post_status == 'pending' && $order_id ) { ?>
                                <a href="<?php echo get_permalink( get_option( 'wpuf_sub_pay_page' ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo $p->ID; ?>">Pay Now</a>
                            <?php } ?>
                        </td>
                    <?php } ?>

                    <td>
                        <?php if ( get_option( 'wpuf_can_edit_post' ) == 'yes' ) { ?>
                            <?php
                            $edit_page = (int) get_option( 'wpuf_edit_page_url' );
                            $url = get_permalink( $edit_page );
                            ?>
                            <a href="<?php echo wp_nonce_url( $url . '?pid=' . $post->ID, 'wpuf_edit' ); ?>"><?php _e( 'Edit', 'wpuf' ); ?></a>
                        <?php } else { ?>
                            &nbsp;
                        <?php } ?>

                        <?php if ( get_option( 'wpuf_can_del_post' ) == 'yes' ) { ?>
                            <a href="<?php echo wp_nonce_url( "?action=del&pid=" . $post->ID, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this post?');"><span style="color: red;"><?php _e( 'Delete', 'wpuf' ); ?></span></a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="wpuf-pagination">
        <?php
        $pagination = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'aag' ),
            'next_text' => __( '&raquo;', 'aag' ),
            'total' => $dashboard_query->max_num_pages,
            'current' => $pagenum
                ) );

        if ( $pagination ) {
            echo $pagination;
        }
        ?>
    </div>

    <?php if ( get_option( 'wpuf_list_user_info' ) == 'yes' ) { ?>
        <div class="wpuf-author">
            <h3><?php _e( 'Author Info', 'wpuf' ); ?></h3>
            <div class="wpuf-author-inside odd">
                <div class="wpuf-user-image"><?php echo get_avatar( $userdata->user_email, 80 ); ?></div>
                <div class="wpuf-author-body">
                    <p class="wpuf-user-name"><a href="<?php echo get_author_posts_url( $userdata->ID ); ?>"><?php printf( esc_attr__( '%s', 'wpuf' ), $userdata->display_name ); ?></a></p>
                    <p class="wpuf-author-info"><?php echo $userdata->description; ?></p>
                </div>
            </div>
        </div><!-- .author -->
    <?php } ?>

    <?php
}
