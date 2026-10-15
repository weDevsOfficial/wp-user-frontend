<?php
/**
 * Admin screen base
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * One WPUF admin screen on its existing menu slug (Dokan `Pageable` shape):
 * the menu keeps registering the page, the screen owns what happens on its
 * `load-*` hook and what it renders.
 *
 * @since WPUF_SINCE
 */
abstract class Screen {

    /**
     * Menu slug (`page=` query value).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    abstract public function slug();

    /**
     * Print the screen.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    abstract public function render();

    /**
     * Runs on the screen's `load-*` hook, before output.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {}

    /**
     * Capability needed to open the screen.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function capability() {
        return wpuf_admin_role();
    }

    /**
     * Whether admin notices are captured and printed inside the screen's
     * notice wrapper (React screens that show notices).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function captures_notices() {
        return false;
    }

    /**
     * Class added to the admin `<body>` while this screen is viewed. React
     * screens return `wpuf-admin-screen`: their Tailwind stylesheet scopes its
     * utilities to it, so they style the screen (and its portals) only.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function body_class() {
        return '';
    }

    /**
     * Whether this is the screen being viewed (by `$plugin_page`, which does
     * not change with the admin language, unlike the hook suffix).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_current() {
        global $plugin_page;

        return $plugin_page === $this->slug();
    }

    /**
     * Routes of the admin app (one page, hash routes) that show this screen.
     * Each route: `id`, `path` (`/post-forms/:id/edit`), `title`, `app` (id
     * the screen bundle registers with the shell), `boot` (BootPayload screen
     * id), `in_app` (true once the route runs in the app; else `page`, the old
     * URL with `:name` filled from the path, opens), `menu` (WPUF submenu slug
     * it lights), `menuLink` (the menu row points at this route), `container`
     * (old mount element id), `notices` (shows admin notices), `bodyClasses`,
     * `group` (load group, see app_groups()).
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [];
    }

    /**
     * Whether this screen runs inside the admin app. When not, its routes open
     * its own page as before.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function in_app() {
        foreach ( $this->app_routes() as $route ) {
            if ( ! empty( $route['in_app'] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * The app route the current request of this screen's old page maps to,
     * e.g. `/post-forms/12/edit` for `page=wpuf-post-forms&action=edit&id=12`,
     * or '' when that route is not in the app (the page renders as before).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        return '';
    }

    /**
     * Query arguments of the old page request that the app page needs too
     * (they decide how the screen loads there), kept on the redirect.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_redirect_args() {
        return [];
    }

    /**
     * Window globals the screen's routes read, printed on the app page
     * (name => value), e.g. `[ 'wpuf_forms_list' => [ ... ] ]`.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        return [];
    }

    /**
     * Load groups of the screen on the app page: group id => callable that
     * loads the group (enqueues) and returns its window globals. Routes name
     * their group (`group`, default the screen slug); a group's stylesheets
     * are on only on its routes (the list and the builder of a screen use
     * different stylesheets). Default: one group, load_in_app() + app_globals().
     *
     * @since WPUF_SINCE
     *
     * @return callable[]
     */
    public function app_groups() {
        return [
            $this->slug() => function () {
                $this->load_in_app();

                return $this->app_globals();
            },
        ];
    }

    /**
     * Load step of the screen's old page before it redirects to the app route:
     * the same as on its own page by default, so actions its load listeners
     * handle (bulk, row, template actions) keep working. A screen whose load
     * only prepares its own display returns nothing here: the app page loads
     * it again, and one-time display state must not be used up on the hop.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_before_redirect() {
        $this->load();
    }

    /**
     * Load the screen on the app page: the same as on its own page by default
     * (its load hooks fire with the same arguments).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        $this->load();
    }
}
