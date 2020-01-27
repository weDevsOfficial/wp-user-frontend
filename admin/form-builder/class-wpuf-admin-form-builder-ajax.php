<?php
/**
 * Ajax handlers
 */
class WPUF_Admin_Form_Builder_Ajax {

    /**
     * Class contructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_wpuf_form_builder_save_form', [ $this, 'save_form' ] );
    }

    /**
     * Save form data
     *
     * @since 2.5
     *
     * @return void
     */
    public function save_form() {
        $post_data =  wp_unslash($_POST);

        if ( isset( $post_data['form_data'] ) ) {
            parse_str( $post_data['form_data'],  $form_data );
        } else {
            wp_send_json_error( __( 'form data is missing', 'wp-user-frontend'));
        }

        if ( !wp_verify_nonce( $form_data['wpuf_form_builder_nonce'], 'wpuf_form_builder_save_form' ) ) {
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

        $data = [
            'form_id'           => absint( $form_data['wpuf_form_id'] ),
            'post_title'        => sanitize_text_field( $form_data['post_title'] ),
            'form_fields'       => $form_fields,
            'form_settings'     => $settings,
            'form_settings_key' => isset( $form_data['form_settings_key'] ) ? $form_data['form_settings_key'] : '',
            'notifications'     => $notifications,
            'integrations'      => $integrations,
        ];

        $form_fields = WPUF_Admin_Form_Builder::save_form( $data );

        wp_send_json_success( [ 'form_fields' => $form_fields, 'form_settings' => $settings ] );
    }
}

new WPUF_Admin_Form_Builder_Ajax();
