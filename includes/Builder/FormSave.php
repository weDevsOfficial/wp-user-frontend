<?php
/**
 * Form builder save
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Builder;

use WeDevs\Wpuf\Platform\Stores\Stores;

use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder;
use WeDevs\Wpuf\Platform\Stores\Normalizers;
use WP_Error;

/**
 * Saves a form from the builder payload: the one implementation behind the
 * REST route `wpuf/v1/admin/forms/{id}` (the builder). While it saves, `$_POST`
 * holds the payload the old AJAX save posted (set_legacy_request()), for the
 * listeners of the `wpuf_form_builder_save_form` hook. Callers check the request
 * (nonce, capability) first; this class validates the payload, normalizes it
 * like develop's form post and writes through the form store.
 *
 * @since WPUF_SINCE
 */
class FormSave {

    /**
     * Save a form.
     *
     * @since WPUF_SINCE
     *
     * @param array $post_data Unslashed request body (`form_fields`, `notifications`,
     *                         `settings`, `integrations`, `legacy_settings`,
     *                         `legacy_settings_keys`, all JSON / query strings;
     *                         `touched` '1' when the builder was edited)
     * @param array $form_data The builder's form fields (`wpuf_form_id`,
     *                         `form_settings_key`, `post_title`, `wpuf_settings`)
     *
     * @return array|WP_Error `form_fields` and `form_settings` as saved, or an error
     */
    public function save( array $post_data, array $form_data ) {
        if ( empty( $form_data['wpuf_form_id'] ) ) {
            return new WP_Error( 'wpuf_form_invalid_id', __( 'Invalid form id', 'wp-user-frontend' ) );
        }

        $form_id = absint( $form_data['wpuf_form_id'] );

        /**
         * Post types the form builder may save into.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $post_types Builder form post types.
         */
        $allowed_post_types = (array) apply_filters( 'wpuf_form_builder_save_post_types', [ 'wpuf_forms', 'wpuf_profile' ] );

        if ( ! in_array( get_post_type( $form_id ), $allowed_post_types, true ) ) {
            return new WP_Error( 'wpuf_form_invalid_id', __( 'Invalid form id', 'wp-user-frontend' ) );
        }

        /**
         * Meta keys the form builder may store form settings under.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $keys Settings meta keys.
         */
        $allowed_settings_keys = (array) apply_filters( 'wpuf_form_builder_settings_meta_keys', [ 'wpuf_form_settings' ] );
        $form_settings_key     = isset( $form_data['form_settings_key'] ) ? sanitize_key( $form_data['form_settings_key'] ) : '';

        if ( ! in_array( $form_settings_key, $allowed_settings_keys, true ) ) {
            return new WP_Error( 'wpuf_form_invalid_settings', __( 'Invalid form settings', 'wp-user-frontend' ) );
        }

        $form_fields   = isset( $post_data['form_fields'] ) ? $post_data['form_fields'] : '';
        $notifications = isset( $post_data['notifications'] ) ? $post_data['notifications'] : '';
        $settings      = [];
        // Null = not sent: the builder has no integrations editor, so the stored ones stay.
        $integrations = null;

        if ( isset( $post_data['settings'] ) ) {
            // Develop posted these as a form: keep its CRLF line breaks.
            $settings = Normalizers::form_post_newlines( json_decode( $post_data['settings'], true ), Stores::forms()->read_meta( $form_id, $form_settings_key ) );
        } else {
            $settings = isset( $form_data['wpuf_settings'] ) ? $form_data['wpuf_settings'] : [];
        }

        // Settings other plugins printed on the builder hooks (legacy slots): the
        // posted value wins for the keys those slots own, as develop's form post.
        if ( isset( $post_data['legacy_settings_keys'] ) ) {
            $legacy_data = [];

            if ( isset( $post_data['legacy_settings'] ) && is_string( $post_data['legacy_settings'] ) ) {
                parse_str( $post_data['legacy_settings'], $legacy_data );
            }

            $settings = HookBridge::merge_posted_settings(
                $settings,
                $legacy_data,
                json_decode( (string) $post_data['legacy_settings_keys'], true )
            );
        }

        if ( isset( $post_data['integrations'] ) ) {
            $integrations = Normalizers::form_post_newlines( json_decode( $post_data['integrations'], true ), Stores::forms()->read_meta( $form_id, 'integrations' ) );
        }

        $form_fields   = json_decode( $form_fields, true );
        $notifications = json_decode( $notifications, true );

        // Edited save (the builder was changed): the values the screen shows while
        // nothing is stored are stored, as develop's form post did. An untouched
        // save changes nothing stored (G3); a new form has them from its creation.
        $touched = ! empty( $post_data['touched'] );

        // Post form selects develop's form post always stored (their first option).
        if ( $touched && 'wpuf_forms' === get_post_type( $form_id ) ) {
            $settings = Normalizers::post_form_selects( $settings );
        }

        // Values develop's form post always stored (Pro): the registration new
        // user status, the post expiration values the screen shows, and the
        // multi-step bar type (its select's first option).
        if ( wpuf_is_pro_active() ) {
            if ( $touched && 'wpuf_profile' === get_post_type( $form_id ) ) {
                $settings = Normalizers::registration_user_status( $settings );
                $settings = Normalizers::registration_form_selects( $settings );
            }

            if ( $touched && 'wpuf_forms' === get_post_type( $form_id ) ) {
                $settings = Normalizers::post_expiration( $settings );
            }

            if ( $touched ) {
                $settings = Normalizers::multistep_progressbar_type( $settings );
            }
        }

        // Server-side validation for fallback PPP cost
        if ( $this->fallback_ppp_cost_missing( $settings ) ) {
            return new WP_Error( 'wpuf_form_ppp_cost_required', __( 'Cost for each additional post after pack limit is reached is required when Pay-per-post billing when limit exceeds is enabled.', 'wp-user-frontend' ) );
        }

        // Server-side validation for the pay-per-post charge (the builder checks it too)
        if ( $this->pay_per_post_cost_missing( $settings ) ) {
            return new WP_Error( 'wpuf_form_ppp_cost_required', __( 'Charge for each post is required when Pay as you post is selected.', 'wp-user-frontend' ) );
        }

        $data = [
            'form_id'           => $form_id,
            'post_title'        => sanitize_text_field( isset( $form_data['post_title'] ) ? $form_data['post_title'] : '' ),
            'form_fields'       => $form_fields,
            'form_settings'     => $settings,
            'form_settings_key' => $form_settings_key,
            'notifications'     => $notifications,
            'integrations'      => $integrations,
        ];

        $restore = $this->set_legacy_request( $post_data, $form_data, $settings );

        try {
            $saved_fields = Admin_Form_Builder::save_form( $data );
        } finally {
            $this->restore_request( $restore );
        }

        return [
            'form_fields'   => $saved_fields,
            'form_settings' => $settings,
        ];
    }

    /**
     * Give the save the request develop's builder sent, for code that reads it on
     * `save_post` (e.g. the Pro BuddyPress module reads `wpuf_settings` from
     * `$_REQUEST['form_data']`): develop posted the whole builder form, so
     * `form_data` carried every `wpuf_settings[...]` input; the React builder
     * sends the settings as JSON. Values are slashed as WordPress slashes
     * request data. Restored after the save (restore_request()).
     *
     * @param array $post_data Unslashed body
     * @param array $form_data Builder form fields
     * @param array $settings  Settings as saved
     *
     * @return array Previous values to restore: [ superglobal => [ key => value|null ] ]
     */
    private function set_legacy_request( array $post_data, array $form_data, $settings ) {
        $legacy_form_data = $form_data;

        if ( is_array( $settings ) ) {
            $legacy_form_data['wpuf_settings'] = $settings;
        }

        $values = [
            'action'        => 'wpuf_form_builder_save_form',
            'form_data'     => http_build_query( $legacy_form_data ),
            'form_fields'   => isset( $post_data['form_fields'] ) ? $post_data['form_fields'] : '',
            'notifications' => isset( $post_data['notifications'] ) ? $post_data['notifications'] : '',
        ];

        $previous = [
            'post'    => [],
            'request' => [],
        ];

        foreach ( $values as $key => $value ) {
            // phpcs:disable WordPress.Security.NonceVerification -- request was verified by the caller (REST permission or AJAX nonce); this only restores develop's request shape.
            $previous['post'][ $key ]    = array_key_exists( $key, $_POST ) ? $_POST[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            $previous['request'][ $key ] = array_key_exists( $key, $_REQUEST ) ? $_REQUEST[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            $_POST[ $key ]               = wp_slash( $value );
            $_REQUEST[ $key ]            = wp_slash( $value );
            // phpcs:enable WordPress.Security.NonceVerification
        }

        return $previous;
    }

    /**
     * Put the request back as it was before set_legacy_request().
     *
     * @param array $previous Previous values
     *
     * @return void
     */
    private function restore_request( array $previous ) {
        foreach ( $previous['post'] as $key => $value ) {
            if ( null === $value ) {
                unset( $_POST[ $key ] );
            } else {
                $_POST[ $key ] = $value;
            }
        }

        foreach ( $previous['request'] as $key => $value ) {
            if ( null === $value ) {
                unset( $_REQUEST[ $key ] );
            } else {
                $_REQUEST[ $key ] = $value;
            }
        }
    }

    /**
     * Whether the fallback pay-per-post cost is required but empty
     *
     * @param array $settings Form settings
     *
     * @return bool True if the cost is required but empty
     */
    private function fallback_ppp_cost_missing( $settings ) {
        // Check if payment options are enabled
        if ( empty( $settings['payment_options'] ) || ! wpuf_is_checkbox_or_toggle_on( $settings['payment_options'] ) ) {
            return false;
        }

        // Check if force pack purchase is selected
        if ( empty( $settings['choose_payment_option'] ) || 'force_pack_purchase' !== $settings['choose_payment_option'] ) {
            return false;
        }

        // Check if fallback PPP is enabled
        if ( empty( $settings['fallback_ppp_enable'] ) || ! wpuf_is_checkbox_or_toggle_on( $settings['fallback_ppp_enable'] ) ) {
            return false;
        }

        // Check if fallback cost is provided
        if ( empty( $settings['fallback_ppp_cost'] ) || floatval( $settings['fallback_ppp_cost'] ) <= 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Whether the pay-per-post charge is required but empty (or 0)
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Form settings
     *
     * @return bool True if the charge is required but empty
     */
    private function pay_per_post_cost_missing( $settings ) {
        if ( empty( $settings['payment_options'] ) || ! wpuf_is_checkbox_or_toggle_on( $settings['payment_options'] ) ) {
            return false;
        }

        if ( empty( $settings['choose_payment_option'] ) || 'enable_pay_per_post' !== $settings['choose_payment_option'] ) {
            return false;
        }

        return empty( $settings['pay_per_post_cost'] ) || floatval( $settings['pay_per_post_cost'] ) <= 0;
    }
}
