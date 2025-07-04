<?php

namespace WeDevs\Wpuf\Hooks;

/**
 * Form Settings Cleanup
 * 
 * Cleans up pro-only settings from form configurations in the free version
 * to prevent unintended activation of premium features.
 */
class Form_Settings_Cleanup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wpuf_form_builder_save_form', [ $this, 'cleanup_pro_settings' ], 10, 1 );
        add_action( 'save_post', [ $this, 'cleanup_form_meta_pro_settings' ], 10, 2 );
    }

    /**
     * Clean up pro settings after form save
     *
     * @param int $form_id Form ID
     */
    public function cleanup_pro_settings( $form_id ) {
        if ( wpuf_is_pro_active() ) {
            return;
        }

        $form_settings = wpuf_get_form_settings( $form_id );
        
        if ( empty( $form_settings ) ) {
            return;
        }

        $cleaned_settings = $this->remove_pro_notification_settings( $form_settings );
        
        // Update if settings were modified
        if ( $cleaned_settings !== $form_settings ) {
            update_post_meta( $form_id, 'wpuf_form_settings', $cleaned_settings );
        }
    }

    /**
     * Clean up pro settings from post meta
     *
     * @param int     $post_id Post ID
     * @param \WP_Post $post    Post object
     */
    public function cleanup_form_meta_pro_settings( $post_id, $post ) {
        if ( wpuf_is_pro_active() || $post->post_type !== 'wpuf_forms' ) {
            return;
        }

        $form_settings = get_post_meta( $post_id, 'wpuf_form_settings', true );
        
        if ( empty( $form_settings ) ) {
            return;
        }

        $cleaned_settings = $this->remove_pro_notification_settings( $form_settings );
        
        // Update if settings were modified
        if ( $cleaned_settings !== $form_settings ) {
            update_post_meta( $post_id, 'wpuf_form_settings', $cleaned_settings );
        }
    }

    /**
     * Remove pro notification settings from form settings
     *
     * @param array $form_settings Form settings array
     * @return array Cleaned form settings
     */
    private function remove_pro_notification_settings( $form_settings ) {
        if ( ! is_array( $form_settings ) ) {
            return $form_settings;
        }

        // List of pro-only notification settings to remove
        $pro_notification_keys = [
            'notification_edit',
            'notification_edit_to', 
            'notification_edit_subject',
            'notification_edit_body'
        ];

        foreach ( $pro_notification_keys as $key ) {
            if ( isset( $form_settings[ $key ] ) ) {
                unset( $form_settings[ $key ] );
            }
        }

        if ( isset( $form_settings['notification'] ) && is_array( $form_settings['notification'] ) ) {
            $notification_pro_keys = [ 'edit', 'edit_to', 'edit_subject', 'edit_body' ];
            
            foreach ( $notification_pro_keys as $key ) {
                if ( isset( $form_settings['notification'][ $key ] ) ) {
                    unset( $form_settings['notification'][ $key ] );
                }
            }
        }

        return $form_settings;
    }
}
