<?php

namespace WeDevs\Wpuf\Hooks;

use WeDevs\Wpuf\Platform\Stores\Stores;

use WeDevs\Wpuf\Platform\Stores\Normalizers;

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
            Stores::forms()->write_settings( $form_id, $cleaned_settings );
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

        $form_settings = Stores::forms()->read_meta( $post_id, 'wpuf_form_settings' );

        if ( empty( $form_settings ) ) {
            return;
        }

        $cleaned_settings = $this->remove_pro_notification_settings( $form_settings );

        // Update if settings were modified
        if ( $cleaned_settings !== $form_settings ) {
            Stores::forms()->write_settings( $post_id, $cleaned_settings );
        }
    }

    /**
     * Remove pro notification settings from form settings
     *
     * @param array $form_settings Form settings array
     * @return array Cleaned form settings
     */
    private function remove_pro_notification_settings( $form_settings ) {
        // One rule for the builder save and this cleanup (task 2.4a); callers
        // already return when Pro is active.
        return Normalizers::form_settings( $form_settings, false );
    }
}
