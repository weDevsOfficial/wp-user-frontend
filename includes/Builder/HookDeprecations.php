<?php
/**
 * Retired Vue form builder hooks
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Builder;

/**
 * Keeps the hooks of the removed Vue form builder firing, the WordPress way
 * (backward-compatibility.md "Vue-only hooks", owner 2026-10-02):
 *
 * - each hook still fires at the matching point of the React builder;
 * - when a callback from outside WPUF (free, Pro or a Pro module) is attached,
 *   the hook fires through `apply_filters_deprecated()` /
 *   `do_action_deprecated()` (a notice under WP_DEBUG) and admins see one
 *   dismissible notice naming the hook and the callback;
 * - WPUF's own listeners have React replacements and are no longer attached;
 *   a Pro module that still listens runs silently;
 * - output of the template actions was Vue markup the React builder cannot run,
 *   so it is discarded.
 *
 * Migration guide: docs/hooks/migration-vue-to-react.md (GUIDE_URL; `docs/` is not in the release zip).
 *
 * @since WPUF_SINCE
 */
class HookDeprecations {

    /**
     * Public copy of docs/hooks/migration-vue-to-react.md (the docs folder is not shipped).
     */
    const GUIDE_URL = 'https://github.com/weDevsOfficial/wp-user-frontend/blob/develop/docs/hooks/migration-vue-to-react.md';

    /**
     * Version the hooks were retired in (stamped at release).
     */
    const SINCE = 'WPUF_SINCE';

    /**
     * Option holding the third-party callbacks found, per hook.
     */
    const OPTION = 'wpuf_retired_builder_hooks';

    /**
     * AJAX action / nonce of the notice's dismiss link.
     */
    const DISMISS_ACTION = 'wpuf_dismiss_retired_builder_hooks';

    /**
     * Retired hooks: name => [ type, replacement ].
     *
     * @var array
     */
    const HOOKS = [
        'wpuf_form_builder_js_root_mixins'                    => [ 'filter', 'window.wpuf.registerFieldPreview / registerFieldSettingInput' ],
        'wpuf_form_builder_js_builder_stage_mixins'           => [ 'filter', 'window.wpuf.registerFieldPreview / filter wpuf.formBuilder.canvasRender' ],
        'wpuf_form_builder_js_form_fields_mixins'             => [ 'filter', 'window.wpuf.registerFieldPreview' ],
        'wpuf_form_builder_js_field_options_mixins'           => [ 'filter', 'window.wpuf.registerFieldSettingInput / filter wpuf.formBuilder.fieldSettings' ],
        'wpuf_form_builder_add_js_templates'                  => [ 'action', 'window.wpuf.registerFieldPreview / registerFieldSettingInput' ],
        'wpuf_builder_field_options'                          => [ 'action', 'slot wpuf-form-builder-field-options-after' ],
        'wpuf_field_option_data_actions'                      => [ 'action', 'filter wpuf.formBuilder.optionDataBulkAdd / slot wpuf-form-builder-option-data-actions' ],
        'wpuf_field_option_data_after'                        => [ 'action', 'slot wpuf-form-builder-option-data-after' ],
        'wpuf_form_builder_template_builder_stage_submit_area' => [ 'action', 'slot wpuf-form-builder-canvas-submit-area' ],
        'wpuf_form_builder_template_builder_stage_bottom_area' => [ 'action', 'slot wpuf-form-builder-canvas-bottom' ],
        'wpuf_form_builder_js_deps'                           => [ 'filter', 'a script of your own that depends on the builder handle (the React bundle declares its own dependencies; Vue, Vuex, toastr, scrollTo and clipboard are gone)' ],
    ];

    /**
     * Vue builder view hooks that HookBridge still fires and renders (their output
     * reaches the React builder), retired too: prefix => replacement. A name
     * matches when it equals the prefix or starts with it (per-row / per-type
     * names such as `wpuf_before_post_form_settings_field_{key}`).
     *
     * @var array
     */
    const BRIDGED = [
        'wpuf_before_post_form_settings_field'         => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_after_post_form_settings_field'          => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_before_registration_form_settings_field' => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_after_registration_form_settings_field'  => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf-form-builder-tabs-'                      => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_form_builder_settings_tabs_'             => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf-form-builder-tab-contents-'              => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_post_form_tab'                           => 'slot wpuf-form-builder-settings-<tab>',
        'wpuf_profile_form_tab'                        => 'slot wpuf-form-builder-settings-<tab>',
    ];

    /**
     * Register the notice and its dismiss handler.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        add_action( 'admin_notices', [ $this, 'render_notice' ] );
        add_action( 'admin_post_' . self::DISMISS_ACTION, [ $this, 'dismiss_notice' ] );
    }

    /**
     * Apply a retired filter.
     *
     * @since WPUF_SINCE
     *
     * @param string $name  Hook name (one of HOOKS).
     * @param mixed  $value Value to filter.
     *
     * @return mixed
     */
    public function filter( $name, $value ) {
        $outside = $this->outside_callbacks( $name );

        if ( empty( $outside ) ) {
            return apply_filters( $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }

        $this->remember( $name, $outside );

        return apply_filters_deprecated( $name, [ $value ], self::SINCE, self::HOOKS[ $name ][1] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
    }

    /**
     * Fire a retired action; its output is discarded (Vue markup).
     *
     * @since WPUF_SINCE
     *
     * @param string $name Hook name (one of HOOKS).
     * @param mixed  ...$args Hook arguments.
     *
     * @return void
     */
    public function action( $name, ...$args ) {
        $outside = $this->outside_callbacks( $name );

        ob_start();

        if ( empty( $outside ) ) {
            do_action( $name, ...$args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        } else {
            $this->remember( $name, $outside );
            do_action_deprecated( $name, $args, self::SINCE, self::HOOKS[ $name ][1] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }

        ob_end_clean();
    }

    /**
     * Fire every retired template action once (the Vue views printed them once
     * per builder page).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function fire_template_actions() {
        foreach ( self::HOOKS as $name => $hook ) {
            if ( 'action' === $hook[0] && 'wpuf_form_builder_add_js_templates' !== $name ) {
                $this->action( $name );
            }
        }
    }

    /**
     * Callbacks attached to a hook that are not WPUF's (free, Pro, Pro modules).
     *
     * @since WPUF_SINCE
     *
     * @param string $name Hook name.
     *
     * @return string[] Readable callback names with their plugin folder.
     */
    public function outside_callbacks( $name ) {
        global $wp_filter;

        if ( empty( $wp_filter[ $name ] ) ) {
            return [];
        }

        $roots = array_filter(
            [
                defined( 'WPUF_ROOT' ) ? wp_normalize_path( WPUF_ROOT ) : '',
                defined( 'WPUF_PRO_ROOT' ) ? wp_normalize_path( WPUF_PRO_ROOT ) : '',
            ]
        );
        $found = [];

        foreach ( $wp_filter[ $name ]->callbacks as $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $file = $this->callback_file( $callback['function'] );

                if ( '' === $file ) {
                    continue;
                }

                $is_wpuf = false;

                foreach ( $roots as $root ) {
                    if ( 0 === strpos( wp_normalize_path( $file ), trailingslashit( $root ) ) ) {
                        $is_wpuf = true;
                        break;
                    }
                }

                if ( ! $is_wpuf ) {
                    $found[] = $this->callback_name( $callback['function'] ) . ' (' . $this->plugin_folder( $file ) . ')';
                }
            }
        }

        return $found;
    }

    /**
     * What replaces a retired hook ('' when the hook is not retired).
     *
     * @since WPUF_SINCE
     *
     * @param string $name Hook name.
     *
     * @return string
     */
    public function replacement_for( $name ) {
        if ( isset( self::HOOKS[ $name ] ) ) {
            return self::HOOKS[ $name ][1];
        }

        foreach ( self::BRIDGED as $prefix => $replacement ) {
            if ( 0 === strpos( $name, $prefix ) ) {
                return $replacement;
            }
        }

        return '';
    }

    /**
     * Record the outside callbacks of a retired hook for the admin notice (the
     * bridge calls this before it fires a bridged hook as deprecated).
     *
     * @since WPUF_SINCE
     *
     * @param string $name Hook name.
     *
     * @return string[] The outside callbacks found.
     */
    public function note( $name ) {
        $outside = $this->outside_callbacks( $name );

        if ( ! empty( $outside ) ) {
            $this->remember( $name, $outside );
        }

        return $outside;
    }

    /**
     * Store the callbacks found for a hook (for the admin notice).
     *
     * @param string   $name    Hook name.
     * @param string[] $outside Callbacks.
     *
     * @return void
     */
    private function remember( $name, $outside ) {
        $known = get_option( self::OPTION, [] );
        $known = is_array( $known ) ? $known : [];

        if ( isset( $known[ $name ] ) && $known[ $name ] === $outside ) {
            return;
        }

        $known[ $name ] = $outside;
        update_option( self::OPTION, $known, false );
        delete_option( self::OPTION . '_dismissed' );
    }

    /**
     * One dismissible notice for admins while retired hooks have outside callbacks.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render_notice() {
        if ( ! current_user_can( wpuf_admin_role() ) || get_option( self::OPTION . '_dismissed' ) ) {
            return;
        }

        $known = get_option( self::OPTION, [] );

        if ( empty( $known ) || ! is_array( $known ) ) {
            return;
        }

        $dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::DISMISS_ACTION ), self::DISMISS_ACTION );
        ?>
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e( 'WP User Frontend: a plugin uses form builder hooks that were retired with the Vue builder.', 'wp-user-frontend' ); ?></strong></p>
            <ul style="list-style: disc; padding-left: 20px;">
                <?php foreach ( $known as $hook => $callbacks ) : ?>
                    <li>
                        <code><?php echo esc_html( $hook ); ?></code>:
                        <?php echo esc_html( implode( ', ', (array) $callbacks ) ); ?>
                        <?php
                        /* translators: %s: replacement for the retired hook */
                        printf( esc_html__( 'Use %s instead.', 'wp-user-frontend' ), '<code>' . esc_html( $this->replacement_for( $hook ) ) . '</code>' );
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p>
                <?php esc_html_e( 'The hooks still run for now but will be removed in a future major release.', 'wp-user-frontend' ); ?>
                <a href="<?php echo esc_url( self::GUIDE_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Migration guide', 'wp-user-frontend' ); ?></a>
                <a href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'wp-user-frontend' ); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Dismiss the notice (nonce + capability), then go back.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function dismiss_notice() {
        check_admin_referer( self::DISMISS_ACTION );

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( esc_html__( 'Permission denied', 'wp-user-frontend' ) );
        }

        update_option( self::OPTION . '_dismissed', 1, false );
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }

    /**
     * File that defines a callback ('' when unknown).
     *
     * @param mixed $callback Callback.
     *
     * @return string
     */
    private function callback_file( $callback ) {
        try {
            if ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
                $callback = explode( '::', $callback );
            }

            if ( is_array( $callback ) ) {
                $reflection = new \ReflectionMethod( $callback[0], $callback[1] );
            } elseif ( is_string( $callback ) || $callback instanceof \Closure ) {
                $reflection = new \ReflectionFunction( $callback );
            } elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
                $reflection = new \ReflectionMethod( $callback, '__invoke' );
            } else {
                return '';
            }

            return (string) $reflection->getFileName();
        } catch ( \ReflectionException $e ) {
            return '';
        }
    }

    /**
     * Readable callback name.
     *
     * @param mixed $callback Callback.
     *
     * @return string
     */
    private function callback_name( $callback ) {
        if ( is_string( $callback ) ) {
            return $callback;
        }

        if ( is_array( $callback ) ) {
            return ( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0] ) . '::' . $callback[1];
        }

        return $callback instanceof \Closure ? 'closure' : get_class( $callback );
    }

    /**
     * Plugin (or mu-plugin / theme) folder of a file, for the notice.
     *
     * @param string $file File path.
     *
     * @return string
     */
    private function plugin_folder( $file ) {
        $file = wp_normalize_path( $file );

        foreach ( [ WP_PLUGIN_DIR, WPMU_PLUGIN_DIR, get_theme_root() ] as $dir ) {
            $dir = trailingslashit( wp_normalize_path( $dir ) );

            if ( 0 === strpos( $file, $dir ) ) {
                $rest = substr( $file, strlen( $dir ) );

                return strtok( $rest, '/' );
            }
        }

        return basename( $file );
    }
}
