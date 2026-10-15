<?php
/**
 * Settings store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

/**
 * Reads and writes the settings section options (`wpuf_general`, …) with the
 * schema registered through `wpuf_settings_sections` / `wpuf_settings_fields`,
 * applying each field's legacy sanitize callback (parity with
 * WeDevs_Settings_API::sanitize_options()).
 *
 * @since WPUF_SINCE
 */
class SettingsStore {

    /**
     * Whether the schema functions are loaded.
     *
     * @var bool
     */
    private $schema_loaded = false;

    /**
     * Registered sections.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function sections() {
        $this->load_schema();

        return wpuf_settings_sections();
    }

    /**
     * Registered fields of every section, keyed by section id (raw schema).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function fields() {
        $this->load_schema();

        return wpuf_settings_fields();
    }

    /**
     * Fields of one section, one per name (see dedupe_fields()).
     *
     * @since WPUF_SINCE
     *
     * @param string     $section_id Section id
     * @param array|null $schema     Raw schema (fields()), to avoid rebuilding it
     *
     * @return array
     */
    public function section_fields( $section_id, $schema = null ) {
        $schema = null === $schema ? $this->fields() : $schema;
        $fields = isset( $schema[ $section_id ] ) && is_array( $schema[ $section_id ] ) ? array_values( $schema[ $section_id ] ) : [];

        return $this->dedupe_fields( $fields );
    }

    /**
     * Stored values of a section.
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id Section id
     * @param bool   $masked     Replace secrets with their masked copy
     * @param array  $fields     Section fields (needed when masking)
     *
     * @return mixed
     */
    public function read( $section_id, $masked = false, $fields = [] ) {
        $values = get_option( $section_id, [] );

        return $masked ? $this->mask_secrets( $values, $fields ) : $values;
    }

    /**
     * Save the changed fields of known sections and fire `wpuf_settings_saved`.
     * Unknown sections and fields are ignored, so a stale client cannot write
     * arbitrary option keys.
     *
     * @since WPUF_SINCE
     *
     * @param array $incoming Changed values keyed by section id
     * @param array $extra    Own-option settings for listeners (tax rates, …)
     *
     * @return array Saved values keyed by section id
     */
    public function save( $incoming, $extra = [] ) {
        $section_ids = wp_list_pluck( $this->sections(), 'id' );
        $schema      = $this->fields();
        $saved       = [];

        foreach ( $incoming as $section_id => $section_values ) {
            if ( ! in_array( $section_id, $section_ids, true ) || ! is_array( $section_values ) ) {
                continue;
            }

            // A whole section can be a Pro preview (SMS, social login without Pro).
            $section = current( wp_list_filter( $this->sections(), [ 'id' => $section_id ] ) );

            if ( is_array( $section ) && $this->is_pro_preview( $section, 'title' ) ) {
                continue;
            }

            $saved[ $section_id ] = $this->save_section( $section_id, $section_values, $this->section_fields( $section_id, $schema ) );
        }

        /**
         * Fires after the settings screen saves options.
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
        do_action( 'wpuf_settings_saved', $saved, $incoming, is_array( $extra ) ? $extra : [] );

        return $saved;
    }

    /**
     * Merge changed fields into a section option. Fields the section never
     * stored get their default (the legacy form posted every std).
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id     Section id
     * @param array  $section_values Changed values
     * @param array  $allowed        Section fields (section_fields())
     *
     * @return mixed The stored option after the save
     */
    public function save_section( $section_id, $section_values, $allowed ) {
        $existing  = get_option( $section_id, [] );
        $existing  = is_array( $existing ) ? $existing : [];
        $sanitized = $existing;

        foreach ( $section_values as $field_name => $value ) {
            $field = $this->find_field( $allowed, $field_name );

            // Only persist fields that are actually registered for this section.
            if ( null === $field ) {
                continue;
            }

            // A Pro preview (shown without Pro) is display only: the legacy
            // screen never posted it, so nothing is stored for it.
            if ( $this->is_pro_preview( $field ) ) {
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

            // A posted form value is a string: a numeric default (`7`) was
            // stored as `"7"` by the legacy screen.
            $default = is_int( $field['default'] ) || is_float( $field['default'] ) ? (string) $field['default'] : $field['default'];

            // The legacy editor posted a wysiwyg default trimmed, with \r\n breaks.
            if ( isset( $field['type'] ) && 'wysiwyg' === $field['type'] ) {
                $default = Normalizers::wysiwyg_default( $default );
            }

            $sanitized[ $field['name'] ] = $this->sanitize_value( $default, $field );
        }

        update_option( $section_id, $sanitized );

        return get_option( $section_id, [] );
    }

    /**
     * Write a whole section option as given (onboarding, installer, upgrades)
     * and fire `wpuf_settings_saved`. The caller builds the values; nothing is
     * sanitized or merged here, so the stored bytes are what it wrote before.
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id Section option name
     * @param mixed  $values     Section values
     *
     * @return mixed The stored option after the write
     */
    public function write_section( $section_id, $values ) {
        update_option( $section_id, $values );

        $saved = get_option( $section_id, [] );

        /** This action is documented in includes/Platform/Stores/SettingsStore.php */
        do_action( 'wpuf_settings_saved', [ $section_id => $saved ], [ $section_id => $values ], [] );

        return $saved;
    }

    /**
     * Delete a section option (Tools > Reset Settings).
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id Section option name
     *
     * @return bool Whether an option was deleted
     */
    public function delete_section( $section_id ) {
        return delete_option( $section_id );
    }

    /**
     * One key of a section option, or the default when it is not stored
     * (wpuf_get_option(): same keys, same fallback).
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id Section option name
     * @param string $key        Field name
     * @param mixed  $fallback   Value when the key is not stored
     *
     * @return mixed
     */
    public function get( $section_id, $key, $fallback = '' ) {
        return wpuf_get_option( $key, $section_id, $fallback );
    }

    /**
     * A WordPress option the plugin reads (`sticky_posts`,
     * `users_can_register`, `default_post_edit_rows`; on multisite
     * `registration` from the network options).
     *
     * @since WPUF_SINCE
     *
     * @param string $name     Option name
     * @param mixed  $fallback Value when the option is not stored
     * @param bool   $network  Read the network option (get_site_option())
     *
     * @return mixed
     */
    public function wp_option( $name, $fallback = false, $network = false ) {
        return $network ? get_site_option( $name, $fallback ) : get_option( $name, $fallback );
    }

    /**
     * Write a plain option (notice dismissals and other flags kept as their
     * own option, not in a settings section).
     *
     * @since WPUF_SINCE
     *
     * @param string $name  Option name
     * @param mixed  $value Value
     *
     * @return bool
     */
    public function set_wp_option( $name, $value ) {
        return update_option( $name, $value );
    }

    /**
     * Set one key of a section option (wpuf_update_option()).
     *
     * @since WPUF_SINCE
     *
     * @param string $section_id Section option name
     * @param string $key        Field name
     * @param mixed  $value      Value
     *
     * @return mixed The stored option after the write
     */
    public function set_value( $section_id, $key, $value ) {
        $options = get_option( $section_id );

        if ( ! is_array( $options ) ) {
            $options = [];
        }

        $options[ $key ] = $value;

        return $this->write_section( $section_id, $options );
    }

    /**
     * Route the legacy settings screen (options.php) through the store: its
     * sections are sanitized by sanitize_legacy_section() instead of
     * WeDevs_Settings_API::sanitize_options() (same rules), and a save of a
     * section from that screen fires `wpuf_settings_saved`.
     *
     * @since WPUF_SINCE
     *
     * @param object $settings_api The WeDevs_Settings_API instance that registered the sections
     * @param array  $sections     Registered sections
     *
     * @return void
     */
    public function hook_legacy_screen( $settings_api, $sections ) {
        foreach ( $sections as $section ) {
            if ( empty( $section['id'] ) ) {
                continue;
            }

            remove_filter( 'sanitize_option_' . $section['id'], [ $settings_api, 'sanitize_options' ] );
            add_filter( 'sanitize_option_' . $section['id'], [ $this, 'sanitize_legacy_section' ] );
            add_action( 'update_option_' . $section['id'], [ $this, 'legacy_section_saved' ], 10, 3 );
        }
    }

    /**
     * Sanitize a section posted by the legacy screen, as
     * WeDevs_Settings_API::sanitize_options() did: each posted key whose
     * field (first registration of that name in any section) has a callable
     * sanitize_callback runs through it; other keys are kept as posted.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $options Posted section values
     *
     * @return mixed
     */
    public function sanitize_legacy_section( $options ) {
        if ( ! $options || ! is_array( $options ) ) {
            return $options;
        }

        $schema = $this->fields();

        foreach ( $options as $option_slug => $option_value ) {
            $callback = $this->legacy_sanitize_callback( $schema, $option_slug );

            if ( $callback ) {
                $options[ $option_slug ] = call_user_func( $callback, $option_value );
            }
        }

        return $options;
    }

    /**
     * Fire `wpuf_settings_saved` after the legacy screen saved a section
     * (only for the section posted to options.php, not for other writes).
     *
     * @since WPUF_SINCE
     *
     * @param mixed  $old_value Previous value
     * @param mixed  $value     Saved value
     * @param string $option    Option name
     *
     * @return void
     */
    public function legacy_section_saved( $old_value, $value, $option ) {
        global $pagenow;

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- options.php verified the option page nonce before saving.
        $option_page = isset( $_POST['option_page'] ) ? sanitize_key( wp_unslash( $_POST['option_page'] ) ) : '';

        if ( 'options.php' !== $pagenow || $option_page !== $option ) {
            return;
        }

        /** This action is documented in includes/Platform/Stores/SettingsStore.php */
        do_action( 'wpuf_settings_saved', [ $option => $value ], [ $option => $value ], [] );
    }

    /**
     * Collapse duplicate named fields within a section: the last registration
     * (Pro's functional field) wins, at the first one's position. Unnamed
     * fields (headers, html groupings) are kept.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Section field list
     *
     * @return array
     */
    public function dedupe_fields( $fields ) {
        $deduped    = [];
        $name_index = [];

        foreach ( $fields as $field ) {
            $name = isset( $field['name'] ) ? $field['name'] : '';

            if ( '' !== $name && isset( $name_index[ $name ] ) ) {
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
     * Find a field definition by name.
     *
     * @since WPUF_SINCE
     *
     * @param array  $fields     Section fields
     * @param string $field_name Field name
     *
     * @return array|null
     */
    public function find_field( $fields, $field_name ) {
        foreach ( $fields as $field ) {
            if ( isset( $field['name'] ) && $field['name'] === $field_name ) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Sanitize a value as the legacy settings API did: shape it like the
     * legacy form posted it (multicheck `{ key: key }` or `''`, multiselect a
     * list), then run the field's sanitize_callback, else a type default. JSON
     * bodies are not slashed, so values are not unslashed.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value Raw value
     * @param array $field Field definition
     *
     * @return mixed
     */
    public function sanitize_value( $value, $field ) {
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
     * Whether a field (or section) is a Pro preview: registered by the free
     * plugin with `is_pro_preview`, or, without Pro, carrying the Pro badge in
     * its label (older registrations). Pro registers the working version
     * without either.
     *
     * @since WPUF_SINCE
     *
     * @param array  $item      Field or section definition
     * @param string $label_key Key holding the label ('label' for fields, 'title' for sections)
     *
     * @return bool
     */
    public function is_pro_preview( $item, $label_key = 'label' ) {
        if ( ! empty( $item['is_pro_preview'] ) ) {
            return true;
        }

        return ! wpuf_is_pro_active()
            && ! empty( $item[ $label_key ] )
            && is_string( $item[ $label_key ] )
            && (bool) preg_match( '/pro-icon|pro-badge|pro_badge/i', $item[ $label_key ] );
    }

    /**
     * Whether a field's default is stored on the first save of its section.
     * Not for display-only types, Pro previews, secrets, multiselects or
     * fields without a default.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition
     *
     * @return bool
     */
    public function persists_default( $field ) {
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
     * Whether a field is a list multiselect (legacy `wpuf_settings_multiselect`).
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition
     *
     * @return bool
     */
    public function is_multiselect( $field ) {
        return ( isset( $field['type'] ) && 'multiselect' === $field['type'] )
            || ( isset( $field['callback'] ) && 'wpuf_settings_multiselect' === $field['callback'] );
    }

    /**
     * Whether a field holds a secret the screens only show masked.
     *
     * @since WPUF_SINCE
     *
     * @param array $field Field definition
     *
     * @return bool
     */
    public function is_secret_field( $field ) {
        return isset( $field['callback'] ) && 'wpuf_settings_password_preview' === $field['callback'];
    }

    /**
     * Replace secret values with their masked copy.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $values Stored section values
     * @param array $fields Section fields
     *
     * @return mixed
     */
    public function mask_secrets( $values, $fields ) {
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
     * Load the settings schema functions (also outside wp-admin, e.g. REST).
     * Runs once; the schema readers call it themselves.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_schema() {
        if ( $this->schema_loaded ) {
            return;
        }

        $this->schema_loaded = true;

        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-options.php' );
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-screen.php' );

        // The settings schema (and Pro role filters) call get_editable_roles(),
        // which lives in wp-admin and is not loaded during REST requests.
        if ( ! function_exists( 'get_editable_roles' ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
    }

    /**
     * The sanitize callback WeDevs_Settings_API::get_sanitize_callback() found
     * for a slug: the first field with that name in any section, if callable.
     *
     * @param array  $schema Raw schema (fields())
     * @param string $slug   Posted key
     *
     * @return callable|false
     */
    private function legacy_sanitize_callback( $schema, $slug ) {
        if ( empty( $slug ) ) {
            return false;
        }

        foreach ( $schema as $fields ) {
            foreach ( (array) $fields as $field ) {
                if ( ! isset( $field['name'] ) || (string) $field['name'] !== (string) $slug ) {
                    continue;
                }

                return isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : false;
            }
        }

        return false;
    }

    /**
     * Multicheck value as the legacy form posted it: `{ key: key }` for the
     * checked boxes, or `''` when none is checked.
     *
     * @param mixed $value List of keys or a `{ key: key }` map
     *
     * @return array|string
     */
    private function to_multicheck( $value ) {
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
     * A number keeps its stored scalar form (a no-op save stays byte
     * identical); non-numeric becomes '', exponent/hex forms are normalised.
     *
     * @param mixed $value Raw value
     *
     * @return mixed
     */
    private function sanitize_number( $value ) {
        if ( ! is_numeric( $value ) ) {
            return '';
        }

        return preg_match( '/^-?\d+(\.\d+)?$/', (string) $value ) ? $value : ( $value + 0 );
    }
}
