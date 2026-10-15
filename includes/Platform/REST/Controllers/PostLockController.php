<?php
/**
 * REST: clear the frontend edit lock of a post
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/posts/{id}/lock`: the "Clear Lock" link of the post edit
 * screen (assets/js/wpuf-admin.js), which posted `wpuf_clear_schedule_lock`
 * to admin-ajax before. For who may edit others' posts and this post.
 *
 * @since WPUF_SINCE
 */
class PostLockController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'post_lock';

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route(
            '/admin/posts/(?P<id>\d+)/lock', WP_REST_Server::DELETABLE, 'delete_lock', [ $this, 'can_clear' ], [
                'id' => [
                    'type'     => 'integer',
                    'required' => true,
                    'minimum'  => 1,
                ],
            ]
        );
    }

    /**
     * Who may clear the lock.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_clear( WP_REST_Request $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        $post_id = (int) $request['id'];

        if ( ! get_post( $post_id ) ) {
            return $this->error( 'not_found', __( 'Post not found.', 'wp-user-frontend' ), 404 );
        }

        if ( ! current_user_can( 'edit_others_posts' ) || ! current_user_can( 'edit_post', $post_id ) ) {
            return $this->error( 'forbidden', __( 'You are not allowed to clear this lock.', 'wp-user-frontend' ), 403 );
        }

        return true;
    }

    /**
     * DELETE /admin/posts/{id}/lock
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function delete_lock( WP_REST_Request $request ) {
        $post_id = (int) $request['id'];

        Stores::submissions()->set_lock_time( $post_id, '' );
        Stores::submissions()->set_lock( $post_id, 'no' );

        return rest_ensure_response( [ 'cleared' => true ] );
    }
}
