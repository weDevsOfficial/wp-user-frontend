<?php

namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Settings REST controller.
 *
 * Powers the React settings screen. Reads and writes the SAME WordPress options
 * and field keys the legacy WeDevs_Settings_API screen uses, applying the SAME
 * per-field sanitize callbacks, so existing user data is never mismatched.
 *
 * @since WPUF_SINCE
 */
class Settings extends WP_REST_Controller {

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
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-react.php' );

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

            $fields[ $section_id ] = $this->dedupe_fields( $section_fields );
        }

        $data = [
            'sections'     => array_values( $sections ),
            'fields'       => $fields,
            'ia'           => wpuf_settings_react_ia(),
            'values'       => $values,
            'pro_sections' => $pro_sections,
            'caps'         => [
                'is_pro'     => class_exists( 'WP_User_Frontend_Pro' ),
                'can_manage' => current_user_can( wpuf_admin_role() ),
            ],
            'modules'      => wpuf_settings_react_modules(),
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
     * Persists each posted section to its own option, applying the exact
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

                $sanitized[ $field_name ] = $this->sanitize_value( $value, $field );
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
     * Uses the field's registered sanitize_callback when callable (exact
     * parity). Otherwise falls back to a type-appropriate default so values
     * are never stored more loosely than the legacy screen allowed.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Raw value.
     * @param array $field Field definition.
     *
     * @return mixed
     */
    protected function sanitize_value( $value, $field ) {
        if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
            return call_user_func( $field['sanitize_callback'], $value );
        }

        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        switch ( $type ) {
            case 'checkbox':
            case 'multicheck':
                return is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : sanitize_text_field( wp_unslash( $value ) );

            case 'multiselect':
                return is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : [];

            case 'textarea':
                return sanitize_textarea_field( wp_unslash( $value ) );

            case 'wysiwyg':
            case 'html':
                return wp_kses_post( wp_unslash( $value ) );

            case 'url':
                return esc_url_raw( wp_unslash( $value ) );

            case 'number':
                return $this->sanitize_number( $value );

            default:
                if ( is_array( $value ) ) {
                    return map_deep( wp_unslash( $value ), 'sanitize_text_field' );
                }

                return sanitize_text_field( wp_unslash( $value ) );
        }
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
