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

    if ( is_user_logged_in() ) {
        wpuf_user_dashboard_post_list( $post_type );
    } else {
        printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( '', false ) );
    }
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
    global $wpdb, $userdata;

    $userdata = get_userdata( $userdata->ID );
    //delete post
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'wpuf_del' ) )
            die( "Security check" );

        //check, if the requested user is the post author
        $post = get_post( $_REQUEST['pid'] );

        if ( $post->post_author == $userdata->ID ) {
            wp_delete_post( $_REQUEST['pid'] );
            echo '<div class="success">' . __( 'Post Deleted', 'wpuf' ) . '</div>';
        } else {
            echo '<div class="error">' . __( 'You are not the post author. Cheeting huh!', 'wpuf' ) . '</div>';
        }
    }

    //get the posts count from db
    $sql = "SELECT count(ID) FROM $wpdb->posts
            WHERE post_author = $userdata->ID AND post_type = '$post_type'
            AND (post_status = 'publish' OR post_status = 'pending' OR post_status = 'draft' OR post_status = 'future') ";

    $total = $wpdb->get_var( $sql );

    //setup the pagination variables
    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit = ( get_option( 'wpuf_list_post_range' ) ) ? get_option( 'wpuf_list_post_range' ) : 10;
    $offset = ( $pagenum - 1 ) * $limit;
    $num_of_pages = ( $total > 0 ) ? ceil( $total / $limit ) : 0;

    $page_links = paginate_links( array(
        'base' => add_query_arg( 'pagenum', '%#%' ),
        'format' => '',
        'prev_text' => __( '&laquo;', 'aag' ),
        'next_text' => __( '&raquo;', 'aag' ),
        'total' => $num_of_pages,
        'current' => $pagenum
            ) );

    //get the posts
    $sql = "SELECT ID, post_title, post_name, post_status, post_date FROM $wpdb->posts
            WHERE post_author = $userdata->ID AND post_type = '$post_type'
            AND (post_status = 'publish' OR post_status = 'pending' OR post_status = 'draft' OR post_status = 'future') ORDER BY post_date DESC LIMIT $offset, $limit";

    $posts = $wpdb->get_results( $sql );
    ?>

    <h2 class="page-head">
        <span class="colour"><?php printf( __( "%s's Dashboard", 'wpuf' ), $userdata->user_login ); ?></span>
    </h2>
    <?php if ( $posts ) { ?>

        <?php if ( get_option( 'wpuf_list_post_count' ) == 'yes' ) { ?>
            <div class="post_count"><?php _e( 'You have created', 'wpuf' ); ?> <?php echo "<span>$total</span> $post_type"; ?></div>
        <?php } ?>

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
                <?php wp_reset_query() ?>
                <?php foreach ($posts as $p) { ?>
                    <tr>
                        <td>
                            <?php if ( $p->post_status == 'pending' || $p->post_status == 'draft' || $p->post_status == 'future' ) { ?>

                                <?php echo wptexturize( $p->post_title ); ?>

                            <?php } else { ?>

                                <a href="<?php echo get_permalink( $p->ID ) ?>"><?php echo wptexturize( $p->post_title ); ?></a>

                            <?php } ?>
                        </td>
                        <td>
                            <?php wpuf_show_post_status( $p->post_status ) ?>
                        </td>

                        <?php
                        if ( get_option( 'wpuf_sub_charge_posting' ) == 'yes' ) {
                            $order_id = get_post_meta( $p->ID, 'wpuf_order_id', true );
                            ?>
                            <td>
                                <?php if ( $p->post_status == 'pending' && $order_id ) { ?>
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
                                <a href="<?php echo wp_nonce_url( $url . '?pid=' . $p->ID, 'wpuf_edit' ); ?>"><?php _e( 'Edit', 'wpuf' ); ?></a>
                            <?php } else { ?>
                                &nbsp;
                            <?php } ?>

                            <?php if ( get_option( 'wpuf_can_del_post' ) == 'yes' ) { ?>
                                <a href="<?php echo wp_nonce_url( "?action=del&pid=" . $p->ID, 'wpuf_del' ) ?>" onclick="return confirm('Are you sure to delete this post?');"><span style="color: red;"><?php _e( 'Delete', 'wpuf' ); ?></span></a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="wpuf-pagination">
            <?php if ( $page_links )
                echo $page_links; ?>
        </div>

    <?php } else { ?>

        <h3><?php _e( 'Currently you have no post', 'wpuf' ); ?></h3>

    <?php } ?>


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
