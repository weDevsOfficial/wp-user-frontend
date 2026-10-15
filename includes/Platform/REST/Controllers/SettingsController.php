<?php

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Stores\Normalizers;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\REST\RestController;
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
class SettingsController extends RestController {

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
        // Load the schema functions now, as before (task 2.4c moved them to the store).
        Stores::settings()->load_schema();
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

        $store      = Stores::settings();
        $sections   = $store->sections();
        $raw_fields = $store->fields();

        $values       = [];
        $fields       = [];
        $pro_sections = [];
        $section_html = [];

        foreach ( $sections as $section ) {
            $section_id            = $section['id'];
            $values[ $section_id ] = $store->read( $section_id );

            // Pro-only feature sections (SMS, Social Login, …) are registered by
            // Free_Loader with `is_pro_preview => true`. When Pro is active it
            // re-registers them functionally (flag absent). Use that flag as the
            // source of truth; fall back to the legacy pro-icon title marker.
            if ( $this->is_pro_preview_section( $section ) ) {
                $pro_sections[] = $section_id;
            }

            // One field per name, re-indexed so it serializes as a JSON array
            // (some modules register fields with string keys).
            $fields[ $section_id ] = $this->kses_field_texts( $store->section_fields( $section_id, $raw_fields ) );

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
            $values[ $section_id ] = $store->mask_secrets( $values[ $section_id ], $fields[ $section_id ] );
        }

        $data = [
            'sections'     => array_values( $sections ),
            'fields'       => $fields,
            'ia'           => wpuf_settings_ia(),
            'values'       => $values,
            'pro_sections' => $pro_sections,
            'caps'         => [
                'is_pro'     => wpuf_is_pro_active(),
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

        // The legacy screen posted a form: keep its CRLF line breaks.
        $stored = [];

        foreach ( array_keys( $incoming ) as $section_id ) {
            $stored[ $section_id ] = Stores::settings()->read( sanitize_key( $section_id ) );
        }

        $incoming = Normalizers::form_post_newlines( $incoming, $stored );

        // The settings store applies the legacy sanitize callbacks, writes the
        // section options and fires wpuf_settings_saved (task 2.4c).
        $saved = Stores::settings()->save( $incoming, $request->get_param( 'extra' ) );

        // Secrets go back masked, as the read route sends them.
        $saved = $this->mask_saved_values( $saved );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Settings saved.', 'wp-user-frontend' ),
                'data'    => [ 'values' => $saved ],
            ]
        );
    }

    /**
     * Mask the secrets in the saved section values (password preview fields and
     * the AI provider keys), as `get_items()` does: the real values never leave
     * the server.
     *
     * @since WPUF_SINCE
     *
     * @param array $saved Saved values by section.
     *
     * @return array
     */
    private function mask_saved_values( $saved ) {
        if ( ! is_array( $saved ) ) {
            return $saved;
        }

        $store = Stores::settings();

        foreach ( $saved as $section_id => $values ) {
            $saved[ $section_id ] = $store->mask_secrets( $values, $store->section_fields( $section_id ) );
        }

        if ( isset( $saved['wpuf_ai'] ) && is_array( $saved['wpuf_ai'] ) && function_exists( 'wpuf_settings_mask_secret' ) ) {
            foreach ( [ 'openai', 'anthropic', 'google' ] as $provider ) {
                $key = $provider . '_api_key';

                if ( isset( $saved['wpuf_ai'][ $key ] ) ) {
                    $saved['wpuf_ai'][ $key ] = wpuf_settings_mask_secret( $saved['wpuf_ai'][ $key ], 4 );
                }
            }
        }

        return $saved;
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
}
