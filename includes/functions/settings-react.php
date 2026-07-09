<?php
/**
 * Helpers that bridge the legacy settings schema to the React settings screen.
 *
 * The React screen renders whatever wpuf_settings_sections() /
 * wpuf_settings_fields() produce (already hook-filtered, so Pro and module
 * fields are included). These helpers only describe how those sections map
 * onto the redesigned tab IA and which optional modules are active — they
 * never change where or how a setting is stored.
 *
 * @package WP_User_Frontend
 * @since   WPUF_SINCE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wpuf_settings_react_ia' ) ) {
    /**
     * Information architecture map for the redesigned settings screen.
     *
     * Maps the new top-level tabs (and their sub-groups) onto the existing
     * legacy section ids. A tab may pull from more than one section. Tabs and
     * groups are presentational only; storage stays keyed by section id.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    function wpuf_settings_react_ia() {
        // The redesigned (Figma) tabs that consolidate several legacy sections.
        $ia = [
            [
                'id'       => 'general',
                'title'    => __( 'General', 'wp-user-frontend' ),
                'icon'     => 'cog',
                'sections' => [ 'wpuf_general' ],
            ],
            [
                'id'       => 'frontend_posting',
                'title'    => __( 'Frontend Posting', 'wp-user-frontend' ),
                'icon'     => 'document',
                'sections' => [ 'wpuf_frontend_posting', 'wpuf_dashboard' ],
            ],
            [
                'id'       => 'login_registration',
                'title'    => __( 'Login & Registration', 'wp-user-frontend' ),
                'icon'     => 'login',
                // My Account + Login/Registration + Social Login as sub-tabs.
                'sections' => [ 'wpuf_my_account', 'wpuf_profile', 'wpuf_social_api' ],
                'subtabs'  => true,
            ],
            [
                'id'       => 'email',
                'title'    => __( 'Email', 'wp-user-frontend' ),
                'icon'     => 'mail',
                'sections' => [ 'wpuf_mails' ],
            ],
            [
                'id'       => 'sms',
                'title'    => __( 'SMS', 'wp-user-frontend' ),
                'icon'     => 'chat',
                'sections' => [ 'wpuf_sms' ],
                'module'   => 'sms',
            ],
            [
                'id'       => 'payments',
                'title'    => __( 'Payments', 'wp-user-frontend' ),
                'icon'     => 'credit-card',
                // Payments + Invoices + Tax as sub-tabs.
                'sections' => [ 'wpuf_payment', 'wpuf_payment_invoices', 'wpuf_payment_tax' ],
                'subtabs'  => true,
            ],
            [
                'id'       => 'advanced',
                'title'    => __( 'Advanced', 'wp-user-frontend' ),
                'icon'     => 'adjustments',
                // Privacy + Content Filtering + SEO as sub-tabs.
                'sections' => [ 'wpuf_privacy', 'wpuf_content_restriction', 'wpuf_seo_settings' ],
                'subtabs'  => true,
            ],
            [
                'id'       => 'integrations',
                'title'    => __( 'Integrations', 'wp-user-frontend' ),
                'icon'     => 'puzzle',
                // AI + n8n as sub-tabs.
                'sections' => [ 'wpuf_ai', 'n8n' ],
                'subtabs'  => true,
            ],
        ];

        // Drop sections that aren't actually registered (e.g. free-only Pro
        // preview stubs that don't exist once Pro is active, where the real
        // feature lives elsewhere). Keep only existing sections per tab, and
        // remove any tab left with no sections.
        $existing_ids = wp_list_pluck( wpuf_settings_sections(), 'id' );

        foreach ( $ia as $i => $tab ) {
            $ia[ $i ]['sections'] = array_values( array_intersect( $tab['sections'], $existing_ids ) );
        }

        $ia = array_values(
            array_filter(
                $ia,
                static function ( $tab ) {
                    return ! empty( $tab['sections'] );
                }
            )
        );

        // Append every registered section not already claimed by a tab above, so
        // Pro / module sections remain reachable — never dropped.
        $claimed = [];
        foreach ( $ia as $tab ) {
            foreach ( $tab['sections'] as $sid ) {
                $claimed[ $sid ] = true;
            }
        }

        /**
         * Sections that have their own dedicated React admin UI and should NOT
         * be surfaced as a tab on the settings screen (e.g. User Directory).
         *
         * @since WPUF_SINCE
         *
         * @param array $excluded Section ids to exclude from the settings tabs.
         */
        $excluded = apply_filters( 'wpuf_settings_react_excluded_sections', [ 'user_directory' ] );

        foreach ( wpuf_settings_sections() as $section ) {
            $sid = $section['id'];

            if ( isset( $claimed[ $sid ] ) || in_array( $sid, $excluded, true ) ) {
                continue;
            }

            $ia[] = [
                'id'       => $sid,
                'title'    => wp_strip_all_tags( $section['title'] ),
                'icon'     => ! empty( $section['icon'] ) ? $section['icon'] : 'adjustments',
                'sections' => [ $sid ],
            ];
        }

        /**
         * Filter the React settings tab IA.
         *
         * Pro and modules extend the tab map here (e.g. to register the SMS
         * tab sections or insert new tabs) without forking the React UI.
         *
         * @since WPUF_SINCE
         *
         * @param array $ia Tab definitions.
         */
        return apply_filters( 'wpuf_settings_react_ia', $ia );
    }
}

if ( ! function_exists( 'wpuf_settings_react_modules' ) ) {
    /**
     * Active optional-module flags exposed to the React settings screen.
     *
     * Lets the UI gate module-dependent tabs/fields the same way the legacy
     * screen does. The schema itself is already module-aware (inactive modules
     * register no fields), so these flags are only for presentation (e.g.
     * showing an "enable module" prompt versus the real settings).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    function wpuf_settings_react_modules() {
        $modules = [
            'is_pro'              => class_exists( 'WP_User_Frontend_Pro' ),
            'sms'                 => false,
            'social_login'        => false,
            'content_restriction' => false,
            'geolocation'         => false,
        ];

        /**
         * Filter the active-module flags for the React settings screen.
         *
         * Pro and module bootstraps set their own flag to true here.
         *
         * @since WPUF_SINCE
         *
         * @param array $modules Module flags.
         */
        return apply_filters( 'wpuf_settings_react_modules', $modules );
    }
}


/**
 * AI section: inject per-provider model lists + each provider's stored key so the
 * React AI panel can re-filter the model dropdown and swap the key field when the
 * provider changes (the legacy screen did this with jQuery).
 *
 * @since WPUF_SINCE
 *
 * @param array $data REST settings payload.
 *
 * @return array
 */
function wpuf_ai_react_inject_data( $data ) {
    if ( ! class_exists( '\WeDevs\Wpuf\AI\Config' ) ) {
        return $data;
    }

    $models      = \WeDevs\Wpuf\AI\Config::get_models();
    $by_provider = [];

    if ( is_array( $models ) ) {
        foreach ( $models as $model_id => $model ) {
            $provider = isset( $model['provider'] ) ? $model['provider'] : '';
            if ( '' === $provider ) {
                continue;
            }
            $by_provider[ $provider ][ $model_id ] = isset( $model['name'] ) ? $model['name'] : $model_id;
        }
    }

    $ai   = get_option( 'wpuf_ai', [] );
    $ai   = is_array( $ai ) ? $ai : [];
    $keys = [];
    foreach ( [ 'openai', 'anthropic', 'google' ] as $provider ) {
        $keys[ $provider ] = isset( $ai[ $provider . '_api_key' ] ) ? $ai[ $provider . '_api_key' ] : '';
    }

    if ( ! isset( $data['extra'] ) || ! is_array( $data['extra'] ) ) {
        $data['extra'] = [];
    }

    $data['extra']['ai'] = [
        'models_by_provider' => $by_provider,
        'keys'               => $keys,
    ];

    return $data;
}
add_filter( 'wpuf_settings_rest_data', 'wpuf_ai_react_inject_data' );

/**
 * Persist every provider's AI key from the React panel's `extra.ai.keys` so
 * switching providers doesn't lose another provider's saved key.
 *
 * @since WPUF_SINCE
 *
 * @param array $saved    Saved section values (unused).
 * @param array $incoming Raw section payload (unused).
 * @param array $extra    Custom own-option payload.
 *
 * @return void
 */
function wpuf_ai_react_persist_keys( $saved, $incoming, $extra ) {
    if ( ! is_array( $extra ) || empty( $extra['ai']['keys'] ) || ! is_array( $extra['ai']['keys'] ) ) {
        return;
    }

    $ai = get_option( 'wpuf_ai', [] );
    $ai = is_array( $ai ) ? $ai : [];

    foreach ( [ 'openai', 'anthropic', 'google' ] as $provider ) {
        if ( isset( $extra['ai']['keys'][ $provider ] ) ) {
            $ai[ $provider . '_api_key' ] = sanitize_text_field( wp_unslash( $extra['ai']['keys'][ $provider ] ) );
        }
    }

    unset( $ai['api_key_current'] );

    update_option( 'wpuf_ai', $ai );
}
add_action( 'wpuf_settings_saved', 'wpuf_ai_react_persist_keys', 10, 3 );

/**
 * Profile Forms for User Roles: feed the React role→form mapping table.
 *
 * The legacy `profile_form_roles` callback stores a role→form map nested under
 * `wpuf_profile['roles']`, which the flat React save can't write. These hooks
 * inject the roles, available profile forms and current map on read, and persist
 * the edited map back to `wpuf_profile['roles']` on save (Pro only).
 *
 * @since WPUF_SINCE
 *
 * @param array $data REST settings payload.
 *
 * @return array
 */
function wpuf_profile_roles_react_inject( $data ) {
    $roles = apply_filters( 'wpuf_settings_user_roles', wpuf_get_user_roles() );

    $forms     = get_posts( [ 'numberposts' => -1, 'post_type' => 'wpuf_profile' ] );
    $form_list = [];
    foreach ( $forms as $form ) {
        $form_list[] = [ 'id' => $form->ID, 'title' => $form->post_title ];
    }

    $val = get_option( 'wpuf_profile', [] );
    $map = ( is_array( $val ) && isset( $val['roles'] ) && is_array( $val['roles'] ) ) ? $val['roles'] : [];

    if ( ! isset( $data['extra'] ) || ! is_array( $data['extra'] ) ) {
        $data['extra'] = [];
    }

    $data['extra']['profile_role_forms'] = [
        'roles' => $roles,
        'forms' => $form_list,
        'map'   => $map,
    ];

    return $data;
}
add_filter( 'wpuf_settings_rest_data', 'wpuf_profile_roles_react_inject' );

/**
 * Persist the Profile-Forms-for-Roles map edited through the React screen.
 *
 * @since WPUF_SINCE
 *
 * @param array $saved    Saved section values (unused).
 * @param array $incoming Raw section payload (unused).
 * @param array $extra    Custom own-option payload.
 *
 * @return void
 */
function wpuf_profile_roles_react_save( $saved, $incoming, $extra ) {
    if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
        return;
    }

    if ( ! is_array( $extra ) || ! isset( $extra['profile_role_forms']['map'] ) || ! is_array( $extra['profile_role_forms']['map'] ) ) {
        return;
    }

    $clean = [];
    foreach ( $extra['profile_role_forms']['map'] as $role => $form_id ) {
        $clean[ sanitize_key( $role ) ] = absint( $form_id );
    }

    $val = get_option( 'wpuf_profile', [] );
    $val = is_array( $val ) ? $val : [];

    // Drop the React proxy field if the flat save wrote it, then store the map.
    unset( $val['profile_form_roles'] );
    $val['roles'] = $clean;

    update_option( 'wpuf_profile', $val );
}
add_action( 'wpuf_settings_saved', 'wpuf_profile_roles_react_save', 10, 3 );

/**
 * Whether the legacy (WeDevs_Settings_API) settings screen should render instead
 * of the React app.
 *
 * Resolution order (first match wins):
 *   1. `?wpuf_settings_ui=legacy|react` — per-request emergency override that
 *      needs no DB write, so a broken React build can still be bypassed.
 *   2. `WPUF_LEGACY_SETTINGS` constant.
 *   3. `wpuf_settings_ui_mode` option ('react' default | 'legacy').
 *
 * Both screens read/write the SAME wpuf_* options, so switching never loses or
 * forks data — they stay in sync by construction.
 *
 * @since WPUF_SINCE
 *
 * @return bool
 */
function wpuf_settings_use_legacy() {
    if ( isset( $_GET['wpuf_settings_ui'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
        return 'legacy' === sanitize_key( wp_unslash( $_GET['wpuf_settings_ui'] ) );
    }

    if ( defined( 'WPUF_LEGACY_SETTINGS' ) && WPUF_LEGACY_SETTINGS ) {
        return true;
    }

    $mode = get_option( 'wpuf_settings_ui_mode', 'react' );

    /**
     * Filter whether the legacy settings screen renders.
     *
     * @since WPUF_SINCE
     *
     * @param bool $is_legacy
     */
    return (bool) apply_filters( 'wpuf_use_legacy_settings', 'legacy' === $mode );
}

/**
 * Toggle the persisted settings UI mode (nonce + capability protected), then
 * redirect back to the settings page.
 *
 * @since WPUF_SINCE
 *
 * @return void
 */
function wpuf_settings_ui_switch() {
    if ( ! isset( $_GET['wpuf_action'] ) || 'switch_settings_ui' !== sanitize_key( wp_unslash( $_GET['wpuf_action'] ) ) ) {
        return;
    }

    if ( ! current_user_can( wpuf_admin_role() ) ) {
        return;
    }

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_switch_settings_ui' ) ) {
        return;
    }

    $mode = get_option( 'wpuf_settings_ui_mode', 'react' );
    update_option( 'wpuf_settings_ui_mode', 'legacy' === $mode ? 'react' : 'legacy' );

    wp_safe_redirect( admin_url( 'admin.php?page=wpuf-settings' ) );
    exit;
}
add_action( 'admin_init', 'wpuf_settings_ui_switch' );

/**
 * Nonce-protected URL that toggles the settings UI mode.
 *
 * @since WPUF_SINCE
 *
 * @return string
 */
function wpuf_settings_ui_switch_url() {
    return wp_nonce_url(
        admin_url( 'admin.php?page=wpuf-settings&wpuf_action=switch_settings_ui' ),
        'wpuf_switch_settings_ui'
    );
}

/**
 * Dev guard: warn (in WP_DEBUG) about settings fields the LEGACY screen can't
 * render, so the classic-fallback never silently breaks. The legacy
 * WeDevs_Settings_API renders by `callback_{type}`; a field with an unsupported
 * type and no explicit `callback` would fatal/blank the classic screen — exactly
 * the regression to avoid now that legacy is a supported fallback.
 *
 * Read-only, dev-only — never runs in production, changes no data.
 *
 * @since WPUF_SINCE
 *
 * @return void
 */
function wpuf_settings_legacy_compat_check() {
    if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
        return;
    }

    $supported = [
        'text', 'hidden', 'url', 'number', 'checkbox', 'multicheck', 'gateway_selector',
        'radio', 'radio_inline', 'select', 'textarea', 'html', 'wysiwyg', 'file', 'password',
        'color', 'toggle',
    ];

    foreach ( wpuf_settings_fields() as $section_id => $section_fields ) {
        if ( ! is_array( $section_fields ) ) {
            continue;
        }

        foreach ( $section_fields as $field ) {
            $type = isset( $field['type'] ) ? $field['type'] : 'text';

            if ( ! in_array( $type, $supported, true ) && empty( $field['callback'] ) ) {
                $name = isset( $field['name'] ) ? $field['name'] : '?';
                error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    sprintf(
                        'WPUF settings: field "%1$s" (section "%2$s") uses type "%3$s" which the legacy WeDevs_Settings_API cannot render and has no callback — the classic settings screen will break for this field. Use a supported type, add a callback, or a React render hint.',
                        $name, $section_id, $type
                    )
                );
            }
        }
    }
}
add_action( 'admin_init', 'wpuf_settings_legacy_compat_check', 99 );
