<?php
/**
 * Runs a REST form submission in the context the classic admin-ajax one had
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Http;

use WP_Screen;

/**
 * The classic forms submit through admin-ajax.php, so while a post, a draft,
 * a registration or a profile is saved `is_admin()` and `wp_doing_ajax()` are
 * true, and the hooks other plugins run on that save see an admin request.
 * Dokan, for one, saves `chosen_product_cat` and adds the parent categories
 * of a product only when `is_admin()`. admin-ajax also parses no front-end
 * request, so `$wp->request` is empty (the guest verification answer sends
 * `home_url( $wp->request )`, the home page). The React forms submit over
 * REST, where both flags are false and `$wp->request` is the REST route.
 * `run()` gives the call the classic context (a current screen that is in
 * the admin, `wp_doing_ajax` true, an empty `$wp` request) and restores the
 * request afterwards. Inside admin-ajax it only runs the callable.
 *
 * @since WPUF_SINCE
 */
class Admin_Ajax_Context {

    /**
     * Run a callable as an admin-ajax submission.
     *
     * @since WPUF_SINCE
     *
     * @param callable $callback The submission
     *
     * @return mixed What the callable returns
     */
    public static function run( callable $callback ) {
        if ( is_admin() ) {
            return $callback();
        }

        // The admin-side submission hooks are built for a non-admin request only
        // (boot_submission_hooks() returns inside the admin): build them first.
        wpuf()->boot_submission_hooks();

        global $current_screen, $wp;

        if ( ! class_exists( 'WP_Screen' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
        }

        $previous = $current_screen ?? null;
        $ajax     = '__return_true';
        $request  = $wp instanceof \WP ? [ $wp->request, $wp->query_string ] : null;

        if ( $request ) {
            $wp->request      = '';
            $wp->query_string = '';
        }

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- scoped: restored in finally.
        $current_screen = WP_Screen::get( 'wpuf-form-submission' );
        add_filter( 'wp_doing_ajax', $ajax, 999 );

        try {
            return $callback();
        } finally {
            remove_filter( 'wp_doing_ajax', $ajax, 999 );

            if ( $request ) {
                $wp->request      = $request[0];
                $wp->query_string = $request[1];
            }

            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the request's own value.
            $current_screen = $previous;
        }
    }
}
