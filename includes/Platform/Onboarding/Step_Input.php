<?php
/**
 * The values a wizard step sent
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Onboarding;

/**
 * Read access to one step's submitted values (the REST body's `values`,
 * already unslashed), each read sanitized for what it is: a ticked box, a
 * text value or a list of keys.
 *
 * @since WPUF_SINCE
 */
class Step_Input {

    /**
     * Submitted values.
     *
     * @var array
     */
    private $values;

    /**
     * @since WPUF_SINCE
     *
     * @param array $values Submitted values (unslashed)
     */
    public function __construct( array $values = [] ) {
        $this->values = $values;
    }

    /**
     * Whether a box was ticked (any non-empty value).
     *
     * @since WPUF_SINCE
     *
     * @param string $key Field name
     *
     * @return bool
     */
    public function flag( $key ) {
        return ! empty( $this->values[ $key ] );
    }

    /**
     * A text value.
     *
     * @since WPUF_SINCE
     *
     * @param string $key      Field name
     * @param string $fallback When missing or not a scalar
     *
     * @return string
     */
    public function text( $key, $fallback = '' ) {
        if ( ! isset( $this->values[ $key ] ) || ! is_scalar( $this->values[ $key ] ) ) {
            return $fallback;
        }

        return sanitize_text_field( (string) $this->values[ $key ] );
    }

    /**
     * A list of keys.
     *
     * @since WPUF_SINCE
     *
     * @param string $key Field name
     *
     * @return string[]
     */
    public function keys( $key ) {
        if ( empty( $this->values[ $key ] ) ) {
            return [];
        }

        return array_values( array_map( 'sanitize_key', array_filter( (array) $this->values[ $key ], 'is_scalar' ) ) );
    }

    /**
     * Every submitted value, for a step handler added by the
     * `wpuf_onboarding_steps` filter.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function all() {
        return $this->values;
    }
}
