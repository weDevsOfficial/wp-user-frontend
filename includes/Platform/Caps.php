<?php
/**
 * Platform capabilities
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

/**
 * One place for the capability checks the admin screens and REST routes copied
 * around (FormList, Subscription, Settings). Every WPUF admin capability maps to
 * `wpuf_admin_role()`, so the `wpuf_admin_role` filter keeps working; the
 * `wpuf_capability` filter can split them later.
 *
 * @since WPUF_SINCE
 */
class Caps {

    /**
     * Manage post and registration forms.
     */
    const MANAGE_FORMS = 'manage_forms';

    /**
     * Manage subscription packs.
     */
    const MANAGE_SUBSCRIPTIONS = 'manage_subscriptions';

    /**
     * Manage plugin settings.
     */
    const MANAGE_SETTINGS = 'manage_settings';

    /**
     * Site-wide actions for administrators: accept / reject / delete payments
     * (develop checked `manage_options` on accept only; delete and reject now
     * need it too), form import, the settings save, the Tools danger zone,
     * onboarding. Defaults to `manage_options`, not `wpuf_admin_role()`.
     */
    const MANAGE_SITE = 'manage_site';

    /**
     * The WordPress capability behind a WPUF capability.
     *
     * @since WPUF_SINCE
     *
     * @param string $cap One of the Caps constants
     *
     * @return string WordPress capability
     */
    public static function capability( $cap ) {
        /**
         * Filter the WordPress capability a WPUF admin capability needs.
         *
         * @since WPUF_SINCE
         *
         * @param string $capability WordPress capability (default: wpuf_admin_role(), `manage_options` for MANAGE_SITE)
         * @param string $cap        WPUF capability (Caps constant)
         */
        $default = self::MANAGE_SITE === $cap ? 'manage_options' : wpuf_admin_role();

        return (string) apply_filters( 'wpuf_capability', $default, $cap );
    }

    /**
     * Whether the current user has a WPUF capability.
     *
     * @since WPUF_SINCE
     *
     * @param string $cap One of the Caps constants
     *
     * @return bool
     */
    public static function can( $cap ) {
        return current_user_can( self::capability( $cap ) );
    }
}
