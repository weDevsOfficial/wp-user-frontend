<?php

namespace WeDevs\Wpuf\Ajax;

use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder;

/**
 * Ajax handlers
 */
class Admin_Form_Builder_Ajax {
    /**
     * Save form data
     *
     * @since 2.5
     *
     * @return void
     */
    public function save_form() {
        $post_data = wp_unslash( $_POST );
        if ( isset( $post_data['form_data'] ) ) {
            parse_str( $post_data['form_data'], $form_data );
        } else {
            wp_send_json_error( __( 'form data is missing', 'wp-user-frontend' ) );
        }

        if ( ! wp_verify_nonce( $form_data['wpuf_form_builder_nonce'], 'wpuf_form_builder_save_form' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( empty( $form_data['wpuf_form_id'] ) ) {
            wp_send_json_error( __( 'Invalid form id', 'wp-user-frontend' ) );
        }

        $form_fields   = isset( $post_data['form_fields'] ) ? $post_data['form_fields'] : '';
        $notifications = isset( $post_data['notifications'] ) ? $post_data['notifications'] : '';
        $settings      = [];
        $integrations  = [];

        if ( isset( $post_data['settings'] ) ) {
            $settings = (array) json_decode( $post_data['settings'] );
        } else {
            $settings = isset( $form_data['wpuf_settings'] ) ? $form_data['wpuf_settings'] : [];
        }

        if ( isset( $post_data['integrations'] ) ) {
            $integrations = (array) json_decode( $post_data['integrations'] );
        }

        $form_fields   = json_decode( $form_fields, true );
        $notifications = json_decode( $notifications, true );

        // Server-side validation for fallback PPP cost
        if ( $this->validate_fallback_ppp_cost_required( $settings ) ) {
            wp_send_json_error( __( 'Cost for each additional post after pack limit is reached is required when Pay-per-post billing when limit exceeds is enabled.', 'wp-user-frontend' ) );
        }

        $data = [
            'form_id'           => absint( $form_data['wpuf_form_id'] ),
            'post_title'        => sanitize_text_field( $form_data['post_title'] ),
            'form_fields'       => $form_fields,
            'form_settings'     => $settings,
            'form_settings_key' => isset( $form_data['form_settings_key'] ) ? $form_data['form_settings_key'] : '',
            'notifications'     => $notifications,
            'integrations'      => $integrations,
        ];

        $form_fields = Admin_Form_Builder::save_form( $data );

        wp_send_json_success(
            [
                'form_fields'   => $form_fields,
                'form_settings' => $settings,
			]
        );
    }

    public function wpuf_get_post_taxonomies_old() {
        $post_data = wp_unslash( $_POST );
        $post_type = $post_data['post_type'];
        $nonce     = $post_data['wpuf_form_builder_setting_nonce'];

        if ( isset( $nonce ) && ! wp_verify_nonce( $post_data['wpuf_form_builder_setting_nonce'], 'form-builder-setting-nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $post_type ) && empty( $post_data['post_type'] ) ) {
            wp_send_json_error( __( 'Invalid post type', 'wp-user-frontend' ) );
        }

        $post_taxonomies = get_object_taxonomies( $post_type, 'objects' );
        $cat = '';
        foreach ( $post_taxonomies as $tax ) {
            if ( $tax->hierarchical ) {
                $args = [
                    'hide_empty'   => false,
                    'hierarchical' => true,
                    'taxonomy'     => $tax->name,
                ];

                $cat .= '<tr class="wpuf_settings_taxonomy"> <th>' . __( 'Default ', 'wp-user-frontend' ) . $post_type . ' ' . $tax->name . '</th> <td>
                <select multiple name="wpuf_settings[default_' . $tax->name . '][]">';
                $categories = get_terms( $args );

                foreach ( $categories as $category ) {
                    $cat .= '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                }

                $cat .= '</select></td>';
            }
        }

        wp_send_json_success(
            [
                'success' => 'true',
                'data'    => $cat,
			]
        );
    }

    public function get_post_taxonomies() {
        $post_data = wp_unslash( $_POST );
        $post_type = $post_data['post_type'];
        $nonce     = $post_data['wpuf_form_builder_setting_nonce'];

        if ( isset( $nonce ) && ! wp_verify_nonce( $post_data['wpuf_form_builder_setting_nonce'], 'form-builder-setting-nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $post_type ) && empty( $post_data['post_type'] ) ) {
            wp_send_json_error( __( 'Invalid post type', 'wp-user-frontend' ) );
        }

        $post_taxonomies = get_object_taxonomies( $post_type, 'objects' );
        $cat = '';

        // Get current form settings to preserve existing values
        $form_id = isset( $post_data['form_id'] ) ? absint( $post_data['form_id'] ) : 0;
        $current_settings = [];

        if ( $form_id ) {
            $current_settings = get_post_meta( $form_id, 'wpuf_form_settings', true );
            if ( ! is_array( $current_settings ) ) {
                $current_settings = [];
            }
        }

        foreach ( $post_taxonomies as $tax ) {
            if ( $tax->hierarchical ) {
                $args = [
                    'hide_empty'   => false,
                    'hierarchical' => true,
                    'taxonomy'     => $tax->name,
                ];

                $field_name = 'default_' . $tax->name;
                $select_id = 'default_' . $tax->name . '_select';

                // Get current value for this taxonomy
                $current_value = isset( $current_settings[ $field_name ] ) ? $current_settings[ $field_name ] : [];
                $data_value = is_array( $current_value ) ? implode( ',', $current_value ) : $current_value;

                $cat .= '<div class="wpuf-mt-6 wpuf-input-container taxonomy-container" data-taxonomy="' . esc_attr( $tax->name ) . '">';
                $cat .= '<div class="wpuf-flex wpuf-items-center">';
                $cat .= '<label for="' . esc_attr( $select_id ) . '" class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">';
                $cat .= sprintf( __( 'Default %s %s', 'wp-user-frontend' ), $post_type, $tax->label );
                $cat .= '</label></div>';

                $cat .= '<select
                    multiple
                    id="' . esc_attr( $select_id ) . '"
                    name="wpuf_settings[' . esc_attr( $field_name ) . '][]"
                    data-value="' . esc_attr( $data_value ) . '"
                    data-taxonomy="' . esc_attr( $tax->name ) . '"
                    class="tax-list-selector wpuf-w-full wpuf-mt-2 wpuf-border-primary">';

                $categories = get_terms( $args );

                if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
                    foreach ( $categories as $category ) {
                        $selected = in_array( $category->term_id, (array) $current_value ) ? 'selected="selected"' : '';
                        $cat .= '<option value="' . esc_attr( $category->term_id ) . '" ' . $selected . '>' . esc_html( $category->name ) . '</option>';
                    }
                }

                $cat .= '</select></div>';
            }
        }

        wp_send_json_success(
            [
                'success' => 'true',
                'data'    => $cat,
			]
        );
    }

    public function get_roles() {
        $roles = wpuf_get_user_roles();

        $html = '<div class="wpuf-mt-6 wpuf-input-container"><div class="wpuf-flex wpuf-items-center"><label for="default_category" class="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">' . __( 'Choose who can submit post ', 'wp-user-frontend' ) . '</label></div>';
        $html .= '<select
                    multiple
                    id="roles"
                    data-roles="roles"
                    name="wpuf_settings[roles][]"
                    :class="setting_class_names(\'dropdown\')">';

        foreach ( $roles as $key => $role ) {
            $html .= '<option value="' . $key . '">' . $role . '</option>';
        }

        $html .= '</select>';

        wp_send_json_success(
            [
                'success' => 'true',
                'data'    => $html,
            ]
        );
    }

    /**
     * Validate if fallback PPP cost is required and empty
     *
     * @param array $settings Form settings
     * @return bool True if validation fails (cost is required but empty), false if validation passes
     */
    private function validate_fallback_ppp_cost_required( $settings ) {
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
}
