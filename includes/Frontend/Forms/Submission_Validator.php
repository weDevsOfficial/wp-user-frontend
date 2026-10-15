<?php
/**
 * Server-side checks of a form submission
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Forms;

/**
 * The checks the browser makes before a submit (required fields, e-mail,
 * URL, number), made again on the server so a request that skips the form
 * (a scripted POST to the submissions route or admin-ajax) cannot store an
 * empty required field or a malformed value.
 *
 * Only fields the visitor certainly saw are checked: a field with
 * conditional logic may have been hidden, and passive fields (HTML, section
 * breaks, captchas, hidden inputs) have nothing to check.
 *
 * @since WPUF_SINCE
 */
class Submission_Validator {

    /**
     * Templates with nothing a visitor fills in.
     */
    const PASSIVE = [
        'custom_html',
        'section_break',
        'step_start',
        'recaptcha',
        'cloudflare_turnstile',
        'really_simple_captcha',
        'math_captcha',
        'custom_hidden_field',
        'action_hook',
        'shortcode',
        'repeat_field',
        'address_field',
        'google_map',
        'signature_field',
        'qr_code',
        'embed',
        'ratings',
        'country_list_field',
        'phone_field',
        'password',
        'user_avatar',
    ];

    /**
     * Templates that post attachment ids under `wpuf_files[ name ]`.
     */
    const UPLOADS = [ 'image_upload', 'file_upload', 'featured_image', 'profile_photo', 'cover_photo', 'avatar' ];

    /**
     * The first problem with the posted values, '' when there is none.
     *
     * @since WPUF_SINCE
     *
     * @param array $fields Form fields (settings arrays, as Form::get_fields())
     * @param array $posted The posted values (unslashed)
     *
     * @return string Error message
     */
    public function validate( array $fields, array $posted ) {
        foreach ( $this->flatten( $fields ) as $field ) {
            $error = $this->check_field( $field, $posted );

            if ( '' !== $error ) {
                return $error;
            }
        }

        return '';
    }

    /**
     * The fields with column children in line.
     *
     * @param array $fields Fields
     *
     * @return array
     */
    private function flatten( array $fields ) {
        $list = [];

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }

            if ( isset( $field['template'] ) && 'column_field' === $field['template'] && ! empty( $field['inner_fields'] ) && is_array( $field['inner_fields'] ) ) {
                foreach ( $field['inner_fields'] as $column ) {
                    $list = array_merge( $list, $this->flatten( (array) $column ) );
                }

                continue;
            }

            $list[] = $field;
        }

        return $list;
    }

    /**
     * One field's problem, '' when there is none.
     *
     * @param array $field  Field settings
     * @param array $posted Posted values
     *
     * @return string
     */
    private function check_field( array $field, array $posted ) {
        $name     = isset( $field['name'] ) ? (string) $field['name'] : '';
        $template = isset( $field['template'] ) ? (string) $field['template'] : '';
        $type     = isset( $field['input_type'] ) ? (string) $field['input_type'] : '';

        if ( '' === $name || in_array( $template, self::PASSIVE, true ) || 'hidden' === $type || $this->is_conditional( $field ) ) {
            return '';
        }

        $label = isset( $field['label'] ) && '' !== trim( wp_strip_all_tags( (string) $field['label'] ) ) ? trim( wp_strip_all_tags( (string) $field['label'] ) ) : $name;

        if ( in_array( $template, self::UPLOADS, true ) ) {
            $files = isset( $posted['wpuf_files'][ $name ] ) ? (array) $posted['wpuf_files'][ $name ] : [];

            return $this->is_required( $field ) && ! array_filter( array_map( 'absint', $files ) ) ? $this->required_message( $label ) : '';
        }

        $value = array_key_exists( $name, $posted ) ? $posted[ $name ] : null;

        if ( $this->is_empty( $value, $template ) ) {
            return $this->is_required( $field ) ? $this->required_message( $label ) : '';
        }

        if ( is_array( $value ) ) {
            return '';
        }

        $value = trim( (string) $value );

        if ( ( 'email' === $type || 'user_email' === $template ) && ! is_email( $value ) ) {
            /* translators: %s: field label */
            return sprintf( __( '%s: please enter a valid e-mail address.', 'wp-user-frontend' ), $label );
        }

        if ( 'url' === $type && false === filter_var( esc_url_raw( $value ), FILTER_VALIDATE_URL ) ) {
            /* translators: %s: field label */
            return sprintf( __( '%s: please enter a valid URL.', 'wp-user-frontend' ), $label );
        }

        if ( 'numeric_text_field' === $template || 'numeric_text' === $type ) {
            return $this->check_number( $field, $value, $label );
        }

        return '';
    }

    /**
     * A number field's value against its minimum and maximum.
     *
     * @param array  $field Field settings
     * @param string $value Posted value
     * @param string $label Field label
     *
     * @return string
     */
    private function check_number( array $field, $value, $label ) {
        if ( ! is_numeric( $value ) ) {
            /* translators: %s: field label */
            return sprintf( __( '%s: please enter a number.', 'wp-user-frontend' ), $label );
        }

        $number = (float) $value;
        $min    = isset( $field['min_value_field'] ) && is_numeric( $field['min_value_field'] ) ? (float) $field['min_value_field'] : null;
        // 0 is "no maximum" in the field settings, as the classic input printed it.
        $max = isset( $field['max_value_field'] ) && is_numeric( $field['max_value_field'] ) && 0.0 !== (float) $field['max_value_field'] ? (float) $field['max_value_field'] : null;

        if ( null !== $min && $number < $min ) {
            /* translators: 1: field label, 2: minimum */
            return sprintf( __( '%1$s: the value must be at least %2$s.', 'wp-user-frontend' ), $label, $field['min_value_field'] );
        }

        if ( null !== $max && $number > $max ) {
            /* translators: 1: field label, 2: maximum */
            return sprintf( __( '%1$s: the value must be at most %2$s.', 'wp-user-frontend' ), $label, $field['max_value_field'] );
        }

        return '';
    }

    /**
     * Whether a posted value is empty: missing, blank text, the select
     * placeholder (-1), or an array without one non-empty value. '0' is a value.
     *
     * @param mixed  $value    Posted value
     * @param string $template Field template
     *
     * @return bool
     */
    private function is_empty( $value, $template ) {
        if ( null === $value ) {
            return true;
        }

        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                if ( ! $this->is_empty( $item, $template ) ) {
                    return false;
                }
            }

            return true;
        }

        $value = trim( wp_strip_all_tags( (string) $value ) );

        return '' === $value || '-1' === $value;
    }

    /**
     * Whether the field is required.
     *
     * @param array $field Field settings
     *
     * @return bool
     */
    private function is_required( array $field ) {
        return isset( $field['required'] ) && 'yes' === $field['required'];
    }

    /**
     * Whether conditional logic may hide the field.
     *
     * @param array $field Field settings
     *
     * @return bool
     */
    private function is_conditional( array $field ) {
        return isset( $field['wpuf_cond']['condition_status'] ) && 'yes' === $field['wpuf_cond']['condition_status'];
    }

    /**
     * The "required" message.
     *
     * @param string $label Field label
     *
     * @return string
     */
    private function required_message( $label ) {
        /* translators: %s: field label */
        return sprintf( __( '%s is required.', 'wp-user-frontend' ), $label );
    }
}
