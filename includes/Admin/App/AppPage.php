<?php
/**
 * The single React admin app page
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\App;

use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Admin\Screens\Registry;

/**
 * One admin page (the WPUF top-level page) with one React mount: the React
 * screens (forms lists, builders, subscriptions, settings, AI form builder)
 * open there as hash routes, like FlyHR, so moving between them does not
 * reload the page.
 *
 * The screens' old pages stay registered (slugs, capabilities, hook suffixes):
 * their load step runs as before and then redirects to the app route
 * (Screens\Registry::load()). A screen that is not in the app yet keeps its
 * page; the app opens it with a normal page load.
 *
 * @since WPUF_SINCE
 */
class AppPage {

    /**
     * Page slug: the WPUF top-level menu page.
     */
    const SLUG = 'wp-user-frontend';

    /**
     * Script handle of the app shell.
     */
    const HANDLE = 'wpuf-admin-app';

    /**
     * Screen registry.
     *
     * @var Registry
     */
    private $screens;

    /**
     * Boot payload.
     *
     * @var BootPayload
     */
    private $boot;

    /**
     * Constructor.
     *
     * @param Registry    $screens Screen registry
     * @param BootPayload $boot    Boot payload
     */
    public function __construct( Registry $screens, BootPayload $boot ) {
        $this->screens = $screens;
        $this->boot    = $boot;
    }

    /**
     * Hooks of the app page (called from Menu::admin_menu when the app is on).
     *
     * @since WPUF_SINCE
     *
     * @param string $hook_suffix Hook suffix of the app page
     *
     * @return void
     */
    public function register( $hook_suffix ) {
        add_action( 'load-' . $hook_suffix, [ $this, 'load' ] );
        add_action( 'admin_head', [ $this, 'point_menu_rows_at_app' ] );
    }

    /**
     * Routes of the app: every screen's routes, in app or page mode.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function routes() {
        $routes = [];

        foreach ( $this->screens->all() as $screen ) {
            foreach ( $screen->app_routes() as $route ) {
                $routes[] = array_merge(
                    [
                        'notices' => false,
                        'menu'    => $screen->slug(),
                        'group'   => $screen->slug(),
                    ],
                    $route,
                    [
                        'screen' => $screen->slug(),
                        'mode'   => ! empty( $route['in_app'] ) ? 'app' : 'page',
                        'page'   => isset( $route['page'] ) ? admin_url( $route['page'] ) : admin_url( 'admin.php?page=' . $screen->slug() ),
                    ]
                );
            }
        }

        /**
         * Filters the routes of the admin app.
         *
         * @since WPUF_SINCE
         *
         * @param array[] $routes Routes.
         */
        return (array) apply_filters( 'wpuf_admin_app_routes', $routes );
    }

    /**
     * Load step of the app page: load every screen that runs in the app, the
     * way its own page loads it, then the shell.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {
        $globals  = [];
        $styles   = wp_styles();
        $baseline = $styles->queue;
        $global   = $this->resolved_styles();
        $queue    = $baseline;

        foreach ( $this->screens->all() as $screen ) {
            if ( ! $screen->in_app() || ! current_user_can( $screen->capability() ) ) {
                continue;
            }

            foreach ( $screen->app_groups() as $group => $load ) {
                $notices = $this->notice_callbacks();
                // Each group loads on the page's own style queue, so a sheet two
                // groups enqueue belongs to both (printed only on their routes;
                // each screen's Tailwind base differs).
                $styles->queue = $baseline;
                $group_globals = call_user_func( $load );

                $owned = array_values( array_diff( $this->resolved_styles(), $global ) );

                foreach ( $owned as $handle ) {
                    $this->style_owners[ $handle ][] = $group;
                }

                // The group's print order: two screens can print the same sheets
                // in a different order (the shell re-sorts them per route).
                $this->style_order[ $group ] = $owned;

                $queue = $this->merge_queue( $queue, $styles->queue );
                // A screen's load step may remove admin notices for its own page
                // (wpuf_remove_admin_notices); other routes still show them.
                $this->restore_notice_callbacks( $notices );

                // Per group: the post and registration lists use the same global names.
                $globals[ $group ] = (array) $group_globals;
            }
        }

        $styles->queue = $queue;

        wp_enqueue_script( self::HANDLE );
        wp_set_script_translations( self::HANDLE, 'wp-user-frontend', WPUF_ROOT . '/languages' );

        // Before every screen bundle (they all depend on the runtime), so a
        // screen knows it runs in the app when it loads.
        wp_enqueue_script( 'wpuf-admin-runtime' );
        $this->boot->attach_app(
            'wpuf-admin-runtime',
            [
                'routes'       => $this->routes(),
                'initialRoute' => $this->initial_route(),
                'globals'      => $globals,
                'styles'       => $this->style_owners,
                'styleOrder'   => $this->style_order,
                'pageUrl'      => admin_url( 'admin.php?page=' . self::SLUG ),
            ]
        );

        $initial              = $this->route_of_path( $this->initial_route() );
        $this->initial_screen = $initial ? $initial['screen'] : '';
        $this->initial_group  = $initial ? $initial['group'] : '';

        add_action( 'admin_print_styles', [ $this, 'hold_inline_styles' ], 1 );
        add_filter( 'style_loader_tag', [ $this, 'route_style_tag' ], 10, 4 );
        add_filter( 'admin_body_class', [ $this, 'body_class' ] );
        add_filter( 'admin_footer_text', '__return_empty_string', 99 );
        add_filter( 'update_footer', '__return_empty_string', 99 );
        add_action( 'admin_notices', [ $this, 'start_notices' ], PHP_INT_MIN );
        add_action( 'all_admin_notices', [ $this, 'end_notices' ], PHP_INT_MAX );
        add_action( 'in_admin_header', [ $this, 'mark_own_notices' ], PHP_INT_MAX );
    }

    /**
     * Load groups that brought each stylesheet handle (handle => group ids).
     *
     * @var array
     */
    private $style_owners = [];

    /**
     * Print order of each load group's stylesheets (group => handles).
     *
     * @var array
     */
    private $style_order = [];

    /**
     * Inline styles of held stylesheets (handle => CSS).
     *
     * @var array
     */
    private $held_inline = [];

    /**
     * Screen of the route the page opens with ('' when unknown).
     *
     * @var string
     */
    private $initial_screen = '';

    /**
     * Load group of the route the page opens with ('' when unknown).
     *
     * @var string
     */
    private $initial_group = '';

    /**
     * Whether a stylesheet waits for its route (owned by groups, none of them
     * the initial route's).
     *
     * @param string $handle Style handle
     *
     * @return bool
     */
    private function is_held( $handle ) {
        $base = preg_replace( '/-rtl$/', '', $handle );

        return isset( $this->style_owners[ $base ] ) && ! in_array( $this->initial_group, $this->style_owners[ $base ], true );
    }

    /**
     * Print a held stylesheet as an inert placeholder; the shell turns it into
     * a link when one of its screens' routes opens (a `disabled` link would
     * still be fetched).
     *
     * @since WPUF_SINCE
     *
     * @param string $tag    Link tag
     * @param string $handle Handle
     * @param string $href   Stylesheet URL
     * @param string $media  Media
     *
     * @return string
     */
    public function route_style_tag( $tag, $handle, $href = '', $media = 'all' ) {
        if ( ! $this->is_held( $handle ) ) {
            return $tag;
        }

        $base   = preg_replace( '/-rtl$/', '', $handle );
        $inline = isset( $this->held_inline[ $base ] ) ? '<style id="' . esc_attr( $base ) . '-inline-css">' . $this->held_inline[ $base ] . '</style>' : '';

        return sprintf(
            '<template data-wpuf-route-style data-id="%1$s-css" data-href="%2$s" data-media="%3$s" data-screens="%4$s">%5$s</template>' . "\n",
            esc_attr( $base ),
            esc_url( $href ),
            esc_attr( $media ),
            esc_attr( implode( ' ', array_unique( $this->style_owners[ $base ] ) ) ),
            $inline // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline CSS registered with wp_add_inline_style(), printed as WordPress would.
        );
    }

    /**
     * Move the inline CSS of held stylesheets into their placeholders (WordPress
     * prints inline styles outside the link tag filter).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hold_inline_styles() {
        $styles = wp_styles();

        foreach ( array_keys( $this->style_owners ) as $handle ) {
            if ( $this->is_held( $handle ) && ! empty( $styles->registered[ $handle ]->extra['after'] ) ) {
                $this->held_inline[ $handle ] = implode( "\n", (array) $styles->registered[ $handle ]->extra['after'] );
                unset( $styles->registered[ $handle ]->extra['after'] );
            }
        }
    }

    /**
     * Add a group's style queue to the page's: each new handle goes right after
     * the handle before it in the group's queue, so the sheets print in the
     * order the screen's own page printed them (same-specificity rules of
     * different sheets depend on that order).
     *
     * @param string[] $queue Page queue so far
     * @param string[] $group Group queue
     *
     * @return string[]
     */
    private function merge_queue( array $queue, array $group ) {
        $previous = null;

        foreach ( $group as $handle ) {
            if ( ! in_array( $handle, $queue, true ) ) {
                $at = null === $previous ? false : array_search( $previous, $queue, true );

                array_splice( $queue, false === $at ? count( $queue ) : $at + 1, 0, [ $handle ] );
            }

            $previous = $handle;
        }

        return $queue;
    }

    /**
     * Enqueued stylesheets with their dependencies resolved.
     *
     * @return string[]
     */
    private function resolved_styles() {
        $styles = clone wp_styles();

        $styles->to_do = [];
        $styles->done  = [];
        $styles->all_deps( $styles->queue );

        return $styles->to_do;
    }

    /**
     * Route of a route path (null when none matches).
     *
     * @param string $route Route path with optional query
     *
     * @return array|null
     */
    private function route_of_path( $route ) {
        $path = strtok( (string) $route, '?' );

        if ( ! $path ) {
            return null;
        }

        foreach ( $this->routes() as $item ) {
            $pattern = '#^' . preg_replace( '#/:[A-Za-z_]+#', '/[^/]+', $item['path'] ) . '/?$#';

            if ( preg_match( $pattern, $path ) ) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Screen slug of the route the app page opened with (an old page redirect),
     * or '' (e.g. a hash route the server cannot see).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function initial_screen() {
        return $this->initial_screen;
    }

    /**
     * Render the app mount.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        echo '<div class="wpuf-admin-app-wrap">';
        echo '<div class="wpuf-admin-notices" id="wpuf-admin-app-notices" hidden>' . $this->notices . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- other plugins' notice markup, printed as WordPress would.
        echo '<div id="wpuf-admin-app" class="wpuf-admin-app"></div>';
        echo '</div>';
    }

    /**
     * Notices printed while the app page loads; the app shows them on routes
     * that show notices.
     *
     * @var string
     */
    private $notices = '';

    /**
     * Start capturing admin notices.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function start_notices() {
        ob_start();
    }

    /**
     * Stop capturing admin notices.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function end_notices() {
        $this->notices = (string) ob_get_clean();
    }

    /**
     * Wrap the notices WPUF (free or Pro) prints in `<div class="wpuf-own-notice">`,
     * so the app's notice style applies to them only; other plugins' notices
     * keep WordPress's look. Runs on the app page only, right before
     * `admin_notices`.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function mark_own_notices() {
        global $wp_filter;

        foreach ( [ 'admin_notices', 'all_admin_notices' ] as $hook ) {
            if ( empty( $wp_filter[ $hook ] ) || ! is_object( $wp_filter[ $hook ] ) ) {
                continue;
            }

            foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
                foreach ( $callbacks as $key => $callback ) {
                    $function = $callback['function'];

                    // The capture callbacks of this page are not notices.
                    if ( is_array( $function ) && isset( $function[0] ) && $this === $function[0] ) {
                        continue;
                    }

                    if ( ! $this->is_own_callback( $function ) ) {
                        continue;
                    }

                    $wp_filter[ $hook ]->callbacks[ $priority ][ $key ]['function'] = static function () use ( $function ) {
                        echo '<div class="wpuf-own-notice">';
                        call_user_func_array( $function, func_get_args() );
                        echo '</div>';
                    };
                }
            }
        }
    }

    /**
     * Whether a hook callback is defined in the free or the Pro plugin.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $callback Hook callback.
     *
     * @return bool
     */
    private function is_own_callback( $callback ) {
        try {
            if ( is_array( $callback ) && 2 === count( $callback ) ) {
                $reflection = new \ReflectionMethod( $callback[0], $callback[1] );
            } elseif ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
                $reflection = new \ReflectionMethod( $callback );
            } elseif ( $callback instanceof \Closure || ( is_string( $callback ) && function_exists( $callback ) ) ) {
                $reflection = new \ReflectionFunction( $callback );
            } else {
                return false;
            }
        } catch ( \ReflectionException $e ) {
            return false;
        }

        $file = $reflection->getFileName();

        if ( ! $file ) {
            return false;
        }

        $file  = wp_normalize_path( $file );
        $roots = [ wp_normalize_path( WPUF_ROOT ) ];

        if ( defined( 'WPUF_PRO_ROOT' ) ) {
            $roots[] = wp_normalize_path( WPUF_PRO_ROOT );
        }

        foreach ( $roots as $root ) {
            if ( 0 === strpos( $file, trailingslashit( $root ) ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Body classes of the app page.
     *
     * @since WPUF_SINCE
     *
     * @param string $classes Admin body classes
     *
     * @return string
     */
    public function body_class( $classes ) {
        return trim( $classes . ' wpuf-admin-screen wpuf-admin-app' );
    }

    /**
     * Point the WPUF submenu rows of screens in the app at their app route, so
     * a click is a hash change, not a page load. Registration is untouched
     * (slugs, capabilities, hook suffixes); only the printed link changes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function point_menu_rows_at_app() {
        global $submenu;

        if ( empty( $submenu[ self::SLUG ] ) ) {
            return;
        }

        $links = [];

        foreach ( $this->routes() as $route ) {
            if ( 'app' === $route['mode'] && ! empty( $route['menuLink'] ) && ! isset( $links[ $route['menu'] ] ) ) {
                $links[ $route['menu'] ] = 'admin.php?page=' . self::SLUG . '#' . $route['path'];
            }
        }

        foreach ( $submenu[ self::SLUG ] as $index => $row ) {
            if ( isset( $links[ $row[2] ] ) ) {
                $submenu[ self::SLUG ][ $index ][2] = $links[ $row[2] ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            }
        }
    }

    /**
     * The route the server asked for (old page redirects), else ''.
     *
     * @return string
     */
    private function initial_route() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route selection.
        $route = isset( $_GET['wpuf_route'] ) ? sanitize_text_field( wp_unslash( $_GET['wpuf_route'] ) ) : '';

        return 0 === strpos( $route, '/' ) ? $route : '';
    }

    /**
     * The admin notice hooks' callbacks.
     *
     * @return array
     */
    private function notice_callbacks() {
        global $wp_filter;

        $saved = [];

        foreach ( [ 'admin_notices', 'all_admin_notices', 'network_admin_notices', 'user_admin_notices', 'in_admin_header' ] as $hook ) {
            $saved[ $hook ] = isset( $wp_filter[ $hook ] ) ? clone $wp_filter[ $hook ] : null;
        }

        return $saved;
    }

    /**
     * Undo what a screen's load step did to the notice hooks for its own page:
     * callbacks it removed from the notice hooks come back (other routes show
     * notices; notices it added stay), and `in_admin_header` is put back as it
     * was (e.g. `wpuf_remove_admin_notices` added for that screen's page).
     *
     * @param array $saved Callbacks from notice_callbacks()
     *
     * @return void
     */
    private function restore_notice_callbacks( array $saved ) {
        global $wp_filter;

        foreach ( $saved as $hook => $before ) {
            if ( 'in_admin_header' === $hook ) {
                if ( null === $before ) {
                    unset( $wp_filter[ $hook ] );
                } else {
                    $wp_filter[ $hook ] = $before; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                }

                continue;
            }

            if ( null === $before ) {
                continue;
            }

            foreach ( $before->callbacks as $priority => $callbacks ) {
                foreach ( $callbacks as $id => $callback ) {
                    if ( ! isset( $wp_filter[ $hook ]->callbacks[ $priority ][ $id ] ) ) {
                        add_action( $hook, $callback['function'], $priority, $callback['accepted_args'] );
                    }
                }
            }
        }
    }
}
