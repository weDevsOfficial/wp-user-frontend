<?php
/**
 * Admin screen registry
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * Knows the WPUF admin screens, which one is open, and captures admin notices
 * for the React screens so they print inside the screen's notice wrapper.
 *
 * @since WPUF_SINCE
 */
class Registry {

    /**
     * Screens keyed by slug.
     *
     * @var Screen[]
     */
    private $screens = [];

    /**
     * Captured notice markup, null while nothing was captured.
     *
     * @var string|null
     */
    private $notices = null;

    /**
     * Whether the capture buffer is open.
     *
     * @var bool
     */
    private $capturing = false;

    /**
     * Register the free screens.
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        foreach ( [ new PostFormsList(), new Subscriptions(), new Settings(), new Transactions(), new Tools(), new Subscribers(), new Premium(), new Help(), new Onboarding(), new Welcome() ] as $screen ) {
            $this->add( $screen );
        }
    }

    /**
     * Add a screen (Pro / add-ons on `wpuf_platform_loaded`).
     *
     * @since WPUF_SINCE
     *
     * @param Screen $screen Screen
     *
     * @return void
     */
    public function add( Screen $screen ) {
        $this->screens[ $screen->slug() ] = $screen;
    }

    /**
     * A screen by slug.
     *
     * @since WPUF_SINCE
     *
     * @param string $slug Menu slug
     *
     * @return Screen|null
     */
    public function get( $slug ) {
        return isset( $this->screens[ $slug ] ) ? $this->screens[ $slug ] : null;
    }

    /**
     * The WPUF screen being viewed, if any.
     *
     * @since WPUF_SINCE
     *
     * @return Screen|null
     */
    public function current() {
        foreach ( $this->screens as $screen ) {
            if ( $screen->is_current() ) {
                return $screen;
            }
        }

        return null;
    }

    /**
     * Run a screen's load step and start capturing notices when it asks.
     *
     * @since WPUF_SINCE
     *
     * @param string $slug Menu slug
     *
     * @return void
     */
    public function load( $slug ) {
        $screen = $this->get( $slug );

        if ( ! $screen ) {
            return;
        }

        if ( $this->redirects_to_app( $screen ) ) {
            $this->load_and_redirect( $screen );

            return;
        }

        $screen->load();

        $body_class = $screen->body_class();

        if ( $body_class ) {
            add_filter(
                'admin_body_class',
                function ( $classes ) use ( $body_class ) {
                    return trim( $classes . ' ' . sanitize_html_class( $body_class ) );
                }
            );
        }

        if ( $screen->captures_notices() ) {
            add_action( 'admin_notices', [ $this, 'start_capture' ], PHP_INT_MIN );
            add_action( 'all_admin_notices', [ $this, 'end_capture' ], PHP_INT_MAX );
            // Nothing is lost when a screen does not print the wrapper.
            add_action( 'admin_footer', [ $this, 'print_notices' ] );
        }
    }

    /**
     * Screens of the admin app, in registration order.
     *
     * @since WPUF_SINCE
     *
     * @return Screen[]
     */
    public function all() {
        return array_values( $this->screens );
    }

    /**
     * Whether an old page request of this screen goes to the admin app.
     *
     * @param Screen $screen Screen
     *
     * @return bool
     */
    private function redirects_to_app( Screen $screen ) {
        return '' !== $screen->app_route_for_request();
    }

    /**
     * Old page of a screen that runs in the admin app: its load step runs as
     * before (form actions, template and bulk handlers keep working), then the
     * request goes to the app route. A redirect made by a load listener (e.g.
     * to the new form's builder) wins.
     *
     * @param Screen $screen Screen
     *
     * @return void
     */
    private function load_and_redirect( Screen $screen ) {
        $redirect = null;
        $spy      = function ( $location ) use ( &$redirect ) {
            if ( $location ) {
                $redirect = $location;
            }

            return $location;
        };

        add_filter( 'wp_redirect', $spy, PHP_INT_MAX );
        $screen->load_before_redirect();
        remove_filter( 'wp_redirect', $spy, PHP_INT_MAX );

        if ( $redirect ) {
            // The listener already sent its Location header; stop here.
            exit;
        }

        if ( wp_safe_redirect( add_query_arg( array_map( 'rawurlencode', $screen->app_redirect_args() ), self::app_redirect_url( $screen->app_route_for_request() ) ) ) ) {
            exit;
        }
    }

    /**
     * URL of the app page that opens a route. The route travels in the query
     * (`wpuf_route`), not the fragment, so a fragment of the old URL (e.g.
     * `#wpuf_ai` on settings links) survives the redirect.
     *
     * @since WPUF_SINCE
     *
     * @param string $route Route path with optional `?query`.
     *
     * @return string
     */
    public static function app_redirect_url( $route ) {
        return add_query_arg( 'wpuf_route', rawurlencode( (string) $route ), admin_url( 'admin.php?page=' . \WeDevs\Wpuf\Admin\App\AppPage::SLUG ) );
    }

    /**
     * Render a screen.
     *
     * @since WPUF_SINCE
     *
     * @param string $slug Menu slug
     *
     * @return void
     */
    public function render( $slug ) {
        $screen = $this->get( $slug );

        if ( $screen ) {
            $screen->render();
        }
    }

    /**
     * Open the notice buffer (first `admin_notices` callback).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function start_capture() {
        if ( $this->capturing ) {
            return;
        }

        $this->capturing = true;
        ob_start();
    }

    /**
     * Close the notice buffer (last `all_admin_notices` callback).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function end_capture() {
        if ( ! $this->capturing ) {
            return;
        }

        $this->capturing = false;
        $this->notices   = (string) ob_get_clean();
    }

    /**
     * Print the captured notices once, in the screen's notice wrapper. The
     * markup is what the notice callbacks printed; WordPress prints it as is.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function print_notices() {
        if ( null === $this->notices ) {
            return;
        }

        $notices       = $this->notices;
        $this->notices = null;

        if ( '' === trim( $notices ) ) {
            return;
        }

        echo '<div class="wpuf-admin-notices">' . $notices . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- other plugins' notice markup, printed as WordPress would.
    }
}
