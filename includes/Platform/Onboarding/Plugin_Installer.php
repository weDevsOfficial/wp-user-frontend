<?php
/**
 * Onboarding: recommended plugins
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

use WP_Error;

/**
 * The wizard's companion plugins: the recommended list, what is still not
 * running, and installing + activating them (one per request from the
 * plugins step, or the rest of a pick within the request's time budget).
 *
 * @since WPUF_SINCE
 */
class Plugin_Installer {

    /**
     * The wizard's run state (last run's install errors).
     *
     * @var State
     */
    private $state;

    /**
     * @since WPUF_SINCE
     *
     * @param State $state Run state
     */
    public function __construct( State $state ) {
        $this->state = $state;
    }

    /**
     * Whether the current user may install and switch on plugins.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function can_install() {
        return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
    }

    /**
     * Install and activate one picked plugin (the plugins step's progress
     * list asks for them one at a time), clearing its failure from the last run.
     *
     * @since WPUF_SINCE
     *
     * @param string $slug Recommended plugin slug
     *
     * @return true|WP_Error
     */
    public function install( $slug ) {
        $plugins = $this->get_recommended_plugins();

        if ( ! isset( $plugins[ $slug ] ) ) {
            return new WP_Error( 'wpuf_onboarding_unknown_plugin', __( 'Unknown plugin.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( ! $this->can_install() ) {
            return new WP_Error( 'wpuf_onboarding_cannot_install', __( 'You do not have permission to install plugins.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        if ( function_exists( 'set_time_limit' ) ) {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- disabled on some hosts.
            @set_time_limit( 120 );
        }

        wp_raise_memory_limit( 'admin' );

        $result = $this->install_and_activate( $slug );
        $errors = $this->state->plugin_errors();
        $name   = $plugins[ $slug ]['name'];

        if ( is_wp_error( $result ) ) {
            $errors[ $name ] = $result->get_error_message();

            $this->state->set_plugin_errors( $errors );

            return new WP_Error( 'wpuf_onboarding_install_failed', $result->get_error_message(), [ 'status' => 500 ] );
        }

        unset( $errors[ $name ] );

        $this->state->set_plugin_errors( $errors );

        return true;
    }

    /**
     * The plugins step's save: install whatever of the pick is still not
     * running (the step installs them one by one before it saves, so this
     * normally finds nothing left), and remember what failed.
     *
     * @since WPUF_SINCE
     *
     * @param string[] $picked Slugs the admin picked.
     *
     * @return void
     */
    public function save( $picked ) {
        $plugins = $this->get_recommended_plugins();
        $errors  = [];

        if ( ! $picked || ! $this->can_install() ) {
            $this->state->set_plugin_errors( [] );

            return;
        }

        // Four plugins downloaded one after another in a single request is the
        // slowest thing the wizard does, and on a host with a short
        // max_execution_time it is what times out. Ask for room, then keep an eye
        // on the clock and stop cleanly rather than being killed mid-install.
        wp_raise_memory_limit( 'admin' );

        $budget = $this->get_install_time_budget();
        $started = microtime( true );

        foreach ( $picked as $slug ) {
            if ( ! isset( $plugins[ $slug ] ) ) {
                continue;
            }

            // Stop while there is still time to render a page. Whatever is left is
            // reported rather than silently dropped, and the step reappears with
            // those plugins still on it, so a second click finishes the job.
            if ( $budget > 0 && ( microtime( true ) - $started ) > $budget ) {
                $errors[ $plugins[ $slug ]['name'] ] = __( 'Not installed yet: the wizard ran out of time on this request. Select it again to finish.', 'wp-user-frontend' );

                continue;
            }

            // Each plugin gets its own slice of the clock where the host allows it.
            if ( function_exists( 'set_time_limit' ) ) {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- disabled on some hosts.
                @set_time_limit( 120 );
            }

            $result = $this->install_and_activate( $slug );

            if ( is_wp_error( $result ) ) {
                $errors[ $plugins[ $slug ]['name'] ] = $result->get_error_message();
            }
        }

        if ( $errors ) {
            $this->state->set_plugin_errors( $errors );
        } else {
            $this->state->set_plugin_errors( [] );
        }
    }

    /**
     * How long this request may spend installing before it stops
     *
     * Leaves roughly a quarter of the host's execution time to render the next
     * screen, so a run that cannot finish reports what is left instead of dying
     * half way. Returns 0 when the host sets no limit, in which case there is
     * nothing to budget against.
     *
     * @since WPUF_SINCE
     *
     * @return float seconds, 0 when the host imposes no limit
     */
    protected function get_install_time_budget() {
        $limit = (int) ini_get( 'max_execution_time' );

        if ( $limit <= 0 ) {
            return 0;
        }

        return max( 5, $limit * 0.75 );
    }

    /**
     * The recommended plugins that are not running yet
     *
     * A plugin that is later deactivated or deleted comes back on the list,
     * and with it the step.
     *
     * @since WPUF_SINCE
     *
     * @return array slug => plugin
     */
    public function get_pending_plugins() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pending = [];

        foreach ( $this->get_recommended_plugins() as $slug => $plugin ) {
            $file = $this->get_installed_file( $plugin['file'] );

            if ( $file && is_plugin_active( $file ) ) {
                continue;
            }

            $plugin['installed'] = (bool) $file;

            $pending[ $slug ] = $plugin;
        }

        return $pending;
    }

    /**
     * Install a recommended plugin from wordpress.org and switch it on
     *
     * @since WPUF_SINCE
     *
     * @param string $slug
     *
     * @return true|\WP_Error
     */
    public function install_and_activate( $slug ) {
        $plugins = $this->get_recommended_plugins();

        if ( ! isset( $plugins[ $slug ] ) ) {
            return new \WP_Error( 'unknown_plugin', __( 'Unknown plugin.', 'wp-user-frontend' ) );
        }

        $basename = $plugins[ $slug ]['file'];

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        if ( ! $this->is_plugin_installed( $basename ) ) {
            $api = plugins_api(
                'plugin_information', [
                    'slug'   => $slug,
                    'fields' => [ 'sections' => false ],
                ]
            );

            if ( is_wp_error( $api ) ) {
                return $api;
            }

            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
            $result   = $upgrader->install( $api->download_link );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            if ( ! $result ) {
                return new \WP_Error( 'install_failed', __( 'Could not install the plugin.', 'wp-user-frontend' ) );
            }
        }

        $installed_file = $this->get_installed_file( $basename );

        if ( ! $installed_file ) {
            return new \WP_Error( 'not_found', __( 'The plugin was not found after installing.', 'wp-user-frontend' ) );
        }

        $activated = activate_plugin( $installed_file );

        if ( is_wp_error( $activated ) ) {
            return $activated;
        }

        $this->clear_activation_redirects();

        return true;
    }

    /**
     * Drop the "just activated" redirects the companion plugins set for themselves
     *
     * Activating a plugin from inside the wizard makes that plugin queue its own
     * welcome or setup redirect, which fires on the next admin screen and throws the
     * admin out of onboarding half way through. Clearing the flags keeps the admin
     * in the wizard; the plugin's own setup screens stay reachable from its menu.
     *
     * Best effort by design. Deleting a transient that was never set is a no-op, and
     * the list is filterable so a plugin using a key not covered here can add it
     * rather than needing a change in this file.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function clear_activation_redirects() {
        $default_transients = [
            // WPUF's own, so finishing an install here does not bounce the admin
            // into the legacy setup wizard.
            'wpuf_activation_redirect',
            'wpuf_onboarding_redirect',
            // The companion plugins offered by this step.
            'wemail_activation_redirect',
            'erp_activation_redirect',
            '_erp_setup_page_redirect',
            'wedocs_activation_redirect',
            'pm_activation_redirect',
            'cpm_activation_redirect',
        ];

        /**
         * Filter the activation redirect flags cleared after a companion plugin installs
         *
         * @since WPUF_SINCE
         *
         * @param array $transients Transient names to delete.
         */
        $transients = apply_filters( 'wpuf_onboarding_activation_redirects', $default_transients );
        $transients = ! empty( $transients ) && is_array( $transients ) ? $transients : $default_transients;

        foreach ( $transients as $transient ) {
            if ( is_string( $transient ) && '' !== $transient ) {
                delete_transient( $transient );
            }
        }
    }

    /**
     * The companion plugins offered in the wizard
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_recommended_plugins() {
        $plugins = [
            'wemail'              => [
                'logo' => 'onboarding/wemail.svg',
                'name' => __( 'weMail', 'wp-user-frontend' ),
                'file' => 'wemail/wemail.php',
                'desc' => __( 'Welcome mails, newsletters and campaigns to the people who sign up.', 'wp-user-frontend' ),
            ],
            'erp'                 => [
                'logo' => 'onboarding/erp.svg',
                'name' => __( 'WP ERP', 'wp-user-frontend' ),
                'file' => 'erp/wp-erp.php',
                'desc' => __( 'Turn your members into CRM contacts, with every interaction on one profile.', 'wp-user-frontend' ),
            ],
            'wedocs'              => [
                'logo' => 'onboarding/wedocs.svg',
                'name' => __( 'weDocs', 'wp-user-frontend' ),
                'file' => 'wedocs/wedocs.php',
                'desc' => __( 'A docs area members can read instead of opening a ticket.', 'wp-user-frontend' ),
            ],
            'wedevs-project-manager' => [
                'logo' => 'onboarding/wedevs-project-manager.svg',
                'name' => __( 'WP Project Manager', 'wp-user-frontend' ),
                'file' => 'wedevs-project-manager/cpm.php',
                'desc' => __( 'Run projects and tasks with the members who sign up.', 'wp-user-frontend' ),
            ],
        ];

        /**
         * Filter the plugins recommended during onboarding
         *
         * @since WPUF_SINCE
         *
         * @param array $plugins
         */
        return apply_filters( 'wpuf_onboarding_recommended_plugins', $plugins );
    }

    /**
     * Whether a plugin is present on the site
     *
     * @since WPUF_SINCE
     *
     * @param string $basename
     *
     * @return bool
     */
    public function is_plugin_installed( $basename ) {
        return (bool) $this->get_installed_file( $basename );
    }

    /**
     * The installed plugin file for a basename
     *
     * Plugins are matched by their folder, so a main file we did not guess
     * exactly still resolves to what is really on disk.
     *
     * @since WPUF_SINCE
     *
     * @param string $basename
     *
     * @return string empty when the plugin is not installed
     */
    public function get_installed_file( $basename ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed = get_plugins();

        if ( array_key_exists( $basename, $installed ) ) {
            return $basename;
        }

        $folder = dirname( $basename );

        foreach ( array_keys( $installed ) as $file ) {
            if ( dirname( $file ) === $folder ) {
                return $file;
            }
        }

        return '';
    }
}
