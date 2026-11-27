<?php
/**
 * Store normalizers
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

/**
 * Shape rules the stores apply before writing, kept in one place (they were
 * copied between Admin_Form_Builder::save_form() and Form_Settings_Cleanup).
 *
 * @since WPUF_SINCE
 */
class Normalizers {

    /**
     * Pro-only notification settings: stored only while Pro is active.
     */
    const PRO_NOTIFICATION_KEYS = [ 'notification_edit', 'notification_edit_to', 'notification_edit_subject', 'notification_edit_body' ];

    /**
     * Pro-only keys inside `notification`.
     */
    const PRO_NOTIFICATION_SUB_KEYS = [ 'edit', 'edit_to', 'edit_subject', 'edit_body' ];

    /**
     * Form settings as they may be stored: without Pro, the Pro-only
     * notification settings are dropped (top level and inside `notification`).
     *
     * @since WPUF_SINCE
     *
     * @param mixed     $settings   Form settings
     * @param bool|null $pro_active Pro state (null: detect)
     *
     * @return mixed Settings (non-arrays unchanged)
     */
    public static function form_settings( $settings, $pro_active = null ) {
        if ( ! is_array( $settings ) ) {
            return $settings;
        }

        if ( null === $pro_active ) {
            $pro_active = wpuf_is_pro_active();
        }

        if ( $pro_active ) {
            return $settings;
        }

        foreach ( self::PRO_NOTIFICATION_KEYS as $key ) {
            unset( $settings[ $key ] );
        }

        if ( isset( $settings['notification'] ) && is_array( $settings['notification'] ) ) {
            foreach ( self::PRO_NOTIFICATION_SUB_KEYS as $key ) {
                unset( $settings['notification'][ $key ] );
            }
        }

        return $settings;
    }

    /**
     * Whether a list meta write (notifications, integrations) would only turn a
     * stored empty value ('' or no meta) into `[]`, which develop never wrote.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value  Value to store
     * @param mixed $stored Stored value
     *
     * @return bool
     */
    public static function is_empty_list_noop( $value, $stored ) {
        return empty( $value ) && empty( $stored );
    }

    /**
     * Registration forms: the new user status the frontend reads.
     *
     * Develop's builder always posted `wpuf_settings[wpuf_user_status]` (Pro's
     * hidden `approved` input, `pending` while "Require approval" is on), so
     * every saved registration form had it. The frontend needs it: without it
     * Pro's registration skips the auto login (`approved` only) and sends the
     * "pending" status mail. The React builder writes it only when the toggle
     * changes, so a missing value is filled from the toggle on save.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Registration form settings
     *
     * @return array
     */
    public static function registration_user_status( $settings ) {
        if ( ! is_array( $settings ) || isset( $settings['wpuf_user_status'] ) ) {
            return $settings;
        }

        $settings['wpuf_user_status'] = isset( $settings['user_status'] ) && wpuf_is_checkbox_or_toggle_on( $settings['user_status'] ) ? 'pending' : 'approved';

        return $settings;
    }

    /**
     * Multi-step progress bar type of a form with multi-step on.
     *
     * Develop's builder posted the type's native select, whose first option is
     * `progressive`, so a form saved with multi-step on always had it. The
     * frontend falls back to `step_by_step` without it, so a form set up in the
     * React builder (which writes an untouched select only after a pick)
     * showed the other bar. A missing value is filled with develop's
     * `progressive` while multi-step is on (owner decision 2026-10-05).
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Form settings
     *
     * @return array
     */
    public static function multistep_progressbar_type( $settings ) {
        if (
            ! is_array( $settings )
            || isset( $settings['multistep_progressbar_type'] )
            || ! isset( $settings['enable_multistep'] )
            || ! wpuf_is_checkbox_or_toggle_on( $settings['enable_multistep'] )
        ) {
            return $settings;
        }

        $settings['multistep_progressbar_type'] = 'progressive';

        return $settings;
    }

    /**
     * Post form selects whose first option develop always stored.
     *
     * Develop's builder posted these selectize selects, so a saved form always
     * had their first option: Label Position `above`, Choose Payment Option
     * `force_pack_purchase`. The frontend reads a missing Label Position as
     * `left`, so a form saved in the React builder (which writes an untouched
     * select only after a pick) showed its labels beside the inputs instead of
     * above them. Missing keys get develop's first option; the builder shows the
     * same (owner decision 2026-10-08); stored values stay as they are.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Form settings
     *
     * @return array
     */
    public static function post_form_selects( $settings ) {
        if ( ! is_array( $settings ) ) {
            return $settings;
        }

        $first_options = [
            'label_position'        => 'above',
            'choose_payment_option' => 'force_pack_purchase',
        ];

        foreach ( $first_options as $key => $value ) {
            if ( ! isset( $settings[ $key ] ) ) {
                $settings[ $key ] = $value;
            }
        }

        return $settings;
    }

    /**
     * Registration form selects whose first option develop always stored.
     *
     * Develop's registration builder posted Label Position (`above`) and the
     * after-registration / after-profile-update redirects (`same`); the frontend
     * reads a missing Label Position as `left` and a missing redirect as "no
     * message". Missing keys get develop's first option (owner decision
     * 2026-10-08, QA story 15); stored values stay. The builder also fills the
     * MailPoet 3 list (its first list) before the save.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Form settings
     *
     * @return array
     */
    public static function registration_form_selects( $settings ) {
        if ( ! is_array( $settings ) ) {
            return $settings;
        }

        $first_options = [
            'label_position'      => 'above',
            'reg_redirect_to'     => 'same',
            'profile_redirect_to' => 'same',
        ];

        foreach ( $first_options as $key => $value ) {
            if ( ! isset( $settings[ $key ] ) ) {
                $settings[ $key ] = $value;
            }
        }

        return $settings;
    }

    /**
     * Post expiration values the builder shows while nothing is stored.
     *
     * With no `expiration_settings` both builders show expiration on, 7, Day(s),
     * Draft (Pro `Post_Form::form_settings_post_expiration()`: the toggle's and
     * the number's `value`, the selects' first options). Develop's form post
     * stored those on the first save, so posts expired as shown; the React
     * builder writes a row only after an edit, so the switch read on while
     * `Post_Expiration` found no `enable_post_expiration` and never expired
     * the post. Missing keys get the shown values (owner decision 2026-10-08);
     * stored values stay as they are.
     *
     * @since WPUF_SINCE
     *
     * @param array $settings Form settings
     *
     * @return array
     */
    public static function post_expiration( $settings ) {
        if ( ! is_array( $settings ) ) {
            return $settings;
        }

        $expiration = isset( $settings['expiration_settings'] ) && is_array( $settings['expiration_settings'] ) ? $settings['expiration_settings'] : [];
        $shown      = [
            'enable_post_expiration'  => 'on',
            'expiration_time_value'   => '7',
            'expiration_time_type'    => 'day',
            'expired_post_status'     => 'draft',
            'post_expiration_message' => '',
        ];

        foreach ( $shown as $key => $value ) {
            if ( ! isset( $expiration[ $key ] ) ) {
                $expiration[ $key ] = $value;
            }
        }

        $settings['expiration_settings'] = $expiration;

        return $settings;
    }

    /**
     * Line breaks as a browser form post sends them: every line break in an
     * edited string as CRLF. Develop's builder and settings screens posted
     * their textareas as a form, so their stored text has `\r\n`; the React
     * screens send JSON with `\n`. A string whose text equals the stored one
     * keeps the stored bytes, so an untouched save changes nothing (G3).
     *
     * @since WPUF_SINCE
     *
     * @param mixed $value  Value from a React save (string or nested array)
     * @param mixed $stored The stored value at the same place, if any
     *
     * @return mixed
     */
    public static function form_post_newlines( $value, $stored = null ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                $value[ $key ] = self::form_post_newlines( $item, is_array( $stored ) && array_key_exists( $key, $stored ) ? $stored[ $key ] : null );
            }

            return $value;
        }

        if ( ! is_string( $value ) || false === strpbrk( $value, "\r\n" ) ) {
            return $value;
        }

        if ( is_string( $stored ) && str_replace( "\r\n", "\n", $stored ) === str_replace( "\r\n", "\n", $value ) ) {
            return $stored;
        }

        return preg_replace( '/\r\n|\r|\n/', "\r\n", $value );
    }

    /**
     * A wysiwyg field's default the way the legacy screen stored it: its
     * `wp_editor()` (TinyMCE) posted the default back with every line trimmed
     * (the PHP source indentation and trailing spaces gone) and `\r\n` line
     * breaks. Edited bodies come from the editor in both screens; only the
     * untouched default differs without this.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $default Field default
     *
     * @return mixed The normalized string; other types unchanged
     */
    public static function wysiwyg_default( $default ) {
        if ( ! is_string( $default ) ) {
            return $default;
        }

        $lines = array_map( 'trim', preg_split( '/\r\n|\n|\r/', $default ) );

        return trim( implode( "\r\n", $lines ) );
    }
}
