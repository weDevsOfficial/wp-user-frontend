<?php
/**
 * Every wpuf/v1 REST route refuses visitors and subscribers (QA story 31).
 *
 * Routes that are public by design are listed in PUBLIC with the reason; a new
 * route that answers a visitor or a subscriber fails here until it is gated or
 * listed.
 *
 * @package WP_User_Frontend
 */

/**
 * @coversNothing
 */
class RestRoutesPermissionTest extends WP_UnitTestCase {

    /**
     * Routes (regex as registered) public on purpose, with the reason.
     *
     * @var array
     */
    const PUBLIC = [
        '/wpuf/v1'                       => 'WordPress namespace index, lists routes only',
        '/wpuf/v1/user_directory/search' => 'frontend user directory search for visitors (free module, as on develop)',
    ];

    /**
     * Route prefixes of the frontend apps: scoped to the logged-in user (a
     * subscriber is the intended caller) or public when the form itself is
     * public. Their gates are proved route by route in FrontendFormRestTest,
     * AccountRestTest, UploadsRestTest and RegistrationRestTest (Pro's
     * registration / profile routes and the registration form's username
     * check), not by this admin-side walk.
     *
     * @var string[]
     */
    const USER_SCOPED = [
        '/wpuf/v1/account',
        '/wpuf/v1/forms/(?P<id>\\d+)',
        '/wpuf/v1/uploads',
        '/wpuf/v1/registration',
    ];

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        do_action( 'rest_api_init' );
    }

    /**
     * A request path for a route regex: numeric params get an id, others a slug.
     */
    private function path_for( $route ) {
        return preg_replace_callback(
            '/\(\?P<([a-z_]+)>([^)]*)\)/',
            function ( $m ) {
                if ( false !== strpos( $m[2], '\d' ) ) {
                    return '1';
                }

                // An alternation such as (accept|reject|delete): its first value.
                return preg_match( '/^[a-z_|-]+$/', $m[2] ) ? explode( '|', $m[2] )[0] : 'x';
            },
            $route
        );
    }

    /**
     * Values for the handler's required arguments, so the request reaches the
     * permission check (WordPress validates arguments first and answers 400).
     */
    private function params_for( $handler ) {
        $params = [];

        foreach ( (array) ( $handler['args'] ?? [] ) as $name => $arg ) {
            if ( empty( $arg['required'] ) ) {
                continue;
            }

            $params[ $name ] = $this->value_for( $arg );
        }

        return $params;
    }

    /**
     * A value that passes an argument schema (enum first value, arrays and
     * objects built from their item / property schemas).
     */
    private function value_for( $schema ) {
        if ( ! empty( $schema['enum'] ) ) {
            return reset( $schema['enum'] );
        }

        $type = is_array( $schema['type'] ?? '' ) ? reset( $schema['type'] ) : ( $schema['type'] ?? 'string' );

        switch ( $type ) {
            case 'integer':
            case 'number':
                return max( 1, (int) ( $schema['minimum'] ?? 1 ) );
            case 'boolean':
                return true;
            case 'array':
                return [ $this->value_for( $schema['items'] ?? [] ) ];
            case 'object':
                $object = [];
                foreach ( (array) ( $schema['properties'] ?? [] ) as $key => $property ) {
                    $object[ $key ] = $this->value_for( $property );
                }
                return $object ?: [ 'x' => 'x' ];
            default:
                return 'x';
        }
    }

    private function is_user_scoped( $route ) {
        foreach ( self::USER_SCOPED as $prefix ) {
            if ( 0 === strpos( $route, $prefix ) ) {
                return true;
            }
        }

        return false;
    }

    public function test_visitors_and_subscribers_are_refused_on_every_wpuf_route() {
        $routes   = rest_get_server()->get_routes();
        $checked  = 0;
        $answered = [];
        $roles    = [
            'visitor'    => 0,
            'subscriber' => self::factory()->user->create( [ 'role' => 'subscriber' ] ),
        ];

        foreach ( $routes as $route => $handlers ) {
            if ( 0 !== strpos( $route, '/wpuf/v1' ) || isset( self::PUBLIC[ $route ] ) || $this->is_user_scoped( $route ) ) {
                continue;
            }

            foreach ( $handlers as $handler ) {
                foreach ( array_keys( $handler['methods'] ) as $method ) {
                    foreach ( $roles as $who => $user_id ) {
                        wp_set_current_user( $user_id );
                        $request = new WP_REST_Request( $method, $this->path_for( $route ) );

                        foreach ( $this->params_for( $handler ) as $key => $value ) {
                            $request->set_param( $key, $value );
                        }

                        $status = rest_do_request( $request )->get_status();
                        ++$checked;

                        if ( ! in_array( $status, [ 401, 403 ], true ) ) {
                            $answered[] = "$who $method $route -> $status";
                        }
                    }
                }
            }
        }

        $this->assertGreaterThan( 40, $checked, 'the walk reached the routes' );
        $this->assertSame( [], $answered, 'routes that answered a visitor or a subscriber' );
    }
}
