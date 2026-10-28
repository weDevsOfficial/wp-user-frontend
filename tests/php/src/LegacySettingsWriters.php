<?php
/**
 * Verbatim copy of the React settings REST controller as it was before task
 * 2.4c, kept as the reference the store tests compare stored bytes against.
 *
 * @package WP_User_Frontend
 */

// phpcs:ignoreFile -- reference copy, kept byte for byte.

/**
 * Settings REST controller.
 *
 * Powers the React settings screen. Reads and writes the SAME WordPress options
 * and field keys the legacy WeDevs_Settings_API screen uses, applying the SAME
 * per-field sanitize callbacks, so existing user data is never mismatched.
 *
 * @since WPUF_SINCE
 */
class WPUF_Legacy_Settings_Api extends WP_REST_Controller {

    /**
     * Route namespace.
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route base.
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $base = 'settings';

    /**
     * Constructor.
     *
     * Ensures the settings schema + IA helpers are loaded, since REST requests
     * do not bootstrap the admin settings subsystem.
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-options.php' );
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-screen.php' );

        // The settings schema (and Pro role filters) call get_editable_roles(),
        // which lives in wp-admin and is not loaded during REST requests.
        if ( ! function_exists( 'get_editable_roles' ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
    }

    /**
     * Register the routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'save_items' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }

    /**
     * Permission check.
     *
     * Mirrors the capability gate used by the legacy settings screen.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }

    /**
     * Get the full settings payload: schema, values, caps and module flags.
     *
     * Schema comes from the hook-filtered wpuf_settings_sections() /
     * wpuf_settings_fields(), so Pro and module-registered fields are included
     * automatically — never hard-coded.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function get_items( $request ) {
        unset( $request );

        $sections   = wpuf_settings_sections();
        $raw_fields = wpuf_settings_fields();

        $values       = [];
        $fields       = [];
        $pro_sections = [];
        $section_html = [];

        foreach ( $sections as $section ) {
            $section_id            = $section['id'];
            $values[ $section_id ] = get_option( $section_id, [] );

            // Pro-only feature sections (SMS, Social Login, …) are registered by
            // Free_Loader with `is_pro_preview => true`. When Pro is active it
            // re-registers them functionally (flag absent). Use that flag as the
            // source of truth; fall back to the legacy pro-icon title marker.
            if ( $this->is_pro_preview_section( $section ) ) {
                $pro_sections[] = $section_id;
            }

            // Re-index each section's field list so it always serializes as a
            // JSON array (some modules register fields with string keys, which
            // would otherwise become an object and break Array methods in JS).
            $section_fields = isset( $raw_fields[ $section_id ] ) && is_array( $raw_fields[ $section_id ] )
                ? array_values( $raw_fields[ $section_id ] )
                : [];

            $fields[ $section_id ] = $this->kses_field_texts( $this->dedupe_fields( $section_fields ) );

            // The legacy screen fired these around each section form; their output
            // is shown at the top and bottom of the section here.
            $top    = $this->capture_section_hook( 'wsa_form_top_' . $section_id, $section );
            $bottom = $this->capture_section_hook( 'wsa_form_bottom_' . $section_id, $section );

            if ( '' !== $top || '' !== $bottom ) {
                $section_html[ $section_id ] = [
                    'top'    => $top,
                    'bottom' => $bottom,
                ];
            }
            $values[ $section_id ] = $this->mask_secrets( $values[ $section_id ], $fields[ $section_id ] );
        }

        $data = [
            'sections'     => array_values( $sections ),
            'fields'       => $fields,
            'ia'           => wpuf_settings_ia(),
            'values'       => $values,
            'pro_sections' => $pro_sections,
            'caps'         => [
                'is_pro'     => class_exists( 'WP_User_Frontend_Pro' ),
                'can_manage' => current_user_can( wpuf_admin_role() ),
            ],
            'modules'      => wpuf_settings_modules(),
            // Output of `wsa_form_top_{section}` / `wsa_form_bottom_{section}`.
            'section_html' => $section_html,
            // Side-channel for settings that live in their OWN option (not a
            // section field) and need a custom React renderer — e.g. tax rates,
            // base country/state, role-based email templates. Pro injects them
            // here and reads them back on save via `wpuf_settings_saved`.
            'extra'        => [],
        ];

        /**
         * Filter the full React settings payload before it is returned.
         *
         * Lets Pro/add-ons inject custom data (under `extra`) or augment field
         * options for settings that are not plain section fields.
         *
         * @since WPUF_SINCE
         *
         * @param array $data The settings payload.
         */
        $data = apply_filters( 'wpuf_settings_rest_data', $data );

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $data,
            ]
        );
    }

    /**
     * Save settings.
     *
     * The client posts only the fields the user changed; each is merged into
     * its section's stored option, applying the exact
     * sanitize callback registered for each field (parity with the legacy
     * WeDevs_Settings_API::sanitize_options()). Unknown sections/fields are
     * ignored so a stale client cannot write arbitrary option keys.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function save_items( WP_REST_Request $request ) {
        $incoming = $request->get_param( 'settings' );

        if ( ! is_array( $incoming ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'No settings provided.', 'wp-user-frontend' ),
                ],
                400
            );
        }

        $sections      = wpuf_settings_sections();
        $section_ids   = wp_list_pluck( $sections, 'id' );
        $fields_schema = wpuf_settings_fields();
        $saved         = [];

        foreach ( $incoming as $section_id => $section_values ) {
            // Only allow known section ids.
            if ( ! in_array( $section_id, $section_ids, true ) || ! is_array( $section_values ) ) {
                continue;
            }

            $existing  = get_option( $section_id, [] );
            $existing  = is_array( $existing ) ? $existing : [];
            // Dedupe so the sanitize/def used on save matches the one rendered on
            // GET (Pro's functional field wins over the free preview of the same
            // name) — keeps save behavior consistent with what the user edited.
            $allowed   = $this->dedupe_fields( isset( $fields_schema[ $section_id ] ) ? $fields_schema[ $section_id ] : [] );
            $sanitized = $existing;

            foreach ( $section_values as $field_name => $value ) {
                $field = $this->find_field( $allowed, $field_name );

                // Only persist fields that are actually registered for this section.
                if ( null === $field ) {
                    continue;
                }

                // A masked secret coming back unchanged keeps the stored value.
                if ( $this->is_secret_field( $field ) && wpuf_settings_is_masked_secret( $value, isset( $existing[ $field_name ] ) ? $existing[ $field_name ] : '' ) ) {
                    continue;
                }

                $sanitized[ $field_name ] = $this->sanitize_value( $value, $field );

                // The legacy multiselect posted nothing when empty, so the key
                // stays absent and readers fall back to the field default.
                if ( $this->is_multiselect( $field ) && empty( $sanitized[ $field_name ] ) ) {
                    unset( $sanitized[ $field_name ] );
                }
            }

            // The legacy screen posted every field of the section with its std
            // (the field default), so the first save of a field stored it.
            foreach ( $allowed as $field ) {
                if ( empty( $field['name'] ) || array_key_exists( $field['name'], $sanitized ) || ! $this->persists_default( $field ) ) {
                    continue;
                }

                $sanitized[ $field['name'] ] = $this->sanitize_value( $field['default'], $field );
            }

            update_option( $section_id, $sanitized );

            $saved[ $section_id ] = get_option( $section_id, [] );
        }

        $extra = $request->get_param( 'extra' );
        $extra = is_array( $extra ) ? $extra : [];

        /**
         * Fires after the React settings screen saves options.
         *
         * Pro/add-ons hook this to persist settings that live in their OWN
         * option (tax rates, role-based email templates, …) from the `$extra`
         * side-channel payload.
         *
         * @since WPUF_SINCE
         *
         * @param array $saved    Saved values keyed by section id.
         * @param array $incoming Raw incoming section payload.
         * @param array $extra    Custom non-section payload (own-option settings).
         */
        do_action( 'wpuf_settings_saved', $saved, $incoming, $extra );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Settings saved.', 'wp-user-frontend' ),
                'data'    => [ 'values' => $saved ],
            ]
        );
    }

    /**
     * Collapse duplicate named fields within a section.
     *
     * Free registers some fields (e.g. the login-form layout/colors as Pro
     * previews) that Pro then re-registers functionally under the SAME name. The
     * legacy screen rendered the last one; React would render BOTH. Keep a single
     * field per name — the LAST registration (Pro's functional version) wins, at
     * the first occurrence's position. Unnamed fields (section headers, html
     * groupings) are always preserved.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Section field list.
     *
     * @return array
     */
    protected function dedupe_fields( $fields ) {
        $deduped    = [];
        $name_index = [];

        foreach ( $fields as $field ) {
            $name = isset( $field['name'] ) ? $field['name'] : '';

            if ( '' !== $name && isset( $name_index[ $name ] ) ) {
                // Replace the earlier registration in place with the later one.
                $deduped[ $name_index[ $name ] ] = $field;
                continue;
            }

            if ( '' !== $name ) {
                $name_index[ $name ] = count( $deduped );
            }

            $deduped[] = $field;
        }

        return array_values( $deduped );
    }

    /**
     * Find a registered field definition by its name within a section.
     *
     * @since WPUF_SINCE
     *
     * @param array  $fields     Section field definitions.
     * @param string $field_name Field name to look up.
     *
     * @return array|null
     */
    protected function find_field( $fields, $field_name ) {
        foreach ( $fields as $field ) {
            if ( isset( $field['name'] ) && $field['name'] === $field_name ) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Sanitize a single value the same way the legacy settings API would.
     *
     * The value is first put in the shape the legacy form posted (multicheck
     * `{ key: key }` or `''`, multiselect a list), then the field's registered
     * sanitize_callback runs when callable (exact parity). Otherwise a
     * type-appropriate default applies. The REST body is JSON, which WordPress
     * does not slash, so values are not unslashed here.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Raw value.
     * @param array $field Field definition.
     *
     * @return mixed
     */
    protected function sanitize_value( $value, $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        if ( 'multicheck' === $type ) {
            $value = $this->to_multicheck( $value );
        } elseif ( $this->is_multiselect( $field ) ) {
            $value = is_array( $value ) ? array_values( $value ) : [];
        }

        if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
            return call_user_func( $field['sanitize_callback'], $value );
        }

        if ( 'multicheck' === $type || $this->is_multiselect( $field ) ) {
            return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : '';
        }

        switch ( $type ) {
            case 'textarea':
                // Custom CSS keeps its `>` selectors; the other textareas are
                // messages printed as HTML, so their allowed markup is kept.
                return 'custom_css' === $field['name'] ? wp_strip_all_tags( $value ) : wp_kses_post( $value );

            case 'wysiwyg':
            case 'html':
                return wp_kses_post( $value );

            case 'url':
                return esc_url_raw( $value );

            case 'number':
                return $this->sanitize_number( $value );

            default:
                if ( is_array( $value ) ) {
                    return map_deep( $value, 'sanitize_text_field' );
                }

                return sanitize_text_field( $value );
        }
    }

    /**
     * Fire a legacy section hook and return its output, filtered with
     * `wp_kses_post` (form inputs are dropped: the React save does not post them).
     *
     * @since WPUF_SINCE
     *
     * @param string $hook    Hook name.
     * @param array  $section Section definition, the legacy hook argument.
     *
     * @return string
     */
    protected function capture_section_hook( $hook, $section ) {
        ob_start();
        do_action( $hook, $section );

        return trim( wp_kses_post( (string) ob_get_clean() ) );
    }

    /**
     * Whether a field's default is stored on the first save of its section, as
     * the legacy screen's std did. Not for display-only types, secrets,
     * multiselects (empty means absent) or fields without a default.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition.
     *
     * @return bool
     */
    protected function persists_default( $field ) {
        if ( ! isset( $field['default'] ) || '' === $field['default'] || [] === $field['default'] ) {
            return false;
        }

        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        if ( in_array( $type, [ 'html', 'hidden' ], true ) || ! empty( $field['is_pro_preview'] ) ) {
            return false;
        }

        return ! $this->is_secret_field( $field ) && ! $this->is_multiselect( $field ) && empty( $field['callback'] );
    }

    /**
     * Filter the field texts React prints as HTML (`desc`) with `wp_kses_post`,
     * as the legacy screen printed them.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Section field definitions.
     *
     * @return array
     */
    protected function kses_field_texts( $fields ) {
        foreach ( $fields as $index => $field ) {
            if ( isset( $field['desc'] ) && is_string( $field['desc'] ) ) {
                $fields[ $index ]['desc'] = wp_kses_post( $field['desc'] );
            }
        }

        return $fields;
    }

    /**
     * Whether a field is a list multiselect (legacy `wpuf_settings_multiselect`).
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition.
     *
     * @return bool
     */
    protected function is_multiselect( $field ) {
        return ( isset( $field['type'] ) && 'multiselect' === $field['type'] )
            || ( isset( $field['callback'] ) && 'wpuf_settings_multiselect' === $field['callback'] );
    }

    /**
     * Shape a multicheck value like the legacy form posted it: `{ key: key }`
     * for the checked boxes, or `''` (the hidden input) when none is checked.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value List of keys or a `{ key: key }` map.
     *
     * @return array|string
     */
    protected function to_multicheck( $value ) {
        if ( ! is_array( $value ) ) {
            return '';
        }

        $checked = [];

        foreach ( $value as $key ) {
            if ( is_scalar( $key ) && '' !== (string) $key ) {
                $checked[ (string) $key ] = (string) $key;
            }
        }

        return $checked ? $checked : '';
    }

    /**
     * Whether a field holds a secret the legacy screen only shows masked
     * (fields rendered with `wpuf_settings_password_preview`).
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition.
     *
     * @return bool
     */
    protected function is_secret_field( $field ) {
        return isset( $field['callback'] ) && 'wpuf_settings_password_preview' === $field['callback'];
    }

    /**
     * Replace secret values with their masked copy before they leave the server.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $values Stored section values.
     * @param array $fields Section field definitions.
     *
     * @return mixed
     */
    protected function mask_secrets( $values, $fields ) {
        if ( ! is_array( $values ) ) {
            return $values;
        }

        foreach ( $fields as $field ) {
            if ( empty( $field['name'] ) || ! $this->is_secret_field( $field ) || ! isset( $values[ $field['name'] ] ) ) {
                continue;
            }

            $values[ $field['name'] ] = wpuf_settings_mask_secret( $values[ $field['name'] ] );
        }

        return $values;
    }

    /**
     * Whether a section is a Pro-preview (upsell) section.
     *
     * Pro-feature sections are registered by Free_Loader with
     * `is_pro_preview => true`; older ones only carry a pro badge in the title.
     *
     * @since WPUF_SINCE
     *
     * @param array $section Section definition.
     *
     * @return bool
     */
    protected function is_pro_preview_section( $section ) {
        if ( ! empty( $section['is_pro_preview'] ) ) {
            return true;
        }

        return ! empty( $section['title'] )
            && (bool) preg_match( '/pro-icon|pro-badge|pro_badge/i', $section['title'] );
    }

    /**
     * Sanitize a number field while preserving its stored scalar form.
     *
     * Keeps the original string/int form so a no-op save stays byte-identical to
     * existing data — coercing "100" → 100 would silently rewrite every site's
     * stored numbers. Rejects non-numeric and exponential/hex notation (a real
     * number input never sends those); plain integer/decimal strings pass through
     * verbatim.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Raw value.
     *
     * @return mixed Empty string when not numeric, else the verbatim value.
     */
    protected function sanitize_number( $value ) {
        if ( ! is_numeric( $value ) ) {
            return '';
        }

        return preg_match( '/^-?\d+(\.\d+)?$/', (string) $value ) ? $value : ( $value + 0 );
    }
}
