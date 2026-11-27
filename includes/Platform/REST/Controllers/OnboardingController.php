<?php
/**
 * Onboarding REST controller
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Onboarding\Wizard;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/onboarding`: the setup wizard's state, a step's save (the step
 * handlers of Platform\Onboarding\Wizard, with the values the step sent), a
 * step visit (where the admin left off) and installing one companion plugin
 * (the plugins step's progress list).
 *
 * @since WPUF_SINCE
 */
class OnboardingController extends RestController {

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'onboarding';

    /**
     * Resource name in error codes.
     *
     * @var string
     */
    protected $resource = 'onboarding';

    /**
     * The wizard.
     *
     * @var Wizard|null
     */
    private $wizard = null;

    /**
     * Register the routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        $step = [
            'type'              => 'string',
            'required'          => true,
            'sanitize_callback' => 'sanitize_key',
        ];

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/plugins/install',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'install_plugin' ],
                    'permission_callback' => [ $this, 'can_install' ],
                    'args'                => [
                        'slug' => [
                            'type'              => 'string',
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_key',
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<step>[a-z_]+)',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'save_step' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => [
                        'step'   => $step,
                        'values' => [
                            'type'    => 'object',
                            'default' => [],
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<step>[a-z_]+)/visit',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'visit_step' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => [
                        'step' => $step,
                    ],
                ],
            ]
        );
    }

    /**
     * Who may run the wizard: as its page.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_manage() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! Caps::can( Caps::MANAGE_SITE ) ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'You do not have permission to run the setup.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Who may install a companion plugin: a wizard user who may also install
     * and activate plugins.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_install() {
        $can = $this->can_manage();

        if ( true !== $can ) {
            return $can;
        }

        if ( ! $this->wizard()->plugins()->can_install() ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'You do not have permission to install plugins.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * Install and activate one recommended plugin.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error slug, installed
     */
    public function install_plugin( $request ) {
        $slug   = (string) $request->get_param( 'slug' );
        $result = $this->wizard()->plugins()->install( $slug );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response(
            [
                'slug'      => $slug,
                'installed' => true,
            ]
        );
    }

    /**
     * The wizard's state.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function get_item( $request ) {
        unset( $request );

        return rest_ensure_response( $this->wizard()->get_state() );
    }

    /**
     * Save a step, then the next step's key and the new state.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function save_step( $request ) {
        $values = $request->get_param( 'values' );
        $result = $this->wizard()->run_step( (string) $request['step'], is_array( $values ) ? $values : [] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $result['state'] = $this->wizard()->get_state();

        return rest_ensure_response( $result );
    }

    /**
     * Remember the step the admin opened.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function visit_step( $request ) {
        $this->wizard()->visit( $request['step'] );

        return rest_ensure_response( $this->wizard()->get_state() );
    }

    /**
     * The wizard service.
     *
     * @return Wizard
     */
    private function wizard() {
        if ( null === $this->wizard ) {
            $this->wizard = wpuf()->platform()->get( Wizard::class );
        }

        return $this->wizard;
    }
}
