<?php

/**
 * Dashboard class
 *
 * @author Tareq Hasan
 * @package WP User Frontend
 */
class WPUF_Frontend_Dashboard {

    function __construct() {
        add_shortcode( 'wpuf_dashboard', array($this, 'shortcode') );
        add_action( 'wpuf_dashboard_shortcode_init', array( $this, 'remove_tribe_pre_get_posts' ) );
    }

    /**
     * Events from the events calendar plugin don't show on the frontend dashboard,
     * that's why this function is reequired.
     *
     * @since 3.1.2
     */
    public function remove_tribe_pre_get_posts() {
        if ( class_exists( 'Tribe__Events__Query' ) ) {
            remove_action( 'pre_get_posts', [ Tribe__Events__Query::class, 'pre_get_posts' ], 50 );
        }
    }

    /**
     * Handle's user dashboard functionality
     *
     * Insert shortcode [wpuf_dashboard] in a page to
     * show the user dashboard
     *
     * @since 0.1
     */
    function shortcode( $atts ) {
        do_action( 'wpuf_dashboard_shortcode_init', $atts );

        $attributes =  shortcode_atts( array( 'form_id'=>'off', 'post_type' => 'post', 'category' =>'off', 'featured_image' => 'default', 'meta' => 'off', 'excerpt' =>'off', 'payment_column' => 'on' ), $atts ) ;
        ob_start();

        if ( is_user_logged_in() ) {
            $this->post_listing( $attributes );
        } else {
            $message = wpuf_get_option( 'un_auth_msg', 'wpuf_dashboard' );
            wpuf_load_template( 'unauthorized.php', array( 'message' => $message ) );
        }

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * List's all the posts by the user
     *
     * @global object $wpdb
     * @global object $userdata
     */
    function post_listing( $attributes ) {
        global $post;
        extract ( $attributes );

        $pagenum = isset( $_GET['pagenum'] ) ? intval( $_GET['pagenum'] ) : 1;

        //delete post
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
            $this->delete_post();
        }

        //show delete success message
        if ( isset( $_GET['msg'] ) && $_GET['msg'] == 'deleted' ) {
            echo '<div class="success">' . __( 'Post Deleted', 'wp-user-frontend' ) . '</div>';
        }
        $post_type  = explode( ",", $post_type );
        $args = array(
            'author'         => get_current_user_id(),
            'post_status'    => array('draft', 'future', 'pending', 'publish', 'private'),
            'post_type'      => $post_type,
            'posts_per_page' => wpuf_get_option( 'per_page', 'wpuf_dashboard', 10 ),
            'paged'          => $pagenum,
        );

        if ( isset($attributes['form_id']) && $attributes['form_id'] != 'off' ) {
            $args['meta_query'] =  array(
                array(
                    'key'     => '_wpuf_form_id',
                    'value'   => $attributes['form_id'],
                    'compare' => 'IN',
                )
            );
        }

        $original_post   = $post;
        $dashboard_query = new WP_Query( apply_filters( 'wpuf_dashboard_query', $args, $attributes ) );
        $post_type_obj   = array();

        foreach ($post_type as $key => $value) {
           $post_type_obj[$value] = get_post_type_object( $value );
        }

        wpuf_load_template( 'dashboard.php', array(
            'post_type'       => $post_type,
            'userdata'        => wp_get_current_user(),
            'dashboard_query' => $dashboard_query,
            'post_type_obj'   => $post_type_obj,
            'post'            => $post,
            'pagenum'         => $pagenum,
            'category'        => $category,
            'featured_image'  => $featured_image,
            'form_id'         => $form_id,
            'meta'            => $meta,
            'excerpt'         => $excerpt,
            'payment_column'  => $payment_column
        ) );

        wp_reset_postdata();

        $this->user_info();
    }

    /**
     * Show user info on dashboard
     */
    function user_info() {
        global $userdata;

        if ( wpuf_get_option( 'show_user_bio', 'wpuf_dashboard', 'on' ) == 'on' ) {
            ?>
            <div class="wpuf-author">
                <h3><?php _e( 'Author Info', 'wp-user-frontend' ); ?></h3>
                <div class="wpuf-author-inside odd">
                    <div class="wpuf-user-image"><?php echo get_avatar( $userdata->user_email, 80 ); ?></div>
                    <div class="wpuf-author-body">
                        <p class="wpuf-user-name"><a href="<?php echo get_author_posts_url( $userdata->ID ); ?>"><?php printf( esc_attr__( '%s', 'wp-user-frontend' ), $userdata->display_name ); ?></a></p>
                        <p class="wpuf-author-info"><?php echo $userdata->description; ?></p>
                    </div>
                </div>
            </div><!-- .author -->
            <?php
        }
    }

    /**
     * Delete a post
     *
     * Only post author and editors has the capability to delete a post
     */
    function delete_post() {
        global $userdata;

        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'wpuf_del' ) ) {
            die( "Security check" );
        }

        //check, if the requested user is the post author
        $maybe_delete = get_post( $_REQUEST['pid'] );

        if ( ($maybe_delete->post_author == $userdata->ID) || current_user_can( 'delete_others_pages' ) ) {
            wp_trash_post( $_REQUEST['pid'] );

            //redirect
            $redirect = add_query_arg( array('msg' => 'deleted'), get_permalink() );

            $redirect = apply_filters( 'wpuf_delete_post_redirect', $redirect );

            wp_redirect( $redirect );
        } else {
            echo '<div class="error">' . __( 'You are not the post author. Cheeting huh!', 'wp-user-frontend' ) . '</div>';
        }
    }

}
