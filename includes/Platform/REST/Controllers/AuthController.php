<?php
/**
 * REST: log in, log out and lost password of the Login widget
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\REST\Rate_Limit;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Widgets\Login_Widget;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/auth/...`, the calls of the "WPUF Ajax Login" widget
 * (assets/js/wpuf-login-widget.js), which posted to admin-ajax before.
 *
 * Login and lost password are for visitors only and carry the widget nonce
 * (`wpuf_lost_pass`) as `wpuf_nonce`; both are rate limited per client
 * address. Logout is for logged-in users; the `wp_rest` cookie nonce
 * protects it.
 *
 * @since WPUF_SINCE
 */
class AuthController extends RestController {

    /**
     * Nonce action of the widget forms.
     *
     * @since WPUF_SINCE
     */
    const NONCE_ACTION = 'wpuf_lost_pass';

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'auth';

    /**
     * The widget (its login, logout and lost password methods).
     *
     * @var Login_Widget
     */
    private $widget;

    /**
     * Rate limiter.
     *
     * @var Rate_Limit
     */
    private $limiter;

    /**
     * @since WPUF_SINCE
     *
     * @param Login_Widget $widget  The widget
     * @param Rate_Limit   $limiter Rate limiter
     */
    public function __construct( Login_Widget $widget, Rate_Limit $limiter ) {
        $this->widget  = $widget;
        $this->limiter = $limiter;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $nonce = [
            'type'     => 'string',
            'required' => true,
        ];

        $this->route(
            '/auth/login', WP_REST_Server::CREATABLE, 'login', [ $this, 'visitor' ], [
                'log'        => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'pwd'        => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'rememberme' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'wpuf_nonce' => $nonce,
            ]
        );
        $this->route(
            '/auth/lost-password', WP_REST_Server::CREATABLE, 'lost_password', [ $this, 'visitor' ], [
                'user_login' => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'wpuf_nonce' => $nonce,
            ]
        );
        $this->route( '/auth/logout', WP_REST_Server::CREATABLE, 'logout', [ $this, 'logged_in' ] );
    }

    /**
     * Visitors only, with the widget nonce.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function visitor( WP_REST_Request $request ) {
        if ( is_user_logged_in() ) {
            return $this->error( 'logged_in', __( 'You are already logged in.', 'wp-user-frontend' ), 400 );
        }

        if ( ! wp_verify_nonce( sanitize_key( (string) $request->get_param( 'wpuf_nonce' ) ), self::NONCE_ACTION ) ) {
            return $this->error( 'bad_nonce', __( 'Permission denied', 'wp-user-frontend' ), 403 );
        }

        return true;
    }

    /**
     * Logged-in users only.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function logged_in() {
        return is_user_logged_in() ? true : $this->error( 'not_logged_in', __( 'You are not logged in.', 'wp-user-frontend' ), 401 );
    }

    /**
     * POST /auth/login
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function login( WP_REST_Request $request ) {
        if ( ! $this->limiter->allow( 'auth_login', 10 ) ) {
            return $this->too_many();
        }

        $result = $this->widget->login(
            [
                'log'        => $request->get_param( 'log' ),
                'pwd'        => $request->get_param( 'pwd' ),
                'rememberme' => $request->get_param( 'rememberme' ),
            ]
        );

        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    /**
     * POST /auth/lost-password
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function lost_password( WP_REST_Request $request ) {
        if ( ! $this->limiter->allow( 'auth_lost_password', 5 ) ) {
            return $this->too_many();
        }

        $result = $this->widget->lost_password( [ 'user_login' => $request->get_param( 'user_login' ) ] );

        return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
    }

    /**
     * POST /auth/logout
     *
     * @since WPUF_SINCE
     *
     * @return \WP_REST_Response
     */
    public function logout() {
        return rest_ensure_response( $this->widget->logout() );
    }

    /**
     * The answer once the rate limit is reached.
     *
     * @since WPUF_SINCE
     *
     * @return WP_Error
     */
    private function too_many() {
        return $this->error( 'too_many', __( 'Too many attempts. Please wait a minute and try again.', 'wp-user-frontend' ), 429 );
    }
}
