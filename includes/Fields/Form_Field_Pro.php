<?php

namespace WeDevs\Wpuf\Fields;

use WeDevs\Wpuf\Free\Pro_Prompt;

class Form_Field_Pro extends Field_Contract {

    /**
     * Render a per-field notice when Pro is inactive so the form shows a clear message instead of a placeholder.
     *
     * @param array  $field_settings
     * @param int    $form_id
     * @param string $type
     * @param int    $post_id
     *
     * @return void
     */
    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $field_name = ! empty( $field_settings['label'] )
            ? $field_settings['label']
            : ucwords( str_replace( '_', ' ', $field_settings['template'] ) );
        $pro_url   = Pro_Prompt::get_pro_url();
        $message   = sprintf(
            /* translators: %1$s: field name, %2$s: opening link tag, %3$s: closing link tag */
            __( '%1$s is available in %2$sPro Version%3$s', 'wp-user-frontend' ),
            '<strong>' . esc_html( $field_name ) . '</strong>',
            '<a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener noreferrer">',
            '</a>'
        );

        echo '<li ';
        $this->print_list_attributes( $field_settings );
        echo '>';
        $this->print_label( $field_settings, $form_id );
        echo '<div class="wpuf-fields">';
        echo '<div class="wpuf-pro-field-notice wpuf-message">';
        echo wp_kses_post( $message );
        echo '</div>';
        echo '</div>';
        echo '</li>';
    }

    /**
     * Check if it's a pro feature
     *
     * @return bool
     */
    public function is_pro() {
        return true;
    }

    /**
     * Get field options setting
     *
     * @return array
     */
    public function get_options_settings() {
        return __return_empty_array();
    }

    /**
     * Get the field props
     *
     * @return array
     */
    public function get_field_props() {
        return __return_empty_array();
    }
}
