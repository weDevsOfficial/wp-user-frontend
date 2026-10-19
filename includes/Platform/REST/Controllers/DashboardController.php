<?php
/**
 * REST: the `[wpuf_dashboard]` posts list of the current user
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Frontend\Dashboard\Dashboard_Service;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/dashboard/posts`: pages of the list and deletes from it. Both
 * take the shortcode attributes only with the signature the page was
 * rendered with for this user, so a request can not widen the query the
 * site owner wrote. Visitors get 401.
 *
 * @since WPUF_SINCE
 */
class DashboardController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'dashboard';

    /**
     * The dashboard list data and actions.
     *
     * @var Dashboard_Service
     */
    private $dashboard;

    /**
     * @since WPUF_SINCE
     *
     * @param Dashboard_Service $dashboard Dashboard service
     */
    public function __construct( Dashboard_Service $dashboard ) {
        $this->dashboard = $dashboard;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $signed = [
            'atts' => [
                'type'     => 'string',
                'required' => true,
            ],
            'sig'  => [
                'type'     => 'string',
                'required' => true,
            ],
        ];

        $this->route(
            '/dashboard/posts', WP_REST_Server::READABLE, 'get_posts', [ $this, 'logged_in' ], array_merge(
                $signed, [
                    'page' => [
                        'type'    => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                ]
            )
        );
        $this->route(
            '/dashboard/posts/(?P<id>\d+)', WP_REST_Server::DELETABLE, 'delete_post', [ $this, 'logged_in' ], array_merge(
                $signed, [
                    'id' => [
                        'type'     => 'integer',
                        'required' => true,
                        'minimum'  => 1,
                    ],
                ]
            )
        );
    }

    /**
     * Logged-in users only.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function logged_in() {
        if ( is_user_logged_in() ) {
            return true;
        }

        return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
    }

    /**
     * GET /dashboard/posts: one page of the list.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function get_posts( WP_REST_Request $request ) {
        $atts = $this->dashboard->verify( $request['atts'], $request['sig'] );

        if ( is_wp_error( $atts ) ) {
            return $atts;
        }

        return rest_ensure_response( $this->dashboard->page( $atts, (int) $request['page'] ) );
    }

    /**
     * DELETE /dashboard/posts/{id}: trash a post of the list.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function delete_post( WP_REST_Request $request ) {
        $atts = $this->dashboard->verify( $request['atts'], $request['sig'] );

        if ( is_wp_error( $atts ) ) {
            return $atts;
        }

        $result = $this->dashboard->delete_post( (int) $request['id'], $atts );

        return is_wp_error( $result ) ? $result : rest_ensure_response( [ 'deleted' => true ] );
    }
}
