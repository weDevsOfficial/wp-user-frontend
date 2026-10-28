<?php
/**
 * REST: setup prerequisites and their quick actions
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Setup\Prerequisites;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/setup/*`: what a screen needs from elsewhere (the pages the
 * settings pick and their shortcodes, the subscription checklist) and the
 * "Add shortcode" quick action.
 *
 * @since WPUF_SINCE
 */
class SetupController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'setup';

    /**
     * Prerequisites service
     *
     * @var Prerequisites
     */
    private $prerequisites;

    /**
     * @param Prerequisites $prerequisites Prerequisites service
     */
    public function __construct( Prerequisites $prerequisites ) {
        $this->prerequisites = $prerequisites;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route( '/admin/setup/pages', WP_REST_Server::READABLE, 'get_pages', $this->permission( Caps::MANAGE_SETTINGS ) );

        $this->route(
            '/admin/setup/pages/(?P<id>\d+)/shortcode', WP_REST_Server::CREATABLE, 'add_shortcode', [ $this, 'can_edit_page' ], [
                'id'      => [
                    'type'     => 'integer',
                    'required' => true,
                    'minimum'  => 1,
                ],
                'setting' => [
                    'type'     => 'string',
                    'required' => true,
                    'enum'     => array_keys( Prerequisites::PAGE_SHORTCODES ),
                ],
            ]
        );

        $this->route( '/admin/setup/subscriptions', WP_REST_Server::READABLE, 'get_subscription_checks', $this->permission( Caps::MANAGE_SUBSCRIPTIONS ) );
    }

    /**
     * Who may add a shortcode to a page: who may save the settings (MANAGE_SITE) and edit that page.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_edit_page( WP_REST_Request $request ) {
        if ( ! Caps::can( Caps::MANAGE_SITE ) ) {
            return $this->error( 'forbidden', __( 'You are not allowed to change this page.', 'wp-user-frontend' ), 403 );
        }

        $page_id = (int) $request['id'];

        if ( 'page' !== get_post_type( $page_id ) ) {
            return $this->error( 'not_found', __( 'Page not found.', 'wp-user-frontend' ), 404 );
        }

        if ( ! current_user_can( 'edit_post', $page_id ) ) {
            return $this->error( 'forbidden', __( 'You are not allowed to change this page.', 'wp-user-frontend' ), 403 );
        }

        return true;
    }

    /**
     * GET /admin/setup/pages
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function get_pages() {
        return rest_ensure_response(
            [
                'requirements' => $this->prerequisites->page_requirements(),
                'map'          => $this->prerequisites->page_map(),
            ]
        );
    }

    /**
     * POST /admin/setup/pages/{id}/shortcode
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function add_shortcode( WP_REST_Request $request ) {
        $changed = $this->prerequisites->add_page_shortcode( (string) $request['setting'], (int) $request['id'] );

        return rest_ensure_response(
            [
                'changed' => $changed,
                'map'     => $this->prerequisites->page_map(),
            ]
        );
    }

    /**
     * GET /admin/setup/subscriptions
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function get_subscription_checks() {
        return rest_ensure_response( [ 'checks' => $this->prerequisites->subscription_checks() ] );
    }
}
