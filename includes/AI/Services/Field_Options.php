<?php
/**
 * AI form builder: field options service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\AI\Services;

use WeDevs\Wpuf\AI\FormGenerator;
use WP_Error;

/**
 * Generating the options of a choice field (dropdown, radio, checkbox,
 * multiselect) from a prompt. Shared by the REST route and the builder's
 * AJAX action; takes plain arguments.
 *
 * @since WPUF_SINCE Moved out of AI\RestController.
 */
class Field_Options {

    /**
     * The provider client.
     *
     * @var FormGenerator
     */
    protected $form_generator;

    /**
     * @since WPUF_SINCE
     *
     * @param FormGenerator $form_generator The provider client.
     */
    public function __construct( FormGenerator $form_generator ) {
        $this->form_generator = $form_generator;
    }

    /**
     * Generate the options of a field.
     *
     * @since WPUF_SINCE
     *
     * @param string $prompt The prompt
     * @param array  $args   {
     *     @type string $field_type    dropdown_field | radio_field | checkbox_field | multiple_select
     *     @type string $output_format one_per_line | value_label
     *     @type string $tone          casual | formal | professional | friendly
     *     @type int    $max_options   1..100
     * }
     *
     * @return array|WP_Error Sanitized options (`[ [ 'label', 'value' ], ... ]`) or the error (status in its data)
     */
    public function generate( $prompt, array $args = [] ) {
        $args = wp_parse_args(
            $args,
            [
                'field_type'    => 'dropdown_field',
                'output_format' => 'one_per_line',
                'tone'          => 'casual',
                'max_options'   => 20,
            ]
        );

        $max_options = (int) $args['max_options'];

        if ( $max_options < 1 || $max_options > 100 ) {
            return new WP_Error(
                'invalid_max_options',
                __( 'Maximum options must be between 1 and 100', 'wp-user-frontend' ),
                [ 'status' => 400 ]
            );
        }

        try {
            $result = $this->form_generator->generate_field_options(
                $prompt,
                [
                    'field_type'    => $args['field_type'],
                    'output_format' => $args['output_format'],
                    'tone'          => $args['tone'],
                    'max_options'   => $max_options,
                ]
            );
        } catch ( \Exception $e ) {
            return new WP_Error(
                'generation_error',
                __( 'An error occurred while generating options. Please try again.', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Both `error => true` and `success => false` are failures.
        if ( ( isset( $result['error'] ) && $result['error'] ) || ( isset( $result['success'] ) && ! $result['success'] ) ) {
            return new WP_Error(
                'generation_failed',
                isset( $result['message'] ) ? $result['message'] : __( 'Failed to generate field options', 'wp-user-frontend' ),
                [ 'status' => 400 ]
            );
        }

        return $this->sanitize( isset( $result['options'] ) ? $result['options'] : [] );
    }

    /**
     * Sanitize generated options.
     *
     * @since WPUF_SINCE
     *
     * @param array $options Raw options from the provider: `[ 'label', 'value' ]` pairs or strings
     *
     * @return array Indexed `[ 'label' => ..., 'value' => ... ]` list
     */
    public function sanitize( $options ) {
        if ( ! is_array( $options ) ) {
            return [];
        }

        $sanitized = [];

        foreach ( $options as $option ) {
            if ( is_array( $option ) && isset( $option['label'] ) && isset( $option['value'] ) ) {
                $sanitized[] = [
                    'label' => sanitize_text_field( $option['label'] ),
                    'value' => sanitize_key( $option['value'] ),
                ];
            } elseif ( is_string( $option ) ) {
                $sanitized[] = [
                    'label' => sanitize_text_field( $option ),
                    'value' => sanitize_key( strtolower( str_replace( ' ', '_', $option ) ) ),
                ];
            }
        }

        return $sanitized;
    }
}
