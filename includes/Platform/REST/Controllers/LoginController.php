<?php
/**
 * REST: the `[wpuf-login]` screens (log in, lost password, reset password)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Free\Simple_Login;
use WeDevs\Wpuf\Platform\REST\Rate_Limit;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/login...`, what the React login app (src/frontend/login) sends:
 * the fields of its form, the same names the classic templates post. They
 * are put in `$_POST` and handled by Simple_Login's own methods, so every
 * check and hook of the classic form runs (form nonce, captchas,
 * `wpuf_process_login_errors`, `authenticate`, `wpuf_login_redirect`,
 * `validate_password_reset`, `wpuf_customer_reset_password`, ...).
 *
 * Visitors only, rate limited per client address. The form nonces
 * (`wpuf_login_action`, `wpuf_lost_pass`, `wpuf_reset_pass`) are checked
 * by Simple_Login as for the classic post.
 *
 * @since WPUF_SINCE
 */
class LoginController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'login';

    /**
     * The login handler.
     *
     * @var Simple_Login
     */
    private $login;

    /**
     * Rate limiter.
     *
     * @var Rate_Limit
     */
    private $limiter;

    /**
     * @since WPUF_SINCE
     *
     * @param Simple_Login $login   The login handler
     * @param Rate_Limit   $limiter Rate limiter
     */
    public function __construct( Simple_Login $login, Rate_Limit $limiter ) {
        $this->login   = $login;
        $this->limiter = $limiter;
    }

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route( '/login', WP_REST_Server::CREATABLE, 'sign_in', [ $this, 'visitor' ] );
        $this->route( '/login/lost-password', WP_REST_Server::CREATABLE, 'lost_password', [ $this, 'visitor' ] );
        $this->route( '/login/reset-password', WP_REST_Server::CREATABLE, 'reset_password', [ $this, 'visitor' ] );
    }

    /**
     * Visitors only.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function visitor() {
        return is_user_logged_in() ? $this->error( 'logged_in', __( 'You are already logged in.', 'wp-user-frontend' ), 400 ) : true;
    }

    /**
     * POST /login: `{ redirect }` once logged in.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function sign_in( WP_REST_Request $request ) {
        if ( ! $this->limiter->allow( 'login_form', 10 ) ) {
            return $this->too_many();
        }

        $this->post( $request );

        $redirect = $this->login->sign_in();

        return null === $redirect ? $this->failed() : rest_ensure_response( [ 'redirect' => esc_url_raw( wp_validate_redirect( $redirect, home_url() ) ) ] );
    }

    /**
     * POST /login/lost-password: `{ message }` once the request is done.
     * The answer is the same whether an account matches or not.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function lost_password( WP_REST_Request $request ) {
        if ( ! $this->limiter->allow( 'login_lost_password', 5 ) ) {
            return $this->too_many();
        }

        $this->post( $request );

        if ( ! $this->login->request_reset() ) {
            return $this->failed();
        }

        return rest_ensure_response(
            [
                'message' => __( 'If an account matches what you entered, an e-mail with a link to reset your password is on its way. Check your inbox.', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * POST /login/reset-password: `{ redirect }` (the login form with the
     * "password reset" notice) once the new password is set.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function reset_password( WP_REST_Request $request ) {
        if ( ! $this->limiter->allow( 'login_reset_password', 10 ) ) {
            return $this->too_many();
        }

        $this->post( $request );

        $redirect = $this->login->apply_reset();

        return null === $redirect ? $this->failed() : rest_ensure_response( [ 'redirect' => esc_url_raw( $redirect ) ] );
    }

    /**
     * Put the sent form fields in `$_POST` / `$_REQUEST`, slashed like PHP's
     * own request data (Simple_Login unslashes them). Passwords are passed
     * as sent: no cleaning that could change them.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return void
     */
    private function post( WP_REST_Request $request ) {
        $this->login->clear_errors();

        $fields = array_merge( (array) $request->get_body_params(), (array) $request->get_json_params() );

        foreach ( $fields as $name => $value ) {
            if ( ! is_string( $name ) || ( ! is_scalar( $value ) && ! is_array( $value ) ) ) {
                continue;
            }

            $_POST[ $name ]    = wp_slash( $value );
            $_REQUEST[ $name ] = wp_slash( $value );
        }
    }

    /**
     * The errors Simple_Login collected, as one error: the first message,
     * every message in `data.errors`.
     *
     * @since WPUF_SINCE
     *
     * @return WP_Error
     */
    private function failed() {
        $errors = array_map( 'wp_kses_post', $this->login->get_errors() );

        if ( ! $errors ) {
            $errors = [ __( 'Something went wrong. Please try again.', 'wp-user-frontend' ) ];
        }

        return new WP_Error(
            'wpuf_login_failed',
            $errors[0],
            [
                'status' => 400,
                'errors' => $errors,
            ]
        );
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
