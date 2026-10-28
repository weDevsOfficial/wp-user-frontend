<?php
/**
 * REST: turn a free module on or off
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/free-modules/{module}`: the switches of the free Modules
 * page (Pro off, assets/js/admin/wpuf-module.js), which posted
 * `wpuf_toggle_free_module` to admin-ajax before. For `manage_options`.
 *
 * @since WPUF_SINCE
 */
class FreeModulesController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'free_modules';

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route(
            '/admin/free-modules/(?P<module>[a-z0-9_-]+)', WP_REST_Server::CREATABLE, 'toggle', [ $this, 'can_manage' ], [
                'status' => [
                    'type'     => 'string',
                    'enum'     => [ 'active', 'inactive' ],
                    'required' => true,
                ],
            ]
        );
    }

    /**
     * Who may switch modules.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_manage() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return $this->error( 'forbidden', __( 'Permission denied', 'wp-user-frontend' ), 403 );
        }

        return true;
    }

    /**
     * POST /admin/free-modules/{module}
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function toggle( WP_REST_Request $request ) {
        $module_id = (string) $request['module'];

        if ( ! isset( wpuf_free_get_modules()[ $module_id ] ) ) {
            return $this->error( 'not_found', __( 'Module not found', 'wp-user-frontend' ), 404 );
        }

        $active = 'active' === $request['status'];
        $result = $active ? wpuf_free_activate_module( $module_id ) : wpuf_free_deactivate_module( $module_id );

        if ( is_wp_error( $result ) ) {
            return $this->error( 'failed', $result->get_error_message(), 400 );
        }

        return rest_ensure_response(
            [
                'active'  => $active,
                'message' => $active
                    ? __( 'Module activated successfully', 'wp-user-frontend' )
                    : __( 'Module deactivated successfully', 'wp-user-frontend' ),
            ]
        );
    }
}
