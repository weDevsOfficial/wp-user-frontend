<?php
/**
 * Helpers of the single React admin app (one admin page, hash routes)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Whether the React admin screens run as one app (one page, hash routes).
 *
 * Always true: the classic per-page admin screens were removed. The function
 * stays because Pro and outside code check it (`function_exists`).
 *
 * @since WPUF_SINCE
 *
 * @return bool
 */
function wpuf_admin_app_enabled() {
    return true;
}

/**
 * URL of a route of the admin app, e.g. `wpuf_admin_app_url( '/post-forms/12/edit' )`.
 *
 * @since WPUF_SINCE
 *
 * @param string $route Route path.
 * @param array  $args  Route query arguments.
 *
 * @return string
 */
function wpuf_admin_app_url( $route = '/', $args = [] ) {
    $route = '/' . ltrim( (string) $route, '/' );
    $query = $args ? '?' . http_build_query( $args ) : '';

    return admin_url( 'admin.php?page=' . \WeDevs\Wpuf\Admin\App\AppPage::SLUG ) . '#' . $route . $query;
}

/**
 * Whether the current admin request is the admin app page.
 *
 * @since WPUF_SINCE
 *
 * @return bool
 */
function wpuf_is_admin_app() {
    global $plugin_page;

    return wpuf_admin_app_enabled() && \WeDevs\Wpuf\Admin\App\AppPage::SLUG === $plugin_page;
}

/**
 * Whether the request is a WPUF admin screen: its own page (`?page=$slug`),
 * or the admin app page opened on that screen's route (old page redirects
 * carry the screen's query arguments, e.g. list action notices).
 *
 * @since WPUF_SINCE
 *
 * @param string $slug Screen page slug, e.g. `wpuf-post-forms`.
 *
 * @return bool
 */
function wpuf_is_admin_screen( $slug ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen detection.
    $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

    if ( $slug === $page ) {
        return true;
    }

    return wpuf_is_admin_app() && wpuf()->platform()->get( \WeDevs\Wpuf\Admin\App\AppPage::class )->initial_screen() === $slug;
}
