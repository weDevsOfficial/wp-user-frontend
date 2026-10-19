<?php

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Frontend\Dashboard\Dashboard_Service;
use WP_Query;

/**
 * Dashboard class
 *
 * @author Tareq Hasan
 */
class Frontend_Dashboard {

    /**
     * Handle's user dashboard functionality
     *
     * Insert shortcode [wpuf_dashboard] in a page to
     * show the user dashboard
     *
     * @since 0.1
     */
    public function shortcode( $atts ) {
        if ( empty( $atts ) ) {
            $atts = [];
        }
        do_action( 'wpuf_dashboard_shortcode_init', $atts );
        $attributes = shortcode_atts(
            [
                'form_id'        => 'off',
                'post_type'      => 'post',
                'category'       => 'off',
                'featured_image' => 'default',
                'meta'           => 'off',
                'excerpt'        => 'off',
                'payment_column' => 'on',
                'post_status'    => 'draft,future,pending,publish,private',
            ], $atts
        );
        $attributes = array_merge( $attributes, $atts );

        if ( is_user_logged_in() && $this->renderer()->mounts( 'dashboard' ) && ! $this->dashboard()->classic_only() ) {
            return $this->mount_dashboard( $attributes );
        }

        ob_start();
        if ( is_user_logged_in() ) {
            $this->post_listing( $attributes );
        } else {
            $message = wpuf_get_option( 'un_auth_msg', 'wpuf_dashboard' );
            wpuf_load_template( 'unauthorized.php', [ 'message' => $message ] );
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
    public function post_listing( $attributes ) {
        global $post;
        //phpcs:ignore
        extract( $attributes );
        $pagenum = isset( $_GET['pagenum'] ) ? intval( wp_unslash( $_GET['pagenum'] ) ) : 1;
        $action  = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
        $msg     = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
        //delete post
        if ( $action === 'del' ) {
            $this->delete_post();
        }
        //show delete success message
        if ( $msg === 'deleted' ) {
            echo wp_kses_post( '<div class="success">' . __( 'Post Deleted', 'wp-user-frontend' ) . '</div>' );
        }
        $post_type = explode( ',', $post_type );
        unset( $attributes['post_type'] );

        // Parse post_status from shortcode attribute
        $post_status = $attributes['post_status'] ? $attributes['post_status'] : 'draft,future,pending,publish,private';
        $post_status = \is_array( $post_status ) ? $post_status : explode( ',', $post_status );
        $post_status = array_map( 'trim', $post_status );
        unset( $attributes['post_status'] );

        $args = [
            'author'         => get_current_user_id(),
            'post_status'    => $post_status,
            'post_type'      => $post_type,
            'posts_per_page' => wpuf_get_option( 'per_page', 'wpuf_dashboard', 10 ),
            'paged'          => $pagenum,
        ];

        if ( isset( $attributes['form_id'] ) && $attributes['form_id'] !== 'off' ) {
            $args['meta_query'] = [
                [
                    'key'     => '_wpuf_form_id',
                    'value'   => $attributes['form_id'],
                    'compare' => 'IN',
                ],
            ];
        }
        if ( isset( $attributes['category__in'] ) ) {
            $taxonomy = [ 'category' ];
            if ( class_exists( 'WooCommerce' ) ) {
                $taxonomy[] = 'product_cat';
            }
            $attributes['category__in'] = get_terms(
                [
                    'name'     => explode( ',', $attributes['category__in'] ),
                    'taxonomy' => $taxonomy,
                    'fields'   => 'ids',
                ]
            );
        }
        if ( isset( $attributes['author__in'] ) ) {
            $attributes['author__in'] = get_users(
                [
                    'nicename__in' => explode( ',', $attributes['author__in'] ),
                    'fields'       => 'ids',
                ]
            );
            unset( $args['author'] );
        }
        $args            = array_merge( $args, $attributes );
        $original_post   = $post;
        $dashboard_query = new WP_Query( apply_filters( 'wpuf_dashboard_query', $args, $attributes ) );
        $post_type_obj   = [];
        foreach ( $post_type as $key => $value ) {
            $post_type_obj[ $value ] = get_post_type_object( $value );
        }
        wpuf_load_template(
            'dashboard.php', [
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
                'payment_column'  => $payment_column,
            ]
        );
        wp_reset_postdata();
        $this->user_info();
    }

    /**
     * Show user info on dashboard
     */
    public function user_info() {
        global $userdata;
        if ( wpuf_get_option( 'show_user_bio', 'wpuf_dashboard', 'on' ) === 'on' ) {
            ?>
            <div class="wpuf-author">
                <h3><?php esc_html_e( 'Author Info', 'wp-user-frontend' ); ?></h3>
                <div class="wpuf-author-inside odd">
                    <div class="wpuf-user-image"><?php echo get_avatar( $userdata->user_email, 80 ); ?></div>
                    <div class="wpuf-author-body">
                        <?php /* translators: %s: user display name */ ?>
                        <p class="wpuf-user-name"><a href="
                        <?php
                        echo esc_url(
                            get_author_posts_url( esc_attr( $userdata->ID ) )
                        );
						?>
                            "><?php printf( '%s', esc_attr( $userdata->display_name ) ); ?></a></p>
                        <p class="wpuf-author-info"><?php echo esc_html( $userdata->description ); ?></p>
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
    public function delete_post() {
        global $userdata;
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        $pid   = isset( $_REQUEST['pid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pid'] ) ) : '';
        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf_del' ) ) {
            return;
        }
        // The list hides Delete when deleting is turned off; the link obeys the same setting.
        if ( 'yes' !== wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) ) {
            echo wp_kses_post( '<div class="error">' . __( 'Deleting posts is turned off.', 'wp-user-frontend' ) . '</div>' );

            return;
        }
        //check, if the requested user is the post author
        $maybe_delete = get_post( $pid );
        $is_author    = $maybe_delete instanceof \WP_Post && (int) $maybe_delete->post_author === (int) $userdata->ID;

        if ( $is_author || current_user_can( 'delete_others_pages' ) ) {
            wp_trash_post( $pid );
            //redirect
            $redirect = add_query_arg( [ 'msg' => 'deleted' ], get_permalink() );
            $redirect = apply_filters( 'wpuf_delete_post_redirect', $redirect );
            wp_redirect( $redirect );
            exit;
        } else {
            echo wp_kses_post(
                '<div class="error">' . __(
                    'You are not the post author. Cheating huh!', 'wp-user-frontend'
                ) . '</div>'
            );
        }
    }

    /**
     * The markup the React dashboard mounts into: the first page of the list
     * (with `?status=`, `?q=`, `?type=` filters) and the overview blocks as boot
     * data (no request on load), the signed attributes the app sends
     * back for other pages and deletes, and a skeleton of the list.
     *
     * A Delete link of the classic list (`?action=del`, bookmarked or from an
     * old page) is still handled first, with the same rules.
     *
     * @since WPUF_SINCE
     *
     * @param array $attributes Shortcode attributes
     *
     * @return string
     */
    protected function mount_dashboard( array $attributes ) {
        $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- delete_post() verifies its nonce.

        ob_start();

        if ( 'del' === $action ) {
            $this->delete_post();
        }

        // delete_post() redirects when it deletes, so anything it printed is a refusal.
        $refusal = ob_get_clean();
        $service = $this->dashboard();
        $atts    = $service->attributes( $attributes );
        $pagenum = isset( $_GET['pagenum'] ) ? absint( wp_unslash( $_GET['pagenum'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $boot    = array_merge(
            [
                'id'       => get_current_user_id(),
                'initial'  => $service->page(
                    $atts,
                    max( 1, $pagenum ),
                    [
                        'status' => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        'search' => isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        'type'   => isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    ]
                ),
                'overview' => wpuf()->platform()->get( Dashboard\Dashboard_Overview::class )->build( $atts ),
                // The classic `?msg=deleted` notice after a classic Delete link.
                'deleted'  => isset( $_GET['msg'] ) && 'deleted' === sanitize_key( wp_unslash( $_GET['msg'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                'error'    => trim( wp_strip_all_tags( $refusal ) ),
            ],
            $service->sign( $atts )
        );

        $skeleton = '<div class="wpuf-dashboard wpuf-dashboard-boot" aria-busy="true"><span class="wpuf-skeleton wpuf-skeleton-label"></span><span class="wpuf-skeleton wpuf-skeleton-card"></span></div>';

        /** This filter is documented in includes/Frontend/Frontend_Account.php */
        $align_wide = ! $service->list_only( $atts ) && (bool) apply_filters( 'wpuf_account_align_wide', function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );

        return $this->renderer()->markup( 'dashboard', $boot, $skeleton, 'wpuf-dashboard-root' . ( $align_wide ? ' alignwide' : '' ) );
    }

    /**
     * The React app renderer.
     *
     * @since WPUF_SINCE
     *
     * @return Renderer
     */
    protected function renderer() {
        return wpuf()->platform()->get( Renderer::class );
    }

    /**
     * The dashboard list data and actions.
     *
     * @since WPUF_SINCE
     *
     * @return Dashboard_Service
     */
    protected function dashboard() {
        return wpuf()->platform()->get( Dashboard_Service::class );
    }
}
