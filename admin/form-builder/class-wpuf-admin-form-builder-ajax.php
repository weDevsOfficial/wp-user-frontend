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
        add_action( 'wp_ajax_wpuf_form_builder_save_form', array( $this, 'save_form' ) );
    }

    /**
     * Save form data
     *
     * @since 2.5
     *
     * @return void
     */
    public function save_form() {
        parse_str( $_POST['form_data'], $form_data );

        if ( ! wp_verify_nonce( $form_data['wpuf_form_builder_nonce'], 'wpuf_form_builder_save_form' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( empty( $form_data['wpuf_form_id'] ) ) {
            wp_send_json_error( __( 'Invalid form id', 'wp-user-frontend' ) );
        }

        $form_fields   = isset( $_POST['form_fields'] ) ? $_POST['form_fields'] : '';
        $notifications = isset( $_POST['notifications'] ) ? $_POST['notifications'] : '';
        $settings      = array();
        $integrations  = array();

        if ( isset( $_POST['settings'] ) ) {
            $settings = (array) json_decode( wp_unslash( $_POST['settings'] ) );
        } else {
            $settings = isset( $form_data['wpuf_settings'] ) ? $form_data['wpuf_settings'] : array();
        }

        if ( isset( $_POST['integrations'] ) ) {
            $integrations = (array) json_decode( wp_unslash( $_POST['integrations'] ) );
        }

        $form_fields   = wp_unslash( $form_fields );
        $notifications = wp_unslash( $notifications );

        $form_fields   = json_decode( $form_fields, true );
        $notifications = json_decode( $notifications, true );

        $data = array(
            'form_id'           => absint( $form_data['wpuf_form_id'] ),
            'post_title'        => sanitize_text_field( $form_data['post_title'] ),
            'form_fields'       => $form_fields,
            'form_settings'     => $settings,
            'form_settings_key' => isset( $form_data['form_settings_key'] ) ? $form_data['form_settings_key'] : '',
            'notifications'     => $notifications,
            'integrations'      => $integrations
        );

        $form_fields = WPUF_Admin_Form_Builder::save_form( $data );

        wp_send_json_success( array( 'form_fields' => $form_fields, 'form_settings' => $settings ) );
    }

}

new WPUF_Admin_Form_Builder_Ajax();
